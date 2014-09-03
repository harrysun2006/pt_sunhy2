<?php
/*
	139移动互联提供的访问Rest API的封装类.
*/
define('MIOP_API_VALIDATION_ERROR', 1);

class Better_Miop_Main {
	public $app_key;
	public $app_secret;

	public $api_client;

	public $mi_params;
	public $user;
	public $session_key;
	public $time;

	public function __construct($app_key, $app_secret,$session) {
		$this->app_key    = $app_key;
		$this->app_secret  = $app_secret;
		$this->validate_miop_params();
		if( $session )
			$this->session_key = $session;
		$this->api_client = new Better_Miop_Client($app_key, $app_secret, $this->session_key, $this->user,$this->time);
	}

	public function validate_miop_params() {
		$this->mi_params = $this->get_valid_miop_params($_POST, $this->app_secret ,'mi_sig');
		if (!$this->mi_params) {
			foreach($_POST as $k => $v) {
				if(strpos($k,'mi_sig'))
				{
					$this->real_login();
					exit;
				}
			}
			$this->mi_params = $this->get_valid_miop_params($_GET, $this->app_secret,'mi_sig');
		}
		if ($this->mi_params) {
			$user        = isset($this->mi_params['user'])        ? $this->mi_params['user'] : null;
			$session_key = isset($this->mi_params['session_key']) ? $this->mi_params['session_key'] : null;
			$time     = isset($this->mi_params['time'])     ? $this->mi_params['time'] : null;
			$this->set_user($user, $session_key, $time);
		} else { //通过GET方式传递参数,并且COOKIE中存储当前用户的信息. 
			foreach($_GET as $k => $v) {
				if(strpos($k,'mi_sig')) {
					$this->real_login();
					exit;
				}
			}
			if (!empty($_COOKIE) && $cookies = $this->get_valid_miop_params($_COOKIE, $this->app_secret,$this->app_key)) {
				$this->set_user($cookies['user'], $cookies['session_key'],$cookies['time']);
			}
		}

		return !empty($this->mi_params);
	}

	public function get_valid_miop_params($params,$app_secret,$namespace='mi_sig') {
		$prefix = $namespace . '_';
		$prefix_len = strlen($prefix);
		$mi_params = array();
		foreach ($params as $name => $val) {
			if (strpos($name, $prefix) === 0) {
				$mi_params[substr($name, $prefix_len)] = self::no_magic_quotes($val);
			}
		}
		if (!isset($params[$namespace]) || !$this->verify_signature($mi_params, $app_secret,$params[$namespace])) {
			return array();
		}
		return $mi_params;
	}

	public function generate_signature($params_array, $app_secret) {
		$str = '';

		ksort($params_array);
		foreach ($params_array as $k=>$v) {
			$str .= "$k=$v";
		}
		$str .= $app_secret;
		return md5($str);
	}

	public function set_user($user, $session_key, $time) {
		$cookies = array();
		$cookies['user'] = $user;
		$cookies['session_key'] = $session_key;
		$cookies['time'] = $time;
		$sig = $this->generate_signature($cookies, $this->app_secret);

		//表明不同网站间COOKIE的信任关系
		header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');

		foreach ($cookies as $name => $val) {
			setcookie($this->app_key . '_' . $name, $val,0,"/");
			$_COOKIE[$this->app_key . '_' . $name] = $val;
		}
		setcookie($this->app_key, $sig,0,"/");
		$_COOKIE[$this->app_key] = $sig;
		$this->user = $user;
		$this->api_client->session_key = $session_key;
		$this->session_key=$session_key;
		$this->time = $time;
	}

	/**
		去除'/'
	 */
	public function no_magic_quotes($val) {
		if (get_magic_quotes_gpc()) {
			return stripslashes($val);
		} else {
			return $val;
		}
	}

	public function verify_signature($mi_params, $app_secret,$expected_sig) {
		$client_sig = $this->generate_signature($mi_params, $app_secret);
		//print_r( "<br/>".$client_sig."<br/>".$expected_sig."<br/>" );
		return $this->generate_signature($mi_params, $app_secret) === $expected_sig;
	}

	public function redirect($url) {
		if (preg_match('/^https?:\/\/([^\/]*\.)?139\.com(:\d+)?/i', $url)) {
			// make sure 139.com url's load in the full frame so that we don't
			// get a frame within a frame.
			echo "<script type=\"text/javascript\">\nwindow.location.href = \"$url\";\n</script>";
		} else {
			header('Location: ' . $url);
		}
		exit;
	}

	public function get_user() {
		return $this->user;
	}

	public function get_current_url() {
		return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	public function require_login() {
		if ($user = $this->get_user()) {
			return $user;
		}
		$this->redirect($this->get_login_url(self::get_current_url()));

	}

	public function real_login() {
		$this->redirect($this->get_login_url(self::get_current_url()));    
	}

	public function get_app_login_url($subdomain='app') {
		return 'http://' . $subdomain . '.139.com';
	}

	public function get_add_app_url($return_url=null) {
		return self::get_app_login_url().'/addapp.php?app_key='.$this->app_key .
			($return_url ? '&return=' . urlencode($return_url) : '');
	}

	public function get_login_url($return_url) {
		return self::get_app_login_url().'/login.php?v=1.0&app_key=' . $this->app_key . ($return_url ? '&return=' . urlencode($return_url)  : '');
	}

}

?>
