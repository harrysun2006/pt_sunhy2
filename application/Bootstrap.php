<?php

/**
 * 白领版项目启动文件
 *
 * @package application
 * @author leip <leip@peptalk.cn>
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public function __construct($application)
	{
		parent::__construct($application);

		$router = new Zend_Controller_Router_Rewrite();
		$routes = array();
		
		/*
		// 设置图片显示的url格式
		$pat = 'avatar/(normal|thumb|tiny)/([0-9]+)/([a-zA-Z0-9]+).(jpg|jpeg|png|gif|bmp)(.*)';
		$routes['avatar'] = new Zend_Controller_Router_Route_Regex(
			$pat,
			array(
				'controller' => 'image',
				'action' => 'avatar',
				),
			array(
				'type' => 1,
				'uid' => 2,
				'hash' => 3,
				)
			);
					
		// 设置图片显示的url格式
		$pat = '(normal|thumb|tiny)/([0-9]+)/([a-zA-Z0-9]+).(jpg|jpeg|png|gif|bmp)(.*)';
		$routes['image'] = new Zend_Controller_Router_Route_Regex(
			$pat,
			array(
				'controller' => 'image',
				'action' => 'index',
				),
			array(
				'type' => 1,
				'uid' => 2,
				'hash' => 3,
				)
			);*/
			
		//	Rss输出用户消息
		$pat = '(rss)/([a-zA-Z0-9\-_]+)(.rss)';
		$routes['rss'] = new Zend_Controller_Router_Route_Regex(
								$pat,
								array(
									'controller' => 'rss',
									),
								array(
									'action' => 'index',
									'username' => 2
									)
								);

		// 设置Server组API接口url格式
		$pat = '(api)/(s2s)/((?:([a-zA-Z0-9\-_]+)){1})((?:/([a-zA-Z0-9\-_]+)){1})((?:/([a-zA-Z0-9\-\._]+)){1}).(xml|json)';
		$routes['api_s2s'] = new Zend_Controller_Router_Route_Regex(
									$pat,
									array(
										'module' => 'api',
										'controller' => 's2s',
										),
									array(
										'action' => 2,
										'todo' => 6,
										'format' => 9,
										)
									);
																		
		// 设置API接口url格式
		$pat = '(api)/((?:([a-zA-Z0-9\-_]+)){1})((?:/([a-zA-Z0-9\-_]+)){1})((?:/([a-zA-Z0-9\-\._]+)){1}).(xml|json)';
		$routes['api_titanic'] = new Zend_Controller_Router_Route_Regex(
									$pat,
									array(
										'module' => 'api',
										),
									array(
										'controller' => 2,
										'action' => 4,
										'todo' => 7,
										'format' => 9,
										)
									);
											
		$pat = '(api)/((?:([a-zA-Z0-9\-_]+)){1})((?:/([a-zA-Z0-9\-_]+)){1}).(xml|json)';
		$routes['api_long'] = new Zend_Controller_Router_Route_Regex(
									$pat,
									array(
										'module' => 'api',
										),
									array(
										'controller' => 2,
										'action' => 5,
										'format' => 6,
										)
									);
		
		$pat = '(api)/((?:([a-zA-Z0-9\-_]+)){1,30}).(xml|json)';
		$routes['api_short'] = new Zend_Controller_Router_Route_Regex(
									$pat,
									array(
										'module' => 'api',
										),
									array(
										'controller' =>2,
										'format' => 3,
										'action' => 'index',
										)
									);

		// 设置开放接口url格式
		$pat = '(public)/((?:([a-zA-Z0-9\-_]+)){1})((?:/([a-zA-Z0-9\-_]+)){1})((?:/([a-zA-Z0-9\-\._]+)){1}).(xml|json)';
		$routes['public_titanic'] = new Zend_Controller_Router_Route_Regex(
									$pat,
									array(
										'module' => 'public',
										),
									array(
										'controller' => 2,
										'action' => 4,
										'todo' => 7,
										'format' => 9,
										)
									);
											
		$pat = '(public)/((?:([a-zA-Z0-9\-_]+)){1})((?:/([a-zA-Z0-9\-_]+)){1}).(xml|json)';
		$routes['public_long'] = new Zend_Controller_Router_Route_Regex(
									$pat,
									array(
										'module' => 'public',
										),
									array(
										'controller' => 2,
										'action' => 5,
										'format' => 6,
										)
									);
		
		$pat = '(public)/((?:([a-zA-Z0-9\-_]+)){1,30}).(xml|json)';
		$routes['public_short'] = new Zend_Controller_Router_Route_Regex(
									$pat,
									array(
										'module' => 'public',
										),
									array(
										'controller' =>2,
										'format' => 3,
										'action' => 'index',
										)
									);									
									
									
		//	设置用户首页的url格式为/:username
		$pat = '((?!'.Better_Config::getAppConfig()->routes->exclude_controllers.')([a-zA-Z0-9\-_].+))'.'((?:/([a-zA-Z0-9\-_]+)){0,1})((?:/([a-zA-Z0-9\-_]+)){0,30})';
		$routes['user'] = new Zend_Controller_Router_Route_Regex(
									$pat,
									array(
										'controller' => 'user',
										),
									array(
										'username' => 1,
										'action' => 3,
										)
									);
		$pat = '((!'.Better_Config::getAppConfig()->routes->exclude_controllers.')([a-zA-Z0-9\-_].+))'.'((?:/([a-zA-Z0-9\-_]+)){0,1})((?:/([a-zA-Z0-9\-_]+)){0,30})';
		$routes['user2'] = new Zend_Controller_Router_Route_Regex(
									$pat,
									array(
										'controller' => 'user',
										),
									array(
										'username' => 1,
										'action' => 3,
										)
									);
		$router->addRoutes($routes);

		//	注册控制器模块
		$frontController = Zend_Controller_Front::getInstance();
		$frontController->setRouter($router);
		
		//	启动PPNS服务通讯
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED) {
			$inited = Better_Cache::remote()->get('ppns_inited');
			if (!$inited) {
				Better_Ppns::getInstance()->initRequest();
				Better_Ppns::getInstance()->init();
				
				Better_Cache::remote()->set('ppns_inited', '1', 0);
			}
		}
	}

}

