<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//get the current userid
$userid = \OCP\User::getUser();
//fetch all eventSources
//$eventSources = OCA\Calendar\Util::fetchEventSources($userid);
//fetch all categories 
//$categories = OCA\Calendar\Util::getCategoryOptions($userid);
//get some translations necessary for the calendar app
$agendaTime = ((int) \OCP\Config::getUserValue(\OCP\User::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt') .  '{ - ' . ((int) \OCP\Config::getUserValue(\OCP\User::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt') .  '}';
$defaultTime = (int) \OCP\Config::getUserValue(\OCP\User::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt';
$defaultView = \OCP\Config::getUserValue(\OCP\User::getUser(), 'calendar', 'currentview', 'month');;
$firstDay = \OCP\Config::getUserValue(\OCP\User::getUser(), 'calendar', 'firstday', 'mo') == 'mo' ? '1' : '0';
//Scripts and Styles
\OCP\Util::addscript('calendar/3rdparty/fullcalendar', 'fullcalendar');
\OCP\Util::addScript('calendar/3rdparty/jstz', 'jstz.min');
\OCP\Util::addscript('calendar/3rdparty/timepicker', 'jquery.ui.timepicker');
\OCP\Util::addscript('calendar/3rdparty/tipsy', 'tipsy');
\OCP\Util::addscript('contacts','jquery.multi-autocomplete');
//\OCP\Util::addscript('', 'jquery.multiselect');
//\OCP\Util::addscript('','oc-vcategories');
\OCP\Util::addscript('calendar', 'app');
\OCP\Util::addscript('calendar', 'calendar');
\OCP\Util::addScript('calendar', 'calendarlist');
\OCP\Util::addscript('calendar', 'custom views/basic2Weeks');
\OCP\Util::addScript('calendar', 'custom views/basic4Weeks');
\OCP\Util::addscript('calendar', 'custom views/listview');
\OCP\Util::addStyle('calendar', 'style');
\OCP\Util::addStyle('calendar/3rdparty/fullcalendar', 'fullcalendar');
\OCP\Util::addStyle('calendar/3rdparty/timepicker', 'jquery.ui.timepicker');
\OCP\Util::addStyle('calendar/3rdparty/tipsy', 'tipsy');
//\OCP\Util::addStyle('', 'jquery.multiselect');
//make the calendar's navigation entry active
OCP\App::setActiveNavigationEntry('calendar_index');
//init ownCloud's template system
$tmpl = new OCP\Template('calendar', 'app', 'user');
//assign important variables
$tmpl->assign('eventSources', $eventSources, false);
$tmpl->assign('categories', $categories);
$tmpl->assign('agendatime', $agendaTime);
$tmpl->assign('defaulttime', $defaultTime);
$tmpl->assign('firstDay', $firstDay);
$tmpl->assign('defaultView', $defaultView);
$tmpl->assign('calendars', $writablecalendars);
$tmpl->assign('subscriptions', $readablecalendars);
//let's go - print the page
$tmpl->printPage();