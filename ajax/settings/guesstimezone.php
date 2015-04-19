<?php
/**
 * Copyright (c) 2011, 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$l = OCP\Util::getL10N('calendar');

$timezone = $_POST['timezone'];
try {
	$tz = new DateTimeZone($timezone);
} catch(\Exception $ex) {
	OC_JSON::error();
	exit;
}

if($timezone == OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timezone')) {
	OCP\JSON::success();
	exit;
}
OCP\Config::setUserValue(OCP\USER::getUser(), 'calendar', 'timezone', $timezone);
$message = array('message'=> $l->t('New Timezone:') . ' ' . $timezone);
OCP\JSON::success($message);
