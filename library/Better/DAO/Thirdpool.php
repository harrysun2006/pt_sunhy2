<?php

/**
 * Better_DAO_Thirdpool
 *
 * @package Better.DAO
 * @author sunhy <sunhy@peptalk.cn>
 */

class Better_DAO_Thirdpool extends Better_DAO_Base
{

	private static $instance = null;

	public function __construct($identifier = 0)
	{
		$this->tbl = BETTER_DB_TBL_PREFIX . '3rdpool';
		parent::__construct($identifier);
	}

	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		return self::$instance;
	}

	/**
	 * 返回可用的tokens
	 * token超限时4sq返回的['meta']['errorType']=='rate_limit_exceeded', 此时evict此token即可
	 * @param array $params
	 */
	public static function usable(array $params)
	{
		$limit = isset($params['limit']) ? $params['limit'] : 60;
		$protocol = isset($params['protocol']) ? $params['protocol'] : '';
		$type = isset($params['type']) ? $params['type'] : 'token';
		$db = self::registerDbConnection('common_server');
		$tbl = BETTER_DB_TBL_PREFIX . '3rdpool';
		$sql = "SELECT * FROM `" . $tbl . "` WHERE protocol = '" . $protocol . "'";
		if ($type == 'token') {
			$sql .= " AND oauth_token <> ''";
		} else if ($type == 'pass') {
			$sql .= " AND username <> '' AND password <> ''";
		}
		$sql .= " ORDER BY dateline ASC, total ASC LIMIT " . $limit;
		$rs = self::squery($sql, $db);
		$rows = $rs->fetchAll();
		return $rows;
	}

	public static function evict(array &$row)
	{
		$db = self::registerDbConnection('common_server');
		$tbl = BETTER_DB_TBL_PREFIX . '3rdpool';
		$db->update($tbl,
		array(
			'dateline' => $row['dateline'], 
			'total' => $row['total'],
		), array(
			'uid' => $row['uid'], 
			'protocol' => $row['protocol'],
		));
	}

	public static function getAllTokens(array $params = array())
	{
	  $db = self::registerDbConnection('common_server');
	  $tbl = BETTER_DB_TBL_PREFIX . '3rdpool';
	  $results = $data = array();
	  $page = $params['page'] ? intval($params['page']) : 1;
	  $reload = $params['reload'] ? intval($params['reload']) : 0;
    $select = $db->select();
    $select->from($tbl . ' AS p', '*');
    if ($params['uid']) $select->where('p.uid = ?', $params['uid']);
    if ($params['protocol']) $select->where($db->quoteInto('p.protocol = ?', $params['protocol']));
    $select->order('p.uid ASC');
    $select->order('p.protocol ASC');
    $sql = $select->__toString();
    $results = $db->fetchAll($sql);
	  return $results;
	}

}
