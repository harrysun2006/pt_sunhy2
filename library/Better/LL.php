<?php 

/**
 * 经纬度混淆
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_LL
{
	public static function isValidLL($lon, $lat)
	{
		$result = false;
		
		if ($lon<=180 && $lat<=180 && $lat>=-180 && $lat>=-180 && $lat!=0 && $lon!=0 && $lat!=-1 && $lon!=-1) {
			$result = true;
		}
		
		return $result;		
	}
	
	public function parse($lon, $lat)
	{
		$result = array(
			'lon' => $lon,
			'lat' => $lat,
			);
			
		list($x, $y) = Better_Functions::LL2XY($lon, $lat);	
		if ($x>0 && $y>0) {
			$lonMin = $lon-0.02;
			$lonMax = $lon+0.02;
			$latMin = $lat-0.02;
			$latMax = $lat+0.02;
			
			$row = Better_DAO_LL_Simple::getInstance()->parse($lonMin, $latMin, $lonMax, $latMax);

			if (isset($row['dislon'])) {
				$result['lon'] += $row['dislon'];
				$result['lat'] += $row['dislat'];
			} else {
				$row = Better_DAO_LL_All::getInstance()->parse($lonMin, $latMin, $lonMax, $latMax);	
				if (isset($row['dislon'])) {
					$result['lon'] += $row['dislon'];
					$result['lat'] += $row['dislat'];
				}
			}
		}
		
		return $result;
	}

}