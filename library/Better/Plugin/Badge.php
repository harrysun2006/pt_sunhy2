<?php

/**
 * 勋章并行计算plugin
 * 
 * @author sunhy <sunhy@peptalk.cn>
 * @package Better.Plugin
 */
class Better_Plugin_Badge extends Better_Plugin_Base
{
	
	public static function &pre_proc($pn, array &$params)
	{
		$badges = &$params['_badges'];
		$reqs = array();
		foreach ($badges as $badge) {
			$rid = self::next_id();
			$reqs[$rid] = array(
				'id' => $badge->id,
				'class' => $badge->class_name,
				'name' => $badge->badge_name,
			);
		}
		return $reqs;
	}

	public static function &post_proc($pn, array &$params, array &$rr)
	{
		$details = &$rr['DETAILS'];
		$name = '';
		$id = 0;
		foreach ($details as $url => $d) {
			if (!$d['VALID']) {
				$rr['VALID'] = 0;
				$rr['ECODE'] = 'X.' . $d['HTTP_CODE'];
				// return;
			}
			if ($d['RET']['touch'] === 1) { // 获得某勋章
				$id = $d['RET']['id'];
				$name = $d['RET']['name'];
			}
		}
		if ($id > 0) $rr['VALUE'] = Better_Language::load()->global->got_badge . '"' . $name . '"';
	}

	public static function &service(array &$params, array &$extra)
	{
		$id = $extra['id'];
		$class = $extra['class'];
		$name = $extra['name'];
		if (!isset($class)) return null;
		$result = false;
		$uid = (int)$params['uid'];
		$cn = 'Better_DAO_Badge_Calculator_' . ucfirst($class);
		if ($uid && class_exists($cn)) {
			$result = call_user_func($cn . '::touch', $params);
		}
		if ($result) {
			$user = Better_User::getInstance($uid);
			$poi_id = $params['poi_id'];
			$user->badge()->got($id, $poi_id);	
		}
		return array(
			'id' => $id,
			'class' => $class,
			'name' => $name,
			'touch' => (int)$result,
		);
	}
}