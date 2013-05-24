<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar;

class DIContainer extends \OCA\AppFramework\DependencyInjection\DIContainer {
	/**
	 * Define your dependencies in here
	 */
	public function __construct(){
		// tell parent container about the app name
		parent::__construct('calendar');

		/** 
		 * CONTROLLERS
		 */
		//controller for backends
		$this['BackendController'] = $this->share(function($c){
			return new Controller\Backend($c['API'], $c['Request'], $c['BackendBusinessLayer']);
		});

		//controller for calendars
		$this['CalendarController'] = $this->share(function($c){
			return new Controller\Calendar($c['API'], $c['Request'], $c['CalendarBusinessLayer']);
		});

		//controller for objects like events, journals, todos
		$this['ObjectController'] = $this->share(function($c){
			return new Controller\Object($c['API'], $c['Request'], $c['ObjectBusinessLayer']);
		});

		//controller for settings
		$this['SettingsController'] = $this->share(function($c){
			return new Controller\Settings($c['API'], $c['Request']);
		});

		//controller for view
		$this['ViewController'] = $this->share(function($c){
			return new Controller\View($c['API'], $c['Request']);
		});

		/**
		 * BUSINESSLAYERS
		 */
		//mapper for backends
		$this['BackendBusinessLayer'] = $this->share(function($c){
			return new BusinessLayer\BackendBusinessLayer($c['BackendMapper'], $c['API']);
		});

		//mapper for cached calendars
		$this['CalendarBusinessLayer'] = $this->share(function($c){
			return new BusinessLayer\CalendarBusinessLayer($c['CalendarCacheMapper'], $c['BackendBusinessLayer'], $c['API']);
		});

		//mapper for cached objects like events, journals, todos
		$this['ObjectBusinessLayer'] = $this->share(function($c){
			return new BusinessLayer\ObjectBusinessLayer($c['ObjectCacheMapper'], $c['CalendarBusinessLayer'], $c['BackendBusinessLayer'], $c['API']);
		});

		/**
		 * MAPPERS
		 */
		//mapper for backends
		$this['BackendMapper'] = $this->share(function($c){
			return new Db\BackendMapper($c['API']);
		});

		//mapper for cached calendars
		$this['CalendarCacheMapper'] = $this->share(function($c){
			return new Db\CalendarCacheMapper($c['API']);
		});

		//mapper for cached objects like events, journals, todos
		$this['ObjectCacheMapper'] = $this->share(function($c){
			return new Db\ObjectCacheMapper($c['API']);
		});
	}
}