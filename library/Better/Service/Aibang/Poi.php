<?php
/**
 * 爱帮网 POI 查询接口
 * 
 * @package Better_Service_Aibang
 * @author  Guo Yimin <guoym@peptalk.cn>
 */

class Better_Service_Aibang_Poi
{
	private static $__keys__ = array(
		array(0, 2, 1, 2, 8, 9, 4, 1, 7, 2, 5, 3, 9),
		array(0, 3, 2, 2, 9, 5, 8, 2, 6, 8, 4, 6, 3),
		array(1, 5, 2, 7, 1, 4, 7, 2, 4, 1, 4, 3, 0),
		array(0, 7, 8, 3, 4, 9, 0, 6, 7, 7, 4, 4, 2),
		array(0, 2, 1, 8, 4, 9, 3, 2, 3, 1, 5, 7, 8),
		array(0, 0, 9, 5, 4, 7, 3, 0, 8, 7, 5, 2, 8),
		array(0, 1, 5, 1, 1, 8, 2, 7, 1, 9, 1, 3, 5),
		array(0, 5, 2, 5, 6, 0, 3, 4, 6, 7, 1, 3, 5),
		array(1, 3, 2, 1, 8, 1, 8, 3, 7, 9, 2, 7, 0),
		array(1, 2, 7, 7, 4, 3, 1, 5, 5, 0, 6, 4, 4),
		array(1, 5, 2, 8, 9, 2, 5, 9, 6, 7, 3, 3, 5),
		array(1, 7, 9, 4, 5, 0, 9, 4, 9, 6, 1, 9, 9),
		array(0, 6, 8, 3, 3, 6, 3, 5, 2, 0, 0, 9, 1),
		array(1, 1, 1, 4, 7, 8, 6, 9, 6, 8, 8, 4, 6),
		array(0, 5, 2, 1, 2, 5, 7, 0, 0, 4, 7, 4, 1),
		array(0, 7, 6, 4, 2, 3, 9, 0, 7, 8, 5, 6, 7),
		array(0, 1, 7, 6, 0, 5, 4, 7, 6, 7, 7, 5, 7),
		array(0, 5, 2, 9, 8, 1, 7, 8, 3, 8, 5, 4, 5),
		array(0, 4, 3, 1, 2, 8, 3, 7, 0, 9, 4, 8, 8),
		array(1, 0, 6, 7, 9, 4, 3, 5, 2, 9, 8, 7, 7),
		array(1, 6, 4, 4, 6, 7, 1, 4, 4, 2, 6, 7, 5),
		array(0, 8, 1, 7, 7, 5, 2, 6, 4, 3, 9, 7, 5),
		array(1, 7, 0, 5, 6, 2, 5, 2, 7, 4, 6, 2, 8),
		array(0, 4, 9, 2, 3, 0, 5, 4, 7, 8, 7, 0, 5),
		array(1, 1, 0, 5, 1, 7, 2, 8, 7, 2, 6, 9, 3),
		array(1, 4, 2, 3, 6, 1, 5, 3, 2, 0, 3, 6, 2),
		array(1, 1, 6, 5, 1, 0, 6, 8, 9, 7, 1, 7, 9),
		array(0, 6, 5, 4, 0, 7, 1, 7, 6, 2, 5, 4, 2),
		array(1, 9, 8, 6, 6, 6, 8, 4, 5, 4, 0, 4, 0),
		array(1, 2, 7, 1, 5, 0, 6, 8, 0, 1, 3, 7, 9),
		array(1, 1, 6, 4, 9, 8, 6, 0, 6, 2, 1, 9, 8),
		array(0, 0, 1, 9, 5, 3, 3, 9, 6, 7, 4, 1, 1),
		array(0, 2, 8, 5, 7, 8, 6, 7, 3, 3, 1, 6, 4),
		array(1, 8, 2, 5, 8, 4, 7, 6, 8, 8, 5, 7, 6),
		array(0, 8, 3, 4, 9, 6, 1, 7, 8, 3, 0, 5, 5),
		array(1, 3, 2, 6, 7, 4, 2, 8, 7, 4, 9, 6, 8),
		array(1, 8, 8, 9, 3, 9, 1, 8, 5, 7, 2, 5, 0),
		array(0, 5, 8, 3, 1, 8, 8, 0, 3, 9, 3, 8, 1),
		array(1, 6, 0, 1, 1, 0, 3, 4, 3, 3, 3, 5, 9),
		array(1, 0, 5, 1, 7, 9, 6, 2, 4, 6, 0, 3, 5),
		array(1, 8, 2, 0, 9, 7, 1, 0, 5, 5, 8, 0, 6),
		array(1, 8, 9, 6, 7, 3, 9, 4, 1, 9, 6, 6, 2),
		array(0, 6, 0, 0, 8, 2, 6, 5, 9, 4, 1, 6, 2),
		array(1, 7, 9, 7, 9, 4, 4, 2, 1, 1, 5, 7, 4),
		array(1, 3, 0, 4, 3, 4, 6, 8, 6, 9, 1, 7, 0),
		array(0, 1, 2, 3, 9, 4, 1, 8, 7, 2, 2, 9, 8),
		array(1, 6, 5, 3, 2, 7, 6, 6, 9, 0, 0, 7, 7),
		array(1, 6, 8, 4, 9, 7, 8, 0, 3, 6, 5, 4, 8),
		array(0, 6, 6, 0, 9, 9, 4, 5, 5, 6, 8, 3, 7),
		array(1, 0, 1, 3, 4, 0, 0, 1, 4, 8, 5, 7, 0),
		array(1, 0, 2, 5, 8, 2, 2, 4, 8, 9, 7, 1, 6),
		array(1, 4, 2, 6, 6, 8, 4, 5, 6, 6, 4, 5, 9),
		array(1, 4, 4, 1, 7, 2, 0, 4, 6, 3, 3, 6, 7),
		array(0, 2, 2, 3, 8, 0, 0, 8, 6, 0, 2, 1, 7),
		array(0, 9, 4, 4, 8, 1, 2, 7, 3, 2, 6, 8, 0),
		array(0, 9, 8, 4, 2, 1, 4, 5, 2, 4, 9, 5, 1),
		array(0, 7, 2, 4, 7, 4, 3, 2, 4, 1, 5, 6, 9),
		array(1, 1, 8, 4, 8, 8, 8, 4, 3, 4, 1, 2, 5),
		array(0, 3, 2, 7, 5, 7, 0, 2, 7, 4, 5, 3, 5),
		array(0, 3, 0, 4, 6, 6, 6, 5, 7, 2, 1, 9, 5),
		array(1, 5, 6, 0, 1, 3, 2, 7, 3, 0, 9, 8, 6),
		array(0, 5, 5, 1, 7, 1, 0, 7, 9, 0, 3, 5, 7),
		array(0, 5, 4, 9, 7, 9, 7, 3, 8, 0, 1, 6, 3),
		array(1, 9, 2, 7, 3, 7, 9, 4, 3, 9, 8, 8, 2),
		array(0, 3, 1, 8, 9, 0, 9, 0, 4, 5, 5, 0, 9),
		array(1, 8, 6, 1, 7, 7, 2, 4, 7, 9, 2, 0, 8),
		array(0, 6, 1, 2, 7, 1, 4, 8, 4, 1, 1, 6, 0),
		array(0, 3, 9, 8, 5, 5, 3, 0, 8, 7, 9, 3, 5),
		array(0, 8, 4, 3, 7, 3, 1, 8, 2, 9, 1, 4, 7),
		array(0, 1, 5, 3, 4, 0, 5, 5, 5, 8, 0, 7, 2),
		array(0, 1, 7, 1, 8, 2, 1, 9, 8, 6, 1, 7, 0),
		array(0, 7, 1, 6, 9, 7, 2, 7, 2, 4, 4, 3, 6),
		array(0, 6, 2, 7, 2, 3, 4, 9, 3, 0, 1, 6, 3),
		array(0, 2, 9, 1, 9, 9, 9, 1, 9, 5, 4, 4, 4),
		array(0, 1, 8, 7, 0, 0, 5, 2, 1, 5, 7, 4, 6),
		array(1, 9, 0, 8, 7, 3, 3, 5, 5, 4, 9, 0, 1),
		array(1, 5, 8, 0, 1, 7, 0, 2, 3, 7, 3, 2, 9),
		array(1, 3, 2, 0, 5, 2, 7, 5, 0, 2, 6, 8, 1),
		array(0, 2, 7, 2, 3, 2, 2, 9, 6, 9, 4, 1, 6),
		array(1, 6, 4, 7, 9, 6, 5, 9, 5, 8, 2, 7, 1),
		array(1, 8, 1, 2, 6, 0, 2, 4, 0, 8, 0, 1, 6),
		array(1, 6, 2, 4, 1, 2, 4, 1, 7, 2, 7, 0, 6),
		array(0, 1, 8, 0, 5, 0, 4, 5, 5, 1, 0, 4, 7),
		array(0, 8, 7, 6, 4, 3, 5, 5, 7, 8, 4, 9, 0),
		array(0, 2, 7, 7, 0, 1, 6, 6, 1, 0, 9, 3, 5),
		array(0, 7, 6, 9, 8, 3, 8, 6, 2, 9, 3, 7, 0),
		array(1, 6, 6, 6, 0, 3, 0, 1, 0, 2, 5, 6, 1),
		array(0, 0, 4, 5, 1, 0, 9, 4, 4, 9, 4, 0, 9),
		array(0, 1, 6, 9, 4, 7, 5, 7, 8, 3, 5, 7, 0),
		array(1, 2, 7, 1, 6, 6, 1, 5, 2, 8, 6, 3, 8),
		array(1, 9, 1, 6, 7, 5, 1, 7, 4, 7, 6, 1, 8),
		array(1, 7, 6, 7, 0, 2, 9, 6, 9, 8, 6, 7, 8),
		array(0, 9, 8, 7, 3, 8, 1, 5, 2, 5, 2, 7, 5),
		array(0, 7, 3, 5, 7, 9, 7, 6, 6, 9, 1, 7, 5),
		array(1, 6, 7, 3, 4, 4, 7, 6, 2, 6, 6, 2, 3),
		array(0, 1, 4, 2, 2, 8, 5, 0, 9, 2, 7, 3, 1),
		array(0, 1, 4, 2, 1, 0, 0, 2, 1, 8, 9, 8, 3),
		array(1, 7, 0, 8, 7, 9, 9, 6, 4, 8, 6, 2, 2),
		array(1, 9, 3, 9, 9, 8, 7, 0, 8, 1, 1, 7, 3),
		array(1, 0, 4, 3, 5, 8, 0, 4, 6, 5, 4, 5, 8),
		array(0, 4, 8, 0, 5, 2, 3, 2, 3, 9, 4, 2, 3),
		array(0, 7, 9, 0, 9, 7, 2, 7, 7, 0, 4, 8, 5),
		array(1, 6, 5, 5, 3, 3, 2, 6, 1, 3, 4, 7, 1),
		array(0, 2, 9, 0, 0, 2, 9, 1, 8, 8, 2, 8, 4),
		array(1, 3, 2, 5, 0, 6, 2, 5, 3, 3, 6, 1, 1),
		array(1, 9, 2, 9, 3, 3, 8, 9, 9, 7, 2, 3, 7),
		array(1, 1, 8, 4, 0, 8, 2, 4, 8, 0, 0, 9, 2),
		array(1, 5, 2, 6, 0, 6, 1, 3, 0, 4, 7, 3, 8),
		array(1, 9, 3, 8, 1, 1, 7, 8, 6, 9, 0, 6, 8),
		array(1, 3, 2, 7, 7, 2, 2, 4, 2, 5, 8, 3, 0),
		array(1, 1, 1, 0, 7, 7, 3, 4, 7, 3, 6, 6, 8),
		array(0, 9, 4, 2, 8, 9, 4, 8, 4, 3, 2, 5, 3),
		array(0, 1, 0, 9, 2, 7, 2, 3, 9, 4, 5, 0, 8),
		array(1, 0, 4, 5, 8, 4, 0, 0, 5, 2, 2, 1, 2),
		array(0, 5, 0, 4, 5, 3, 2, 5, 4, 1, 3, 6, 9),
		array(1, 3, 0, 2, 7, 8, 1, 7, 7, 3, 5, 5, 9),
		array(1, 3, 7, 0, 0, 5, 8, 1, 7, 5, 6, 5, 2),
		array(1, 8, 1, 9, 9, 9, 4, 8, 6, 0, 7, 7, 3),
		array(0, 8, 3, 6, 2, 7, 4, 2, 1, 9, 1, 6, 8),
		array(0, 4, 4, 4, 2, 6, 0, 4, 0, 1, 5, 1, 7),
		array(1, 2, 7, 4, 7, 6, 6, 6, 3, 7, 7, 2, 9),
		array(0, 9, 8, 9, 3, 3, 3, 9, 0, 7, 4, 2, 3),
		array(0, 7, 6, 0, 9, 1, 7, 2, 4, 5, 8, 3, 3),
		array(1, 6, 1, 5, 5, 3, 1, 3, 2, 1, 0, 5, 6),
		array(0, 6, 2, 4, 1, 6, 6, 3, 4, 9, 2, 7, 0),
		array(1, 6, 3, 2, 3, 6, 1, 7, 7, 5, 6, 7, 1),
		array(1, 0, 4, 9, 2, 3, 3, 6, 2, 6, 9, 3, 2),
		array(0, 3, 7, 3, 9, 1, 3, 9, 5, 8, 5, 8, 9),
		array(1, 9, 0, 0, 3, 0, 9, 1, 2, 7, 8, 0, 3),
		array(1, 0, 1, 2, 7, 7, 0, 0, 1, 8, 4, 1, 1),
		array(0, 0, 5, 5, 9, 6, 9, 8, 1, 2, 1, 7, 2),
		array(0, 1, 8, 7, 9, 0, 3, 5, 6, 3, 2, 9, 4),
		array(1, 3, 1, 5, 7, 5, 0, 8, 5, 3, 2, 5, 0),
		array(1, 1, 7, 3, 5, 0, 7, 7, 9, 6, 8, 9, 0),
		array(0, 7, 7, 0, 9, 4, 2, 8, 8, 0, 2, 2, 0),
		array(1, 6, 5, 8, 3, 1, 0, 9, 0, 2, 7, 2, 9),
		array(1, 3, 5, 8, 4, 7, 6, 3, 1, 4, 3, 4, 7),
		array(0, 8, 8, 7, 8, 2, 7, 0, 3, 9, 6, 2, 9),
		array(1, 1, 6, 2, 6, 7, 5, 2, 5, 0, 8, 5, 5),
		array(0, 9, 6, 7, 3, 0, 2, 3, 9, 5, 3, 7, 4),
		array(1, 5, 2, 7, 3, 6, 0, 8, 3, 3, 9, 0, 3),
		array(0, 3, 6, 8, 9, 1, 7, 7, 3, 8, 7, 3, 8),
		array(0, 1, 2, 5, 4, 9, 8, 0, 3, 6, 4, 0, 4),
		array(1, 2, 4, 1, 6, 8, 1, 5, 8, 3, 6, 4, 3),
		array(1, 9, 3, 1, 0, 8, 4, 4, 0, 1, 6, 0, 8),
		array(0, 4, 5, 1, 0, 2, 1, 7, 1, 6, 1, 3, 3),
		array(0, 9, 5, 6, 8, 2, 2, 4, 0, 3, 9, 8, 1),
		array(1, 9, 3, 5, 4, 3, 1, 2, 2, 2, 0, 8, 7),
		array(0, 5, 6, 8, 1, 5, 7, 7, 8, 9, 4, 0, 6),
		array(1, 0, 4, 6, 4, 6, 7, 4, 6, 0, 3, 6, 2),
		array(1, 3, 3, 0, 2, 5, 3, 1, 9, 2, 3, 6, 8),
		array(0, 6, 9, 6, 3, 6, 9, 6, 2, 1, 5, 0, 7),
		array(1, 6, 5, 3, 0, 0, 0, 6, 2, 3, 8, 6, 0),
		array(1, 0, 7, 1, 2, 0, 3, 0, 3, 0, 8, 8, 0),
		array(0, 7, 1, 4, 3, 1, 8, 6, 7, 8, 1, 5, 4),
		array(0, 6, 3, 5, 5, 4, 8, 9, 4, 8, 3, 1, 7),
		array(0, 6, 4, 3, 1, 0, 7, 2, 9, 0, 5, 6, 7),
		array(0, 6, 3, 7, 7, 0, 6, 8, 6, 7, 4, 6, 0),
		array(0, 4, 2, 7, 2, 4, 1, 4, 6, 1, 8, 1, 7),
		array(1, 1, 7, 9, 0, 7, 0, 5, 1, 8, 6, 3, 5),
		array(1, 2, 0, 2, 7, 2, 7, 9, 1, 2, 7, 0, 3),
		array(0, 3, 3, 6, 2, 0, 9, 1, 1, 0, 3, 5, 8),
		array(1, 4, 0, 9, 9, 2, 5, 6, 5, 6, 8, 0, 5),
		array(0, 3, 5, 3, 3, 3, 4, 6, 7, 5, 7, 0, 5),
		array(0, 5, 8, 8, 5, 8, 5, 4, 7, 0, 5, 7, 3),
		array(0, 5, 0, 7, 6, 4, 2, 7, 8, 3, 6, 1, 4),
		array(0, 4, 7, 8, 6, 5, 3, 7, 7, 5, 7, 0, 7),
		array(1, 3, 6, 5, 3, 0, 8, 5, 4, 9, 7, 7, 1),
		array(1, 4, 8, 2, 8, 2, 8, 3, 4, 9, 4, 6, 7),
		array(1, 4, 1, 6, 9, 4, 5, 7, 7, 4, 6, 7, 7),
		array(0, 2, 8, 2, 3, 0, 7, 7, 1, 0, 1, 1, 0),
		array(1, 2, 2, 4, 5, 4, 7, 1, 0, 1, 8, 6, 7),
		array(0, 0, 7, 2, 4, 7, 2, 8, 2, 4, 4, 3, 9),
		array(1, 9, 1, 3, 2, 4, 1, 3, 3, 7, 5, 6, 1),
		array(1, 4, 7, 4, 6, 8, 6, 7, 4, 4, 1, 2, 8),
		array(0, 1, 6, 7, 3, 9, 0, 4, 7, 2, 9, 6, 7),
		array(0, 1, 3, 9, 1, 1, 1, 1, 6, 3, 0, 1, 1),
		array(1, 2, 7, 0, 2, 0, 7, 9, 7, 2, 1, 5, 2),
		array(0, 9, 1, 0, 4, 2, 8, 2, 2, 4, 2, 4, 0),
		array(1, 1, 7, 9, 7, 9, 3, 0, 5, 3, 4, 5, 2),
		array(0, 0, 7, 4, 3, 0, 8, 6, 7, 7, 7, 9, 6),
		array(0, 7, 0, 4, 0, 6, 7, 6, 3, 2, 0, 7, 1),
		array(0, 4, 8, 8, 0, 5, 3, 0, 7, 8, 4, 7, 9),
		array(0, 6, 3, 3, 3, 6, 6, 3, 7, 0, 4, 8, 3),
		array(0, 1, 2, 0, 6, 0, 3, 1, 0, 9, 9, 8, 0),
		array(0, 7, 0, 3, 8, 2, 5, 0, 7, 5, 0, 0, 4),
		array(1, 8, 8, 8, 2, 0, 6, 2, 5, 6, 2, 3, 2),
		array(1, 6, 2, 5, 8, 0, 1, 9, 7, 3, 7, 6, 0),
		array(0, 3, 6, 1, 9, 1, 6, 8, 2, 6, 5, 2, 5),
		array(0, 3, 9, 7, 8, 9, 4, 5, 4, 8, 5, 5, 1),
		array(1, 1, 5, 5, 2, 5, 3, 4, 5, 3, 5, 0, 9),
		array(1, 0, 9, 4, 9, 6, 1, 7, 0, 0, 6, 0, 1),
		array(0, 8, 4, 9, 9, 9, 3, 4, 1, 3, 5, 7, 7),
		array(0, 7, 8, 0, 0, 3, 5, 5, 9, 4, 1, 8, 1),
		array(1, 7, 3, 7, 6, 3, 2, 5, 6, 2, 7, 5, 0),
		array(0, 0, 2, 6, 0, 6, 6, 2, 7, 6, 1, 6, 2),
		array(1, 1, 6, 4, 7, 7, 9, 7, 0, 6, 2, 6, 6),
		array(0, 2, 1, 1, 4, 7, 6, 8, 8, 8, 9, 4, 3),
		array(0, 0, 8, 7, 5, 1, 9, 3, 1, 9, 8, 6, 0),
		array(0, 3, 4, 4, 0, 7, 1, 8, 7, 2, 7, 9, 9),
		array(1, 0, 4, 5, 3, 6, 0, 6, 6, 6, 4, 1, 5),
		array(0, 9, 7, 9, 9, 5, 9, 2, 3, 0, 4, 6, 2),
		array(1, 6, 5, 2, 7, 2, 1, 3, 5, 2, 5, 2, 1),
		array(1, 9, 9, 4, 8, 6, 3, 7, 8, 3, 3, 0, 6),
		array(0, 8, 2, 6, 6, 7, 8, 2, 1, 3, 2, 9, 2),
		array(0, 4, 8, 1, 9, 2, 4, 8, 4, 5, 4, 6, 4),
		array(1, 1, 7, 0, 7, 3, 5, 1, 4, 9, 5, 3, 1),
		array(1, 7, 8, 8, 3, 5, 3, 1, 5, 7, 6, 1, 9),
		array(1, 4, 5, 6, 5, 3, 2, 5, 3, 0, 3, 5, 5),
		array(0, 0, 2, 1, 3, 8, 9, 1, 0, 9, 7, 6, 7),
		array(0, 0, 7, 6, 1, 9, 1, 9, 5, 8, 9, 4, 0),
		array(1, 5, 4, 4, 6, 8, 7, 3, 9, 9, 0, 7, 4),
		array(1, 3, 0, 4, 8, 1, 2, 3, 9, 7, 1, 9, 5),
		array(1, 2, 6, 1, 4, 6, 9, 4, 7, 1, 1, 2, 6),
		array(0, 1, 6, 7, 5, 8, 3, 2, 7, 0, 4, 1, 1),
		array(1, 6, 2, 7, 8, 7, 6, 8, 7, 2, 0, 3, 3),
		array(0, 2, 1, 9, 2, 6, 7, 5, 9, 5, 2, 2, 2),
		array(0, 5, 2, 0, 4, 7, 7, 3, 8, 1, 5, 0, 9),
		array(1, 6, 5, 8, 6, 4, 0, 9, 6, 9, 0, 1, 8),
		array(1, 2, 0, 8, 7, 9, 2, 4, 4, 0, 9, 8, 9),
		array(1, 6, 5, 2, 0, 6, 1, 0, 4, 4, 1, 5, 8),
		array(1, 5, 4, 2, 5, 6, 2, 5, 6, 2, 2, 9, 5),
		array(1, 6, 9, 7, 2, 5, 1, 0, 6, 9, 1, 8, 1),
		array(0, 0, 3, 9, 9, 0, 6, 7, 9, 5, 7, 4, 6),
		array(1, 5, 8, 9, 9, 0, 6, 7, 9, 7, 9, 6, 1),
		array(1, 3, 6, 4, 6, 3, 6, 8, 4, 5, 2, 8, 3),
		array(0, 7, 4, 8, 4, 9, 7, 8, 0, 0, 1, 2, 2),
		array(0, 4, 2, 9, 1, 3, 8, 8, 3, 0, 0, 9, 8),
		array(1, 9, 0, 9, 2, 1, 2, 9, 3, 6, 5, 3, 2),
		array(1, 1, 0, 2, 0, 5, 9, 9, 5, 4, 7, 8, 9),
		array(1, 6, 0, 5, 9, 9, 1, 9, 0, 5, 4, 7, 1),
		array(1, 0, 4, 0, 0, 3, 2, 4, 1, 6, 4, 6, 5),
		array(1, 7, 3, 7, 3, 3, 7, 6, 1, 7, 7, 8, 6),
		array(0, 9, 1, 7, 3, 5, 1, 8, 9, 3, 8, 6, 2),
		array(1, 4, 9, 9, 3, 7, 5, 4, 4, 4, 4, 4, 0),
		array(0, 3, 7, 7, 4, 3, 6, 1, 1, 3, 5, 1, 6),
		array(0, 8, 5, 4, 3, 9, 3, 3, 1, 3, 4, 8, 1),
		array(1, 6, 1, 9, 4, 6, 4, 6, 4, 5, 2, 1, 5),
		array(1, 1, 1, 6, 8, 3, 9, 1, 1, 3, 0, 9, 9),
		array(0, 5, 1, 6, 8, 4, 8, 8, 2, 4, 4, 9, 2),
		array(0, 2, 3, 0, 1, 4, 2, 7, 1, 9, 9, 0, 6),
		array(0, 8, 4, 2, 5, 1, 4, 9, 5, 2, 0, 4, 3),
		array(0, 9, 1, 2, 5, 0, 6, 6, 5, 0, 3, 1, 8),
		array(1, 7, 8, 7, 1, 7, 4, 6, 3, 3, 3, 3, 9),
		array(0, 3, 7, 2, 9, 4, 1, 5, 4, 7, 2, 1, 0),
		array(1, 2, 8, 1, 1, 6, 4, 7, 8, 2, 0, 5, 2),
		array(1, 8, 3, 5, 4, 8, 0, 9, 7, 8, 0, 1, 8),
		array(1, 7, 9, 9, 0, 4, 5, 7, 2, 9, 0, 1, 9),
		array(0, 6, 6, 5, 6, 7, 0, 4, 0, 7, 8, 5, 1),
		array(0, 6, 0, 6, 3, 1, 1, 5, 0, 9, 2, 2, 3),
		array(1, 6, 3, 5, 6, 7, 1, 6, 6, 9, 7, 4, 9),
		array(0, 9, 5, 9, 8, 2, 4, 3, 3, 2, 3, 5, 6),
		array(0, 1, 6, 3, 8, 9, 9, 2, 8, 2, 5, 8, 6),
		array(1, 4, 7, 6, 6, 5, 7, 3, 3, 3, 4, 1, 1),
		array(1, 8, 2, 9, 0, 3, 8, 6, 8, 3, 3, 7, 3),
		array(0, 2, 8, 4, 8, 5, 4, 8, 9, 5, 0, 5, 7),
	);
	private $_dataHandler = null;
	private static $_instance = null;
	
	/**
	 * 超时时间(秒)
	 */
	public $timeout = 3;
	/**
	 * session id，似乎可以hard code
	 */
	public $session = '123456';
	/**
	 *  是否显示不可信结果
	 */
	public $fold = 0;
	/**
	 *  服务器IP，aibang限制了IP
	 */
	public $ip = '221.224.52.9';
	/**
	 * 默认查询，aibang不提供没有关键字的查询，这个设置用来代替获取所有POI的功能
	 */
	public $query = 'category:餐馆;ktv;银行;宾馆酒店;医院;学校;电影院;商场';
	/**
	 * 爱帮的搜索API
	 */
	public $search_api = 'http://www.aibang.com:8080/bd/bedo/search';
	
	
	private function __construct()
	{
		$this->_dataHandler = Better_DAO_Poi_Aibang::getInstance();	
		
		$this->query = Better_Config::getAppConfig()->service->aibang->default_query;
		$this->ip = Better_Config::getAppConfig()->service->aibang->limit_ip;
		$this->timeout = Better_Config::getAppConfig()->service->aibang->timeout;
		$this->search_api = Better_Config::getAppConfig()->service->aibang->search_api;
	}
	
	public static function getInstance()
	{
		if (self::$_instance==null) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	/**
	 * 搜索爱帮poi
	 * 
	 */
	public function &search(array $params)
	{
		$result = array(
			'total' => 0,
			'rows' => array(),
			'aibang_ids' => array(),
			);
			
		$page = (int)$params['page'];
		$count = (int)$params['count'];
		
		if ($params['x'] && $params['y']) {
			$x = (float)$params['x'];
			$y = (float)$params['y'];
		} else if ($params['lon'] && $params['lat']) {
			list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
		} else {
			$x = $y = 0;	
		}
		
		$query = trim($params['keyword']);
		$range = $params['wifi_range'] ? ((float)$params['wifi_range'])*2 : (float)$params['range'];
		$range = (float)$params['range'];
		$category = (int)$params['category'];
		if ($category) {
			$tmp = Better_DAO_Poi_Category::getInstance()->get($category);
			if ($tmp['category_name']) {
				$query .= $query=='' ? $tmp['category_name'] .';'.$tmp['ab_tags']: ';'.$tmp['category_name'].';'.$tmp['ab_tags'];
			}
		}
		
		$range || $range = 5000;
		$page || $page = 1;
		$count || $count = BETTER_PAGE_SIZE;
		$from = $page*$count - $count + 1;
		$to = $from + $count - 1;

		list($rows, $info) = $this->getPois($x, $y, $range, $query, $from, $to);
		
		$result['real_count'] = count($rows);
		foreach ($rows as $k=>$row) {
			if (preg_match('/共产党/', $row['name'])) {
				continue;
			} else {
				if (Better_LL::isValidLL($row['lon'], $row['lat'])) {
					$row['intro'] = &$row['desc'];
					$row['poi_id'] = 0;
					$row['aibang_id'] = $row['bizid'];
					$row['city'] = '';
					
					$result['rows'][] = $row;
					$result['aibang_ids'][] = $row['bizid'];
				}
			}
		}
		$result['total'] = $info['total'];
		$result['count'] = count($result['rows']);

		return $result;
	}

	/**
	 * 查询附近的POI
	 * 
	 * @param $x,$y  int    中心点的XY坐标
	 * @param $range int    范围，单位为米
	 * @param $query string 搜索关键字，查所有POI时query设为空字符串
	 * @param $from  int    返回结果开始位置 从 1 开始
	 * @param $to    int    返回结果结束位置
	 * 
	 * @return array poi 数组
	 */
	public function getPois($x, $y, $range, $query, $from, $to)
	{
		$city = '';
		$query_0 = $query;
				
		if ($query == '') {
			$query = 'category:' . $this->query;
		} else {
			$city = Better_Registry::get('AIBANG_CITY');		
			if ($x && $y) {
				
			} else {
				if (!$city) {			
					$ip = Better_Functions::getIP();
					$city = Better_IPtoLocation::getInstance(APPLICATION_PATH.'/../public/data/')->ip2city($ip);
		
					list($foobar, $city) = explode('省', $city);
					$city = str_replace('市', '', $city);					
				}
			}
		}

		list($lon, $lat) = $this->_xy2ll($x, $y);
		$a = "loc:{$lon}|{$lat}";
		$as = intval($range);
		$ip = urlencode($this->ip);
		$fold = $this->fold;
		$session = $this->session;
		
		$q = urlencode($query);
		$url = $this->search_api . "?as=$as&q=$q&s=bedo_search&ip=$ip&from=$from&to=$to&session=$session";	
		if ($query_0) {
			$url .= '&rc=3';
		} else {
			$url .= '&rc=2';
		}

		if ($lon && $lat) {
			$url .= '&a='.$a;
		} else {
			if ($city!='') {
				$url .= '&city='.$city;
			}
		}

		Better_Log::getInstance()->logInfo($url, 'aibang_url');
		$body = $this->_httpGet($url);
		if ($body) {
			list($data, $info) = $this->_toArray($body);
			
			if ($from > $info['total']) {
				return array(array(), array());
			}
			
			if ($this->_dataHandler) {
				$results = array();
				foreach ($data as $k=>$row) {
					$this->_dataHandler->save($row);
					
					$row['city'] = $city;
					$row['address'] = $row['addr'];
					if (APPLICATION_ENV!='production') {
						$row['address'] .= '__AB';
					} 
					$row['category_image'] = Better_Poi_Category::mapCategoryToImage(Better_Service_Aibang_Category::trans($row['tag']));
					$row['logo_url'] = BETTER_STATIC_URL.'/images/poi/category/101/'.$row['category_image'];

					$results[] = $row;
				}
			}
			return array($results, $info);
		}
		
		return array(array(), array());
	}
	
	private function _gzdecode($data)
	{
		if (function_exists('gzdecode')) {
			return gzdecode($data);
		}
		$flags = ord(substr($data, 3, 1));
		$headerlen = 10;
		$extralen = 0;
		$filenamelen = 0;

		if ($flags & 4) {
			$extralen = unpack('v', substr($data, 10, 2));
			$extralen = $extralen[1];
			$headerlen += 2 + $extralen;
		}
		if ($flags & 8) {// Filename
			$headerlen = strpos($data, chr(0), $headerlen) + 1;
		}
		if ($flags & 16) {// Comment
			$headerlen = strpos($data, chr(0), $headerlen) + 1;
		}
		if ($flags & 2) {// CRC at end of file
			$headerlen += 2;
		}
		$unpacked = @gzinflate(substr($data, $headerlen));
		if ($unpacked === false) {
			$unpacked = $data;
		}
		return $unpacked;
	}

	private function _httpGet($url)
	{
		$ch = curl_init($url);
		if ($ch === false) {
			return '';
		}
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true, 
			CURLOPT_TIMEOUT => $this->timeout,
		));
		$body = curl_exec($ch);
		
		curl_close($ch);
		return $this->_gzdecode($body);
	}
	
	private function _toArray($xml)
	{
		$xmldom = new DOMDocument("1.0", "utf-8");
		if (!$xmldom->loadXML($xml)) {
			return array();
		}
		
		$infoArr['total'] = $xmldom->getElementsByTagName("total")->item(0)->nodeValue;
		$infoArr['total'] = $infoArr['total'] > 200 ? 200 : $infoArr['total'];

		$infoArr['result_num'] = $xmldom->getElementsByTagName("result_num")->item(0)->nodeValue;
		$infoArr['spend_time'] = $xmldom->getElementsByTagName("spend_time")->item(0)->nodeValue;
		$infoArr['spend_time'] = $xmldom->getElementsByTagName("spend_time")->item(0)->nodeValue;
		$infoArr['city'] = $xmldom->getElementsByTagName("city")->item(0)->nodeValue;
		$infoArr['address'] = $xmldom->getElementsByTagName("address")->item(0)->nodeValue;
		$infoArr['key'] = $xmldom->getElementsByTagName("key")->item(0)->nodeValue;
		
		
		$docsNode = $xmldom->getElementsByTagName("docs")->item(0);
		if (!$docsNode) {
			return array();
		}
		$docNodeList = $docsNode->childNodes;
		$docArr = array();
		foreach ($docNodeList as $docNode) {
			$attArr = array();
			$attNodeList = $docNode->childNodes; //属性结束如id,name...
			if (!$attNodeList) {
				continue;
			}
			foreach ($attNodeList as $attNode) {
				$attTagName = $attNode->nodeName;
				$attValue = $attNode->nodeValue;
				if ('#text' != $attTagName) {
					$attArr[$attTagName] = $attValue;
				}
				list($strLon, $strLat) = explode(',', $attArr['mapxy']);
				$lon = $this->_decodeLL($strLon);
				$lat = $this->_decodeLL($strLat);
				$attArr['lon'] = $lon;
				$attArr['lat'] = $lat;
				list($x, $y) = $this->_ll2xy($lon, $lat);
				$attArr['x'] = $x;
				$attArr['y'] = $y;
			}
			if (!empty($attArr)) {
				$docArr[] = $attArr;
			}
		}
		return array($docArr, $infoArr);
	}
	
	private function _decodeLL($D)
	{
		$G = self::$__keys__[ord(substr($D, -4, -3) /*$D[-4:-3]*/
		) & 3 | (ord(substr($D, -3, -2) /*$D[-3:-2]*/
		) & 3) << 2 | (ord(substr($D, -2, -1) /*$D[-2:-1]*/
		) & 3) << 4 | (ord(substr($D, -1) /*$D[-1:]*/
		) & 3) << 6]; //#__getKeyNum__("lpleooeksmpFGIK")=222
		$C = 23 + $G[0] * 30; //                # if G[0]==0: C=23; elif G[0]==1: C=53
		//return "".join([chr(ord(D[F])-C-G[F+1]) for F in range(len(D)-4)])
		$ll = "";
		$len = strlen($D) - 4;
		for ($F = 0; $F < $len; $F++) {
			$ll.= chr(ord($D[$F]) - $C - $G[$F + 1]);
		}
		return $ll;
	}

	//lon,lat -> x,y
	private function _ll2xy($lon, $lat)
	{
		$PI = pi();
		$x = round($lon / 360 * 256 * pow(2, 17));
		$y = round(log(tan(($lat * $PI / 180 + $PI / 2) / 2)) * 256 / $PI / 2 * pow(2, 17));
		return array($x, $y);
	}
	
	private function _xy2ll($x, $y)
	{
		$PI = pi();
		$lon = $x / 93206.7556; //pow(2,17) / 256 * 360;
		$lat = (atan(exp($y / pow(2, 17) / 256 * $PI * 2)) * 2 - $PI / 2) * 180 / $PI;
		return array($lon, $lat);
	}
}