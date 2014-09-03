<?php

/**
 * 获得勋章的人
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_BadgeController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init();	
	}		
	
	public function indexAction()
	{
		$badgeId = (int)$this->getRequest()->getParam('id', 0);
		
		$output = Better_Badge::getBadgeUsers($badgeId, $this->page, 11);
		foreach ($output as $k=>$v) {
			$this->output[$k] = $v;
		}
		
		$this->output['rows'] = Better_Output::filterUsers($this->output['rows']);
		
		$this->output();
	}
	
	/**
	 * 勋章DIV
	 * @return unknown_type
	 */
	public function getdivAction()
	{
		$badgeId = (int)$this->getRequest()->getParam('id', 0);
		$UserId = (int)$this->getRequest()->getParam('uid', '172470');
		$_isyou = $this->user->uid == $UserId ? true : false;
		
		$userObject =  Better_User::getInstance($UserId);
		$userInfo = $userObject->getUserInfo();
		
		$badgeAll = $userObject->badge()->getMyBadges();;
		$badge = $badgeAll[$badgeId];	

		$time = $_COOKIE["offset_time"];
		$btime = date('Y-m-d H:i:s', intval($badge['get_time'])+intval($time)) . ' ' . $this->lang->global->got_badge;
		$tmpb .= <<<EOF
<div id="bigbadge_{$badge['id']}" style="width: 690px; height: 419px; padding: 15px 20px 0 20px;!padding: 7px 10px 0 10px; background-color: #fff;">
    <div style='height:330px;'>
    <ul>
    	<li class="left badge_big_icon">
    		<img src="{$badge['badge_big_picture']}" class="pngfix" />
    	</li>
    	<li class="left badge_info">
    		<div style="font-size: 25px; color: #333; border-bottom: 2px solid #1db8ee; padding-bottom: 10px; !padding-bottom: 5px;font-weight: bold; line-height: 40px;">{$badge['badge_name']}</div>
    		<div style="font-size: 14px; color: #7589AE; margin-top: 5px; font-weight: bold; line-height:30px; word-wrap: break-word;"><span>{$badge['got_tips']}</span></div>
    		
    		<div style="margin-top: 30px;">
    			<div class="left" style="margin-right: 12px;">
    				<img height="52" width="52" style="padding: 2px; border: 1px solid #dde1e0;" src="{$userInfo['avatar_url']}" />
    			</div>
    			<div class="left" style="font-size: 14px; font-weight: bold; padding: 0 0;width:250px;">
    				<span style="color: #0F7CC5; font-family: arial; font-weight: bold;">{$userInfo['nickname']}</span>
    				<span style="color: #7589AE;">@{$badge['poi']['city']} {$badge['poi_name']}</span>
    				<br>
    				<span style="font-size: 12px;">{$btime}</span>
    			</div>
    			<div class="clearfix"></div>
    		</div>	    								
EOF;
if ($_isyou && $badge['total']) {
	$bMsg = $badge['exchanged'] ? $this->lang->javascript->global->badge_exchanged : $this->lang->javascript->global->badge_not_exchanged;
	$tmpb .= "
	<div style='padding-top:10px;color:#aaa;'>{$bMsg}</div>
	";
}
		$tmpb .= <<<EOF
    	</li>
    </ul>
    </div>
	<div class='badge_users'>
    	<div class="badge_users_title">{$this->lang->javascript->badge_users}</div>
    	<table width='100%' cellspacing='0'>
    			<tr><td width='20' id='badge_users_prev_{$badge['id']}_{$userInfo['uid']}' valign="middle"><a href='javascript:void(0);' class='badge_users_page' bid='{$badge['id']}' pf='badge_users_' direct='prev' uid='{$userInfo['uid']}' onClick="_prevBadgeUser('{$badge['id']}', '{$UserId}');"><img src="images/badge_left.jpg" /></a></td>
    			<td>
    				<table width="100%">
    				<tbody>
    					<tr id='badge_users_{$badge['id']}_{$userInfo['uid']}'></tr>
    				</tbody>
    				</table>
    			</td>
    			<td width='20' id='badge_users_next_{$badge['id']}_{$userInfo['uid']}' valign="middle"><a href='javascript:void(0);' class='badge_users_page' bid='{$badge['id']}' pf='badge_users_' direct='next' uid='{$userInfo['uid']}' onClick="_nextBadgeUser('{$badge['id']}', '{$UserId}');"><img src="images/badge_right.jpg" /></a></td></tr>
    	</table>
    	<span id='badge_users_page_{$badge['id']}_{$userInfo['uid']}' style='display:none;'>1</span>
    </div>	    					
</div>
EOF;
		
		if (!headers_sent() && !@ini_get('zlib.output_compression') && strstr($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip')) {
			@ob_start('gz_handler');	
		}		
		echo $tmpb;exit();
	}
}