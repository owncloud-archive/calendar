<?php
/**
 * Copyright (c) 2011 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('calendar');

// Create default calendar ...
$calendars = OC_Calendar_Calendar::allCalendars(OCP\USER::getUser(), false);
if( count($calendars) == 0) {
	OC_Calendar_Calendar::addDefaultCalendars(OCP\USER::getUser());
	$calendars = OC_Calendar_Calendar::allCalendars(OCP\USER::getUser(), true);
}

//Fix currentview for fullcalendar
if(OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month') == "oneweekview") {
	OCP\Config::setUserValue(OCP\USER::getUser(), "calendar", "currentview", "agendaWeek");
}
if(OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month') == "onemonthview") {
	OCP\Config::setUserValue(OCP\USER::getUser(), "calendar", "currentview", "month");
}
if(OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month') == "listview") {
	OCP\Config::setUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'agendaDay');
}
if(OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month') == 'list') {
	OCP\Config::setUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'agendaDay');
}

OCP\Util::addScript('calendar', '../3rdparty/fullcalendar/js/fullcalendar');
OCP\Util::addStyle('calendar', '../3rdparty/fullcalendar/css/fullcalendar');
OCP\Util::addScript('calendar', '../3rdparty/timepicker/js/jquery.ui.timepicker');
OCP\Util::addStyle('calendar', '../3rdparty/timepicker/css/jquery.ui.timepicker');
if(OCP\Config::getUserValue(OCP\USER::getUser(), "calendar", "timezone") == null || OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timezonedetection') == 'true') {
	OCP\Util::addScript('calendar', 'geo');
}
OCP\Util::addScript('calendar', 'calendar');
OCP\Util::addStyle('calendar', 'style');
OCP\Util::addScript('calendar', '../3rdparty/jquery.multiselect/js/jquery.multiselect');
OCP\Util::addStyle('calendar', '../3rdparty/jquery.multiselect/css/jquery.multiselect');
OCP\Util::addScript('calendar','jquery.multi-autocomplete');
OCP\Util::addScript('core','tags');
OCP\Util::addScript('calendar','on-event');
OCP\Util::addScript('calendar','settings');
OCP\App::setActiveNavigationEntry('calendar_index');
$tmpl = new OCP\Template('calendar', 'calendar', 'user');
$timezone=OCP\Config::getUserValue(OCP\USER::getUser(),'calendar','timezone','');
$tmpl->assign('timezone',$timezone);
$tmpl->assign('timezones',DateTimeZone::listIdentifiers());

if(array_key_exists('showevent', $_GET)) {
	$tmpl->assign('showevent', $_GET['showevent']);
}
$tmpl->printPage();
