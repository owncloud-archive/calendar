<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

$id = $_POST['id'];

$vcalendar = OC_Calendar_App::getVCalendar($id, false, false);
$vevent = $vcalendar->VEVENT;

$allday = $_POST['allDay'];
$delta = new DateInterval('P0D');
$delta->d = $_POST['dayDelta'];
$delta->i = $_POST['minuteDelta'];
OC_Calendar_App::isNotModified($vevent, $_POST['lastmodified']);

$dtstart = $vevent->DTSTART;
$dtend = OC_Calendar_Object::getDTEndFromVEvent($vevent);
if ($allday && $dtstart->hasTime()) {
	$dtstart['VALUE'] = $dtend['VALUE'] = 'date';
	$dtend->setDateTime($dtend->getDateTime()->modify('+1 day'));
}
if (!$allday && !$dtstart->hasTime()) {
	$dtstart['VALUE'] = $dtend['VALUE'] = 'date-time';
}
$dtstart->setDateTime($dtstart->getDateTime()->add($delta));
$dtend->setDateTime($dtend->getDateTime()->add($delta));
unset($vevent->DURATION);

$now = new DateTime('now');
$now->setTimeZone(new \DateTimeZone('UTC'));
$vevent->__get('LAST-MODIFIED')->setDateTime($now);
$vevent->DTSTAMP = $now;

try {
	OC_Calendar_Object::edit($id, $vcalendar->serialize());
} catch(Exception $e) {
	OCP\JSON::error(array('message'=>$e->getMessage()));
	exit;
}

$lastmodified = $vevent->__get('LAST-MODIFIED')->getDateTime();
OCP\JSON::success(array('lastmodified'=>(int)$lastmodified->format('U')));
