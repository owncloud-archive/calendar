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

use \OCA\Calendar\BusinessLayer\BackendBusinessLayer;
use \OCA\Calendar\BusinessLayer\CalendarBusinessLayer;
use \OCA\Calendar\BusinessLayer\ObjectBusinessLayer;
use \OCA\Calendar\BusinessLayer\BusinessLayerException;

use OCA\Calendar\Db\Object;
use OCA\Calendar\JSON\JSONObject;
use OCA\Calendar\JSON\JSONObjectReader;

class ObjectController extends Controller {

	protected $objectBusinessLayer;

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param BusinessLayer $businessLayer: a businessLayer instance
	 */
	public function __construct(API $api, Request $request,
								ObjectBusinessLayer $businessLayer){
		parent::__construct($api, $request);
		$this->objectBusinessLayer = $businessLayer;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 */
	public function index() {
		try {
			$userId = $this->api->getUserId();
			$calendarId = $this->params('calendarId');

			$returnRawICS = $this->returnRawICS();
			$limit = $this->header('X-OC-CAL-LIMIT');
			$offset = $this->header('X-OC-CAL-OFFSET');
			$expand = $this->header('X-OC-CAL-EXPAND');
			$start = $this->header('X-OC-CAL-START');
			$end = $this->header('X-OC-CAL-END');

			$this->parseBooleanString($expand);
			$this->parseDateTimeString($start);
			$this->parseDateTimeString($end);

			if($start === null || $end === null) {
				$objectCollection = $this->objectBusinessLayer
					->findAll($calendarId,
							  $userId,
							  $limit,
							  $offset);
			} else {
				$objectCollection = $this->objectBusinessLayer
					->findAllInPeriod($calendarId,
									  $start,
									  $end,
									  $userId,
									  $limit,
									  $offset);
			}

			if($expand === true) {
				$objectCollection->expand($start, $end);
			}

			if($returnRawICS === true) {
				$VObject = $objectCollection->getVObject();
				$ics = $VObject->serialize();
				return new Response($ics, Http::STATUS_OK);
			} else {
				$jsonObjectCollection = new JSONObjectCollection($objectCollection);
				$json = $jsonObjectCollection->serialize();
				return new JSONResponse($json, Http::STATUS_OK);
			}
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @API
	 *
	 * @brief returns $object specified by it's UID
	 * @return an instance of a Response implementation 
	 */
	public function show() {
		try {
			$userId	= $this->api->getUserId();
			$calendarId = $this->params('calendarId');
			$objectURI = $this->params('objectId');

			$returnRawICS = $this->returnRawICS();
			$expand = $this->header('X-OC-CAL-EXPAND');
			$start = $this->header('X-OC-CAL-START');
			$end = $this->header('X-OC-CAL-END');

			$this->parseBooleanString($expand);
			$this->parseDateTimeString($start);
			$this->parseDateTimeString($end);

			$object	= $this->objectBusinessLayer->find($calendarId, $objectURI, $userId);

			if($expand === true) {
				$objectCollection = $object->expand($start, $end);
				if($returnRawICS === true) {
					$serializer = $objectCollection->getVObject();
				} else {
					$serializer = new JSONObjectCollection($objectCollection);
				}
			} else {
				if($returnRawICS === true) {
					$serializer = $object->getVObject();
				} else {
					$serializer = new JSONObject($object);
				}
			}

			$serialized = $serializer->serialize();

			if($returnRawICS === true) {
				return new Response($serialized, Http::STATUS_OK);
			} else {
				return new JSONResponse($serialized, Http::STATUS_OK);
			}
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			return new JSONResponse(null, Http::STATUS_NOT_FOUND);
		} catch (JSONException $ex) {
			//do smth
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
			$calendarId = $this->params('calendarId');
			$requestBody = $this->request->params;
			$returnRawICS = $this->returnRawICS();

			if($this->isRequestBodyRawICS() === true) {
				$reader = new ICSObjectReader($requestBody);
			} else {
				$reader = new JSONObjectReader($requestBody);
			}

			$object = $reader->getObject();

			$object = $this->objectBusinessLayer->create($object, $calendarid, $userId);

			if($returnRawICS === true) {
				$VObject = $object->getVObject();
				$ics = $VObject->serialize();
				return new Response($ics, Http::STATUS_CREATED);
			} else {
				$jsonObject = new JSONObject($object);
				$json = $jsonObject->serialize();
				return new JSONResponse($json, Http::STATUS_CREATED);
			}
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_BAD_REQUEST);
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
			$calendarId = $this->api->params('calendarId');
			$objectURI = $this->api->params('objectId');
			$requestBody = $this->request->params;
			$returnRawICS = $this->returnRawICS();

			if($this->isRequestBodyRawICS() === true) {
				$reader = new ICSObjectReader($requestBody);
			} else {
				$reader = new JSONObjectReader($requestBody);
			}

			$object = $reader->getObject();

			$object = $this->objectBusinessLayer->update($object, $objectURI, $calendarId, $userId);

			if($returnRawICS === true) {
				$VObject = $object->getVObject();
				$ics = $VObject->serialize();
				return new Response($ics, Http::STATUS_CREATED);
			} else {
				$jsonObject = new JSONObject($object);
				$json = $jsonObject->serialize();
				return new JSONResponse($json, Http::STATUS_CREATED);
			}
		} catch(BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_BAD_REQUEST);
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
		try {
			$userId = $this->api->getUserId();
			$calendarId = $this->params('calendarId');
			$objectURI = $this->params('objectId');

			$this->objectBusinessLayer->delete($calendarId, $objectURI, $userId);

			return new JSONResponse(null, HTTP::STATUS_NO_CONTENT);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_BAD_REQUEST);
		}
	}
}