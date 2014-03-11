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

use OCA\Calendar\BusinessLayer\ObjectBusinessLayer;
use \OCA\Calendar\BusinessLayer\BusinessLayerException;

use OCA\Calendar\Db\ObjectType;
use OCA\Calendar\JSON\JSONObject;

abstract class ObjectTypeController extends ObjectController {

	private $objectType;

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param BusinessLayer $businessLayer: a businessLayer instance
	 * @param string $objectType: itemtype of wanted elements, use OCA\Calendar\Db\ObjectType::...
	 */
	public function __construct(API $api, Request $request,
								ObjectBusinessLayer $businessLayer, $type){
		parent::__construct($api, $request, $businessLayer);
		$this->objectType = $type;
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
			$limit = $this->header('X-OC-CAL-LIMIT');
			$offset = $this->header('X-OC-CAL-OFFSET');
			$expand = $this->header('X-OC-CAL-EXPAND');
			$start = $this->header('X-OC-CAL-START');
			$end = $this->header('X-OC-CAL-END');

			$this->parseBoolean($expand);
			$this->parseDateTime($start);
			$this->parseDateTime($end);

			if($start === null || $end === null) {
				$objectCollection = $this->objectBusinessLayer
										->findAllByType($calendarId,
														$this->objectType,
														$userId,
														$limit,
														$offset);
			} else {
				$objectCollection = $this->objectBusinessLayer
										->findAllByTypeInPeriod($calendarId,
																$this->objectType,
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
	 */
	public function show() {
		try {
			$userId 	= $this->api->getUserId();
			$calendarId = $this->params('calendarId');
			$objectId = $this->getObjectId();

			$returnRawICS = $this->returnRawICS();
			$expand = $this->header('X-OC-CAL-EXPAND');
			$start = $this->header('X-OC-CAL-START');
			$end = $this->header('X-OC-CAL-END');

			$this->parseBooleanString($expand);
			$this->parseDateTimeString($start);
			$this->parseDateTimeString($end);

			$object = $this->objectBusinessLayer->findByType($calendarId, $objectId, $this->objecttype, $userId);

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
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_NOT_FOUND);
		}
	}

	private function getObjectId() {
		list($routeApp, $routeController, $routeMethod) = explode('.', $this->params('_route'));
		return $this->params(substr($routeController, 0, strlen($routeController) - 1) . 'Id');
	}
}