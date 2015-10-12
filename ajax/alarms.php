<?php

/**
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();
$l = OC_L10N::get('calendar');

$alarms = OC_Calendar_Object::getAlarmsToDisplay();

$alarmsIdsSent = array();
$ouput = array();
$timeFormat = OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'H:i' : 'h:i a';
$dateFormat = OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'dateformat', 'dd-mm-yy') == 'dd-mm-yy' ? 'd-m-Y' : 'm-d-Y';
$tz = OC_Calendar_App::getTimezone();
$midnight = new DateTime('now', new DateTimeZone($tz));
$midnight->setTime(23, 59, 59);

while($row = $alarms->fetchRow()){

	$startDate = new DateTime($row['startdate'], new DateTimeZone('UTC'));
	$startDate->setTimezone(new DateTimeZone($tz));
	$oneDay = 60 * 60 * 24;

	if($startDate->getTimestamp() <= $midnight->getTimestamp()){

		$ouput[] = $l->t('Reminder (%s): %s is starting at %s', array(
		  $row['displayname'],
		  $row['summary'],
		  $startDate->format($timeFormat)));
	}else{

		$ouput[] = $l->t('Reminder (%s): %s is starting at %s on %s', array(
		  $row['displayname'],
		  $row['summary'],
		  $startDate->format($timeFormat),
		  $startDate->format($dateFormat)
		));
	}


	$alarmsIdsSent[] = $row['id'];
}

OC_Calendar_Object::setAlarmsSent($alarmsIdsSent);
OCP\JSON::success(array('events' => $ouput));
