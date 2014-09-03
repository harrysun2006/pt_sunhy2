<?php

class Plugin_IndexController extends Better_Controller
{
	
	public function indexAction() {
		$request = $this->getRequest();
		$id = $request->getParam('ID', '');
		$fields = json_decode($request->getParam('FIELDS', ''), true);
		$plugin = $fields['PLUGIN'];
		$params = $fields['PARAMS'];
		$extra = $fields['EXTRA'];
		if (!isset($params)) $params = array();
		if (!isset($extra)) $extra = array();
		Better_Plugin::service($plugin, $params, $extra);
	}
}
?>