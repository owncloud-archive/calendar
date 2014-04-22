<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$calendarid = $_POST['calendarid'];
$calendar = OC_Calendar_App::getCalendar($calendarid, true, true);
if(!$calendar) {
	OCP\JSON::error(array('message'=>'permission denied'));
	exit;
}

try {
	OC_Calendar_Calendar::setCalendarActive($calendarid, $_POST['active']);
} catch(Exception $e) {
	OCP\JSON::error(array('message'=>$e->getMessage()));
	exit;
}

// We can skip security here, because we just checked it above.
$calendar = OC_Calendar_App::getCalendar($calendarid, false);
OCP\JSON::success(array(
	'active' => $calendar['active'],
	'eventSource' => OC_Calendar_Calendar::getEventSourceInfo($calendar),
));