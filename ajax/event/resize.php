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

$accessclass = $vevent->CLASS;
$permissions = OC_Calendar_App::getPermissions($id, OC_Calendar_App::EVENT, $accessclass);
if(!$permissions & OCP\PERMISSION_UPDATE) {
	OCP\JSON::error(array('message'=>'permission denied'));
	exit;
}

$delta = new DateInterval('P0D');
$delta->d = $_POST['dayDelta'];
$delta->i = $_POST['minuteDelta'];

OC_Calendar_App::isNotModified($vevent, $_POST['lastmodified']);

$dtend = OC_Calendar_Object::getDTEndFromVEvent($vevent);
$dtend->setDateTime($dtend->getDateTime()->add($delta));
unset($vevent->DURATION);


$now = new DateTime('now');
$now->setTimeZone(new \DateTimeZone('UTC'));
$lastModified = $vcalendar->create('LAST-MODIFIED');
$lastModified->setValue($now);
$vevent->LAST_MODIFIED = $lastModified;
$vevent->DTSTAMP = $now;

OC_Calendar_Object::edit($id, $vcalendar->serialize());
$lastmodified = $vevent->__get('LAST-MODIFIED')->getDateTime();
OCP\JSON::success(array('lastmodified'=>(int)$lastmodified->format('U')));
