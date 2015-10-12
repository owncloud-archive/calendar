<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// Init owncloud

$l = OCP\Util::getL10N('calendar');

// Check if the app is enabled
OCP\JSON::checkAppEnabled('calendar');

// Get data
if( isset( $_POST['timezone'] ) ) {

	// normal operation?
	if (OCP\User::isLoggedIn()) {

		// additional check
		OCP\JSON::callCheck();

		// set the value
		OCP\Config::setUserValue( OCP\USER::getUser(), 'calendar', 'timezone', $_POST['timezone'] );

	// public link-shared calendar
	} elseif (\OC::$server->getSession()->exists('public_link_token')) {
		// save the value in session
		\OC::$server->getSession()->set('public_link_timezone', $_POST['timezone']);
	
	// this isn't right...
	} else {
		OCP\JSON::error(array('data' => array( 'message' => $l->t('Invalid request') )));
		exit;
	}
	
	// result
	OCP\JSON::success(array('data' => array( 'message' => $l->t('Timezone changed') )));
	
// no data
} else {
	OCP\JSON::error(array('data' => array( 'message' => $l->t('Invalid request') )));
}
