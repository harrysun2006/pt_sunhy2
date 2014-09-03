<?php

/**
 * 约会吴刚
 * 在任意地点发吼吼，只要其中包含“中秋”、“月”、“家人”三者其一的字样，都可以获得中秋勋章。
 * 男生将得到“约会嫦娥”勋章，女生将得到“约会吴刚”勋章。未设置性别者获得“玉兔陪你”勋章。
 * 9月20日0点—23日24点
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yuehuiwugang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$gender = $params['gender'];

		$start = APPLICATION_ENV=='production' ? gmmktime(20, 0, 0, 9, 19, 2010) : gmmktime(20, 0, 0, 6, 19, 2010);
		$end = gmmktime(20, 0, 0, 9, 23, 2010);
		$now = time();
		
		if ($gender=='female' && $now>=$start && $now<=$end) {
			$blog = &$params['blog'];
			
			if ($blog['type']=='normal') {
				$message = $blog['message'];
				
				if (preg_match('/中秋/', $message)) {
					$result = true;
				}
			}
		}
		
		return $result;
	}
}