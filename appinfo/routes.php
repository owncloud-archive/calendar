<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com> 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar;
//bootstrap the calendar app
require_once(__DIR__ . '/bootstrap.php');

//create alias for \OCA\AppFramework\App
use \OCA\AppFramework\App as App;

//!Index
$this->create('calendar_index', '/')->action(
	function($params){
		App::main('ViewController', 'index', $params, new DIContainer());
	}
);

//load calendar and goto a specific date
$this->create('calendar_index_goto_date', '/showDate/{date}')->action(
	function($params){
		App::main('ViewController', 'showDate', $params, new DIContainer());
	}
);

//load calendar and goto specific event
$this->create('calendar_index_goto_event', '/showEvent/{backend}/{uri}/{uid}')->action(
	function($params){
		App::main('ViewController', 'showEvent', $params, new DIContainer());
	}
);

//load printable view of specific date
$this->create('calendar_print_date_all', '/printDate/{date}')->action(
	function($params){
		App::main('ViewController', 'printDate', $params, new DIContainer());
	}
);

//load printable view of a calendar of a specific date
$this->create('calendar_print_date', '/printDate/{backend}/{uri}/{date}')->action(
	function($params){
		App::main('ViewController', 'printDate', $params, new DIContainer());
	}
);

//load printable view of a timerange
$this->create('calendar_print_timerange_all', '/printTimeRange/{from}/{to}')->action(
	function($params){
		App::main('ViewController', 'printTimeRange', $params, new DIContainer());
	}
);

//load printable view of a calendar in a specific timerange
$this->create('calendar_print_timerange', '/printTimeRange/{backend}/{uri}/{from}/{to}')->action(
	function($params){
		App::main('ViewController', 'printTimeRange', $params, new DIContainer());
	}
);

//load printable view of an event
$this->create('calendar_print_event', '/printEvent/{backend}/{uri}/{uid}')->action(
	function($params){
		App::main('ViewController', 'printEvent', $params, new DIContainer());
	}
);

//save current view
$this->create('calendar_set_view', '/setView/{view}')->action(
	function($params){
		App::main('ViewController', 'setView', $params, new DIContainer());
	}
);

//get all calendars
$this->create('calendar_get_all_calendars', '/calendars')->action(
	function($params){
		App::main('CalendarController', 'getAllCalendars', $params, new DIContainer());
	}
);

//get all properties of a calendar
$this->create('calendar_get_calendar', '/getCalendar/{backend}/{uri}')->action(
	function($params){
		App::main('CalendarController', 'getCalendarByURI', $params, new DIContainer());
	}
);

//get a specific property of a calendar
$this->create('calendar_get_calendars_property', '/getCalendar/{backend}/{uri}/{property}')->action(
	function($params){
		App::main('CalendarController', 'getCalendarProperty', $params, new DIContainer());
	}
);

//set a speficic property of a calendar
$this->create('calendar_set_property', '/setCalendar/{backend}/{uri}/{property}')->action(
	function($params){
		App::main('CalendarController', 'setCalendarProperty', $params, new DIContainer());
	}
);

//create a new calendar
$this->create('calendar_create_calendar', '/createCalendar')->action(
	function($params){
		App::main('CalendarController', 'createCalendar', $params, new DIContainer());
	}
);

//update a calendar
$this->create('calendar_update_calendar', '/updateCalendar/{backend}/{uri}')->action(
	function($params){
		App::main('CalendarController', 'updateCalendar', $params, new DIContainer());
	}
);

//delete a calendar
$this->create('calendar_delete_calendar', '/deleteCalendar/{backend}/{uri}')->action(
	function($params){
		App::main('CalendarController', 'deleteCalendar', $params, new DIContainer());
	}
);

//get events of a calendar in a specific timerange
$this->create('calendar_get_events', '/getEvents/{backend}/{uri}/{from}/{to}')->action(
	function($params){
		App::main('ObjectController', 'getEvents', $params, new DIContainer());
	}
);

//export all calendars
$this->create('calendar_export_all', '/export/all')->action(
	function($params){
		App::main('ExportController', 'exportAll', $params, new DIContainer());
	}
);

//export all calendars in a specific timerange
$this->create('calendar_export_all_timerange', '/export/all/{from}/{to}')->action(
	function($params){
		App::main('ExportController', 'exportAllInTimeRange', $params, new DIContainer());
	}
);

//export a calendar
$this->create('calendar_export_calendar', '/export/calendar/{backend}/{uri}')->action(
	function($params){
		App::main('ExportController', 'exportCalendar', $params, new DIContainer());
	}
);

//export a calendar in a specific timerange
$this->create('calendar_export_calendar_timerange', '/export/calendar/{backend}/{uri}/{from}/{to}')->action(
	function($params){
		App::main('ExportController', 'exportCalendarInTimeRange', $params, new DIContainer());
	}
);

//export an event
$this->create('calendar_export_event', '/export/event/{backend}/{uri}/{uid}')->action(
	function($params){
		App::main('ExportController', 'exportEvent', $params, new DIContainer());
	}
);

//import an ics file
$this->create('calendar_import_file', '/import/file/{file}')->action(
	function($params){
		App::main('ImportController', 'importFile', $params, new DIContainer());
	}
);

//import an ics file in a specific timerange
$this->create('calendar_import_file_timerange', '/import/file/{file}/{from}/{to}')->action(
	function($params){
		App::main('ImportController', 'importFileInTimeRange', $params, new DIContainer());
	}
);

//import raw ics
$this->create('calendar_import_raw', '/import/raw/{raw}')->action(
	function($params){
		App::main('ImportController', 'importRaw', $params, new DIContainer());
	}
);

//import raw ics in a specific timerange
$this->create('calendar_import_raw_timerange', '/import/raw/{raw}/{from}/{to}')->action(
	function($params){
		App::main('ImportController', 'importRawInTimeRange', $params, new DIContainer());
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



//!Import




$this->create('calendar_data', '/js/calendardata.js')->action(
	function($params){
		App::main('CalendarController', 'calendarDataJs', $params, new DIContainer());
	}
);

$this->create('view_data', '/js/viewdata.js')->action(
	function($params){
		App::main('ViewController', 'viewDataJs', $params, new DIContainer());
	}
);