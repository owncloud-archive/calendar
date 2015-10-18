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

$regex = "/^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?:\:\d{2,5})?(?:[\/?#]\S*)?$/iu";
// Get data
if( isset( $_POST['webhookdefaulturl'] ) ) {
    $webhookdefaulturl = trim($_POST['webhookdefaulturl']);
    $match = preg_match($regex, $webhookdefaulturl);
    if ($match === 1 OR empty($webhookdefaulturl)) {
        OCP\Config::setUserValue( OCP\USER::getUser(), 'calendar', 'webhookdefaulturl', $webhookdefaulturl );
        OCP\JSON::success(array('data' => array( 'message' => $l->t('Default webhook url reminder changed') )));
    } else {
        OCP\JSON::error(array('data' => array( 'message' => $l->t('Url is invalid') )));
    }
}else{
    OCP\JSON::error(array('data' => array( 'message' => $l->t('Invalid request') )));
}

