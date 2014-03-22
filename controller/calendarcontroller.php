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

use \OCA\Calendar\AppFramework\DoesNotExistException;

use \OCA\Calendar\BusinessLayer\CalendarBusinessLayer;
use \OCA\Calendar\BusinessLayer\ObjectBusinessLayer;
use \OCA\Calendar\BusinessLayer\BusinessLayerException;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\CalendarCollection;

use \OCA\Calendar\Http\JSONResponse;

use \OCA\Calendar\Http\JSON\JSONCalendar;
use \OCA\Calendar\Http\JSON\JSONCalendarCollection;
use \OCA\Calendar\Http\JSON\JSONCalendarReader;

class CalendarController extends Controller {

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	public function index() {
		try {
			$userId = $this->api->getUserId();
			$limit = $this->header('X-OC-CAL-LIMIT', 'integer');
			$offset	= $this->header('X-OC-CAL-OFFSET', 'integer');

			$calendarCollection = $this->calendarBusinessLayer->findAll($userId, $limit, $offset);
			$jsonCalendarCollection = new JSONCalendarCollection($calendarCollection);

			return new JSONResponse($jsonCalendarCollection->serialize());
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			return new JSONResponse(null, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	 public function show() {
		try {
			$userId = $this->api->getUserId();
			$calendarId = $this->params('calendarId');

			$calendar = $this->calendarBusinessLayer->find($calendarId, $userId);

			$jsonCalendar = new JSONCalendar($calendar);

			$response = new Reponse();
			$response->addHeader('X-Content-Type-Options', 'nosniff');
			$response->addHeader('Content-type', 'application/json; charset=utf-8');

			return new JSONResponse($jsonCalendar->serialize());
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			return new JSONResponse(null, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	public function create() {
		try {
			$userId = $this->api->getUserId();
			$json = $this->request->params;

			$jsonReader = new JSONCalendarReader($json);
			$calendar = $jsonReader->getCalendar()
								   ->setUserId($userId)
								   ->setOwnerId($userId);

			$calendar = $this->calendarBusinessLayer->create($calendar, $userId);
			$jsonCalendar = new JSONCalendar($calendar);

			return new JSONResponse($jsonCalendar->serialize(), HTTP::STATUS_CREATED);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			return new JSONResponse(null, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	public function update() {
		try {
			$userId = $this->api->getUserId();
			$calendarId = $this->params('calendarId');
			$json = $this->request->params;

			$jsonReader = new JSONCalendarReader($json);
			$calendar = $jsonReader->getCalendar()
								   ->setUserId($userId);

			$calendar = $this->calendarBusinessLayer->update($calendar, $calendarId, $userId);
			$jsonCalendar = new JSONCalendar($calendar);

			return new JSONResponse($jsonCalendar->serialize(), HTTP::STATUS_CREATED);
		} catch(BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			return new JSONResponse(null, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/** 
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	public function destroy() {
		try {
			$userId	= $this->api->getUserId();
			$calendarId	= $this->params('calendarId');

			$this->calendarBusinessLayer->delete($calendarId, $userId);

			return new JSONResponse(null, HTTP::STATUS_NO_CONTENT);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			return new JSONResponse(null, HTTP::STATUS_BAD_REQUEST);
		}
	}
}