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

use OCA\Calendar\Exception\NoBackendSetup as NoBackendSetup;

use OCA\Calendar\Calendar\Item as CalendarItem;

class Calendar extends \OCA\AppFramework\Controller\Controller {
	private $businessLayer;

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param ItemMapper $itemMapper: an itemwrapper instance
	 */
	public function __construct($api, $request, $businessLayer){
		//call parent's constructor
		parent::__construct($api, $request);
		$this->businessLayer = $businessLayer;
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * returns all calendars of the current user
	 */
	 public function getAllCalendars(){
	 	$userid = $this->api->getUserId();
	 	return $this->renderJSON($this->businessLayer->findAll($userid));
	}

	public function getCalendar($userid = null, $uri = null){
		$userid = $this->api->getUserId();
		$uri = $this->param('uri');
		
		return $this->renderJSON($this->businessLayer->find($uri, $userid));
	}

	public function getPropertyOfCalendar($userid = null, $uri = null, $property = null){
	 	if($userid === null){
			$userid = $this->api->getUserId();
	 	}
	 	
		if($uri === null || $property === null){
			return false;
		}
		
		if($this->itemMapper->doesCalendarExist($userid, $uri) === false){
			throw new DoesNotExistException('Calendar does not exist.');
			exit;
		}
		
		return $this->itemMapper->getPropertyOfCalendar($userid, $uri, $property);
	}

	public function setPropertyOfCalendar($userid = null, $uri = null, $property = null, $value = null){
	 	if($userid === null){
			$userid = $this->api->getUserId();
	 	}
	 	
		//$backend->updateCalendar
		$this->itemMaappter->setPropertyOfCalendar($userid, $uri, $property, $value);
	}

	public function updateCalendar($userid = null, $uri = null, $calendar = null){
	 	if($userid === null){
			$userid = $this->api->getUserId();
	 	}
	 	
		if($uri === null || $calendar === null){
			return false;
		}
		
		if(($calendar instanceof CalendarItem) === false){
			//Todo - write log - no valid calendar item
			return false;
		}
		
		$backend = $this->backend->getCorrespondingBackend($uri);
		$result = $backend->api->updateCalendar($uri, $calendar);
		
		if($result === true){
			$this->itemMapper->updateCalendar($uri, $calendar);
			return true;
		}else{
			return false;
		}
	}

	public function removeCalendar($userid = null, $uri = null){
	 	if($userid === null){
			$userid = $this->api->getUserId();
	 	}
	 	
		if($uri === null){
			return false;
		}
		
		if($this->itemMapper->doesCalendarExist($userid, $uri) === false){
			throw new DoesNotExistException('Calendar does not exist.');
			exit;
		}
		
		$backend = $this->backend->getCorrespondingBackend($uri);
		$result = $backend->api->removeCalendar($uri);
		
		if($result === false){
			//Todo - write log
			$this->itemMapper->hideCalendar($uri);
		}else{
			$this->itemMapper->removeCalendar($uri);
		}
	}

	/* //!wrapper methods for JSON interface */
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function getAllCalendarsJSON(){
		$userid = $this->api->getUserId();
		
		$allCalendars = $this->getAllCalendars($userid);
		
		return $this->renderJSON($allCalendars);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function getCalendarJSON(){
		$userid = null;
		$uri = $this->param('uri');
		
		$calendar = $this->getCalendar($userid, $uri);
		
		return $this->renderJSON($calendar);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function getPropertyOfCalendarJSON(){
		$userid = null;
		$uri = $this->param('uri');
		$property = $this->param('property');
		
		$value = $this->getPropertyOfCalendar($userid, $uri, $property);
		
		return $this->renderJSON($value);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function setPropertyOfCalendarJSON(){
		$userid = null;
		$uri = $this->param('uri');
		$property = $this->param('property');
		$value = $this->param('value');
		
		$result = $this->setPropertyOfCalendar($userid, $uri, $property, $value);
		
		return $this->renderJSON($result);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function removeCalendarJSON(){
		$userid = null;
		$uri = $this->param('uri');
		
		$result = $this->removeCalendar($userid, $uri);
		
		return $this->renderJSON($result);
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function calendarDataJs(){
		$template = 'js.calendardata';
		$data = array('data' => $this->getAllCalendars($this->api->getUserId()));
		$oCtemplate = 'blank';
		$header = array('Content-Type'=>'application/javascript');
		
		return $this->render($template, $data, $oCtemplate, $header);
	}
}