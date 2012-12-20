<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
//get the timezone
$timezone = $_POST['tz'];
//get all valid timezones
$validtimezones = DateTimeZone::listIdentifiers();
$validtimezones = array_flip($validtimezones);
//check if the timezone is valid
if(array_key_exists($timezone, $validtimezones)){
	//update user's current timezone in database
	OCP\Config::setUserValue(OCP\User::getUser(), 'calendar', 'timezone', $timezone);
	OCP\JSON::success();
	exit;
}
OCP\JSON::error();