<?php
include('oocurl/OOCurl.php');

class ExpensifyAPI {
	
	public $api_url = 'https://www.expensify.com/api';
	private $username, $password, $login_cookie, $user_agent;
	
	public function __construct($username = null, $password = null, $login_cookie = false, $user_agent = 'ExpensifyAPI v1.0 (https://github.com/ckeboss/expensify-api)') {
		if(empty($username) || empty($password)) {
			throw new Exception('You must provide a username and password.');
		}
		
		$this->username = $username;
		$this->password = $password;
		
		if(!$login_cookie) {
			if(!is_writable(dirname(__FILE__) .'/cookies/login_cookie')) {
				if(!is_writable(dirname(__FILE__) ) && !is_writable(dirname(__FILE__) .'/cookies')) {
					throw new Exception('Directory '.dirname(__FILE__).'/cookies could not be created or is not writable');
				}
				
				if(!touch(dirname(__FILE__).'/cookies/login_cookie')) {
					throw new Exception('Could not create new cookie file in '.dirname(__FILE__) . '/cookies/login_cookie');
				}
			}
			
			$login_cookie = dirname(__FILE__).'/cookies/login_cookie';
		} else if(!is_writable($login_cookie)) {
			throw new Exception('Cookie file '.$login_cookie.' could not be found or is not writable.');
		}
		
		$this->login_cookie = $login_cookie;
		$this->user_agent = $user_agent;
	}
	
	private function authenticate() {
	
		$curl = new Curl($this->api_url);
		
		if(!$curl) {
			throw new Exception('Could not create Curl object.');
		}
		
		$curl->useragent = $this->user_agent;
		$curl->cookiesession = true;
		$curl->cookiejar = $this->login_cookie;
		$curl->cookiefile = $this->login_cookie;
		
		$curl->post = 3;
		$curl->postfields = 'command=SignIn&email='.urlencode($this->username).'&password='.urlencode($this->password);
		
		$login_resp = json_decode($curl->exec());
		$curl->close();
		
		if($login_resp->jsonCode != 200) {
			return false;
		}
		
		return true;
	}
	
	private function check_if_authenticated() {
		$curl = new Curl($this->api_url);
		
		$curl->useragent = $this->user_agent;
		$curl->cookiesession = true;
		$curl->cookiefile = $this->login_cookie;
		
		$curl->post = 3;
		$curl->postfields = 'command=Get&returnValueList=reportListBeta&limit=1';
		
		$api_resp = json_decode($curl->exec());
		$curl->close();
		
		if($api_resp->jsonCode != 200) {
			return false;
		}
		
		return true;
	}
	
	private function login_if_not_already($respCode, $callback, &$resp) {
		if($respCode == 407) {
			if(!$this->authenticate()) {
				return false;
			} else {
				return call_user_func_array($callback[0], $callback[1]);
			}
		}
		
		return $resp;
	}
	
	public function getReports($limit = 10, $offset = 0, $sort_by = 'started', $retry = false) {
		$curl = new Curl($this->api_url);
		
		$curl->useragent = $this->user_agent;
		$curl->cookiesession = true;
		$curl->cookiefile = $this->login_cookie;
		
		$curl->post = 5;
		$curl->postfields = 'command=Get&returnValueList=reportListBeta&limit='.urlencode($limit).'&offset='.urlencode($offset).'&sortBy='.urlencode($sort_by);
		
		$reports_resp = json_decode($curl->exec());
		$curl->close();
		
		if(!$retry) {
			return $this->login_if_not_already($reports_resp->jsonCode, array(array($this, 'getReports'), array($limit, $offset, $sort_by, true)), $reports_resp);
		}
		
		if($reports_resp->jsonCode != 200) {
			return false;
		}
		
		return $reports_resp;
	}
	
	public function getReport($report_id, $retry = false) {
		$curl = new Curl($this->api_url);
		
		$curl->useragent = $this->user_agent;
		$curl->cookiesession = true;
		$curl->cookiefile = $this->login_cookie;
		
		$curl->post = 2;
		$curl->postfields = 'command=GetReportStuff&reportID='.urlencode($report_id);
		
		$report_resp = json_decode($curl->exec());
		$curl->close();
		
		if(!$retry) {
			return $this->login_if_not_already($report_resp->jsonCode, array(array($this, 'getReport'), array($report_id, true)), $report_resp);
		}
		
		if($report_resp->jsonCode != 200) {
			return false;
		}
		
		return $report_resp;
	}
	
}