<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev@georgswebsite.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
$data = $_POST['data'];
$data = explode(',', $data);
$data = end($data);
$data = base64_decode($data);
OCP\JSON::checkLoggedIn();
OCP\App::checkAppEnabled('calendar');
$import = new OC_Calendar_Import($data);
$import->setUserID(OCP\User::getUser());
$import->setTimeZone(OC_Calendar_App::$tz);
$import->disableProgressCache();
if(!$import->isValid()) {
	OCP\JSON::error();
	exit;
}
$newcalendarname = strip_tags($import->createCalendarName());
$newid = OC_Calendar_Calendar::addCalendar(OCP\User::getUser(),$newcalendarname,'VEVENT,VTODO,VJOURNAL',null,0,$import->createCalendarColor());
$import->setCalendarID($newid);
$import->import();
$count = $import->getCount();
if($count == 0) {
	OC_Calendar_Calendar::deleteCalendar($newid);
	OCP\JSON::error(array('message'=>OC_Calendar_App::$l10n->t('The file contained either no events or all events are already saved in your calendar.')));
}else{
	OCP\JSON::success(array('message'=>$count . ' ' . OC_Calendar_App::$l10n->t('events have been saved in the new calendar') . ' ' . $newcalendarname, 'eventSource'=>OC_Calendar_Calendar::getEventSourceInfo(OC_Calendar_Calendar::find($newid))));
}
