<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de> 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar;
	
//bootstrap the calendar
require_once \OC_App::getAppPath('calendar') . '/appinfo/bootstrap.php';

function routerCheck($types = array()){
	//is the user logged in?
	\OCP\User::checkLoggedIn();
	//is the calendar app enabled?
	\OCP\App::checkAppEnabled('calendar');
	//create a default calendar if no one exists
	//Util::createDefaultCalendar(\OCP\User::getUser());
	foreach($types as $type){
		switch($type){
			case 'page':
				
				break;
			case 'ajax':
				
				break;
			default:
				break;
		}
	}
}

/**
 * Routes
 */
//the calendar itself
//objectid may be used to jump to an event
$this->create('calendar_index', '/{objectid}')
	 ->defaults(array('objectid' => null))
	 ->requirements(array('objectid'))
	 ->action(function($params){
		$objectid = $param['objectid'];
		routerCheck(array('page'));
		require_once \OC_App::getAppPath('calendar') . '/routers/index.php';
	});

//the interface for fetching the json events
//md5 is the md5 hash of the calendarid
$this->create('calendar_get_events', '/events/{calendarid}')
	 ->action(function($params){
	 	$calendarid = $params['calendarid'];
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/events.php';
	});

$this->create('calendar_set_view', '/setView/{view}')
	 ->action(function($params){
	 	$view = $params['view'];
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/changeview.php';
	});

//attendees

//backend

//caching

//calendar management
$this->create('calendar_ajax_calendar_create', '/createCalendar')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/create.php';
	}
);

$this->create('calendar_ajax_calendar_delete', '/deleteCalendar')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/delete.php';
	}
);

$this->create('calendar_ajax_calendar_edit', '/editCalendar')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/edit.php';
	}
);

$this->create('calendar_ajax_calendar_get', '/getCalendar')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/get.php';
	}
);

$this->create('calendar_ajax_calendar_trigger', '/triggerCalendar')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/trigger.php';
	}
);

//categories

//event management
$this->create('calendar_ajax_event_create', '/createEvent')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/create.php';
	}
);

$this->create('calendar_ajax_event_delete', '/deleteEvent')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/delete.php';
	}
);

$this->create('calendar_ajax_event_edit', '/editEvent')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/edit.php';
	}
);

$this->create('calendar_ajax_event_get', '/getEvent')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/get.php';
	}
);

$this->create('calendar_ajax_event_move', '/moveEvent')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/get.php';
	}
);

$this->create('calendar_ajax_event_resize', '/resizeEvent')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/calendar/get.php';
	}
);

//export & import
$this->create('calendar_ajax_export', '/export')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/export/export.php';
	}
);

$this->create('calendar_ajax_import', '/import')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/import/import.php';
	}
);

$this->create('calendar_ajax_import_drop', '/dropimport')->post()->action(
	function($params){
		routerCheck(array('ajax'));
		require_once \OC_App::getAppPath('calendar') . '/routers/ajax/import/drop.php';
	}
);

//settings



//user interface
