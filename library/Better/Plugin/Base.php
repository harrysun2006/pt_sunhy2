<?php

/**
 * 插件抽象类
 * 
 * @author sunhy <sunhy@peptalk.cn>
 * @package Better.Plugin
 */
class Better_Plugin_Base
{

	protected static $context;
	private static $id = 0;

	public function __get($key)
	{
		return isset(Better_Plugin::$context[$key]) ? Better_Plugin::$context[$key] : null;
	}
	
	public function __set($key, $val)
	{
		Better_Plugin::$context[$key] = $val;
	}

	public function __construct()
	{
		// 可以使用魔术方法__get/__set或直接使用parent::$context访问上下文
		self::$context = &Better_Plugin::$context;
		self::$id = 0;
	}

	protected static function next_id()
	{
		return ++self::$id;
	}

	/**
	 * 预处理，根据参数及上下文返回需要PS处理的请求数组
	 * 数组中的每个元素将生成一次请求，请求的url固定配置，提交的内容为任意数组对象EXTRA
	 * 如：
	 * array(
	 *    1 => array(
	 *       'id' => '76',
	 *       'class' => 'Guanqianjie',
	 *       'name' => '观前街',
	 *    ),
	 *    2 => array(...),
	 *    ...
	 *    11 => array(...),
	 * )
	 * 1,2,...,11为请求号，可以使用next_id()方法生成，只要确保唯一即可。
	 * Plugin方法中使用请求号管理Host<==>PS之间的请求响应，大体流程为:
	 *   1. Plugin将$params和EXTRA序列化后提交到PS:
	 *   {"FIELDS":{"PLUGIN":...,"PARAMS":...,"EXTRA":...}}
	 *   2. PS反序列化FIELDS域，得到PLUGIN，PARAMS和EXTRA
	 *   $fields = json_decode($_REQUEST['FIELDS'], true)
	 *   $extra = $fields['EXTRA'];
	 *   其中:
	 *   PLUGIN为插件名，如: badge#1，badge#2，karma，...
	 *   PARAMS为各请求响应公共的$params参数，如: ('uid'=>..., 'poi_id'=>..., 'blog'=>array(...))
	 *   EXTRA为各请求响应私有的参数，可以存放业务对象，如Better_Badge，便于service处理
	 *   3. PS处理完后将结果序列化后输出:
	 *   {"id":76,"class":"Guanqianjie","name":"观前街","touch",0}
	 *   4. Plugin得到响应后将内容反序列化为数组
	 *   5. 同步请求返回结果
	 *   6. Plugin为每个插件实例执行后处理
	 * 
	 * @param string $pn: 插件名称
	 * @param array $params: 参数
	 */
	public static function &pre_proc($pn, array &$params)
	{
		return null;
	}

	/**
	 * 后处理, 根据参数及上下文对结果进一步处理
	 * 
	 * @param string $pn: 插件名称
	 * @param array $params: 参数
	 * @param array $ret: 返回值
	 */
	public static function post_proc($pn, array &$params, array &$rr)
	{
	}

	public static function pre_call(&$ch, array &$extra, array &$params)
	{
	}
	
	public static function post_call(&$ch, array &$params, array &$ret)
	{
	}

	/**
	 * PS端服务接口
	 * 
	 */
	public static function &service(array &$params, array &$extra)
	{
		return null;
	}
}