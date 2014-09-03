<?php
/**
 *
 * @author leip
 * @version 
 */

/**
 * ParseBlogRow helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_ParseBlogRow {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	protected $jsLang = null;
	
	/**
	 *  
	 */
	public function ParseBlogRow($row, $tbl='followings') {
		switch ($row['type']) {
			case 'tips':
				$html = $this->parseTipsRow($row, $tbl);
				break;
			case 'checkin':
				$html = $this->parseCheckinRow($row, $tbl);
				break;
			default:
				$html = $this->parseShoutRow($row, $tbl);
				break;
		}
		
		return $html;
	}
	
	protected function parseBlogIcon($type)
	{
		switch ($type) {
			case 'checkin':
				$icon = 'checkin16.png';
				break;
			case 'tips':
				$icon = 'tips16.png';
				break;
			default:
				$icon = 'shout16.png';
				break;
		}
		
		return $icon;
	}
	
	protected function parseShoutRow(&$row, $tbl='followings') {
		$protected = $row['priv']=='public' ? '0' : '1';
		$bid = $row['bid'];
		$bidKey = str_replace('.', '_', $bid);
		$icon = $this->parseBlogIcon($row['type']);

		//if (uid!=betterUser.uid && withHisFuncLinks==true && ((priv=='public' && userProtected==false) || $.inArray(uid, betterUser.friends)>=0)) {
		$rt = $this->view->lang->javascript->blog->rt;
		$fav = $this->view->lang->javascript->global->favorite->title;
		
		$locationTips = $this->locationTips($row);
		$source = $this->source($row);
		$message = $this->parseMessage($row);

$html=<<<EOF
 <tr protected="{$protected}" bid="{$bid}" tblid="{$tbl}" priv="{$row['priv']}" uid="{$row['uid']}" id="listRow_{$tbl}_{$bidKey}" class="listRow"> 
 <td class="avatar icon">
 	<a href="/{$row['username']}#"><img width="48" alt="" src="{$row['avatar_thumb']}" class="avatar pngfix" onerror="this.src=Better_AvatarOnError"></a>
 </td>
 <td class="info"> 
 	<div class="text"></div> 
 	<div class="status message_row"><img class="pngfix" src="images/{$icon}"> <a username="{$row['username']}" id="nickname_{$bidKey}" class="user" href="/{$row['username']}#">gmail</a> <span class="message_row" id="message_{$bidKey}">{$message}</span> </div> 
 	<div class="ext">
 		<span id="blogListRowFuncDiv_{$tbl}_{$bidKey}" class="action hide listRowFuncs" style="display: none;">
 			<a href="javascript:void(0);">{$rt}</a> 
 			<a id="favoritesFuncA_{$tbl}_{$bidKey}" href="javascript:void(0)">{$fav}</a> 
 		</span>
 		<span id="listRowAddress_{$bidKey}" class="time"> 
 			{$locationTips} {$source}
 		</span>
 	</div> 
 </td> 
 <td style="width:50px;"> 
 </td> 
 </tr> 
EOF;
		return $html;
	}
	
	protected function parseCheckinRow(&$row) {
		return $this->parseShoutRow($row);
	}
	
	protected function parseTipsRow(&$row) {
		return $this->parseShoutRow($row);
	}
	
	protected function parseMessage(&$row) {
		$txt = trim($row['message']);
		if ($txt!='') {
			$txt = nl2br($txt);
			$txt = Better_Blog::parseBlogAt($txt);
		} else if ($txt=='' && $row['attach_thumb']) {
			$txt = $this->jsLang->blog_with_photo_no_message.' ';
		} else if ($txt=='' && $row['upbid']) {
			$txt = $this->jsLang->global->blog->rt;
		}
		
		if ($row['type']!='tips') {
			if ($row['priv']=='private') {
				$txt .= '<span style="color:#f09800; font-weight: bold;">('.$this->jsLang->global->priv->screat.')</span>';
			} else if ($row['priv']=='protected') {
				$txt .= '<span style="color:#44c8e9; font-weight: bold;">('.$this->jsLang->global->priv->friend.')</span>';
			}
		}
		
		$pat = '/((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w]+\.([\.a-zA-Z0-9]+)((\?|\/|:)+[\w\.\/=\?%\-&~`@\':+!#]*)*)/ies';
		$txt = preg_replace($pat, '\$this->replaceHttpLink(\1)', $txt);
		
		return $txt;
	}
	
	protected function replaceHttpLink($match) {
		/*
		$pat = '/'.$_SERVER['SERVER_NAME'].'/';
		if (preg_match($pat, $match)) {
			$link = '<a href="'.$match.'" target="_blank">'.$match.'</a>';
		} else {
			$link = $match;
		}*/
		
		$link = ' <a href="'.$match.'" target="_blank">'.$match.'</a> ';
		
		return $link;
	}
	
	protected function source(&$row) {
		$url = '';
		
		$source = $this->view->lang->javascript->global->blog->by;
		
		switch (strtolower(trim($row['source']))) {
			case 'ppc':
			case 'wm':
			case 'win':
				$url = 'wmdownload';
				$source .= $this->jsLang->global->blog->source->win;
				break;
			case 's60':
				$url = 's60download';
				$source .= $this->jsLang->global->blog->source->s60;
				break;
			case 'uiq':
				$source .= $this->jsLang->global->blog->source->uiq;
				break;
			case 'spn':
				$source .= $this->jsLang->global->blog->source->spn;
				break;
			case 'brw':
				$source .= $this->jsLang->global->blog->source->brw;
				break;
			case 'plm':
				$source .= $this->jsLang->global->blog->source->plm;
				break;
			case 'j2m':
				$source .= $this->jsLang->global->blog->source->j2m;
				break;
			case 'blackberry':
				$url = 'blackberrydownload';
				$source .= $this->jsLang->global->blog->source->blackberry;
				break;
			case 'ifn':
			case 'iphone':
				$url = 'iphonedownload';
				$source .= $this->jsLang->global->blog->source->ifn;
				break;
			case 'and':
				$url = 'androiddownload';
				$source .= $this->jsLang->global->blog->source->and;
				break;
			case 'msn':
				$source .= $this->jsLang->global->blog->source->msn;
				break;
			case 'mobile':
				$source .= $this->jsLang->global->blog->source->cell;
				break;
			case 'sms':
				$source .= $this->jsLang->global->blog->source->sms;
				break;
			case 'mms':
				$source .= $this->jsLang->global->blog->source->mms;
				break;
			case 'java':
				$source .= $this->jsLang->global->blog->source->java;
				break;
			case 'better':
			case 'kai':
			case 'web':
				$source .= $this->jsLang->global->blog->source->web;
				break;
			default:
				$source .= $row['source'];
				break;
		}
		
		if ($url) {
			$source = '<a href="/tools/'.$url.'" class="place">'.$source.'</a>';
		} else {
			$source = '<span class="place">'.$source.'</span>';
		}
		
		return $source;
	}
	
	protected function locationTips(&$row) {
		$lon = $row['lon'];
		$lat = $row['lat'];
		$time = $row['dateline'] ? $row['dateline'] :  ($row['time'] ? $row['time'] : '');
		$poi = $row['poi'] ? $row['poi'] : array();
		$isUser = $row['is_user'] ? (bool)$row['is_user'] : false;
		$result = $row['use_poi']==0 ? '' : $row['location_tips'];
		
		if ($poi['poi_id'] && $result!='') {
			$result = str_replace('{POI}', '<a href="/poi/'.$poi['poi_id'].'" class="place">'.$result.'</a> - ', $this->view->lang->javascript->noping->better->locationtips);
		} else if ($isUser && $poi['poi_id'] && $result=='') {
			$result = str_replace('{POI}', '<a href="/poi/'.$poi['poi_id'].'" class="place">'.$poi['city'].' '.$poi['name'].'</a> - ', $this->view->lang->javascript->noping->better->locationtips);
		} else {
			$result = str_replace('@{POI}', '', $this->view->lang->javascript->noping->better->locationtips);
		}

		if ($time) {
			$result = str_replace('{TIME}', $this->compareTime($time), $result);	
		} else {
			$result = str_replace('{TIME}', '', $result);
		}
		
		return $result;
	}

	protected function compareTime($time1, $time2=null) {
		$time2 || $time2 = time();
		
		$oneMinute = 60;
		$oneHour = $oneMinute*60;
		$oneDay = $oneHour*24;
		$oneWeek = $oneDay*7;
		
		$offset = $time2 - $time1;
		
		if ($offset<$oneMinute) {
			$str = $this->view->lang->javascript->date->justnow;
		} else if ($offset<$oneHour) {
			$str = intval($offset/$oneMinute).$this->view->lang->javasrcipt->date->minute.$this->view->lang->javascript->date->before;
		} else if ($offset<$oneDay) {
			$str = intval($offset/$oneHour).$this->view->lang->javascript->date->hour.$this->view->lang->javascript->date->before;
		} else if ($offset<$oneWeek) {
			$str = intval($offset/$oneDay).$this->view->lang->javascript->date->day.$this->view->lang->javascript->date->before;
		} else {
			$year = date('Y', $time1);
			$month = date('m', $time1);
			$day = date('d', $time1);

			$str = str_replace('{YEAR}', $year, $this->jsLang->noping->global->ddyymm);
			$str = str_replace('{MONTH}', $month, $str);
			$str = str_replace('{DAY}', $day, $str);
		}
		
		return $str;
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
		
		$this->jsLang = $view->lang->javascript;
	}
}
