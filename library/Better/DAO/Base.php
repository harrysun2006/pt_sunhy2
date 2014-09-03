<?php

/**
 * DAO基类
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Base extends Zend_Db_Table
{
	
	/**
	 * 缓存标识符
	 *
	 * @var unknown_type
	 */
	protected static $cacheIdentifier = '';
	
	/**
	 * 已经注册过的数据库连接，避免重复打开数据库
	 *
	 * @var array
	 */
	protected static $registedDbConnection = array();
	
	/**
	 * 注册过的数据库server
	 *
	 * @var array
	 */
	protected static $registedDbServer = array(
																	'user' => array(),
																	'attach' => array(),
																	'global' => array(),
																	);
	
	protected $_primary = '';
	protected $_name = '';
	
	/**
	 * 数据分片标识符
	 * 对于用户类对象来说，就是用户的id，对于附件来说，就是附件的id
	 *
	 * @var misc
	 */
	protected $identifier = null;
	
	/**
	 * Zend_Db实例（读）
	 *
	 * @var Zend_Db
	 */
	protected $rdb = null;
	
	/**
	 * Zend_Db实例（写）
	 *
	 * @var Zend_Db
	 */
	protected  $wdb = null;
	
	/**
	 * 主表名
	 *
	 * @var string
	 */
	protected $tbl = '';
	
	/**
	 * 表主键名
	 *
	 * @var string
	 */
	protected $priKey = '';
	
	/**
	 * 默认的排序键
	 * 
	 * @var string
	 */
	protected $orderKey = '';
	
	protected static $_queries = 0;
	public static $queryTime = 0;
	
	protected static $defaultXY = "GeomFromText('POINT(0 0)')";
	
	/**
	 * 
	 * 数据库连接参数生成的唯一hash字符，当多个数据库连接配置一样时，可以复用连接
	 * @var unknown_type
	 */
	protected $dbHash = '';
	
	public function __construct($identifier=null)
	{
		$this->identifier = $identifier;
		$this->_primary = &$this->priKey;
		$this->_name = &$this->tbl;
	}
	
	/**
	 * 魔术方法，建立一些简便的数据操作方式
	 *
	 * @param $method
	 * @param $params
	 * @return unknown_type
	 */
	function __call($method, $params)
	{

		if (preg_match('/^findAll([a-zA-Z0-9_]+)$/is', $method)) {
			
			$key = preg_replace('/^findAll([a-zA-Z0-9_]+)/is', '\1', $method);
			return $this->getAll(array(
							strtolower($key) => $params[0],
							), $params[1], $params[2]);
			
		} elseif (preg_match('/^find([a-zA-Z0-9_]+)$/is', $method)) {

			$key = preg_replace('/^find([a-zA-Z0-9_]+)$/is', '\1', $method);
			return $this->get(array(
							strtolower($key) => $params[0],
							));
							
		} elseif (preg_match('/^delete([a-zA-Z0-9_]+)$/is', $method)) {
			
			$key = preg_replace('/^delete([a-zA-Z0-9_]+)$/is', '\1', $method);
			return $this->delete($params[0], strtolower($key));
			
		} elseif (preg_match('/^update([a-zA-Z0-9_]+)$/is', $method)) {
			$key = preg_replace('/^update([a-zA-Z0-9_]+)$/is', '\1', $method);
			return $this->updateByCond(array(
							strtolower($key) => $params[0],
							), isset($params[1]) ? $params[1] : '');
		}
	}
	
	/**
	 * 设置缓存标识
	 *
	 * @param $id
	 * @return null
	 */
	public static function setCacheIdentifier($id)
	{
		self::$cacheIdentifier = $id;
	}
	
	/**

	 */
	public function find($val)
	{
		return $this->get($val);
	}
	
	/**
	 * 获取读数据库链接
	 * 
	 * @return
	 */
	public function getRdb()
	{
		return $this->rdb;
	}
	
	/**
	 * 获取写数据库链接
	 * 
	 * @return 
	 */
	public function getWdb()
	{
		return $this->wdb;
	}
	
	/**
	 * 获取表名
	 * 
	 * @return stirng
	 */
	public function getTable()
	{
		return $this->tbl;
	}
	
	/**
	 * 设置数据库对象
	 *
	 * @param $db
	 * @return unknown_type
	 */
	public function setDb($db)
	{
		$this->rdb = &$db;
		$this->wdb = &$db;
		$this->db = &$db;
	}
	
	/**
	 * 生成一个唯一的数据库链接标识hash
	 * 
	 * @param $config
	 * @return string
	 */
	protected static function dbConfigHash(&$config)
	{
		$hash = md5($config['host'].'|'.$config['dbname'].'|'.$config['username'].'|'.$config['password'].'|'.$config['adapter'].'|'.$config['charset'].'|'.$config['port']);
		
		return $hash;
	}
	
	/**
	 * 
	 * 关闭数据库连接
	 * @param unknown_type $dbId
	 */
	public static function closeDbConnection($dbId)
	{
		try {
			$hash = self::dbConfigHash($config);
			self::$registedDbConnection[$hash]->closeConnection();
			unset(self::$registedDbConnection[$hash]);
		} catch (Exception $e) {
			
		}
	}
	
	public function reconnect()
	{
		$this->rdb->closeConnection();
		$this->wdb->closeConnection();
	}
	
	/**
	 * 
	 * 重新连接数据库
	 * 常驻后台的php进程有可能长时间不运作导致mysql连接失效
	 * 
	 * @param unknown_type $serverId
	 */
	public static function refreshDbConnection($serverId)
	{
		$cs = self::assignDbConnection($serverId);
		
		try {
			$cs['r']->closeConnection();
			$cs['w']->closeConnection();
		} catch (Exception $e) {
			
		}
		
		return self::assignDbConnection($serverId, true);
	}

	/**
	 * 注册一个数据库连接
	 *
	 * @param $dbId
	 * @return Zend_Db
	 */
	public static function &registerDbConnection($dbId, $new=false)
	{
		$dbConfig = Better_Config::getDbConfig();
		$db = null;
		
		try {
			$config = $dbConfig->$dbId->toArray();
			$hash = self::dbConfigHash($config);
			$config['options'] = array('autoReconnectOnUnserialize' => 1);
			if (!isset(self::$registedDbConnection[$hash]) || $new===true) {
				self::$registedDbConnection[$hash] = Zend_Db::factory($config['adapter'], $config);
				$config['charset'] && self::$registedDbConnection[$hash]->query('SET NAMES '.$config['charset']);
			}
			
			$db = & self::$registedDbConnection[$hash];
			
		} catch (Exception $e) {
			Better_Log::getInstance()->logInfo('Could not connect to database, dbId: ['.$dbId.'], config: ['.serialize($config).']'."\n".$e->getTraceAsString(), 'exception', true);
		}
		
		return $db;
	}
	
	
	/**
	 * 分配Db连接资源
	 *
	 * @return array
	 */
	public static function assignDbConnection($serverId, $new=false)
	{
		$rdbKey = $serverId.'_read';
		$wdbKey = $serverId.'_write';
		$dbconfig = Better_Config::getDbConfig();
       
		$wconfig = $dbconfig->$wdbKey->toArray();

		$rdbs = intval($wconfig['read_dbs']);
		if ($rdbs>0) {
			$rdbKey .= '_'.rand(0,$rdbs-1);
		}

		$rconfig = $dbconfig->$rdbKey->toArray();
		
		$rHash = self::dbConfigHash($rconfig);
		$wHash = self::dbConfigHash($wconfig);

		if ($rHash==$wHash) {
			$wdb = self::registerDbConnection($rdbKey, $new);
			$rdb = &$wdb;
		} else {
			$rdb = self::registerDbConnection($rdbKey, $new);
			$wdb = self::registerDbConnection($wdbKey, $new);
		}

		return array(
					'r' => &$rdb,
					'w' => &$wdb,
					);
	}

	/**
	 * 分配用户Db数据库
	 *
	 * @param $action 读或写
	 * @return string
	 */
	protected  function assignUserDb($new=false)
	{
		$row = array();

		$nextSid = Better_Config::getDbConfig()->global->user_servers;
		$this->serverId = rand(1, $nextSid);
		
		if ($this->identifier) {
			if (!isset(self::$registedDbServer['user'][$this->identifier])) {
				$tbl = Better_DAO_User_Assign::getInstance($new);
				$row = $tbl->fetchRow('uid='.$this->identifier);
				
				if ($row['sid']) {
					self::$registedDbServer['user'][$this->identifier] = $this->serverId = $row['sid'];
				}
			} else {
				$this->serverId = self::$registedDbServer['user'][$this->identifier];
			}
		}

		return $this->serverId;
					
	}
		
	/**
	 * 分配用户Db连接资源
	 * 
	 * @return null
	 */
	protected function assignUserDbConnection($new=false)
	{
		$dbKey = 'user_server_'.$this->assignUserDb($new);
		$cs = self::assignDbConnection($dbKey, $new);
		$this->rdb = $cs['r'];
		$this->wdb = $cs['w'];
		
		!$this->rdb->isConnected() && $this->rdb->getConnection();
		!$this->wdb->isConnected() && $this->wdb->getConnection();
	}
	
	/**
	 * 取sql查询次数
	 * 
	 * @return integer
	 */
	public static function getQueries()
	{
		return self::$_queries;
	}
	
	/**
	 * 
	 * 执行一次数据库查询
	 * 使用该静态方法来查询数据库，程序中可以记录查询执行的时间、程序中总共执行了多少次sql等调试信息
	 * 
	 * @param unknown_type $sql
	 * @param unknown_type $db
	 * @param unknown_type $params
	 */
	public static function squery($sql, $db=null, $params=array())
	{
		!defined('BETTER_LONG_DB_QUERY') && define('BETTER_LONG_DB_QUERY', Better_Config::getDbConfig()->global->long_query_time);
		
		if ($sql instanceof Zend_Db_Select) {
			$sql = $sql->__toString();
		}
		
		if (BETTER_DB_DEBUG) {
			Better_Timer::start('sql_'.md5($sql));
		}

		!$db->isConnected() && $db->getConnection();
		
		if (count($params)>0) {
			$rs = $db->query($sql, $params);
		} else {
			$rs = $db->query($sql);
		}
		
		if (defined('LOG_PAGE_QUERIES') && LOG_PAGE_QUERIES===true) {
			$_queryTime = Better_Timer::end('sql_'.md5($sql));
			self::$queryTime += $_queryTime;
			
			if (is_array($sql)) {
				$log = 'Keys : ('.implode(',', array_keys($sql)).') , Values : ('.implode(',', $sql).')';
			} else {
				$log = $sql;
			}
			Better_Log::getInstance()->logAlert('['.(self::$_queries+1).'] SQL: (Query time : '.$_queryTime.') ['.$log.']', 'page_queries');

		} else if (BETTER_DB_DEBUG) {
			
			$_queryTime = Better_Timer::end('sql_'.md5($sql));
			self::$queryTime += $_queryTime;
		}
		
		self::$_queries++;
		
		return $rs;		
	}
	
	/**
	 * 运行一条sql
	 * 
	 * @param $sql
	 * @param $db
	 * @return unknown_type
	 */
	public function query($sql, $db=null, $params=array())
	{
		$db==null && $db = &$this->rdb;
		return self::squery($sql, $db, $params);
	}
	
	/**
	 * 根据表主键查询一行数据
	 *
	 * @param $val
	 * @return array
	 */
	public function get($val)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$this->parseWhere($select, $val);
		$select->limit(1);

		$result = self::squery($select, $this->rdb);

		return $result->fetch();
	}
	
	/**
	 * 取结果行数
	 * 
	 * @param $val
	 * @return integer
	 */
	public function getCount($val=null)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			'COUNT(*) AS count',
			));
		$this->parseWhere($select, $val);
		
		$result = self::squery($select, $this->rdb);
		$data = $result->fetch();
		
		return isset($data['count']) ? $data['count'] : 0;
	}
	
	/**
	 * 根据查询条件获取一个结果集
	 *
	 * @param $val
	 * @param $limit
	 * @param $method
	 * @return array
	 */
	public function getAll($val=null, $limit='0', $method='limit')
	{
		$method = $method=='limit' ? 'limit' : 'limitPage';

		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$this->parseWhere($select, $val);
		if (is_array($val) && isset($val['order'])) {
			$this->parseOrder($select,$val['order']);
		} else {
			$select->order($this->orderKey.' DESC');
		}
		$limit!=null && self::$method($select, $limit);

		$result = self::squery($select, $this->rdb);
		$data = $result->fetchAll();

		return is_array($data) ? $data : array();
	}
	
	/**
	 * 执行Replace的SQL操作
	 * 
	 * @param $data
	 * @param $tbl
	 * @return unknown_type
	 */
	public function replace($data, $tbl='')
	{
		$tbl =='' && $tbl = $this->tbl;
		
		$keys = '`'.implode('`,`', array_keys($data)).'`';
		$values = ':'.implode(',:', array_keys($data));
		
		$sql = "REPLACE INTO `{$tbl}` ({$keys})
		VALUES ({$values})";
		
		try {
			$result = $this->query($sql, $this->wdb, $data);
		} catch(Exception $e) {
			Better_Log::getInstance()->logEmerg('SQL_REPLACE_ERROR:['.$e->getMessage().']'.' Message:['.$e->getTraceAsString().']', 'db_error');
		}

		return $this->wdb->lastInsertId() ? $this->wdb->lastInsertId() : $result;
	}
	
	/**
	 * 执行带坐标字段的表的Replace
	 * 
	 * @param $data
	 * @param $tbl
	 * @return unknown_type
	 */
	protected function _replaceXY($data, $tbl='')
	{
		$tbl=='' && $tbl = $this->tbl;

		if (isset($data['xy'])) {
			$keys = '`'.implode('`,`', array_keys($data)).'`';
			$geo = $data['xy'];
			unset($data['xy']);
			$values = ':'.implode(',:', array_keys($data)).','.$geo;
			
		} else if (isset($data['x']) && isset($data['y'])) {
			$geo = "GeomFromText('POINT(".$data['x']." ".$data['y'].")')";
			unset($data['x']);
			unset($data['y']);
			
			$keys = '`'.implode('`,`', array_keys($data)).'`,`xy`';
			$values = ':'.implode(',:', array_keys($data)).','.$geo;
		} else {
			$geo = "GeomFromText('POINT(".Better_Config::getAppConfig()->location->default_x." ".Better_Config::getAppConfig()->location->default_y."))";
			$keys = '`'.implode('`,`', array_keys($data)).'`,`xy`';
			$values = ':'.implode(',:', array_keys($data)).','.$geo;
		
		}

		$sql = "REPLACE INTO `{$tbl}` ({$keys})
		VALUES ({$values})";

		try {
			$result = $this->query($sql, $this->wdb, $data);
		} catch(Exception $e) {
			Better_Log::getInstance()->logEmerg('SQL_REPLACE_ERROR:['.$e->getMessage().']'.' Message:['.$e->getTraceAsString().']', 'database');
			throw new Better_Exception("SQL : ".$sql."<hr />".$e->getMessage());
		}

		return $this->wdb->lastInsertId() ? $this->wdb->lastInsertId() : 1;
	}
	
	/**
	 * 插入新数据
	 *
	 * @param $data
	 * @return integer
	 */
	public function insert($data)
	{
		$rows_affected = $this->wdb->insert($this->tbl, $data);
		return $this->wdb->lastInsertId() ? $this->wdb->lastInsertId() : $rows_affected;
	}

	/**
	 *	操作带有xy的表
	 *
	 * @param array $data
	 * @return integer
	 */
	protected function _insertXY($data, $tbl='')
	{
		$tbl=='' && $tbl = $this->tbl;

		if (isset($data['x']) && isset($data['y'])) {
			$data['xy'] = new Zend_Db_Expr("GeomFromText('POINT(".$data['x']." ".$data['y'].")')");
			unset($data['x']);
			unset($data['y']);
		} else {
			$data['xy'] = new Zend_Db_Expr("GeomFromText('POINT(".Better_Config::getAppConfig()->location->default_x." ".Better_Config::getAppConfig()->location->default_y.")')");
		}

		$inserted = false;
		
		try {
			$result = $this->wdb->insert($tbl, $data);
			$inserted = true;
		} catch(Exception $e) {
			Better_Log::getInstance()->logEmerg('SQL_INSERT_ERROR:[Table: ('.$tbl.')'.$e->getMessage().']'.' Message:['.$e->getTraceAsString().']', 'database');
		}

		return $inserted==true ? ($this->wdb->lastInsertId() ? $this->wdb->lastInsertId() : 1) : 0;
	}

	
	/**
	 * 更新表中某字段（加或减一个数字，避免直接读取带来的时间差导致某字段不一致）
	 *
	 * @param $key
	 * @param $where
	 * @param $step
	 * @return unknown_type
	 */
	public function increase($key, $where='', $step=1)
	{
		$id = $step>0 ? '+' : '-';
		$where=='' && $where = ' 1';
		if (is_array($where)) {
			$tmp = '1 ';
			foreach($where as $k=>$v) {
				$tmp .= ' AND '.$this->wdb->quoteInto($k.'=?', $v);
			}
			$where = $tmp;
		} else if ($where=='') {
			$where = ' 1';
		}

		$sql = 'UPDATE `'.$this->tbl.'` SET `'.$key.'`=`'.$key.'`'.$id.abs($step).' WHERE '.$where;
		
		return self::squery($sql, $this->wdb);

	}

	/**
	 * 根据条件执行更新操作（传入简单的where条件）
	 *
	 * @param $data
	 * @param $where
	 * @return unknown_type
	 */
	public function updateByCond($data,$where='')
	{

		if (is_array($where)) {
			$tmp = $where;
			$tmp2 = array();
			foreach($tmp as $k=>$v) {
				if (is_array($v)) {
					$tmp2[] = $this->wdb->quoteInto('`'.$k.'` IN (?)', $v);
				} else {
					$tmp2[] = $this->wdb->quoteInto('`'.$k.'`=?', $v);
				}
			}
			$where = implode(' AND ', $tmp2);
		}
		
		return $this->wdb->update($this->tbl, $data, $where);
	}
	
	/**
	 * 根据条件执行更新操作
	 *
	 * @param $data 数组，键名对应数据库字段，键值对应数据库值
	 * @param $val 如果传入一个数组，则根据数组生成where条件，如果不是数组，则将使用表主键查询$val值
	 */
	public function update($data, $val='', $cond='AND', $tbl='')
	{
		$tbl=='' && $tbl = $this->tbl;
		$whereArr = array();
		if (is_array($val)) {
			foreach($val as $key=>$value) {
				$whereArr[] = $this->wdb->quoteInto('`'.$key.'`=?', $value);
			}
		} else {
			$whereArr[] = $this->wdb->quoteInto('`'.$this->priKey.'`=?', $val);
		}

		return $this->wdb->update($tbl, $data, implode(' '.$cond.' ', $whereArr));
	}
	
	/**
	 * 更新带xy字段的表
	 * 
	 * @param $data
	 * @param $val
	 * @param $cond
	 * @param $tbl
	 * @return unknown_type
	 */
	protected function _updateXY($data, $val='', $cond='AND', $tbl='')
	{
		$tbl == '' && $tbl = $this->tbl;
		
		if (isset($data['x']) && isset($data['y'])) {
			$data['xy'] = new Zend_Db_Expr("GeomFromText('POINT(".$data['x']." ".$data['y'].")')");
			unset($data['x']);
			unset($data['y']);
		}
		
		if (is_array($val)) {
			$where = '1 ';
			foreach($val as $key=>$value) {
				$where .= $cond.$this->wdb->quoteInto('`'.$key.'`=?', $value);
			}
		} else {
			$where = $this->wdb->quoteInto('`'.$this->priKey.'`=?', $val);
		}

		$rows = 0;
		try {

			$rows = $this->wdb->update($tbl, $data, $where);
		} catch(Exception $e) {
			Better_Log::getInstance()->logEmerg('SQL_UPDATE_ERROR:['.$e->getMessage().']'.' Message:['.$e->getTraceAsString().']', 'database');
		}

		return $rows;
		
	}

	/**
	 * 执行删除操作
	 *
	 * @param $val
	 * @param $key 如果不指定$key,则使用表主键
	 */
	public function delete($val,$key='')
	{
		$key = $key=='' ? $this->priKey : $key;
		return $this->wdb->delete($this->tbl, $this->wdb->quoteInto($key.'=?', $val));
	}
	
	/**
	 * 根据特定条件删除数据
	 * 
	 * @param $cond
	 * @return unknown_type
	 */
	public function deleteByCond($cond)
	{
		$where = array();
		foreach ($cond as $k=>$v) {
			$where[] = $this->wdb->quoteInto($k.'=?', $v);
		}
		return $this->wdb->delete($this->tbl, implode(' AND ', $where));;
	}
	
	/**
	 * 根据起始偏移量和要取的数据行数生成limit
	 *
	 * @see Zend_Db_Select->limit
	 * @param Zend_Db_Select $select
	 * @param $limit
	 * @return unknown_type
	 */
	protected static function limit(Zend_Db_Select $select, $limit)
	{
		if ($limit!='0') {
			if (strpos(',',$limit)) {
				list($offset, $count) = explode(',', $limit);
				$select->limit($count, $offset);
			} else {
				$select->limit($limit);
			}
		}
		
		return $select;
	}
	
	/**
	 * 根据页数和每页的数据量生成limit条件
	 *
	 * @see Zend_Db_Select->limit
	 * @param Zend_Db_Select $select
	 * @param $limit
	 * @return unknown_type
	 */
	protected static function limitPage(Zend_Db_Select $select, $limit)
	{
		list($page, $offset) = explode(',', $limit);
		$select->limitPage($page, $offset);
		
		return $select;
	}
	
	/**
	 * 分析where条件
	 *
	 * @param $select
	 * @param $val
	 * @return unknown_type
	 */
	protected function parseWhere($select, $val)
	{
		if (is_array($val)) {
			foreach($val as $key=>$value) {
				//排除order的键
				if ($key!='order') {
					if ($key=='__since__') {
						$select->where("`".$this->priKey."`>?", $value);
					} else {
						if ( is_array($value) ) {
							$new_value = array();
							foreach ($value as $v) {
								$new_value[] = (string)$v;
							}
							$select->where("`".$key."` IN (?) ", $new_value);
						} else {
							$value = (string)$value;
							$select->where('`'.$key.'`=?', $value);
						}
					}
				} else if ($key=='') {
					$select->where($value);
				}
			}
		} else if ($val!=null) {
			$val = (string)$val;
			$select->where('`'.$this->tbl.'`.`'.$this->priKey.'`=?', $val);
		}
		
		return $select;
	}
	
	/**
	 * 分析order条件
	 *
	 * @param $select
	 * @param $val
	 * @return unknown_type
	 */
	protected function parseOrder($select,$val=null)
	{
		if (is_array($val)) {
			foreach($val as $key=>$value) {
				$select->order($key.' '.$value);
			}
		} elseif ($val!=null && strtoupper($val)!='DESC' && strtoupper($val)!='ASC') {
			$select->order($val);
		} else {
			$select->order($this->orderKey.' '.$val);
		}
	}
	
	/**
	 * 清空数据表
	 *
	 * @return bool
	 */
	public function truncate()
	{
		try {
			$flag = self::squery("TRUNCATE `{$this->tbl}`", $this->wdb);
			Better_Log::getInstance()->logInfo($this->tbl.' truncated', 'truncate');
		} catch (Exception $e) {
			Better_Log::getInstance()->logEmerg($e->getTraceAsString(), 'truncate');
		}
	}
}

?>