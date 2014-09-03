<?php

/**
 * 经纬度修正
 * 
 * @package Better.DAO.LL
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_LL_Base extends Better_DAO_Base
{

	public function parse($lonMin, $latMin, $lonMax, $latMax)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			'dislon', 'dislat'
			));
		$select->where('lon>=?', $lonMin);
		$select->where('lon<?', $lonMax);
		$select->where('lat>=?', $latMin);
		$select->where('lat<?', $latMax);
		$select->limit(1);

		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return $row;
	}
}