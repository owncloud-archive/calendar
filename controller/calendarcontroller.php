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

use \OCA\Calendar\AppFramework\DoesNotExistException;

use \OCA\Calendar\BusinessLayer\BackendBusinessLayer;
use \OCA\Calendar\BusinessLayer\CalendarBusinessLayer;
use \OCA\Calendar\BusinessLayer\ObjectBusinessLayer;
use \OCA\Calendar\BusinessLayer\BusinessLayerException;

use OCA\Calendar\Db\Calendar;
use OCA\Calendar\JSON\JSONCalendar;
use OCA\Calendar\JSON\JSONCalendarReader;


class CalendarController extends \OCA\Calendar\AppFramework\Controller\Controller {

	protected $calendarBusinessLayer;

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param BusinessLayer $businessLayer: a businessLayer instance
	 */
	public function __construct(API $api, Request $request,
								CalendarBusinessLayer $businessLayer){
		parent::__construct($api, $request);
		$this->calendarBusinessLayer = $businessLayer;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	public function index() {
		$userId	= $this->api->getUserId();
		$limit	= $this->params('limit');
		$offset	= $this->params('offset');

		try {
			$calendars = $this->calendarBusinessLayer->findAll($userId, $limit, $offset);

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
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	 public function show() {
		$userId		= $this->api->getUserId();
		$calendarId	= $this->params('calendarId');

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
	 * @CSRFExemption
	 * @API
	 */
	public function create() {
		$userId	= $this->api->getUserId();
		$json	= $this->request->params;

		try {
			$jsonReader	= new JSONCalendarReader($json);
			$calendar	= $jsonReader->getCalendar();
			$calendar->setUserId($userId);
			$calendar->setOwnerId($userId);

			$calendar		= $this->calendarBusinessLayer->create($calendar, $userId);
			$jsonCalendar	= new JSONCalendar($calendar);

			return new JSONResponse($jsonCalendar, HTTP::STATUS_CREATED);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	public function update() {
		$userId		= $this->api->getUserId();
		$calendarId	= $this->params('calendarId');
		$json		= $this->request->params;

		try {
			$jsonReader	= new JSONCalendarReader($json);
			$calendar	= $jsonReader->getCalendar();

			$calendar		= $this->calendarBusinessLayer->update($calendar, $calendarId, $userId);
			$jsonCalendar	= new JSONCalendar($calendar);

			return new JSONResponse($jsonCalendar, HTTP::STATUS_CREATED);
		} catch(BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
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

	/** 
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	public function destroy() {
		$userId		= $this->api->getUserId();
		$calendarId	= $this->params('calendarId');

		try {
			$this->calendarBusinessLayer->delete($calendarId, $userId);

			return new JSONResponse();
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}
}