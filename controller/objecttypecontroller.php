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
			$userId 	= $this->api->getUserId();
			$calendarId = $this->params('calendarId');

			$limit		= $this->params('X-OC-CAL-LIMIT');
			$offset		= $this->params('X-OC-CAL-OFFSET');

			$expand		= $this->params('X-OC-CAL-EXPAND');
			$start		= $this->params('X-OC-CAL-START');
			$end		= $this->params('X-OC-CAL-END');

			$this->parseBoolean($expand);
			$this->parseDateTime($start);
			$this->parseDateTime($end);

			$objects = array();
			if($start === null || $end === null) {
				$objects = $this->objectBusinessLayer
								->findAllByType($calendarId,
												$this->objectType,
												$userId,
												$limit,
												$offset);
			} else {
				$objects = $this->objectBusinessLayer
								->findAllByTypeInPeriod($calendarId,
														$this->objectType,
														$start,
														$end,
														$userId,
														$limit,
														$offset);
			}

			$jsonObjects = array();
			if($expand === false) {
				foreach($objects as $object) {
					$jsonObjects[] = new JSONObject($object);
				}
			} else {
				foreach($objects as $object) {
					$expandedObjects = $object->expand($start, $end);
					foreach($expandedObjects as $expandedObject) {
						$jsonObjects = array_merge($jsonObjects, new JSONObject($expandedObject));
					}
				}
			}

			return new JSONResponse($jsonObjects);
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

			$limit		= $this->params('X-OC-CAL-LIMIT');
			$offset		= $this->params('X-OC-CAL-OFFSET');

			$expand		= $this->params('X-OC-CAL-EXPAND');
			$start		= $this->params('X-OC-CAL-START');
			$end		= $this->params('X-OC-CAL-END');
	
			list($routeApp, $routeController, $routeMethod) = explode('.', $this->params('_route'));
			$objectId = $this->params(substr($routeController, 0, strlen($routeController) - 1) . 'Id');

			$object = $this->objectBusinessLayer
						   ->findByType($calendarId,
						   				$objectId,
						   				$this->objecttype,
						   				$userId);

			$jsonObject = new JSONObject($object);

			return new JSONResponse($jsonObject);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_NOT_FOUND);
		}
	}
}