<?php

/**
 * feedback 处理类
 * @author yanglei
 *
 */
class Better_Feedback {
	
	public static function insertFeedback($data){
		//	新增发送email
		//Better_Email_Feedback::send($data);
		
		return Better_DAO_Feedback::getInstance()->insert($data);
	}
}

?>