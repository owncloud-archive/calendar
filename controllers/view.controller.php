<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Controller;

use OCA\AppFramework\DoesNotExistException as DoesNotExistException;
use OCA\AppFramework\RedirectResponse as RedirectResponse;

class View extends \OCA\AppFramework\Controller\Controller {
	
	private $calendarBusinessLayer;
	private $objectBusinessLayer;
	
	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param ItemMapper $itemMapper: an itemwrapper instance
	 */
	public function __construct($api, $request){
		parent::__construct($api, $request);
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
		$this->loadWebJS();
		$this->loadWebCSS();

		return $this->render('app', array());
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page and jumps to a date
	 * @return an instance of a Response implementation
	 */
	public function showDate() {
		$this->loadWebJS();
		$this->loadWebCSS();

		
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page and jumps to an event
	 * @return an instance of a Response implementation
	 */
	public function showEvent() {
		$this->loadWebJS();
		$this->loadWebCSS();

		
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders a printable page of a specific date
	 * @return an instance of a Response implementation
	 */
	public function printDate() {
		$this->loadPrintCSS();
		
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders a printable page of a specific time range
	 * @return an instance of a Response implementation
	 */
	public function printTimeRange() {
		$this->loadPrintCSS();
		
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders a printable page of an specific event
	 * @return an instance of a Response implementation
	 */
	public function printEvent() {
		$this->loadPrintCSS();
		
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function firstRunAfterUpdate(){
		$this->api->addScript('upgrade.js');
		return $this->index();
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function viewDataJs(){
		return $this->render('js.viewdata', array(), 'blank', array('Content-Type'=>'application/javascript'));
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief saves the current view
	 * @return void
	 */
	public function printable(){
		$templateName = 'printable';
		return $this->render($templateName, array(), 'blank');
	}


	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief saves the current view
	 * @return void
	 */
	public function setView(){
		$newview = $this->param('view');
		switch($newview) {
			case 'agendaWeek';
			case 'basic2Weeks':
			case 'basic4Weeks':
			case 'list':
				OCP\Config::setUserValue($this->api->getUserId(), 'calendar', 'currentview', $newview);
				break;
			default:
				break;
		}
		return $this->renderJSON();
	}
	
	private function loadPrintCSS() {
		
	}
	
	private function loadWebJS() {
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
		

	}
	
	private function loadWebCSS() {
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
}
