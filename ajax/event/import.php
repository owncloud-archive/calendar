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

$errarr = OC_Calendar_Object::validateRequest($_POST);
if($errarr) {
	//show validate errors
	OCP\JSON::error($errarr);
	exit;
}else{
	$cal = $_POST['calendar'];
	$vcalendar = OC_Calendar_Object::createVCalendarFromRequest($_POST);
	try {
		OC_Calendar_Object::add($cal, $vcalendar->serialize());
	} catch(Exception $e) {
		OCP\JSON::error(array('message'=>$e->getMessage()));
		exit;
	}
	OCP\JSON::success();
}