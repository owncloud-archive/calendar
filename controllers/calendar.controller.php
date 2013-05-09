<?php
/**
 * Copyright (c) 2013 Georg Ehrke <ownclouddev at georgswebsite dot de>
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
	//! vars
	//backend controller
	private $backend;
	//object controller
	private $object;


	//!constructor
	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param ItemMapper $itemMapper: an itemwrapper instance
	 */
	public function __construct($api, $request, $itemMapper, $backend, $object){
		//call parent's constructor
		parent::__construct($api, $request);
		$this->itemMapper = $itemMapper;
		$this->backend = $backend;
		$this->object = $object;
	}

	 public function getAllCalendars($userid = $this->api->getUserId()){
		$allCalendars = $this->itemMapper->getAllCalendars($userid);

		if(count($allCalendars) === 0){
			return false;
		}

		return $allCalendars;
	}

	public function getCalendar($userid = $this->api->getUserId(), $uri = null){
		if($uri === null){
			return false;
		}
		
		if($this->itemMapper->doesCalendarExist($userid, $uri) === false){
			throw new DoesNotExistException('Calendar does not exist.');
			exit;
		}
		
		return $this->itemMapper->getCalendar($userid, $uri);
	}

	public function getPropertyOfCalendar($userid = $this->api->getUserId(), $uri = null, $property = null){
		if($uri === null || $property === null){
			return false;
		}
		
		if($this->itemMapper->doesCalendarExist($userid, $uri) === false){
			throw new DoesNotExistException('Calendar does not exist.');
			exit;
		}
		
		return $this->itemMapper->getPropertyOfCalendar($userid, $uri, $property);
	}

	public function setPropertyOfCalendar($userid = $this->api->getUserId(), $uri = null, $property = null, $value = null){
		//$backend->updateCalendar
		$this->itemMaappter->setPropertyOfCalendar($userid, $uri, $property, $value);
	}

	public function updateCalendar($userid = $this->api->getUserId(), $uri = null, $calendar = null){
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

	public function removeCalendar($userid = $this->api->getUserId(), $uri = null){
		if($uri === null){
			return false;
		}
		
		if($this->itemMapper->doesCalendarExist($userid, $uri) === false){
			throw new DoesNotExistException('Calendar does not exist.');
			exit;
		}
		
		$backend = $this->backend->getCorrespondingBackend($uri);
		$result = $backend->api->removeCalendar($uri);
		
		if($result === false)
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
		$userid = $this->api->getUserId();
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
		$userid = $this->api->getUserId();
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
		$userid = $this->api->getUserId();
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
		$userid = $this->api->getUserId();
		$uri = $this->param('uri');
		
		$result = $this->removeCalendar($userid, $uri);
		
		$this->renderJSON($result);
	}
}