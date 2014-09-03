<?php

/**
 * POI举报
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Report extends Better_Poi_Base
{
	protected static $instance = array();
	
	protected function __construct($poiId)
	{
		parent::__construct($poiId);
	}
	
	public static function getInstance($poiId)
	{
		if (!isset(self::$instance[$poiId])) {
			self::$instance[$poiId] = new self($poiId);
		}
		
		return self::$instance[$poiId];
	}	
	
	/**
	 * 某个人24小时内是否举报过了
	 * 
	 * @return boll
	 */
	public function reported($uid, $reason)
	{
		return Better_DAO_Poi_Report::getInstance()->reported($this->poiId, $uid, $reason);
	}

	/**
	 * 举报
	 * 
	 * @param array $params
	 * @return array
	 */
	public function report(array $params)
	{
		$reasons = array(
			'closedown', 'incorrect', 'duplicate', 'other'
			);
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1,
			'INVALID_REASON' => -1,
			'INVALID_POI' => -2,
			);
		$code = $codes['FAILED'];
		$results = array();
		
		$reason = $params['reason'];
		$uid = $params['uid'];
		$content = $params['content'];
		
		if (in_array($reason, $reasons)) {
			if ($this->poiId) {
				$result = Better_DAO_Poi_Report::getInstance()->insert(array(
					'uid' => $uid,
					'poi_id' => $this->poiId,
					'reason' => $reason,
					'report_time' => time(),
					'content' => $content,
					'status'=> 'no_progress'
					));
				$code = $codes['SUCCESS'];
			} else {
				$code = $codes['INVALID_POI'];
			}
		} else {
			$code = $codes['INVALID_REASON'];
		}
		
		$results['code'] = $code;
		$results['codes'] = &$codes;
		
		return $results;
	}
}