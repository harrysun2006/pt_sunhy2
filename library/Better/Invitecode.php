<?php
/**
 * 邀请码
 */	

class Better_Invitecode{
		
		private static $instance = null;
		
		public static function getInstance(){
			if(self::$instance==null){
				self::$instance = new self();
			}
			return self::$instance;
		} 
		
		
		public function createInvitecode($count=10){

			for($i=0;$i<$count;$i++) {
				$code = self::genCode();
				Better_DAO_Invitecode::getInstance()->insert(array('code'=>$code, 'enable'=>'1'));
			}
		}
		
		
		public function deleteInvitecode($code){
			return Better_DAO_Invitecode::getInstance()->delete($code,'code');
		}
		
		
		public function exists($code){
			$return = Better_DAO_Invitecode::getInstance()->find(array('code'=>$code));

			return $return;
		}
		
		
		public function getOneCode(){
			return Better_DAO_Invitecode::getInstance()->getOne();
		}
		
		public static function genCode()
		{
			$cnt = 0;
			$code = self::_genCode();
			
			return $code;
		}
				
		protected static function _genCode()
		{
			$seq = Better_DAO_InviteSequence::getInstance()->get();
			$tmp = $seq^intval(Better_Config::getAppConfig()->user->id_const);
			$code = '';
			for ($i=(strlen($tmp)-1);$i>=0;$i--) {
				$code .= substr($tmp, $i,1);
			}
			$code = $code.rand(10,99);
			
			return $code;
		}
		
	}

?>