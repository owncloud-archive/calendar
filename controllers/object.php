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

use OCA\Calendar\Db\Object;
use OCA\Calendar\Db\JSONObject;

class ObjectController extends \OCA\Calendar\AppFramework\Controller\Controller {

	protected $objectBusinessLayer;

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param BusinessLayer $businessLayer: a businessLayer instance
	 */
	public function __construct(API $api, Request $request,
								ObjectBusinessLayer $businessLayer){
		//call parent's constructor
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
		$userId = $this->api->getUserId();
		$calendarId = $this->params('calendarId');

		try {
			$objects = $this->objectBusinessLayer->findAll($calendarId, $userId);
			$jsonObjects = array();
			foreach($objects as $object) {
				$jsonObjects[] = new JSONObject($object);
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
	 *
	 * @brief returns $object specified by it's UID
	 * @return an instance of a Response implementation 
	 */
	public function show() {
		$userId = $this->api->getUserId();
		$calendarId = $this->params('calendarId');
		$objectURI = $this->params('objectId');

		try {
			$object = $this->objectBusinessLayer->find($calendarId, $objectURI, $userId);
			$jsonObject = new JSONObject($object);
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
		$userId = $this->api->getUserId();
		$calendarId = $this->params('calendarId');

		try {
			$json = $this->params('data');
			$jsonReader = new JSONObjectReader($json);
			$object = $jsonReader->getObjectObject();

			$this->objectBusinessLayer->purgeDelete($calendarId, $userId, false);
			$object = $this->objectBusinessLayer->create($object, $calendarid, $userId);

			return new JSONResponse(array(), Http::STATUS_CREATED);
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
		$userId = $this->api->getUserId();
		$calendarId = $this->api->params('calendarId');
		$objectURI = $this->api->params('objectId');

		try {
			$json = $this->params('data');
			$jsonReader = new JSONObjectReader($json);
			$object = $jsonReader->getObjectObject();

			$this->objectBusinessLayer->purgeDelete($calendarId, $userId, false);
			$calendar = $this->objectBusinessLayer->update($object, $objectURI, $calendarId, $userId);

			return new JSONResponse(array(), Http::STATUS_CREATED);
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
	public function destroy() {
		$userId = $this->api->getUserId();
		$calendarId = $this->params('calendarId');
		$objectURI = $this->params('objectId');

		try {
			$this->objectBusinessLayer->markDeleted($calendarId, $objectURI, $userId);
			return new JSONResponse();
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
	public function restore() {
		$userId = $this->api->getUserId();
		$calendarId = $this->params('calendarId');
		$objectURI = $this->params('objectId');

		try {
			$this->objectBusinessLayer->unmarkDeleted($calendarId, $objectURI, $userId);
			return new JSONResponse();
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, Http::STATUS_BAD_REQUEST);
		}
	}
}