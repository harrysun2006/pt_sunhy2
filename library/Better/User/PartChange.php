<?php

class Better_User_PartChange
{
	
	public static function commonTbl($tbl, $fromRdb, $fromWdb, $toWdb, $uid)
	{
		$tbl = BETTER_DB_TBL_PREFIX.$tbl;
		
		
		$sql  = "SELECT * FROM `".$tbl."` WHERE uid='".$uid."'";
		$rows = $fromRdb->query($sql)->fetchAll();
		
		echo "Trans table [".$tbl."] for [".$uid."], total rows:[".count($rows)."]\n";
		foreach ($rows as $row) {
			$toWdb->insert($tbl, $row);
		}
		
		$fromWdb->query("DELETE FROM `".$tbl."` WHERE uid='".$uid."'");		
		
		Better_Log::getInstance()->logInfo('Uid:['.$uid.'], Table:['.$tbl.'], Rows:['.count($rows).']', 'part_change', true);
	}
	
	public static function aiTbl($tbl, $fromRdb, $fromWdb, $toWdb, $uid, $aiKey='id')
	{
		$tbl = BETTER_DB_TBL_PREFIX.$tbl;
		
		$sql  = "SELECT * FROM `".$tbl."` WHERE uid='".$uid."'";
		$rows = $fromRdb->query($sql)->fetchAll();
		
		echo "Trans table [".$tbl."] for [".$uid."], total rows:[".count($rows)."]\n";
		foreach ($rows as $row) {
			unset($row[$aiKey]);
			$toWdb->insert($tbl, $row);
		}
		
		$fromWdb->query("DELETE FROM `".$tbl."` WHERE uid='".$uid."'");		
		
		Better_Log::getInstance()->logInfo('Uid:['.$uid.'], Table:['.$tbl.'], Rows:['.count($rows).']', 'part_change', true);
	}
	
	public static function xyTbl($tbl, $fromRdb, $fromWdb, $toWdb, $uid, $xyKey='xy')
	{
		$tbl = BETTER_DB_TBL_PREFIX.$tbl;
		
		$keys = array();
		$tmp = $fromRdb->query("SHOW COLUMNS FROM `".$tbl."`")->fetchAll();
		foreach ($tmp as $v) {
			if ($v['Field']!=$xyKey) {
				$keys[] = $v['Field'];
			}
		}
		$dbKeys = $keys;
		
		$keys[] = new Zend_Db_Expr('X('.$xyKey.') AS x');
		$keys[] = new Zend_Db_Expr('Y('.$xyKey.') AS y');
		$select = $fromRdb->select();
		$select->from($tbl, $keys);
		$select->where('uid=?', $uid);
		
		$rs = $fromRdb->query($select);
		try {
			$rows = $rs->fetchAll();
		} catch (Exception $e) {
			die($e->getTraceAsString());
		}
		
		echo "Trans table [".$tbl."] for [".$uid."], total rows:[".count($rows)."]\n";
		foreach ($rows as $row) {
			$x = $row['x'];
			$y = $row['y'];
			unset($row['x']);
			unset($row['y']);
			$d = $row;
			$d['xy'] = new Zend_Db_Expr("GeomFromText('POINT(".$x." ".$y.")')");
			$toWdb->insert($tbl, $d);
		}
		
		$fromWdb->query("DELETE FROM `".$tbl."` WHERE uid='".$uid."'");		
		
		Better_Log::getInstance()->logInfo('Uid:['.$uid.'], Table:['.$tbl.'], Rows:['.count($rows).']', 'part_change', true);
	}	
}