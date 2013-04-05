<?php
/**
 * Copyright (c) 2013 Georg Ehrke <ownclouddev at georgswebsite dot de> 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar;
//bootstrap the calendar app
require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/dicontainer.php');

//create alias for \OCA\AppFramework\App
use \OCA\AppFramework\App as App;

//!Index
$this->create('calendar_index', '/')->action(
	function($params){
		App::main('ViewController', 'index', $params, new DIContainer());
	}
);

//!Goto date / event
$this->create('calendar_index_goto_date', '/show/date/{date}')->action(
	function($params){
		App::main('ViewController', 'index', $params, new DIContainer());
	}
);

$this->create('calendar_index_goto_event', '/show/event/{event}')->action(
	function($params){
		App::main('ViewController', 'index', $params, new DIContainer());
	}
);


//!Print date / event
$this->create('calendar_print_date_all', '/print/date/{date}')->action(
	function($params){
		App::main('ViewController', 'printable', $params, new DIContainer());
	}
);

$this->create('calendar_print_date', '/print/date/{calendar}/{date}')->action(
	function($params){
		App::main('ViewController', 'printable', $params, new DIContainer());
	}
);

$this->create('calendar_print_event', '/print/event/{event}')->action(
	function($params){
		App::main('ViewController', 'printable', $params, new DIContainer());
	}
);


//!View Configuration
$this->create('calendar_set_view', '/set/view/{view}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);


//!Calendar
$this->create('calendar_get_all_calendars', '/calendar')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);


$this->create('calendar_get_calendar', '/calendar/{uri}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_get_calendars_property', '/calendar/{uri}/get/{property}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_set_property', '/set/calendar/{uri}/{property}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_create_calendar', '/create/calendar')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_update_calendar', '/update/calendar/{uri}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_delete_calendar', '/delete/calendar/{uri}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_disable_calendar', '/set/disable/calendar/{uri}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_enable_calendar', '/set/enable/calendar/{uri}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);


//!Events
$this->create('calendar_set_view', '/set/view/{view}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);


//!Attendees
$this->create('calendar_set_view', '/set/view/{view}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);


//!Reminders
$this->create('calendar_set_view', '/set/view/{view}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);


//!Repeating
$this->create('calendar_set_view', '/set/view/{view}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);


//!Export
$this->create('calendar_export_all', '/export/all')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_export_all_timerange', '/export/all/{from}/{to}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_export_calendar', '/export/calendar/{calendar}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_export_calendar_timerange', '/export/calendar/{calendar}/{from}/{to}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_export_event', '/export/event/{event}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);


//!Import
$this->create('calendar_import_file', '/import/file/{file}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_import_file_timerange', '/import/file/{file}/{from}/{to}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_import_raw', '/import/raw/{raw}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

$this->create('calendar_import_raw_timerange', '/import/raw/{raw}/{from}/{to}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);