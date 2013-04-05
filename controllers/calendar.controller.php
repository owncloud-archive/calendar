<?php
/**
 * Copyright (c) 2013 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Controller;

use OCA\AppFramework\DoesNotExistException as DoesNotExistException;
use OCA\AppFramework\RedirectResponse as RedirectResponse;

use OCA\Calendar\Exception\NoBackendSetup as NoBackendSetup;

class Calendar extends \OCA\AppFramework\Controller {
	//! vars
	//backend controller
	private $backend;
	//object controller
	private $object;


	//!constructor
	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param ItemMapper $itemMapper: an itemwrapper instance
	 */
	public function __construct($api, $request, $itemMapper, $backend, $object){
		//call parent's constructor
		parent::__construct($api, $request);
		$this->itemMapper = $itemMapper;
		$this->backend = $backend;
		$this->object = $object;
	}



}