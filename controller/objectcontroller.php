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

use OCA\Calendar\Db\Object;
use OCA\Calendar\JSON\JSONObject;
use OCA\Calendar\JSON\JSONObjectReader;

class ObjectController extends \OCA\Calendar\AppFramework\Controller\Controller {

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
	 * @API
	 */
	public function index() {
		try {
			$userId		= $this->api->getUserId();
			$calendarId	= $this->params('calendarId');
			$limit		= $this->params('limit');
			$offset		= $this->params('offset');
			$expand		= $this->params('expand');
			$start		= $this->params('start');
			$end		= $this->params('end');

			$this->parseBooleanString($expand);
			$this->parseDateTimeString($start);
			$this->parseDateTimeString($end);

			if($start === null || $end === null) {
				$objects = $this->objectBusinessLayer
								->findAll($calendarId,
										  $userId,
										  $limit,
										  $offset);
			} else {
				$objects = $this->objectBusinessLayer
								->findAllInPeriod($calendarId,
												  $start,
												  $end,
												  $userId,
												  $limit,
												  $offset);
			}

			$jsonObjects = array();

			if($expand === true) {
				foreach($objects as $object) {
					$expandedObjects = $object->expand($start, $end);
					foreach($expandedObjects as $expandedObject) {
						$jsonObjects = array_merge($jsonObjects, new JSONObject($expandedObject));
					}
				}
			} else {
				foreach($objects as $object) {
					$jsonObjects[] = new JSONObject($object);
				}
			}

			return new JSONResponse($jsonObjects, Http::STATUS_OK);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 *
	 * @brief returns $object specified by it's UID
	 * @return an instance of a Response implementation 
	 */
	public function show() {
		try {
			$userId		= $this->api->getUserId();
			$calendarId	= $this->params('calendarId');
			$objectURI	= $this->params('objectId');
			$expand		= $this->params('expand');
			$start		= $this->params('start');
			$end		= $this->params('end');

			$this->parseBooleanString($expand);
			$this->parseDateTimeString($start);
			$this->parseDateTimeString($end);

			$object	= $this->objectBusinessLayer->find($calendarId, $objectURI, $userId);

			if($expand === true) {
				$expandedObjects = $object->expand($start, $end);
				$jsonObject = array();
				foreach($expandedObjects as $expandedObject) {
					$jsonObject[] = new JSONObject($expandedObject);
				}
			} else {
				$jsonObject	= new JSONObject($object);
			}

			return new JSONResponse($jsonObject);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	public function create() {
		try {
			echo("reached create method");
			$userId		= $this->api->getUserId();
			$calendarId	= $this->params('calendarId');
			$json		= $this->request->params;

			$jsonReader	= new JSONObjectReader($json);
			$object		= $jsonReader->getObject();

			print_r($object);
			exit;

			$object		= $this->objectBusinessLayer->create($object, $calendarid, $userId);
			$jsonObject	= new JSONObject($object);

			return new JSONResponse($jsonObject, Http::STATUS_CREATED);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	public function update() {
		try {
			$userId		= $this->api->getUserId();
			$calendarId	= $this->api->params('calendarId');
			$objectURI	= $this->api->params('objectId');
			$json		= $this->request->params;

			$jsonReader	= new JSONObjectReader($json);
			$object		= $jsonReader->getObject();

			$object		= $this->objectBusinessLayer->update($object, $objectURI, $calendarId, $userId);
			$jsonObject	= new JSONObject($object);

			return new JSONResponse($jsonObject, Http::STATUS_CREATED);
		} catch(BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	public function patch() {
		return new JSONResponse(array(), HTTP::STATUS_NOT_IMPLEMENTED);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @API
	 */
	public function destroy() {
		try {
			$userId		= $this->api->getUserId();
			$calendarId	= $this->params('calendarId');
			$objectURI	= $this->params('objectId');

			$this->objectBusinessLayer->delete($calendarId, $objectURI, $userId);

			return new JSONResponse();
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_BAD_REQUEST);
		}
	}

	protected function parseBoolean(&$string) {
		if($string === true || $string === 1 || $string === 'true') {
			$string = true;
		} else {
			$string = false;
		}
	}

	protected function parseDateTime(&$string) {
		if($string !== null) {
			$string = DateTime::createFromFormat(\DateTime::RFC2822, $string);
			if($string === false) {
				$string = null;
			}
		}
	}
}