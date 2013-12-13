<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Controller;

use OCA\Calendar\BusinessLayer\BusinessLayerException;

class BackendController extends \OCA\Calendar\AppFramework\Controller\Controller {
	
	protected $businessLayer;
	
	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param BusinessLayer $businessLayer: a businessLayer instance
	 */
	public function __construct($api, $request, $businessLayer){
		//call parent's constructor
		parent::__construct($api, $request);
		$this->businessLayer = $businessLayer;
	}

	/**
	 * @Ajax
	 */
	public function index() {
		try {
			$allBackends = $this->businessLayer->findAll();
			return new JSONResponse($allBackends);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @Ajax
	 */
	 public function show() {
	 	$backendId = $this->params('backendId');

		try {
			$backend = $this->businessLayer->find($backendId);
			return new JSONResponse($backend);
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @Ajax
	 */
	public function create() {
		return new JSONResponse(array(), HTTP::STATUS_NOT_IMPLEMENTED);
	}

	/**
	 * @Ajax
	 */
	public function update() {
		$backendId = $this->params('backendId');
		$json = $this->params('data');

		try {
			//you may only update whether or not the backend is enabled (via the json api)
			$jsonarray = json_decode($json);
			if(array_key_exists('enabled', $jsonarray)) {
				$backend = $this->businessLayer->find($backendId);
				$backend->setEnabled($jsonarray['enabled']);
				$this->businessLayer->update($backend);
				$this->show();
			}
		} catch (BusinessLayerException $ex) {
			$this->api->log($ex->getMessage(), 'warn');
			$msg = $this->api->isDebug() ? array('message' => $ex->getMessage()) : array();
			return new JSONResponse($msg, HTTP::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @Ajax
	 */
	public function destroy() {
		return new JSONResponse(array(), HTTP::STATUS_NOT_IMPLEMENTED);
	}

	/**
	 * @Ajax
	 */
	public function restore() {
		return new JSONResponse(array(), HTTP::STATUS_NOT_IMPLEMENTED);
	}
}