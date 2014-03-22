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

use \OCA\Calendar\BusinessLayer\BusinessLayer;

use \DateTime;

abstract class Controller extends \OCA\Calendar\AppFramework\Controller\Controller{

	protected $calendarBusinessLayer;
	protected $objectBusinessLayer;

	public function __construct(API $api, Request $request,
								CalendarBusinessLayer $calendarBusinessLayer,
								ObjectBusinessLayer $objectBusinessLayer){
		parent::__construct($api, $request);
		$this->calendarBusinessLayer = $calendarBusinessLayer;
		$this->objectBusinessLayer = $objectBusinessLayer;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	public function patch() {
		return new JSONResponse(array(), HTTP::STATUS_NOT_IMPLEMENTED);
	}

	/*
	 * Lets you access http request header
	 * @param string $key the key which you want to access in the http request header
	 * @param mixed $default If the key is not found, this value will be returned
	 * @return mixed content of header field
	 */
	protected function header($key, $type='string', $default=null){
		$key = 'HTTP_' . strtoupper($key);

		if(isset($this->request->server[$key]) === false) {
			return $default;
		} else {
			$value = $this->request->server[$key];
			if(strtolower($type) === 'datetime') {
				$value = DateTime::createFromFormat(DateTime::ISO8601);
			} else {
				settype($value, $type);
			}
			return $value;
		}
	}

	/**
	 * did user request raw ics instead of json
	 * @param boolean
	 */
	protected function doesClientAcceptRawICS() {
		$accept = $this->header('accept');

		$textCalendarPosition = strpos($accept, 'text/calendar');
		if($textCalendarPosition === false) {
			return false;
		}

		$applicationJSONPosition = strpos($accept, 'application/json');
		$applicationCalendarJSONPosition = strpos($accept, 'application/calendar+json');

		if($applicationJSONPosition === false && $applicationCalendarJSONPosition === false) {
			return true;
		}

		$firstApplicationPosition = min($applicationJSONPosition, $applicationCalendarJSONPosition);

		return ($firstApplicationPosition < $textCalendarPosition) ? false : true;
	}

	/**
	 * did user request raw ics instead of json
	 * @param boolean
	 */
	protected function didClientSendRawICS() {
		$contentType = $this->header('content-type'); 

		if(strpos($contentType, ';')) {
			$explodeContentType = explode(';', $contentType);
			$contentType = $explodeContentType[0];
		}

		$didClientSendRawICS = false;
		switch($contentType) {
			case 'text/calendar':
				$didClientSendRawICS = true;
				break;

			case 'application/json':
			case 'application/calendar+json':
				$didClientSendRawICS = false;
				break;

			default:
				$didClientSendRawICS = false;
				break;
		}

		return $didClientSendRawICS;
	}
}