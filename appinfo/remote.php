<?php
/**
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
if (!\OC::$server->getAppManager()->isInstalled('calendar')) {
	throw new Exception('App not installed: calendar');
}

if (substr(OCP\Util::getRequestUri(), 0, strlen(OC_App::getAppWebPath('calendar') . '/caldav.php')) == OC_App::getAppWebPath('calendar') . '/caldav.php') {
	$baseuri = OC_App::getAppWebPath('calendar') . '/caldav.php';
}

// only need authentication apps
$RUNTIME_APPTYPES = array('authentication');
OC_App::loadApps($RUNTIME_APPTYPES);
if (\OC::$server->getAppManager()->isInstalled('contacts')) {
	\OCP\Share::registerBackend('addressbook', 'OCA\Contacts\Share\Addressbook', 'contact');
}

// Backends
$authBackend = new \OC\Connector\Sabre\Auth();
$principalBackend = new \OC\Connector\Sabre\Principal(
	\OC::$server->getConfig(),
	\OC::$server->getUserManager()
);
$caldavBackend    = new \OCA\Calendar\Sabre\Backend();

// Root nodes
$Sabre_CalDAV_Principal_Collection = new \Sabre\CalDAV\Principal\Collection($principalBackend);
$Sabre_CalDAV_Principal_Collection->disableListing = true; // Disable listening

$calendarRoot = new \OCA\Calendar\Sabre\CalendarRoot($principalBackend, $caldavBackend);
$calendarRoot->disableListing = true; // Disable listening

$nodes = array(
	$Sabre_CalDAV_Principal_Collection,
	$calendarRoot,
);

// Fire up server
$server = new \Sabre\DAV\Server($nodes);
$server->httpRequest->setUrl(\OC::$server->getRequest()->getRequestUri());
$server->setBaseUri($baseuri);
// Add plugins
$server->addPlugin(new \OC\Connector\Sabre\MaintenancePlugin());
$server->addPlugin(new \Sabre\DAV\Auth\Plugin($authBackend,'ownCloud'));
$server->addPlugin(new \Sabre\CalDAV\Plugin());
$server->addPlugin(new \Sabre\DAVACL\Plugin());
$server->addPlugin(new \Sabre\CalDAV\ICSExportPlugin());
$server->addPlugin(new \OC\Connector\Sabre\ExceptionLoggerPlugin('caldav', \OC::$server->getLogger()));
$server->addPlugin(new \OC\Connector\Sabre\AppEnabledPlugin(
	'calendar',
	OC::$server->getAppManager()
));

// And off we go!
$server->exec();
