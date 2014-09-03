<?php

/**
 * 找朋友
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_FindfriendsController extends Better_Controller_Api
{
	public $SNS = array('sina.com', 'kaixin001.com', 'sohu.com', 'fanfou.com', 'msn.com');
	public $oauthSNS = array('qq.com', 'qqsns.com', 'renren.com', 'douban.com', '163.com', '4sq.com' );
	public $followkaiSNS = array('sina.com');
	
	public function init()
	{
		parent::init();
		$this->auth();
		
		if ('221.224.52.9' == Better_Functions::getIP()) {
			//$this->SNS = array('sina.com', 'kaixin001.com', 'sohu.com', 'fanfou.com', 'msn.com');
		}
	}


	public function _emailpartners2($domains)
	{
		$lang = $this->lang->toArray();
		
		$this->xmlRoot = 'categories';
		
		$this->data[$this->xmlRoot][0]['category']['id'] = '1';
		$this->data[$this->xmlRoot][0]['category']['name'] = 'Email';
		
		foreach ($domains as $domain) {
			
			$this->data[$this->xmlRoot][0]['category']['items'][] = array(
				'email' => $this->api->getTranslator('item')->translate(array(
					'data' => array(
						'domain' => '@'.$domain,
						'name' => $lang['email_domain'][str_replace('.', '_', $domain)]['name'],
						'image_url' => Better_Config::getAppConfig()->base_url.'/images/emails/'.$domain.'.png',
						),
					)),
				);
		}
		
		$this->data[$this->xmlRoot][1]['category']['id'] = '2';
		$this->data[$this->xmlRoot][1]['category']['name'] = 'SNS';
		
		$sns = array('sina.com', 'kaixin001.com');
		foreach ($sns as $v) {
			$data = array(
						'domain' => $v . '_SNS',
						'name' => $lang['sns'][str_replace('.', '_', $v)]['name'],
						'image_url' => Better_Config::getAppConfig()->base_url . '/images/3rdSite/' . str_replace('.com', '', $v) . '.gif?ver='.BETTER_VER_CODE,
					);
			$this->data[$this->xmlRoot][1]['category']['items'][] = array('item' => $this->api->getTranslator('email')->translate(array('data' => $data)));
		}
		
		$this->output();
	}	
	
	public function _partners2($domains, $lang)
	{
		$this->xmlRoot = 'partners';
		
		$this->data[$this->xmlRoot]['categories'][0]['category']['id'] = '1';
		$this->data[$this->xmlRoot]['categories'][0]['category']['name'] = '找社区好友';
		
		$sns = $this->SNS ;
		
		$syncSites = (array)Better_User_Syncsites::getInstance($this->uid)->getSites();
		$sync_keys = array_keys($syncSites);
		if (in_array('qq.com', $sync_keys)) $sns = array_merge($sns, array('qq.com'));
		foreach ($sns as $v) {
			$username = '';
			if (in_array($v, $sync_keys)) {
				$username = $syncSites[$v]['username'];
			} 
			
			$data = array(
						'domain' => $v . '_sns',
						'name' => $lang['sns'][str_replace('.', '_', $v)]['name'],
						'image_url' => Better_Config::getAppConfig()->base_url . '/images/3rdSite/' . str_replace('.com', '', $v) . '.gif?ver='.BETTER_VER_CODE,
						'needpassword' => 'true',
					);
			if ($username) {
				$data['username'] = $username;
			}	
			$data['need_bind'] = true;		
			$this->data[$this->xmlRoot]['categories'][0]['category']['items'][] = array('item' => $this->api->getTranslator('email')->translate(array('data' => $data)));
		}		
		
		
		$this->data[$this->xmlRoot]['categories'][1]['category']['id'] = '2';
		$this->data[$this->xmlRoot]['categories'][1]['category']['name'] = '找Email联系人';		
		
		foreach ($domains as $domain) {
			$this->data[$this->xmlRoot]['categories'][1]['category']['items'][] = array(
				'item' => $this->api->getTranslator('email')->translate(array(
					'data' => array(
						'domain' => $domain,
						'name' => $lang['email_domain'][str_replace('.', '_', $domain)]['name'],
						'image_url' => Better_Config::getAppConfig()->base_url.'/images/emails/'.$domain.'.png',
						'needpassword' => 'true',
						),
					)),
				);
		}	


		
		$this->output();		
	}		
	
	/**
	 * 13.9 取第三方应用列表
	 * 
	 * @return
	 */
	public function partnersAction()
	{
		$this->xmlRoot = 'partners';

		$domains = array(
			'sina.com', 'sohu.com', '163.com', 
			//'126.com', 
			'tom.com', 'yeah.net', 'gmail.com', 
			);
			
		$lang = $this->lang->toArray();
		
		$ver = $this->getRequest()->getParam('ver', '1');
		if ($ver == 2) {
			$this->_partners2($domains, $lang);
			exit;
		}
		
		foreach ($domains as $domain) {
			$this->data[$this->xmlRoot]['emails'][] = array(
				'email' => $this->api->getTranslator('email')->translate(array(
					'data' => array(
						'domain' => $domain,
						'name' => $lang['email_domain'][str_replace('.', '_', $domain)]['name'],
						'image_url' => Better_Config::getAppConfig()->base_url.'/images/emails/'.$domain.'.png',
						),
					)),
				);
		}			
		
		$this->output();
	}
	
	/**
	 * 13.7 寻找号码簿里面的好友
	 * 
	 * @return
	 */
	public function byphoneAction()
	{
		$ver = $this->getRequest()->getParam('ver', '1');
		switch ($ver) {
			case '3':
				$this->_byphoneVer3();
				break;
			case '2':
				$this->_byphoneVer2();
				break;
			default: 
				$this->_byphoneVer1();
				break;
		}
	}
	
	/**
	 * 13.5 寻找email帐号里的kai好友
	 */
	public function byemailAction()
	{
		$ver = $this->getRequest()->getParam('ver', '1');
		
		switch ($ver) {
			case '2':
				$this->_byemailVer2();
				break;
			default:
				$this->_byemailVer1();
				break;	
		}
	}
	

	
	/**
	 * 13.4 取得合作的第三方邮箱
	 * 
	 * @return
	 */
	public function emailpartnersAction()
	{
		$ver = $this->getRequest()->getParam('ver', '1');
		 
		$this->xmlRoot = 'emails';
		$domains = array(
			'sina.com', 'sohu.com', 'tom.com', '163.com', 'yeah.net', 'gmail.com', 
			);
		$lang = $this->lang->toArray();
			
		if ($ver == 2 ) {
			//$this->_emailpartners2($domains, $lang);
			exit;	
		}
		
		foreach ($domains as $domain) {
			$this->data[$this->xmlRoot][] = array(
				'email' => $this->api->getTranslator('email')->translate(array(
					'data' => array(
						'domain' => '@'.$domain,
						'name' => $lang['email_domain'][str_replace('.', '_', $domain)]['name'],
						'image_url' => Better_Config::getAppConfig()->base_url.'/images/emails/'.$domain.'.png',
						),
					)),
				);
		}
		
		$this->output();
	}

	/**
	 * 13.2寻找kai上的im好友
	 * 
	 * @return
	 */
	public function byimAction()
	{
		$this->xmlRoot = 'users';
		$this->needPost();
		
		$partner = strtolower(trim($this->post['partner']));
		$id = trim($this->post['id']);
		$password = $this->post['password'];
		
		if (Better_Functions::checkEmail($id)) {
			
			if ($password!='') {
				
				//	目前只支持msn，先强制设定一下
				switch ($partner) {
					case 'msn':
						$service = new Better_Service_MsnFriends($id, $password);
						$result = $service->search();
						if ($result==1) {
							$results = $service->getResults();
							foreach ($results['rows'] as $row) {
								$this->user->friends()->hasRequest($row['uid']) || $this->user->friends()->request($row['uid']);
								
								$this->data[$thix->xmlRoot][] = array(
									'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
										'data' => &$row,
										'userInfo' => &$this->userInfo,
										)),
									);
							}
						} else {
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.findfriends.partner_error');
						}
						break;
					default:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.findfriends.invalid_partner');
						break;
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.findfriends.password_required');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.findfriends.invalid_id');
		}
		
		$this->output();	
	}
	
	/**
	 * 13.1 取得第三方im列表
	 * 
	 * @return
	 */
	public function impartnersAction()
	{
		$this->xmlRoot = 'partners';
		/*
		$this->data[$this->xmlRoot] = array(
			'partner' => array(
				'id' => 'msn',
				'name' => 'MSN',
				'needpassword' => 'true',
				'image_url' => Better_Config::getAppConfig()->base_url.'/images/im/msn.png',
				),
			);*/
		
		$this->output();
	}
	
	/**
	 * 13.10 取第三方应用列表
	 */
	public function allpartnersAction()
	{
		$this->xmlRoot = 'partners';
		
		$usage = $this->getRequest()->getParam('usage', '');
		$lang = $this->lang->toArray();
		
		switch ($usage) {
			case 'findfriends':
			case 'invitefriends':
				$domains = array(
					'sina.com', 'sohu.com', 'tom.com', '163.com', 'yeah.net', 'gmail.com', 
					);
					
				foreach ($domains as $domain) {
					$this->data[$this->xmlRoot][] = array(
						'partner' => $this->api->getTranslator('partner')->translate(array(
							'data' => array(
										'type' => 'email',
										'id' => $domain,
										'domain' => $domain,
										'needpassword' => 'true',
										'name' => $lang['email_domain'][str_replace('.', '_', $domain)]['name'],
										'image_url' => Better_Config::getAppConfig()->base_url.'/images/emails/'.$domain.'.png?ver='.BETTER_VER_CODE,
										),
							)),
						);			
				}
				break;
			case 'bindsns':
				$keys = array(
					'sina', 
					'qq',
					'qqsns',
					'msn',
					'renren', 
					'kaixin001', 
					'douban', 
					'fanfou',
					'facebook',
					'twitter',
					'4sq', 								
					'sohu',
					'163',				 
					'139', 
					'follow5',
					'zuosa', 
					//'bedo',
					);
					
				$ip = Better_Functions::getIp();
				if ($ip != '221.224.52.24')	{
					$k = array_search('qqsns', $keys);
					unset($keys[$k]);
				} 
				
				$sites = $this->user->syncsites()->getSites();

				foreach ($keys as $key) {
					
					if ($key != 'bedo') {
						$domain = $key . '.com';
					} else {
						$domain = 'bedo.cn';
					}
					$bindedName = '';
					if (isset($sites[$domain])) {
						$bindedName = $sites[$domain]['username'];
					}
					
					$_url = '';
					if ( in_array($domain, $this->oauthSNS) ) {
						$get['domain'] = $domain;
						$get['uid'] = $this->uid;
						$get['key'] = md5($this->uid . 'bindoauthsns');						
						$_url = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml?' . http_build_query($get); 
					}
					
					in_array($domain, $this->followkaiSNS) ? $followkai = 'true' : $followkai = 'false';
					in_array($domain, $this->SNS) ? $ff_id = '@'. $domain . '_SNS' : $ff_id = '';
					
					$notice = $lang['sns'][str_replace('.', '_', $domain)]['notice'];
					if ( 'renren.com' == $domain ) {
						$_uid = Better_Registry::get('user')->getUid();
						$clientCache = Better_User::getInstance($_uid)->cache()->get('client');
						$platform = $clientCache['platform'];
						if ( in_array($platform, array(1, 'S60', 's60', 11)) ){
							$notice = '';
						}					
					}
					$_data = array(
							'data' => array(
								'notice' => $notice,
								'id' => $key,
								'findfriend_id' => $ff_id,
								'name' => $lang['sns'][str_replace('.', '_', $domain)]['name'],
								'domain' => $domain,
								'needpassword' => 'true',
								'image_url' => Better_Config::getAppConfig()->base_url.'/images/3rdSite/'.$key.'.gif?ver='.BETTER_VER_CODE,
								'type' => 'sns',
								'binded_name' => $bindedName,
								'auth_url' => $_url,
								'followkai' => $followkai,
								),
							);
					if ('sina.com' == $domain) 	{
						$_sms_no = $this->getSinaSMS();
						$_data['data']['sms_no'] = $_sms_no;
						$_data['data']['sms_content'] = '我正在用#开开#手机客户端玩新浪微博！开始用开开来记录我的足迹啦，随时和你分享“我在哪儿” 以及我的街拍！一起来签到、找附近好友、探索城市！';
						
					}	
					$this->data[$this->xmlRoot][] = array(
						'partner' => $this->api->getTranslator('partner')->translate($_data),
						);
				}
				break;
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.findfriends.invalid_usage');
				break;
		}

		$this->output();		
	}
	
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function getSinaSMS()
	{
		$sms0 = '1069019555610045';
		
		$cache = Better_Cache::local();
		$key = 'sina_sms_no';
		if ( !( $sms = $cache->get($key) ) ) {
			$sms = '';
			$ch = curl_init();
			$url = "http://api.weibo.cn/interface/f/ttt/v3/getwmsmsnum.php?wm=4004_0009";
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$str_xml = curl_exec($ch);
			$info = curl_getinfo($ch);
			if ( $info['http_code'] != 200 ) {
				error_log('sina_sms_no_error');
			} else {
				$xml = new DOMDocument('1.0', 'utf-8');				
				$ok = @$xml->loadXML($str_xml);
				if ($ok) {
					$sms = $xml->getElementsByTagName("cmcc")->item(0)->nodeValue;
					$cache->set($key, $sms, 3600);
				}
			}
						
		}
		
		if ($sms) {
			$r = $sms;
		} else {
			$r = $sms0;
		}
		
		return $r;
	}

	
	/**
	 * 根据电话簿找好友（版本1）
	 * 
	 */
	private function _byphoneVer1()
	{
		$this->needPost();
		$this->xmlRoot = 'users';
	
		$data = $this->getRequest()->getRawBody();

		if ($data) {
			$results = Better_Mobile_Contacts::decrpt($data);

			if (count($results)>0 && isset($results['users']['user'])) {
				$phones = Better_Mobile_Contacts::parse($results, $this->uid);

				$searcher = Better_Search::factory(array(
					'what' => 'user',
					'page' => 1,
					'count' => 9999,
					'keyword' => array_unique($phones),
					'method' => 'mysql',
					));
				$result = $searcher->searchCell();

				foreach ($result['rows'] as $row) {
					if (!$this->user->isFriend($row['uid'])) {
						$this->user->friends()->hasRequest($row['uid']) || $this->user->friends()->request($row['uid']);
						
						$this->data[$this->xmlRoot][] = array(
							'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
								)),
							);
					}
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.findfriends.invalid_result');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.findfriends.invalid_post_data');
		}
		
		$this->output();		
	}
	
	/**
	 * 根据电话簿找好友（版本2）
	 * 
	 */	
	private function _byphoneVer2()
	{
		$this->needPost();
		$this->xmlRoot = 'byphone';
		$this->data[$this->xmlRoot] = array(
			'users' => array(),   // 已经注册开开但不是好友
			'phones' => array(),  // 未注册开开的电话号码
			'emails' => array()   // 未注册开开的email地址
			);
		
		$data = $this->getRequest()->getRawBody();
		$data = str_replace('decode2=false', '', $data);
		
		if ($data) {
			$results = Better_Mobile_Contacts::decrpt($data, true);
			$map = array(); // 按手机号/email建hashmap

			if (count($results)>0 && isset($results['users'])) {
				$cells = $emails = array();
				$f_uids = array($this->userInfo['uid']);
				$f_cells = array($this->userInfo['cell_no']);
				$f_emails = array($this->userInfo['email']);
				foreach ($results['users'] as $u) {
					if (!is_array($u['contact'])) continue;
					foreach ($u['contact'] as $c) {
						if (key_exists('cell_no', $c)) {
							$map[$c['cell_no']] = $u;
							$cells[] = $c['cell_no'];
						} else if (key_exists('email', $c)) {
							$map[$c['email']] = $u;
							$emails[] = $c['email'];
						}
						$data = array(
							'id' => $u['id'],
							'name' => $u['name'],
							'category' => $c['category'],
							'content' => $c['content'],
						);
						Better_Mobile_Contacts::log($this->uid, $data);
					}
				}

				$maxfindfriends = Better_Config::getAppConfig()->api->maxfindfriends;	
				$checknum = 0;
				
				if (count($cells) > 0) {
					$searcher = Better_Search::factory(array(
						'what' => 'user',
						'page' => 1,
						'count' => 50,
						'keyword' => array_unique($cells),
						'method' => 'mysql',
					));
					$result = $searcher->searchCell();
					
					foreach ($result['rows'] as $row) {
						if ($map[$row['cell_no']]) $row['adbname'] = $map[$row['cell_no']]['name'];
						$is_friend = $this->user->isFriend($row['uid']);
						//echo $row['uid'] . ':' . $is_friend . "\n";
						if ($is_friend) {
							if ($row['cell_no']) $f_cells[] = $row['cell_no'];
							continue;
						}
						if ($row['cell_no'] && !in_array($row['cell_no'], $f_cells)) {
							$checknum++;
							if($checknum > $maxfindfriends) break;
							$this->data[$this->xmlRoot]['users'][] = array(
								'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
									'data' => &$row,
									'userInfo' => &$this->userInfo,
								)),
							);
							if ($row['cell_no']) $f_cells[] = $row['cell_no'];
							$f_uids[] = $row['uid'];
						}
					}
				}

				if (count($emails)>0) {
					$searcher = Better_Search::factory(array(
						'what' => 'user',
						'page' => 1,
						'count' => 50,
						'keyword' => array_unique($emails),
						'method' => 'mysql'
					));
					$result = $searcher->searchEmail();
					
					foreach ($result['rows'] as $row) {
						if ($map[$row['email']]) $row['adbname'] = $map[$row['email']]['name'];
						$is_friend = $this->user->isFriend($row['uid']);
						if ($is_friend) {
							if ($row['email']) $f_emails[] = $row['email'];
							continue;
						}
						if ($row['email'] && !in_array($row['email'], $f_emails)) {
							$checknum++;
							if($checknum > $maxfindfriends) break;
							if (!in_array($row['uid'], $f_uids)) {
								$this->data[$this->xmlRoot]['users'][] = array(
									'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
										'data' => &$row,
										'userInfo' => &$this->userInfo,
									)),
								);
							}
							if ($row['email']) $f_emails[] = $row['email'];
							$f_uids[] = $row['uid'];
						}
					}
				}
				$f_uids = array_unique($f_uids);
				$f_cells = array_unique($f_cells);
				$f_emails = array_unique($f_emails);
				//sns好友缓存
				$this->user->cache()->set('findedUids_phone', $f_uids);	
				foreach ($cells as $k=>$v) {
					if (!in_array($v, $f_cells)) {
						$checknum++;
						if($checknum > $maxfindfriends) break;
						$this->data[$this->xmlRoot]['phones'][] = array(
							'phone' => array(
								'id' => $k,
								'no' => $v
								),
							);
					}
				}
				
				foreach ($emails as $k=>$v) {
					if (!in_array($v, $f_emails)) {	
						$checknum++;
						if($checknum > $maxfindfriends) break;
						$this->data[$this->xmlRoot]['emails'][] = array(
							'email' => array(
								'id' => $k,
								'no' => $v
								),
							);
					}
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.findfriends.invalid_result');
			}			
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.findfriends.invalid_post_data');			
		}
		
		$this->output();
	}

	/**
	 * 根据电话簿找好友（版本3）
	 * 
	 */	
	private function _byphoneVer3()
	{
		$this->needPost();
		$this->xmlRoot = 'byphone';
		$this->data[$this->xmlRoot] = array(
			'users' => array(),   // 已经注册开开但不是好友
			'phones' => array(),  // 未注册开开的电话号码
			'emails' => array(),  // 未注册开开的email地址
			'cell_no' => '',      // 绑定手机号码
			'hash' => '',         // 已传地址簿的md5值
		);
		
		$data = $this->getRequest()->getRawBody();
		$data = str_replace('decode2=false', '', $data);

		$this->data[$this->xmlRoot]['cell_no'] = $this->userInfo['cell_no'];
		if ($data) {
			$results = Better_Mobile_Contacts::decrpt($data, true);
			$book = Better_AddressBook::save(array(
				'user' => $this->userInfo,
				'content' => $results['xml'],
				'items' => $results['users'],
			));
		} else {
			$book = Better_AddressBook::get($this->uid);
		}
		$this->data[$this->xmlRoot]['hash'] = $book['data']['hash'];
		$rev_uids = Better_AddressBook::findReversed($this->userInfo);

		$map = array(); // 以手机号/email为key, addbookdt为value的map
		$uids = $cells = $emails = array();
		$f_uids = array($this->userInfo['uid']);
		$f_cells = array($this->userInfo['cell_no']);
		$f_emails = array($this->userInfo['email']);
		foreach ($book['items'] as &$item) {
			if ($item['category'] == 'unknown') continue;
			else if ($item['category'] == 'cell_no') $cells[] = $item['content'];
			else if ($item['category'] == 'email') $emails[] = $item['content'];
			$map[$item['content']] = $item;
		}
		foreach ($rev_uids as &$item) $uids[] = $item['uid'];

		$maxfindfriends = Better_Config::getAppConfig()->api->maxfindfriends;	
		$checknum = 0;

		if (count($uids) > 0) {
			$searcher = Better_Search::factory(array(
				'what' => 'user',
				'page' => 1,
				'count' => 50,
				'keyword' => array_unique($uids),
				'method' => 'mysql'
			));
			$result = $searcher->search();

			foreach ($result['rows'] as $row) {
				$is_friend = $this->user->isFriend($row['uid']);
				if ($is_friend) {
					if ($row['cell_no']) $f_cells[] = $row['cell_no'];
					if ($row['email']) $f_emails[] = $row['email'];
					continue;
				}
				if ($row['email'] && !in_array($row['email'], $f_emails)) {
					$checknum++;
					if($checknum > $maxfindfriends) break;
					$this->data[$this->xmlRoot]['users'][] = array(
						'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
						)),
					);
					if ($row['cell_no']) $f_cells[] = $row['cell_no'];
					if ($row['email']) $f_emails[] = $row['email'];
					$f_uids[] = $row['uid'];
				}
			}
		}

		if (count($cells) > 0) {
			$searcher = Better_Search::factory(array(
				'what' => 'user',
				'page' => 1,
				'count' => 50,
				'keyword' => array_unique($cells),
				'method' => 'mysql',
			));
			$result = $searcher->searchCell();
			foreach ($result['rows'] as $row) {
				if ($map[$row['cell_no']]) $row['adbname'] = $map[$row['cell_no']]['name'];
				$is_friend = $this->user->isFriend($row['uid']);
				if ($is_friend) {
					if ($row['cell_no']) $f_cells[] = $row['cell_no'];
					continue;
				}
				if ($row['cell_no'] && !in_array($row['cell_no'], $f_cells)) {
					$checknum++;
					if($checknum > $maxfindfriends) break;
					if (!in_array($row['uid'], $f_uids)) {
						$this->data[$this->xmlRoot]['users'][] = array(
							'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
							)),
						);
					}
					if ($row['cell_no']) $f_cells[] = $row['cell_no'];
					$f_uids[] = $row['uid'];
				}
			}
		}

		if (count($emails) > 0) {
			$searcher = Better_Search::factory(array(
				'what' => 'user',
				'page' => 1,
				'count' => 50,
				'keyword' => array_unique($emails),
				'method' => 'mysql'
			));
			$result = $searcher->searchEmail();
			
			foreach ($result['rows'] as $row) {
				if ($map[$row['email']]) $row['adbname'] = $map[$row['email']]['name'];
				$is_friend = $this->user->isFriend($row['uid']);
				if ($is_friend) {
					if ($row['email']) $f_emails[] = $row['email'];
					continue;
				}
				if ($row['email'] && !in_array($row['email'], $f_emails)) {
					$checknum++;
					if($checknum > $maxfindfriends) break;
					if (!in_array($row['uid'], $f_uids)) {
						$this->data[$this->xmlRoot]['users'][] = array(
							'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
							)),
						);
					}
					if ($row['email']) $f_emails[] = $row['email'];
					$f_uids[] = $row['uid'];
				}
			}
		}

		$f_uids = array_unique($f_uids);
		$f_cells = array_unique($f_cells);
		$f_emails = array_unique($f_emails);
		
		foreach ($cells as $k => $v) {
			if (!in_array($v, $f_cells)) {
				$checknum++;
				if($checknum > $maxfindfriends) break;
				$cid = key_exists($v, $map) && key_exists('cid', $map[$v]) ? $map[$v]['cid'] : $k;
				$this->data[$this->xmlRoot]['phones'][] = array(
					'phone' => array(
						'id' => $cid,
						'no' => $v
					),
				);
			}
		}
		
		foreach ($emails as $k => $v) {
			if (!in_array($v, $f_emails)) {	
				$checknum++;
				if($checknum > $maxfindfriends) break;
				$cid = key_exists($v, $map) && key_exists('cid', $map[$v]) ? $map[$v]['cid'] : $k;
				$this->data[$this->xmlRoot]['emails'][] = array(
					'email' => array(
						'id' => $cid,
						'no' => $v
					),
				);
			}
		}
		
		// error_log(print_r($book,true),3,'D:/1.txt');
		$this->output();
	}

	/**
	 * 找Email好友版本1
	 */
	private function _byemailVer1()
	{
		$this->xmlRoot = 'users';
		$this->needPost();
		
		$partner = strtolower(trim(urldecode($this->post['partner'])));
		$id = trim(urldecode($this->post['id']));
		$password = $this->post['password'];
		
		if (preg_match('/@/', $partner)) {
			list($a, $domain) = explode('@', $partner);
		} else if (count(explode('.', $partner))>0) {
			$domain = $partner;
		} else {
			switch ($partner) {
				case 'sina':
				case 'sohu':
				case '163':
				case 'tom':
				case '126':
				case 'gmail':
				case 'yahoo':
					$domain = $partner.'.com';
					break;
				case $this->lang->email_domain->sina_com->name:
					$domain = 'sina.com';
					break;
				case $this->lang->email_domain->sohu_com->name:
					$domain = 'sohu.com';
					break;
				case 'yeah':
					$domain = $partner.'.net';
					break;
				case 'yahoo_com_cn':
				case 'yahoo.com.cn':
					$domain = 'yahoo.com.cn';
					break;
				case 'yahoo_cn':
				case 'yahoo.cn':
					$domain = 'yahoo.cn';
					break;
			}
		}
		
		if (Better_Functions::checkEmail($id)) {
			list($id, $foobar) = explode('@', $id);
			$foobar && $domain = $foobar;
		}
		
		$domains = array(
			'@sina.com', 
			'@sohu.com', 
			'@tom.com', 
			'@126.com', 
			'@163.com', 
			'@yeah.net', 
			'@gmail.com', 
			'@yahoo.com', 
			'@yahoo.com.cn', 
			'@yahoo.cn',
			);

		if (Better_Functions::checkEmail($id.'@'.$domain)) {
			
			if ($password!='') {
				if (in_array('@'.$domain, $domains)) {

					$service = new Better_Service_EmailContacts($id, $password, $domain);
					
					$result = $service->search();
					
					if ($result==1) {
						$results = $service->getResults();
						foreach ($results['rows'] as $row) {
							$is_friend = $this->user->isFriend($row['uid']);
							if ($is_friend) continue;
							if ($row['email']!=$id.'@'.$domain && $row['uid']!=$this->uid 
							&& !$this->user->isFriend($row['uid']) && !$this->user->friends()->hasRequest($row['uid'])
							&& !$this->user->isBlocking($row['uid']) && !$this->user->isBlockedBy($row['uid'])
							) {
								$flag = $this->user->friends()->request($row['uid']);
								
								if ($flag>0) {
									$this->data[$this->xmlRoot][] = array(
										'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
											'data' => &$row,
											'userInfo' => &$this->userInfo,
											)),
										);			
								}					
							}
						}
					} else if ($result==2) {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.findfriends.login_failed');
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.findfriends.partner_error');
					}

				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.findfriends.invalid_partner');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.findfriends.password_required');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.findfriends.invalid_id');
		}
		
		$this->output();			
	}
	
	private function _byemailVer2()
	{
		$this->xmlRoot = 'byemail';
		$this->needPost();
		
		$this->data[$this->xmlRoot] = array(
			'users' => array(),
			'emails' => array(),
			);
		
		$partner = strtolower(trim($this->post['partner']));
		$id = strtolower(trim($this->post['id']));
		$password = $this->post['password'];
		if (strpos($partner, '_sns') !== false) {
			$partner = str_replace('_sns', '', $partner);
			$this->_bysns($partner, $id, $password);
			exit;
		}
		
		if (preg_match('/@/', $partner)) {
			list($a, $domain) = explode('@', $partner);
		} else if (count(explode('.', $partner))>0) {
			$domain = $partner;
		} else {
			switch ($partner) {
				case 'sina':
				case 'sohu':
				case '163':
				case 'tom':
				case '126':
				case 'gmail':
				case 'yahoo':
					$domain = $partner.'.com';
					break;
				case $this->lang->email_domain->sina_com->name:
					$domain = 'sina.com';
					break;
				case $this->lang->email_domain->sohu_com->name:
					$domain = 'sohu.com';
					break;
				case 'yeah':
					$domain = $partner.'.net';
					break;
				case 'yahoo_com_cn':
				case 'yahoo.com.cn':
					$domain = 'yahoo.com.cn';
					break;
				case 'yahoo_cn':
				case 'yahoo.cn':
					$domain = 'yahoo.cn';
					break;
			}
		}
		
		if (Better_Functions::checkEmail($id)) {
			list($id, $foobar) = explode('@', $id);
			$foobar && $domain = $foobar;
		}
		
		$domains = array(
			'@sina.com', 
			'@sohu.com', 
			'@tom.com', 
			//'@126.com', 
			'@163.com', 
			'@yeah.net', 
			'@gmail.com', 
			'@yahoo.com', 
			'@yahoo.com.cn', 
			'@yahoo.cn',
			);

		if (Better_Functions::checkEmail($id.'@'.$domain)) {
			
			if ($password!='') {
				if (in_array('@'.$domain, $domains)) {

					$service = new Better_Service_EmailContacts($id, $password, $domain);
					$result = $service->search();
					$finded = array();
					
					 if ($result==2) {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.findfriends.login_failed');
					} else {
						$maxfindfriends = Better_Config::getAppConfig()->api->maxfindfriends;				
						$checknum = 0;
						$results = $service->getResults();
						is_array($results['rows']) || $results['rows'] = (array)$results['rows'];
						
						$finded_uids = array();
						foreach ($results['rows'] as $row) {
							$is_friend = $this->user->isFriend($row['uid']);
							if ($is_friend) continue;
							
							$finded_uids[] = $row['uid'];
							
							if (strtolower($row['email'])!=$id.'@'.$domain && $row['uid']!=$this->uid) {
								$checknum++;
								if($checknum>$maxfindfriends){
									break;
								}								
								$finded[] = strtolower($row['email']);
								$this->data[$this->xmlRoot]['users'][] = array(
									'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
										'data' => &$row,
										'userInfo' => &$this->userInfo,
										)),
									);										
							}
						}
						
						//email好友缓存
						$this->user->cache()->set('findedUids_email', $finded_uids);		
						
						$all = (array)Better_Cache::remote()->get('email_contacts_'.$this->uid);
						
						$emails = array_diff($all, $finded);
						if (count($emails)>0) {	
							foreach ($emails as $email) {
								if (Better_Functions::checkEmail($email) && strtolower($email)!=$id.'@'.$domain) {
									$checknum++;
									if($checknum>$maxfindfriends){
										break;
									}
									$this->data[$this->xmlRoot]['emails'][] = array(
										'email' => $email
										);
								}
							}
						}
					}

				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.findfriends.partner_invalid');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.findfriends.password_required');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.findfriends.invalid_id');
		}
		
		$this->output();			
	}
	
	/**
	 * 
	 * @param $uid
	 * @param $protocol
	 * @return unknown_type
	 */
	public function __getThirdInfo($uid, $protocol)
	{		
		$refusername = $refimageurl = '';
		$syncSites = (array)Better_User_Syncsites::getInstance($uid)->getSites();
		$_info = $syncSites[$protocol];
		$service_new = Better_Service_PushToOtherSites::factory($protocol, $_info['username'], $_info['password'], $_info['oauth_token'], $_info['oauth_token_secret']);
	
		if ('sina.com' == $protocol) {
			$logined = $service_new->verify_credentials();
		} else {
			$logined = $service_new->fakeLogin();
		}			

		if ( $logined && $service_new->userinfo_json) {
			$refusername = $service_new->userinfo_json->name;
			$refimageurl = $service_new->userinfo_json->profile_image_url;
		}
		return array($refusername, $refimageurl);		
	}
	
	
	/**
	 * 
	 */
	public function __getMsnInfo($email)
	{
		$refusername = $refimageurl = '';
		
		$contacts = Better_Registry::get('contacts');
		foreach ($contacts as $v){
			if ($v['email'] == $email){
				$refusername = $v['nickname'];
				break;
			}
		}
		
		return array($refusername, $refimageurl);
	}
	
	
	/**
	 * 找社区好友
	 */
	public function _bysns($partner, $id, $password)
	{
		$bind = isset($this->post['bind'])? ($this->post['bind']=='true' ? true: false) : false; // 是否同时绑定到sns
		
		$partner = str_replace('@', '', $partner);
		
		$sns = $this->SNS;
		$sns = array_merge($sns, array('qq.com', 'msn.com'));
		if ( !in_array($partner, $sns) ) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.findfriends.invalid_id');
			$this->output();			
		}
		
		$oauth_token = $oauth_token_secret = '';
		if ( ( $id && !$password ) || ('sina.com' == $partner && !$password ) ) {
			$syncSites = (array)Better_User_Syncsites::getInstance($this->uid)->getSites();
			$sync_keys = array_keys($syncSites);
			if (in_array($partner, $sync_keys)) {
				$id = $syncSites[$partner]['username'];
				$password = $syncSites[$partner]['password'];
				$oauth_token = $syncSites[$partner]['oauth_token'];
				$oauth_token_secret = $syncSites[$partner]['oauth_token_secret'];
			}			
		}
		
		$this->data[$this->xmlRoot] = array(
			'users' => array(),
			);		
			
		$partner = str_replace('_sns', '', $partner);
		
		//同时绑定sns
		$bind_result = $msg = '';
		if($bind){
			$service = Better_Service_PushToOtherSites::factory($partner, $id, $password);
			$ck = $service->checkAccount($this->uid, $partner, $id);
			
			if ($ck) {
				$logined = $service->fakeLogin();
				if ($logined) {
					$_accecss_token = $_accecss_token_secret = '';
					if ( 'sina.com' == $partner ) {
						$_accecss_token = $service->_accecss_token;	
						$_accecss_token_secret = $service->_accecss_token_secret;						
					}
					$this->user->syncsites()->add($partner, $id, $password, $_accecss_token, $_accecss_token_secret, $service->tid);
					$bind_result = 'true';
					$msg = '绑定成功';					
				} else {
					$bind_result = 'false';
					$msg = '第三方登录失败';
				}						
			} else {
				$logined = 2;//重复绑定
				$bind_result = 'false';
				$msg = '这个帐号已经被其他用户绑定过了';
			}
		}
		$this->data[$this->xmlRoot]['bindsns']['result'] = $bind_result;										
		$this->data[$this->xmlRoot]['bindsns']['msg'] = $msg;	
		
		$service = new Better_Service_SnsContacts($id, $password, $partner, $oauth_token, $oauth_token_secret);
		$result = $service->search();	
		$finded = array();
		
		if ($result == 2) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.findfriends.login_failed');
		} else {
			$maxfindfriends = Better_Config::getAppConfig()->api->maxfindfriends;				
			$checknum = 0;
			$results = $service->getResults();
			is_array($results['rows']) || $results['rows'] = (array)$results['rows'];
			
			$finded_uids = array();
			foreach ($results['rows'] as $row) {
				$is_friend = $this->user->isFriend($row['uid']);
				if ($is_friend) continue;
				
				$finded_uids[] = $row['uid'];
				
				if (strtolower($row['email']) != $id  && $row['uid'] != $this->uid) {
					$checknum++;
					if($checknum>$maxfindfriends){
						break;
					}								
					$finded[] = strtolower($row['email']);
					
					if ($partner == 'msn.com') {
						$refusername = $row['msn_nickname'];
						//error_log('msn:' . $refusername);
						$refimageurl = '';
					} else {
						list($refusername, $refimageurl) = $this->__getThirdInfo($row['uid'], $partner);
					}
					
					if ($refusername) $row['nickname'] =  $row['username'] = $refusername;
					if ($refimageurl) $row['avatar_normal'] = $refimageurl;
					
					$this->data[$this->xmlRoot]['users'][] = array(
						'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							)),
						);
						
										
				}
			}

			//sns好友缓存
			$this->user->cache()->set('findedUids_sns', $finded_uids);		
		}		
		
		
		$this->output();
	}
}