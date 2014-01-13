<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * Copyright (c) 2012 Bernhard Posselt <nukeawhale@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\DependencyInjection;

use \OC\AppFramework\DependencyInjection\DIContainer as AppFrameworkDIContainer;

use OCA\Calendar\Controller\BackendController;
use OCA\Calendar\Controller\CalendarController;
use OCA\Calendar\Controller\ObjectController;
use OCA\Calendar\Controller\EventsController;
use OCA\Calendar\Controller\JournalsController;
use OCA\Calendar\Controller\TodosController;
use OCA\Calendar\Controller\SettingsController;
use OCA\Calendar\Controller\ViewController;
use OCA\Calendar\BusinessLayer\BackendBusinessLayer;
use OCA\Calendar\BusinessLayer\CalendarBusinessLayer;
use OCA\Calendar\BusinessLayer\ObjectBusinessLayer;
use OCA\Calendar\Db\BackendMapper;
use OCA\Calendar\Db\CalendarMapper;
use OCA\Calendar\Db\ObjectMapper;

class DIContainer extends AppFrameworkDIContainer {
	/**
	 * Put your class dependencies in here
	 * @param string $appName the name of the app
	 */
	public function __construct(){

		parent::__construct('calendar');

		/** 
		 * CONTROLLERS
		 */
		//controller for backends
		$this['BackendController'] = $this->share(function($c){
			return new BackendController($c['API'], $c['Request'], $c['BackendBusinessLayer']);
		});

		//controller for calendars
		$this['CalendarsController'] = $this->share(function($c){
			return new CalendarController($c['API'], $c['Request'], $c['CalendarBusinessLayer']);
		});

		//controller for objects like events, journals, todos
		$this['ObjectsController'] = $this->share(function($c){
			return new ObjectController($c['API'], $c['Request'], $c['ObjectBusinessLayer']);
		});

		//controller for events
		$this['EventsController'] = $this->share(function($c){
			return new EventsController($c['API'], $c['Request'], $c['ObjectBusinessLayer']);
		});

		//controller for todos
		$this['TodosController'] = $this->share(function($c){
			return new TodosController($c['API'], $c['Request'], $c['ObjectBusinessLayer']);
		});

		//controller for journals
		$this['JournalsController'] = $this->share(function($c){
			return new JournalsController($c['API'], $c['Request'], $c['ObjectBusinessLayer']);
		});

		//controller for view
		$this['ViewController'] = $this->share(function($c){
			return new ViewController($c['API'], $c['Request'], $c['CalendarBusinessLayer'], $c['ObjectBusinessLayer']);
		});

		/**
		 * BUSINESSLAYERS
		 */
		//mapper for backends
		$this['BackendBusinessLayer'] = $this->share(function($c){
			return new BackendBusinessLayer($c['BackendMapper'], $c['API']);
		});

		//mapper for cached calendars
		$this['CalendarBusinessLayer'] = $this->share(function($c){
			return new CalendarBusinessLayer($c['CalendarMapper'], $c['BackendBusinessLayer'], $c['API'], $c['TimeFactory']);
		});

		//mapper for cached objects like events, journals, todos
		$this['ObjectBusinessLayer'] = $this->share(function($c){
			return new ObjectBusinessLayer($c['ObjectMapper'], $c['CalendarBusinessLayer'], $c['BackendBusinessLayer'], $c['API'], $c['TimeFactory']);
		});

		/**
		 * MAPPERS
		 */
		//mapper for backends
		$this['BackendMapper'] = $this->share(function($c){
			return new BackendMapper($c['API']);
		});

		//mapper for cached calendars
		$this['CalendarMapper'] = $this->share(function($c){
			return new CalendarMapper($c['API']);
		});

		//mapper for cached objects like events, journals, todos
		$this['ObjectMapper'] = $this->share(function($c){
			return new ObjectMapper($c['API']);
		});
	}
}