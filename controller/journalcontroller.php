<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Controller;

use \OC\AppFramework\Http\Request;
use \OC\AppFramework\Core\API;

use \OCA\Calendar\BusinessLayer\ObjectBusinessLayer;
use OCA\Calendar\Db\ObjectType;

class JournalsController extends ObjectTypeController {

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param BusinessLayer $businessLayer: a businessLayer instance
	 */
	public function __construct(API $api, Request $request,
								ObjectBusinessLayer $businessLayer){
		parent::__construct($api, $request, $businessLayer, ObjectType::JOURNAL);
	}
}