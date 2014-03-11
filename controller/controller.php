<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Controller;

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Http\Http;
use \OCA\Calendar\AppFramework\Http\Request;
use \OCA\Calendar\AppFramework\Http\JSONResponse;

use \OCA\Calendar\AppFramework\DoesNotExistException;

abstract class Controller extends \OCA\Calendar\AppFramework\Controller\Controller{

	/*
	 * Lets you access http request header
	 * @param string $key the key which you want to access in the http request header
	 * @param mixed $default If the key is not found, this value will be returned
	 * @return mixed content of header field
	 */
	protected function header($key, $default=null){
		$key = 'HTTP_' . strtoupper($key);

		return isset($this->request->server[$key])
			? $this->request->server[$key]
			: $default;
	}

	/**
	 * parses a string as boolean
	 * @param pointer to string that should be parsed
	 */
	protected function parseBoolean(&$string) {
		if($string === true || $string === 1 || $string === '1' || $string === 'true') {
			$string = true;
		} else {
			$string = false;
		}
	}

	/**
	 * parses a string as DateTime object
	 * @param pointer to string that should be parsed
	 */
	protected function parseDateTime(&$string) {
		if($string !== null) {
			$string = DateTime::createFromFormat(\DateTime::RFC2822, $string); //use other format
			if($string === false) {
				$string = null;
			}
		}
	}

	/**
	 * did user request raw ics instead of json
	 * @param boolean
	 */
	protected function returnRawICS() {
		$accept = $this->header('accept');
		$textCalPos = strpos($accept, 'text/calendar');

		if($textCalPos === false) {
			return false;
		}

		$applJSONPos = strpos($accept, 'application/json');
		$applCalJSONPos = strpos($accept, 'application/calendar+json');

		if($applJSONPos === false && $applCalJSONPos === false) {
			return true;
		}

		$applPos = min($applJSONPos, $applCalJSONPos);

		return ($applPos < $textCalPos) ? false : true;
	}

	/**
	 * did user request raw ics instead of json
	 * @param boolean
	 */
	protected function isRequestBodyRawICS() {
		$contentType = $this->header('content-type'); 

		//TODO - TAKE CARE OF CHARSET !!!111ONEONEONEELEVEN

		$isRawICS = false;
		switch($contentType) {
			case 'text/calendar':
				$isRawICS = true;
				break;

			case 'application/json':
			case 'application/calendar+json':
				$isRawICS = false;
				break;

			default:
				$isRawICS = false
				break;
		}

		return $isRawICS;
	}
}