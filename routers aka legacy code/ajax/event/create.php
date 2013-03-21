<?php
/**
 * Copyright (c) 2013 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//more general checks
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();
//setup all registered calendar backends
OCA\Calendar::setupBackends();
//get current username to deal with
$userid = OCP\User::getUser();
//get the variables submitted by the user
$title  = (string) $_POST['title'];
$start  = (int)    $_POST['start'];
$end    = (int)    $_POST['end'];
$allDay = (bool)   $_POST['allDay'];
//some validation
if($end < $start){
	OCP\JSON::error(array('message'=>"$end is less than $start"));
	exit;
}
//initialize property array
$properties = array();
//get the user's the default calendar
$cal = OCA\Calendar::getUsersDefaultCalendar($userid);
//create the new calendar object
$createObject = OCA\Calendar::createObject($cal, $properties);
//was the object created successfully?
if($createObject){
	//yeah, it was
	OCP\JSON::success();
}else{
	//noop, something failed
	OCP\JSON::error();
}