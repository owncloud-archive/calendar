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

class View extends \OCA\AppFramework\Controller {
	

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param ItemMapper $itemMapper: an itemwrapper instance
	 */
	public function __construct($api, $request){
		parent::__construct($api, $request);
	}


	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * Redirects to the index page
	 */
	public function redirectToIndex(){
		$url = $this->api->linkToRoute('calendar_index');
		return new RedirectResponse($url);
	}


	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function index(){

		// thirdparty javscripts
		$this->api->add3rdPartyScript('backbone-min');
		$this->api->add3rdPartyScript('fullcalendar-min');
		$this->api->add3rdPartyScript('timepicker-min');
		$this->api->add3rdPartyScript('tipsy-min');
		
		// thirdpary stylesheets
		$this->api->add3rdPartyStyle('fullcalendar');
		$this->api->add3rdPartyStyle('timepicker');
		$this->api->add3rdPartyStyle('tipsy.mod');
		
		// your own stuff
		$this->api->addStyle('style');

		$this->api->addScript('app');
		$this->api->addScript('calendar');
		$this->api->addScript('calendarlist');
		
		$this->api->addScript('custom views/basic2Weeks');
		$this->api->addScript('custom views/basic4Weeks');
		$this->api->addScript('custom views/listview');

		// example database access
		// check if an entry with the current user is in the database, if not
		// create a new entry

		$templateName = 'app';
		return $this->render($templateName, array());
	}
}
