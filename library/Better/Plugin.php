<?php
/**
 * Better插件对象
 * 
 * 目前大量的后台计算逻辑都通过Hook来进行调用，随着需求不断增加进来，Hook调用链越来越长，
 * 而且也出现了同一个Hook被重复调用以及Hook子类之间互相调用的情况。而有些Hook在运算逻辑
 * 上是不依赖于其他Hook的，或者可以通过分成有限的批次完成（同一批次的Hook彼此不互相依赖，
 * 后一批次Hook执行的参数依赖于前一批次Hook执行的结果），这样如果同一批次的Hook可以并发
 * 进行，将大大缩短请求的处理时间。
 * 
 * @author sunhy 
 * @package Better
 */
class Better_Plugin
{
	const MO_SYNC = 1;
	const MO_ASYNC = 2;
	
	public static $context = array();
	protected static $instance = NULL;
	protected static $server = '';      // PS地址
	protected static $to_sync = 3000;   // 同步请求的超时时间
	protected static $to_async = 30000; // 异步请求的超时时间
	protected static $rolling = 60;     // curl_multi最大请求数
	protected static $CALL_DRET = array(
		'VALID' => 0,
		'HTTP_CODE' => 204,
		'RET' => array(),
	);
	protected static $RUN_DRET = array(
		'VALID' => 0,
		'ECODE' => 0,
		'ETEXT' => 'UNDEF',
		'VALUE' => '',
		'DETAILS' => array(),
	);
	
	private function __construct()
	{
		$kai = Better_Config::getAppConfig();
		self::$server = isset($kai->curl->server) ? $kai->curl->server : 
			(isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/plugin.php';
		self::$to_sync = isset($kai->curl->sync->timeout) ? $kai->curl->sync->timeout : 3000;
		self::$to_async = isset($kai->curl->async->timeout) ? $kai->curl->async->timeout : 30000;
		self::$rolling = isset($kai->curl->rolling) ? $kai->curl->rolling : 100;
	}

	private function __clone()
	{
	}

	public static function instance()
	{
		if (self::$instance == NULL) self::$instance = new self();
		return self::$instance;
	}

	/**
	 * 同步/异步运行一组插件, 插件组的调用顺序及上下文由caller负责
	 * 1. $name可以是一个/一组插件的名称, 如: 'badge'或array('badge#1', 'badge#2', 'karma', 'rp')
	 *    同名组件使用'类名#序号'加以区分不同实例
	 * 2. $params约定: '_xxx'为预处理参数, 'xxx'为执行参数, '#xxx'为后处理参数, 针对第一层元素
	 * 3. 返回结果: array(
	 *    'badge' => array(
	 *       'VALID' => 0/1, 失败/成功
	 *       'ECODE' => 错误代码
	 *       'ETEXT' => 错误内容
	 *       'VALUE' => 插件返回的结果, 如: '获得三人行勋章'
	 *       'DETAILS' => array(...)
	 *    ),
	 *    'karma' => array(...)
	 *    ...
	 * )
	 * 结果中的DETAILS一般保留sync_call的返回结果(已反序列化为数组), caller可以使用DETAILS的内容自行格式化结果。 
	 * 
	 */
	public static function &run($name, $mode = Better_Plugin::MO_SYNC, array $params = array())
	{
		if (self::$instance == NULL) self::$instance = new self();
		if (is_array($name)) $_names = &$name;
		else $_names = array($name);
		$_names = array_unique($_names);
		$pis = array();
		$calls = array();
		foreach ($_names as $pn) {
			if (!isset($pis[$pn])) {
				$pos = strpos($pn, '#');
				$cn = ($pos === false) ? $pn : substr($pn, 0, $pos);
				$pnf = 'Better_Plugin_'.ucfirst($cn);
				// 不存在或非Better_Plugin_Base子类的忽略
				if (!class_exists($pnf)) continue;
				$pi = new $pnf();
				if (!is_subclass_of($pi, 'Better_Plugin_Base')) continue;
				$pis[$pn] = $pi;
				$reqs = $pi->pre_proc($pn, $params);
				$calls[$pn] = isset($reqs) ? $reqs : array();
			}
		}
		$rets = array();
		if ($mode == self::MO_SYNC) {
			$rets = self::sync_call($pis, $calls, $params);
			foreach ($pis as $pn => $pi) {
				if (isset($rets[$pn])) {
					$rr = array(
						'VALID' => 1,
						'ECODE' => 0,
						'ETEXT' => '',
						'VALUE' => '',
						'DETAILS' => $rets[$pn],
					);
					$pi->post_proc($pn, $params, $rr);
					$rets[$pn] = $rr;
				}
			}
		} else if ($mode == self::MO_ASYNC) {
			self::async_call($pis, $calls, $params);
		}
		// echo str_replace("\n", '<br>', print_r($rets, true)); exit;
		return $rets;
	}

	/**
	 * 序列化$params中形如'xxx'的参数
	 * 如: $params = array(
	 *    '_pre_a' => 'one',
	 *    'b' => 'two',
	 *    '#post_c' => array(
	 *       'c1' => 7.89,
	 *       '#c2' => 'world',
	 *       'c3' => '2011-6-9',
	 *    ),
	 *    'd' => array(
	 *       'd1' => 1.23,
	 *       '#d2' => 'hello',
	 *       'd3' => '2011-6-6',
	 *    ),
	 *    'e' => '?a=1&b=2&c=\'hello\'',
	 *    'f' => '{"z":1,"y":2}',
	 * )
	 * 返回: array(
	 *    'FIELDS' => array(
	 *       'b' => 'two',
	 *       'd' => array(
	 *          'd1' => 1.23,
	 *          '#d2' => 'hello',
	 *          'd3' => '2011-6-6',
	 *       ),
	 *       'e' => '?a=1&b=2&c=\'hello\'',
	 *       'f' => '{"z":1,"y":2}',
	 *    ),
	 *    'JSON' => 'FIELDS={"b":"two","d":{"d1":1.23,"#d2":"hello","d3":"2011-6-6"},"e":"?a=1&b=2&c='hello'","f":"{\"z\":1,\"y\":2}"}'
	 * )
	 * PS端需要反序列化!!!
	 * @param array $params
	 * @param boolean $format: 是否进行JSON序列化
	 */
	protected static function &get_fields0(array &$params, $format = 1)
	{
		$fields = array();
		foreach ($params as $k => $v)
		{
			$f = substr($k, 0, 1);
			if ($f == '_' || $f == '#') continue;
			if (is_array($v)) {
				$vv = self::get_fields($v, 0);
				$fields[$k] = $vv['FIELDS'];
			} else {
				$fields[$k] = $v;
			}
		}
		$json = $format ? 'FIELDS=' . json_encode($fields) : '';
		return array(
			'FIELDS' => &$fields,
			'JSON' => $json,
		);
	}

	protected static function &get_fields(array &$params)
	{
		$fields = array();
		foreach ($params as $k => $v)
		{
			$f = substr($k, 0, 1);
			if ($f == '_' || $f == '#') continue;
			$fields[$k] = $v;
		}
		$json = 'FIELDS=' . json_encode($fields);
		return array(
			'FIELDS' => &$fields,
			'JSON' => $json,
		);
	}

	/**
	 * 过滤数组中_/#开头的元素
	 * @param array $params
	 */
	protected static function &filter_fields(array &$params)
	{
		$fields = array();
		foreach ($params as $k => $v)
		{
			$f = substr($k, 0, 1);
			if ($f == '_' || $f == '#') continue;
			$fields[$k] = $v;
		}
		return $fields;
	}

	protected static function &sync_call(array &$pis, array &$calls, array &$params)
	{
		return self::sync_call2($pis, $calls, $params);
	}

	protected static function &sync_call1(array &$pis, array &$calls, array &$params)
	{
		$mh = curl_multi_init();
	    $chss = array();
	    $rets = array();
	    $flds = self::filter_fields($params);
	    foreach ($calls as $pn => $reqs)
	    {
	    	$pi = &$pis[$pn];
	    	$chs = array();
	    	$ret = array();
	    	foreach ($reqs as $id => $req)
	    	{
	    		$ch = curl_init();
	    		if (isset($pi)) $pi->pre_call($ch, $req, $params); // 每个插件可以设置各自的curl参数
	    		$post_fields['PLUGIN'] = $pn;
	    		$post_fields['PARAMS'] = &$flds;
	    		$post_fields['EXTRA'] = &$req;
	    		$json = 'FIELDS=' . json_encode($post_fields);
	    		// 方便profiler区分
	    		curl_setopt($ch, CURLOPT_URL, self::$server . '?id=' . $id);
	    		// curl_setopt($ch, CURLOPT_URL, self::$server);
	    		curl_setopt($ch, CURLOPT_HEADER, 0);
	    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	    		curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::$to_sync);
	    		curl_multi_add_handle($mh, $ch);
	    		$chs[$id] = $ch;
	    		$ret[$id] = self::$CALL_DRET;
	    	}
	    	$chss[$pn] = &$chs;
	    	$rets[$pn] = &$ret;
	    }

	    $running = null;
	    do {
	    	$mrc = curl_multi_exec($mh, $running);
	    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

	    while ($running && $mrc == CURLM_OK) {
	    	if (curl_multi_select($mh) != -1) {
	    		do {
	    			$mrc = curl_multi_exec($mh, $running);
	    		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	    	}
	    }

	    if ($mrc != CURLM_OK) return $rets;

	    foreach ($calls as $pn => $reqs)
	    {
	    	$pi = &$pis[$pn];
	    	$chs = &$chss[$pn];
	    	$ret = &$rets[$pn];
	    	foreach ($reqs as $id => $req)
	    	{
	    		$ch = &$chs[$id];
	    		$ce = curl_errno($ch);
	    		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    		$ret[$id]['HTTP_CODE'] = $code;
	    		if ($ce == '' && $code == 200) {
	    			$ret[$id]['VALID'] = 1;
	    			$out = curl_multi_getcontent($ch);
	    			$ret[$id]['RET'] = json_decode($out, true);
	    			// $ret[$id]['RET'] = $out;
	    		}
	    		if (isset($pi)) $pi->post_call($ch, $params, $ret[$id]); // 每个插件可以读取各自的curl返回
	    		curl_close($ch);
	    		curl_multi_remove_handle($mh, $ch);
	    	}
	    	$chs = null;
	    }
	    curl_multi_close($mh);
	    $chss = null;
	    // echo str_replace("\n", '<br>', print_r($rets, true)); exit;
	    return $rets;
	}

	protected static function &sync_call2(array &$pis, array &$calls, array &$params)
	{
		$mh = curl_multi_init();
	    $all = array();
		$rets = array();
	    $flds = self::filter_fields($params);
	    foreach ($calls as $pn => $reqs)
	    {
	    	$pi = &$pis[$pn];
	    	$ret = array();
	    	foreach ($reqs as $id => $req)
	    	{
	    		$ret[$id] = self::$CALL_DRET;
	    		$all[$id] = array(
	    			'id' => $id,
	    			'pn' => $pn,
	    			'pi' => $pi, 
	    			'extra' => $req,
	    		);
	    	}
	    	$rets[$pn] = &$ret;
	    }

	    reset($all);
	    $i = 0;
    	do {
    		$req = current($all);
    		if ($req === false) break;
        	$id = $req['id'];
        	$pn = $req['pn'];
        	$pi = $req['pi'];
        	$extra = $req['extra'];
        	$ch = curl_init();
        	if (isset($pi)) $pi->pre_call($ch, $extra, $params); // 每个插件可以设置各自的curl参数
    		$pfs['PLUGIN'] = $pn;
    		$pfs['PARAMS'] = &$flds;
    		$pfs['EXTRA'] = &$extra;
    		$json = 'FIELDS=' . json_encode($pfs);
    		// +id生成唯一URL，方便对应
    		curl_setopt($ch, CURLOPT_URL, self::$server . '?id=' . $id);
    		curl_setopt($ch, CURLOPT_HEADER, 0);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    		curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::$to_sync);
    		curl_multi_add_handle($mh, $ch);
    		next($all);
    		$i++;
    	} while ($i <= self::$rolling || self::$rolling == 0);

	    $running = null;
		do {
	        while (($mrc = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
	        if ($mrc != CURLM_OK) break;

	        while ($done = curl_multi_info_read($mh)) {
	        	$ch = $done['handle'];
	        	$info = curl_getinfo($ch);
	        	$url = $info['url'];
	        	$code = $info['http_code'];
	           	$ce = curl_errno($ch);
	        	$pos = strpos($url, '?id=');
	        	$id = substr($url, $pos+4);
	        	$req = &$all[$id];
	        	$pn = $req['pn'];
	        	$pi = $req['pi'];
	           	$ret = &$rets[$pn];
	    		$ret[$id]['HTTP_CODE'] = $code;
	           	if ($ce == '' && $code == 200) {
	    			$out = curl_multi_getcontent($ch);
	    			$ret[$id]['VALID'] = 1;
	    			$ret[$id]['RET'] = json_decode($out, true);
	    			// $ret[$id]['RET'] = $out;
	    		}
	    		if (isset($pi)) $pi->post_call($ch, $params, $ret[$id]); // 每个插件可以读取各自的curl返回
	    		curl_close($ch);
	    		curl_multi_remove_handle($mh, $ch);

	    		$req = current($all);
	    		if ($req === false) continue;
	    		$id = $req['id'];
	        	$pn = $req['pn'];
	        	$pi = $req['pi'];
	        	$extra = $req['extra'];
	    		$ch = curl_init();
	        	if (isset($pi)) $pi->pre_call($ch, $extra, $params); // 每个插件可以设置各自的curl参数
	    		$pfs['PLUGIN'] = $pn;
	    		$pfs['PARAMS'] = &$flds;
	    		$pfs['EXTRA'] = &$extra;
	    		$json = 'FIELDS=' . json_encode($pfs);
	    		// 唯一URL，方便对应
	    		curl_setopt($ch, CURLOPT_URL, self::$server . '?id=' . $id);
	    		curl_setopt($ch, CURLOPT_HEADER, 0);
	    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	    		curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::$to_sync);
	    		curl_multi_add_handle($mh, $ch);
	    		next($all);
	        }
	    } while ($running);
    
	    curl_multi_close($mh);
	    // echo str_replace("\n", '<br>', print_r($rets, true)); exit;
	    return $rets;
	}

	/**
	 * 异步(离线)调用
	 * @param $pis
	 * @param $calls
	 * @param $params
	 */
	protected static function async_call(array &$pis, array &$calls, array &$params)
	{
	}


	protected static function error()
	{
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		exit;
	}

	/**
	 * PS端服务接口
	 * 
	 * @param $plugin: 插件名, 如: Badge, Karma, Rp
	 * @param $params: caller传入的参数
	 * @param $extra: pre_proc返回的$req
	 */
	public static function &service($plugin, array &$params, array &$extra)
	{
		if (self::$instance == NULL) self::$instance = new self();
		if (is_null($plugin)) self::error();
		$pnf = 'Better_Plugin_'.ucfirst($plugin);
		// 不存在或非Better_Plugin_Base子类的忽略
		if (!class_exists($pnf)) self::error();
		$pi = new $pnf();
		if (!is_subclass_of($pi, 'Better_Plugin_Base')) self::error();
		$ret = $pi->service($params, $extra);
		if (is_null($ret)) self::error();
		if (!is_array($ret)) $ret = array('value' => $ret);
		echo json_encode($ret);
	}

}