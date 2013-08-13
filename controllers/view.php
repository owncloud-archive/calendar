<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Controller;

use \OCA\Calendar\AppFramework\Controller\Controller;
use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Http\Http;
use \OCA\Calendar\AppFramework\Http\Request;
use \OCA\Calendar\AppFramework\Http\JSONResponse;

use OCA\Calendar\AppFramework\DoesNotExistException;

use \OCA\Calendar\BusinessLayer\BackendBusinessLayer;
use \OCA\Calendar\BusinessLayer\CalendarBusinessLayer;
use \OCA\Calendar\BusinessLayer\ObjectBusinessLayer;

use \OCA\Calendar\BusinessLayer\BusinessLayerException;

use OCA\Calendar\Db\Calendar;
use OCA\Calendar\Db\JSONCalendar;
use OCA\Calendar\Db\Object;
use OCA\Calendar\Db\JSONObject;

class ViewController extends \OCA\Calendar\AppFramework\Controller\Controller {
	
	private $calendarBusinessLayer;
	private $objectBusinessLayer;
	
	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param ItemMapper $itemMapper: an itemwrapper instance
	 */
	public function __construct(API $api, Request $request,
								CalendarBusinessLayer $calendarBusinessLayer, 
								ObjectBusinessLayer $objectBusinessLayer){
		parent::__construct($api, $request);
		$this->calendarBusinessLayer = $calendarBusinessLayer;
		$this->objectBusinessLayer = $objectBusinessLayer;

		// thirdparty javscripts
		$this->api->add3rdPartyScript('fullCalendarPro/fullcalendar');
		$this->api->add3rdPartyScript('underscore/underscore');
		$this->api->add3rdPartyScript('backbone/backbone');
		//$this->api->add3rdPartyScript('timepicker-min');
		//$this->api->add3rdPartyScript('tipsy-min');
		// calendar javascripts
		$this->api->addScript('application');
		$this->api->addScript('calendar');
		$this->api->addScript('settings');
		// thirdpary stylesheets
		$this->api->add3rdPartyStyle('fullcalendar');
		$this->api->add3rdPartyStyle('timepicker');
		$this->api->add3rdPartyStyle('tipsy.mod');
		// calendar stylesheets
		$this->api->addStyle('animations');
		$this->api->addStyle('datepicker');
		$this->api->addStyle('fullcalendar');
		$this->api->addStyle('style');
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function index(){
		return $this->render('app', array());
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders \DateTimeZone::listAbbreviations(); as JSON
	 * @return an instance of a JSONResponse implementation
	 */
	public function timezoneIndex() {
		$timezones = \DateTimeZone::listAbbreviations();
		return new JSONResponse($timezones);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 *
	 * @brief saves the new view
	 * @return an instance of a JSONResponse implementation
	 */
	public function setView(){
		$newView = $this->param('view');
		switch($newView) {
			case 'agendaDay';
			case 'agendaWeek';
			case 'basic2Weeks':
			case 'basic4Weeks':
			case 'list':
				\OCP\Config::setUserValue($this->api->getUserId(), 'calendar', 'currentview', $newView);
				return new JSONResponse(array('newView' => $newView));
				break;
			default:
				return new JSONRespose(array('message'=>'Invalid view'), HTTP::STATUS_BAD_REQUEST);
				break;
		}
	}
}