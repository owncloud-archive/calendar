<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Controller;

use OCA\AppFramework\DoesNotExistException as DoesNotExistException;
use OCA\AppFramework\RedirectResponse as RedirectResponse;

use OCA\Calendar\Exception\NoBackendSetup as NoBackendSetup;

class Backend extends \OCA\AppFramework\Controller\Controller {
	
	private $businessLayer;
	
	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param ItemMapper $itemMapper: an itemwrapper instance
	 */
	public function __construct($api, $request, $businessLayer){
		//call parent's constructor
		parent::__construct($api, $request);
		//define itemMapper
		$this->businessLayer = $businessLayer;
	}

	/**
	 * @brief gets all backends
	 * @returns array of backend objects
	 * 
	 * gets all backends
	 */
	public function getBackends(){
		return $this->businessLayer->findAll();
	}

	/**
	 * @brief gets all enabled backends
	 * @returns array of backend objects
	 * 
	 * gets all enabled backends
	 */
	public function getEnabledBackends(){
		return $this->renderJSON($this->businessLayer->findAllEnabled());
	}


	/**
	 * @brief gets the default backend
	 * @returns array of backend objects
	 * 
	 * gets all enabled backends
	 */
	public function getDefaultBackend(){
		return $this->renderJSON($this->businessLayer->getDefault());
	}


	/**
	 * @brief sets default backend
	 * @returns void
	 * 
	 * sets default backend
	 */
	public function setDefaultBackend(){
		//TODO render backend var
		$backend = $this->param($backend);
		return $this->renderJSON($this->businessLayer->setDefaultBackend($backend));
	}


	/**
	 * @brief disables a backend
	 * @returns void
	 * 
	 * disables a backend
	 */
	public function disableBackend(){
		$backend = $this->param($backend);
		return $this->renderJSON($this->businessLayer->disable($backend));
	}


	/**
	 * @brief enables a backend
	 * @returns void
	 * 
	 * enables
	 */
	public function enableBackend($backend){
		$backend = $this->param($backend);
		return $this->renderJSON($this->businessLayer->enable($backend));
	}
}