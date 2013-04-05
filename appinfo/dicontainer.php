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
			return new Controller\Calendar($c['API'], $c['Request'], $c['CalendarMapper'], $c['BackendController'], $c['ObjectController']);
		});
		
		//controller for objects like events, journals, todos
		$this['ObjectController'] = $this->share(function($c){
			return new Controller\Object($c['API'], $c['Request'], $c['ObjectMapper'], $c['BackendController'], $c['CalendarController']);
		});
		
		//controller for settings
		$this['SettingsController'] = $this->share(function($c){
			return new Controller\Settings($c['API'], $c['Request'], $c['SettingsMapper']);
		});
		
		//controller for view
		$this['ViewController'] = $this->share(function($c){
			return new Controller\View($c['API'], $c['Request'], $c['ViewMapper']);
		});

		/**
		 * MAPPERS
		 */
		//mapper for calendars
		$this['BackendMapper'] = $this->share(function($c){
			return new Mapper\Backend($c['API']);
		});
				
		//mapper for calendars
		$this['CalendarMapper'] = $this->share(function($c){
			return new Mapper\Calendar($c['API']);
		});
		
		//mapper for objects like events, journals, todos
		$this['ObjectMapper'] = $this->share(function($c){
			return new Mapper\Object($c['API']);
		});
		
		//mapper for calendars
		$this['SettingsMapper'] = $this->share(function($c){
			return new Mapper\Calendar($c['API']);
		});
		
		//mapper for objects like events, journals, todos
		$this['ViewMapper'] = $this->share(function($c){
			return new Mapper\Object($c['API']);
		});

	}
}