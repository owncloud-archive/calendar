<?php
/**
 * Copyright (c) 2013 Georg Ehrke <ownclouddev at georgswebsite dot de>
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
			return new Controller\Backend($c['API'], $c['Request'], $c['BackendMapper']);
		});

		//controller for calendars
		$this['CalendarController'] = $this->share(function($c){
			return new Controller\Calendar($c['API'], $c['Request'], $c['CachedCalendarMapper'], $c['BackendController'], $c['ObjectController']);
		});

		//controller for objects like events, journals, todos
		$this['ObjectController'] = $this->share(function($c){
			return new Controller\Object($c['API'], $c['Request'], $c['CachedObjectMapper'], $c['BackendController'], $c['CalendarController']);
		});

		//controller for settings
		$this['SettingsController'] = $this->share(function($c){
			return new Controller\Settings($c['API'], $c['Request']);
		});

		//controller for view
		$this['ViewController'] = $this->share(function($c){
			return new Controller\View($c['API'], $c['Request'], $c['BackendController']);
		});

		/**
		 * MAPPERS
		 */
		//mapper for backends
		$this['BackendMapper'] = $this->share(function($c){
			return new Mapper\Backend($c['API']);
		});

		//mapper for cached calendars
		$this['CachedCalendarMapper'] = $this->share(function($c){
			return new Mapper\CachedCalendar($c['API']);
		});

		//mapper for cached objects like events, journals, todos
		$this['CachedObjectMapper'] = $this->share(function($c){
			return new Mapper\CachedObject($c['API']);
		});
	}
}