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

class CalendarController extends \OCA\Calendar\AppFramework\Controller\Controller {

	private $calendarBusinessLayer;

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param BusinessLayer $businessLayer: a businessLayer instance
	 */
	public function __construct(API $api, Request $request,
								CalendarBusinessLayer $businessLayer){
		//call parent's constructor
		parent::__construct($api, $request);
		$this->calendarBusinessLayer = $businessLayer;
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	public function index() {
		$userId = $this->api->getUserId();

		try {
			$calendars = $this->calendarBusinessLayer->findAll($userId);

			$jsonCalendars = array();
			foreach($calendars as $calendar) {
				$jsonCalendars[] = new JSONCalendar($calendar);
			}

			return new JSONResponse($jsonCalendars);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	 public function show() {
		$userId = $this->api->getUserId();
		$calendarId = $this->params('calendarId');

		try {
			$calendar = $this->calendarBusinessLayer->find($calendarId, $userId);

			$jsonCalendar = new JSONCalendar($calendar);

			return new JSONResponse($jsonCalendar);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	public function create() {
		$userId = $this->api->getUserId();

		try {
			$json = $this->params('data');
			$jsonReader = new JSONCalendarReader($json);
			$calendar = $jsonReader->getCalendarObject();
			$calendar->setUserId($userId);

			$this->calendarBusinessLayer->purgeDelete($userId, false);
			$calendar = $this->calendarBusinessLayer->create($calendar, $userId);

			return new JSONResponse(array(), HTTP::STATUS_CREATED);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	public function update() {
		$userId = $this->api->getUserId();
		$calendarId = $this->api->params('calendarId');

		try {
			$json = $this->params('data');
			$jsonReader = new JSONCalendarReader($json);
			$calendar = $jsonReader->getCalendarObject();

			$this->calendarBusinessLayer->purgeDelete($userId, false);
			$calendar = $this->calendarBusinessLayer->update($calendar, $userId);

			return new JSONResponse(array(), HTTP::STATUS_CREATED);
		} catch(BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	public function destroy() {
		$userId = $this->api->getUserId();
		$calendarId = $this->params('calendarId');

		try {
			$this->calendarBusinessLayer->markDeleted($calendarId, $userId);
			return $this->renderJSON();
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	public function restore() {
		$userId = $this->api->getUserId();
		$calendarId = $this->params('calendarId');

		try {
			$this->calendarBusinessLayer->unmarkDeleted($calendarId, $userId);
			return $this->renderJSON();
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}
}