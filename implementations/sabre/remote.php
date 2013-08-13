<?php
/**
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Implementations\Sabre;

use \OCA\Calendar\AppFramework\Core\API;

use \OCA\Calendar\BusinessLayer\BackendBusinessLayer;
use \OCA\Calendar\Db\BackendMapper;

OCP\App::checkAppEnabled('calendar');

//check which url is being used to access CalDAV
$requestURI = \OCP\Util::getRequestUri();
$calDAVRoute = \OCP\Util::linkToRoute('calendar_caldav');
$routelength = strlen($calDAVRoute);

if( substr($requestURI, 0, $routelength) === $calDAVRoute) {
	$baseuri = $calDAVRoute;
}

// we need filesystem for the local file calendar backend
$RUNTIME_APPTYPES = array('authentication', 'filesystem');
\OC_App::loadApps($RUNTIME_APPTYPES);

// Backends
$authBackend		= new Auth();
$principalBackend	= new Principal();
$caldavBackend		= new CalDAV();
$requestBackend		= new Request();

// Root nodes
$principalCollection = new Collection($principalBackend);
$principalCollection->disableListing = true; // Disable listening

$calendarRoot = new CalendarRoot($principalBackend, $caldavBackend);
$calendarRoot->disableListing = true; // Disable listening

$nodes = array(
	$principalCollection,
	$calendarRoot,
);

// Fire up server
$server = new Sabre_DAV_Server($nodes);
$server->httpRequest = $requestBackend;
$server->setBaseUri($baseuri);
// Add plugins
$server->addPlugin(new Sabre_DAV_Auth_Plugin($authBackend, 'ownCloud'));
$server->addPlugin(new Sabre_CalDAV_Plugin());
$server->addPlugin(new Sabre_DAVACL_Plugin());
$server->addPlugin(new Sabre_DAV_Browser_Plugin(false)); // Show something in the Browser, but no upload
$server->addPlugin(new Sabre_CalDAV_ICSExportPlugin());

// And off we go!
$server->exec();