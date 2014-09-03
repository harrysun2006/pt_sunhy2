<?php

/**
 * 勋章基类
 * 
 * @package Better.Badge
 * @author leip <leip@peptalk.cn>
 * 
 */

abstract class Better_Badge_Base
{
	protected $badgeId = 0;
	protected $user = null;
	protected $params = array();
	
	public function __construct($badgeId)
	{
		$this->badgeId = $badgeId;
	}

	public function __set($var, $val=null)
	{
		$this->params[$var] = $val;
	}
	
	public function __get($var)
	{
		return isset($this->params[$var]) ? $this->params[$var] : null;
	}
	
	/**
	 * 设置勋章参数
	 * 
	 * @return null
	 */
	public function setParams(array $params)
	{
		$this->params = $params;
		if ($this->params['id']) {
			$this->params['badge_big_picture'] = Better_Config::getAppConfig()->base_url.'/images/badges/big/'.$this->params['badge_picture'];
			$this->params['badge_picture'] = Better_Config::getAppConfig()->base_url.'/images/badges/'.$this->params['badge_picture'];
			
			$this->params['badge_name'] = Better_Language::loadDbKey('badge_name', $this->params);
			$this->params['got_tips'] = Better_Language::loadDbKey('got_tips', $this->params);
		}		
	}
	
	/**
	 * 获取勋章参数
	 * 
	 * @return array
	 */
	public function &getParams()
	{
		if (count($this->params)==0) {
			$this->params = Better_DAO_Badge::getInstance()->get(array(
				'id' => $this->badgeId
				));
			if ($this->params['id']) {
				$this->params['badge_big_picture'] = Better_Config::getAppConfig()->base_url.'/images/badges/big/'.$this->params['badge_picture'];
				$this->params['badge_picture'] = Better_Config::getAppConfig()->base_url.'/images/badges/'.$this->params['badge_picture'];
				$this->params['badge_name'] = Better_Language::loadDbKey('badge_name', $this->params);
				$this->params['got_tips'] = Better_Language::loadDbKey('got_tips', $this->params);		
			}
		}
		
		return $this->params;
	}	

	/**
	 * 检测用户是否满足获得当前勋章的条件
	 * 
	 * @param $user
	 * @return bool
	 * 
	 */
	public function touch(array $cond)
	{
		$result = false;
		$uid = (int)$cond['uid'];
		$now = time();
		$btm = $this->params['btm'] >0 ? $this->params['btm'] : $now;
		$etm = $this->params['etm'] >0 ? $this->params['etm'] : $now;
		$condition = $this->params['condition'];
		//Better_Log::getInstance()->logInfo($this->params['class_name']."--".$condition,'diybadge');
		if($now>=$btm && $now<=$etm) {
			
			if($condition){
				$params = $this->params;
				foreach ($cond as $k=>$v) {
					$params[$k] = $v;
				}	
				$result = self::diytouch($condition,$params);
			} else {
				$className = 'Better_DAO_Badge_Calculator_'.$this->params['class_name'];			
				if ($uid && class_exists($className)) {
					$params = $this->params;
					foreach ($cond as $k=>$v) {
						$params[$k] = $v;
					}					
					$result = call_user_func($className.'::touch', $params);
				}
			}
		}	
		return $result;
	}
	/*
	 * 自助勋章	
	*/
	public function diytouch($condition,$params){
		//Better_Log::getInstance()->logInfo("result:\n".serialize($condition)."\n".serialize($params),'gotbadge');
		$result = false;	
		/*	
		$todo = str_replace("CC","$cc->",$condition);
		$result = eval('return ".$todo.";");	
		*/
	
		$cc = new Better_User_Diybadge($params);		
		$todo = str_replace('CC::','$cc->',$condition);	
		Better_Log::getInstance()->logInfo($todo,'diybadge');
		try{
			$result =  eval("return ".$todo.";");	
		} catch(Exception $e){
			Better_Log::getInstance()->logInfo($todo."--\n".$e,'diybadgeerror');
		}
		return $result;
	}
	
	
	
	
	
	
}