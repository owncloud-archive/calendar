<?php
/**
 * Copyright (c) 2013 Georg Ehrke <ownclouddev@georgswebsite.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/*
 * This class helps managing sources for calendars backends
 */
namespace OCA\Calendar;
class Source{
	public static function add(){
		
	}
	public static function all(){
		
	}
	public static function delete(){
		
	}
	public static function find(){
		
	}
	public static function update(){
		
	}
	private static function createSourceObject
}

class SourceObject{
	//does the source require authentication
	private $isAuth;
	//login credentials
	private $username;
	private $password;
	//URL of the source
	private $url
	//protocol of the sourc
	private $protocol;
	//variable for other informations
	private $otherproperties;
	
	function __construct($url = null, $username = null, $password = null){
		$this->other = array();
		//set the url if given as a parameter
		$this->setURL($url);
		//set auth if a username is given
		if(!is_null($username)){
			$this->setAuth($username, $password);
		}
	}
	
	//!Auth
	
	public function disaleAuth(){
		$this->isAuth = null;
		$this->username = null;
		$this->password = null;
		return true;
	}
	
	public function enableAuth(){
		$this->isAuth = true;
		return true;
	}
	
	public function setAuth($username, $password){
		$this->enableAuth();
		$this->username = $username;
		$this->password = $password;
		return true;
	}
	
	//!URL
		
	public function getURL(){
		return $this->url;
	}

	public function setURL($url){
		//set url
		$this->url = $url;
		//parse url
		$parsed_url = parse_url($url);
		//check if there is a valid protocol
		if(array_key_exists('scheme', $parsed_url)){
			//set protocol
			$this->setProtocol($parsed_url['scheme']);
		}else{
			//set null if no protocol exists
			$this->setProtocol(null);
		}
		return true;
	}
	
	//!Protocol
	
	public function getProtocol(){
		//get the protocol
		return $this->protocol;
	}
	
	private function setProtocol($protocol){
		//set the protocol
		$this->protocol = $protocol;
		return true;
	}
	
	//!Other properties
	
	public function setProperty($key, $value){
		//check if value of property is null
		if(is_null($value)){
			//delete key if value is null
			unset($this->otherproperties[$key]);
		}else{
			//set value if value is not null
			$this->otherproperties[$key] = $value;
		}
		return true;
	}
	
	public function getProperty($key){
		//check if key exists
		if(array_key_exists($key, $this->otherproperties)){
			//return key if it exists
			return $this->otherproperties;
		}
		//return null if key doesn't exist
		return null;
	}
}