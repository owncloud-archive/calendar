<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// Init owncloud


$l = OCP\Util::getL10N('calendar');

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

// Get data
if( isset( $_POST['defaultalarms'] ) ) {
    $defaultalarms = $_POST['defaultalarms'];
    $match = preg_match('/^(?:display){0,1}?\b\|?(?:email){0,1}?\b\|?(?:webhook){0,1}?\b$/i', $defaultalarms);
    if ($match === 1 OR empty($defaultalarms)) {
        OCP\Config::setUserValue( OCP\USER::getUser(), 'calendar', 'defaultalarms', strtoupper($defaultalarms) );
        OCP\JSON::success(array('data' => array( 'message' => $l->t('Default reminders changed') )));
    } else {
        OCP\JSON::error(array('data' => array( 'message' => $l->t('Invalid request') )));
    }
}else{
    OCP\JSON::error(array('data' => array( 'message' => $l->t('Invalid request') )));
}
