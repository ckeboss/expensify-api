<?php

class ExpensifyApi {
	
	public $api_url = 'https://www.expensify.com/api';
	private $username, $password, $login_cookie;
	
	public function __construct($username = null, $password = null, $login_cookie = false) {
		if(empty($username) || empty($password)) {
			throw new Exception('You must provide a username and password.');
		}
		
		$this->username = $username;
		$this->password = $password;
		
		if(!$login_cookie) {
			if(!is_writable($_SERVER['DOCUMENT_ROOT'].'/cookies/login_cookie')) {
				$fp = fopen($_SERVER['DOCUMENT_ROOT'].'/cookies/login_cookie', 'wb');
				if(!$fp) {
					throw new Exception('Could not create new cookie file in '.$_SERVER['DOCUMENT_ROOT'] . '/cookies/login_cookie');
				}
				fclose($fp);
				
				$login_cookie = $_SERVER['DOCUMENT_ROOT'].'/cookies/login_cookie';
			}
		} else if(!is_writable($login_cookie)) {
			throw new Exception('Cookie file '.$login_cookie.' could not be found or is not writable.');
		}
		
		$this->login_cookie = $login_cookie;
	}
	
	private function authenticate()
	
	public function read_data($command, $params = array(), $set_cookie = false, $limit = 251, $offest = 0, $sortBy = '') {

		//set POST variables
		$url = 'https://www.expensify.com/api';
		$fields = array(
		/* 						'states' => '', */
		/* 						'submitterEmail' => '', */
								'limit' => $limit,
								'offset' => $offest,
								'sortBy' => $sortBy,
		/* 						'sortOrder' => '', */
								'command' => $command,
		/* 						'pageName' => 'reports', */
		/* 						'callerName' => 'anonymous', */
		/* 						'tabID' => '4944', */
		/* 						'dialogName' => '', */
						);
		if(!empty($params)) {
			$fields = array_merge($fields, $params);
		}
		
		//url-ify the data for the POST
		$fields_string = '';
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		$fields_string = rtrim($fields_string, '&');
		
		//open connection
		$ch = curl_init();
		
		curl_setopt( $ch, CURLOPT_COOKIESESSION, true );
		if($set_cookie) {
			curl_setopt( $ch, CURLOPT_COOKIEJAR, APP.'tmp'.DS.'cookies'.DS.'testing_cookie');
		}
		curl_setopt( $ch, CURLOPT_COOKIEFILE, APP.'tmp'.DS.'cookies'.DS.'testing_cookie');
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		
		//execute post
		$result = curl_exec($ch);
		//close connection
		curl_close($ch);
		
		return json_decode($result);
	}
}