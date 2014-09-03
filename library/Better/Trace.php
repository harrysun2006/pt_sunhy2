<?php

class Better_Trace
{
	private static function calcGrid($lon1,$lat1,$lon2,$lat2)
	{
		$rad = pi() / 180;
		$R = 6.3781e6;
		$x = ($lon2-$lon1)*$rad*$R*cos( (($lat1+$lat2)/2) * $rad);
		$y = ($lat2-$lat1)*$rad*$R;
		return array($x, $y);
	}
	
	private static function firstLevel($rows, $radius)
	{
		$orig = array('lat'=>$rows[0]['poi']['lat'], 'lon'=>$rows[0]['poi']['lon']);
		$cluster = array();
		$max = array();
		foreach ($rows as $row) {
			list($x, $y) = self::calcGrid($orig['lon'], $orig['lat'], $row['poi']['lon'], $row['poi']['lat']);
			$grid = ceil($x / $radius) . '-' . ceil($y / $radius);
			if ($cluster[$grid]) {
				$cluster[$grid]['numCheckins'] += $row['checkin_count'];
				$cluster[$grid]['checkin_time'] = $cluster[$grid]['checkin_time'] >= $row['checkin_time']? $cluster[$grid]['checkin_time'] : $row['checkin_time'];
				if ($row['checkin_count'] > $max[$grid]) {
					$max[$grid] = $row['checkin_count'];
					$cluster[$grid]['lat'] = $row['poi']['lat'];
					$cluster[$grid]['lon'] = $row['poi']['lon'];
					$cluster[$grid]['poi'] = $row['poi'];
				}
			} else {
				$max[$grid] = $row['checkin_count'];
				$cluster[$grid]['numCheckins'] = $row['checkin_count'];
				$cluster[$grid]['lat'] = $row['poi']['lat'];
				$cluster[$grid]['lon'] = $row['poi']['lon'];
				$cluster[$grid]['poi'] = $row['poi'];
				$cluster[$grid]['checkin_time'] = $row['checkin_time'];
			}
		}
		return $cluster;
	}
	
	private static function nextLevel($rows, $radius)
	{
		$orig = array('lat'=>$rows[0]['poi']['lat'], 'lon'=>$rows[0]['poi']['lon']);
		$cluster = array();
		$max = array();
		foreach ($rows as $row) {
			list($x, $y) = self::calcGrid($orig['lon'], $orig['lat'], $row['poi']['lon'], $row['poi']['lat']);
			$grid = ceil($x / $radius) . '-' . ceil($y / $radius);
			if ($cluster[$grid]) {
				$cluster[$grid]['numCheckins'] += $row['numCheckins'];
				$cluster[$grid]['checkin_time'] = $cluster[$grid]['checkin_time'] >= $row['checkin_time']? $cluster[$grid]['checkin_time'] : $row['checkin_time'];
				if ($row['numCheckins'] > $max[$grid]) {
					$max[$grid] = $row['numCheckins'];
					$cluster[$grid]['lat'] = $row['lat'];
					$cluster[$grid]['lon'] = $row['lon'];
					$cluster[$grid]['poi'] = $row['poi'];
				}
			} else {
				$max[$grid] = $row['numCheckins'];
				$cluster[$grid]['numCheckins'] = $row['numCheckins'];
				$cluster[$grid]['lat'] = $row['lat'];
				$cluster[$grid]['lon'] = $row['lon'];
				$cluster[$grid]['poi'] = $row['poi'];
				$cluster[$grid]['checkin_time'] = $row['checkin_time'];
			}
		}
		return $cluster;
	}
	
	public static function clusterMarkers($rows, $zoomcount=15)
	{
		$return = array();
		for($zoomlevel=$zoomcount; $zoomlevel>=1; $zoomlevel--){
			$radius = self::getRadius($zoomlevel);
			if ($zoomlevel == $zoomcount) {
				$return[$zoomlevel] = self::firstLevel($rows, $radius);
			} else {
				$return[$zoomlevel] = self::nextLevel($return[$zoomlevel+1], $radius);
			}
		}
		//ksort($return);
		return $return;
	}
	
	public static function clusterMarkers1($rows, $zoomcount=15){
		$return = array();
		for($zoomlevel=1; $zoomlevel<=$zoomcount; $zoomlevel++){
			$radius = self::getRadius($zoomlevel);	
			$activeClusterId = 0;
			$cluster = array();

			foreach($rows as $k=>$row){
				$rows[$k]['cluster_flag'] = 0;
			}
	
			$count = count($rows);
			for($i=0; $i<$count; $i++){
				$row = $rows[$i];
				if($row['cluster_flag']==0){
					$cluster[$activeClusterId]['numCheckins'] = $row['checkin_count'];
					$cluster[$activeClusterId]['lat'] = $row['poi']['lat'];
					$cluster[$activeClusterId]['lon'] = $row['poi']['lon'];
					$cluster[$activeClusterId]['poi'] = $row['poi'];
					$cluster[$activeClusterId]['checkin_time'] = $row['checkin_time'];
					
					for($m=$i+1; $m<$count; $m++){
						if($rows[$m]['cluster_flag']==0){
							$lat = $rows[$m]['poi']['lat'];
							$lon = $rows[$m]['poi']['lon'];
							if(Better_Service_Lbs::getDistance($lon, $lat, $cluster[$activeClusterId]['lon'], $cluster[$activeClusterId]['lat']) <= $radius){
								$cluster[$activeClusterId]['numCheckins'] += $rows[$m]['checkin_count'];
								$cluster[$activeClusterId]['checkin_time'] = $cluster[$activeClusterId]['checkin_time'] >= $rows[$m]['checkin_time']? $cluster[$activeClusterId]['checkin_time'] : $rows[$m]['checkin_time'];
								$rows[$m]['cluster_flag'] = 1;
							}
						}
					}
					$activeClusterId++;
				}
			}
			
			$return[$zoomlevel] = $cluster;
		}
		
		return $return;
	}
	
	/*
	 * 根据zoom获得半径
	 */
	private static function getRadius($zoomlevel){
		$radius = 0;
		switch($zoomlevel)
		{
			case 1:
				$radius = 5000000;
				break;
			case 2:
				$radius = 2000000;
				break;
			case 3:
				$radius = 1000000;
				break;
			case 4:
				$radius = 500000;
				break;
			case 5:
				$radius = 200000;
				break;
			case 6:
				$radius = 100000;
				break;
			case 7:
				$radius = 50000;
				break;
			case 8:
				$radius = 20000;
				break;
			case 9:
				$radius = 10000;
				break;
			case 10:
				$radius = 5000;
				break;
			case 11:
				$radius = 2000;
				break;
			case 12:
				$radius = 1000;
				break;
			case 13:
				$radius = 500;
				break;
			case 14:
				$radius = 200;
				break;
			case 15:
				$radius = 100;
				break;
			default:
				break;
		}
		$radius = floatval($radius*2);
	
		return $radius;
	}


}