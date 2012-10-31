<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();
//get the variables submitted by the user
$title  = (string) $_POST['title'];
$start  = (int)    $_POST['start'];
$end    = (int)    $_POST['end'];
$allDay = (bool)   $_POST['allDay'];
if($end < $start){
	OCP\JSON::error(array('message'=>"$end is less than $start");
	exit;
}
$properties = array();
$cal = OCA\Calendar::getUsersDefaultCalendar(OCP\User::getUser());
$createObject = OCA\Calendar::createObject($cal, $properties);
if($createObject){
	OCP\JSON::success();
}else{
	OCP\JSON::error();
}