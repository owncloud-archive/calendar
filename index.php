<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//more general checks
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('calendar');

// Create default calendar with read&write permission ...
if( count(OCA\Calendar::getAll(OCP\USER::getUser(), true)) == 0){
	OCA\Calendar::addCalendar(OCP\User::getUser, 'database', 'rw');
}

$calendars = OCA\Calendar::
//fetch eventSources
$eventSources;
foreach($calendar as $)





// Create default calendar ...
$calendars = OC_Calendar_Calendar::allCalendars(OCP\USER::getUser(), false);
if( count($calendars) == 0){
	OC_Calendar_Calendar::addCalendar(OCP\USER::getUser(),'Default calendar');
	$calendars = OC_Calendar_Calendar::allCalendars(OCP\USER::getUser(), true);
}

//fetch eventSources
$eventSources = array();
foreach($calendars as $calendar){
	if($calendar['active'] == 1) {
		$eventSources[] = OC_Calendar_Calendar::getEventSourceInfo($calendar);
	}
}
$events_baseURL = OCP\Util::linkTo('calendar', 'ajax/events.php');
$eventSources[] = array('url' => $events_baseURL.'?calendar_id=shared_events',
		'backgroundColor' => '#1D2D44',
		'borderColor' => '#888',
		'textColor' => 'white',
		'editable' => 'false');

OCP\Util::emitHook('OC_Calendar', 'getSources', array('sources' => &$eventSources));
$categories = OC_Calendar_App::getCategoryOptions();


$firstDay = (OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'firstday', 'mo') == 'mo' ? '1' : '0');
$defaultView = OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month');
$agendatime = ((int) OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt') .  '{ - ' . ((int) OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt') .  '}';
$defaulttime = ((int) OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt');
OCP\Util::addscript('3rdparty/fullcalendar', 'fullcalendar');
OCP\Util::addStyle('3rdparty/fullcalendar', 'fullcalendar');
OCP\Util::addscript('3rdparty/timepicker', 'jquery.ui.timepicker');
OCP\Util::addStyle('3rdparty/timepicker', 'jquery.ui.timepicker');
if(OCP\Config::getUserValue(OCP\USER::getUser(), "calendar", "timezone") == null || OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timezonedetection') == 'true'){
	OCP\Util::addscript('calendar', 'geo');
}
OCP\Util::addscript('calendar', 'calendar');
OCP\Util::addscript('calendar', 'listview');
OCP\Util::addscript('calendar', 'init');
OCP\Util::addStyle('calendar', 'style');
OCP\Util::addscript('', 'jquery.multiselect');
OCP\Util::addStyle('', 'jquery.multiselect');
OCP\Util::addscript('contacts','jquery.multi-autocomplete');
OCP\Util::addscript('','oc-vcategories');
OCP\App::setActiveNavigationEntry('calendar_index');
$tmpl = new OCP\Template('calendar', 'calendar', 'user');
$tmpl->assign('eventSources', $eventSources,false);
$tmpl->assign('categories', $categories);
$tmpl->assign('firstDay', $firstDay);
$tmpl->assign('defaultView', $defaultView);
$tmpl->assign('agendatime', $agendatime);
$tmpl->assign('defaulttime', $defaulttime);
if(array_key_exists('showevent', $_GET)){
	$tmpl->assign('showevent', $_GET['showevent'], false);
}
$tmpl->printPage();
