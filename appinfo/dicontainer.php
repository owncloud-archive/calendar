<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar;

class DIContainer extends \OCA\AppFramework\DIContainer {
	/**
	 * Define your dependencies in here
	 */
	public function __construct(){
		// tell parent container about the app name
		parent::__construct('calendar');

		/** 
		 * CONTROLLERS
		 */
		$this['ItemController'] = $this->share(function($c){
			return new ItemController($c['API'], $c['Request'], $c['ItemMapper']);
		});

		$this['SettingsController'] = $this->share(function($c){
			return new SettingsController($c['API'], $c['Request']);
		});

		/**
		 * MAPPERS
		 */
		$this['ItemMapper'] = $this->share(function($c){
			return new ItemMapper($c['API']);
		});
	}
}