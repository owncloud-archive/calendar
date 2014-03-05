<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar;

use \OCA\Calendar\AppFramework\Core\API;

$api = new API('calendar');

/*
 * Constants for calendar:
 */
//current version of json api
define('OCA\Calendar\JSONAPIVERSION', '1.0');

//current version of php api
define('OCA\Calendar\PHPAPIVERSION',  '1.0');

/*
 * add navigation entry
 */
\OCP\App::addNavigationEntry(array(
	'id' => 'calendar',
	'order' => 10,
	'href' => \OCP\Util::linkToRoute('calendar.view.index'),
	'icon' => \OCP\Util::imagePath('calendar', 'calendar.svg'),
	'name' => \OC_L10N::get('calendar')->t('Calendar'),
));

/*
 * register things like cron, admin page, hooks, search, sharing, etc.
 */
$api->addRegularTask('OCA\Calendar\Cron', 'run');
$api->registerAdmin('admin/settings');
$api->connectHook('OC_User', 'post_createUser', '\OC\Calendar\Util\UserHooks', 'create');
$api->connectHook('OC_User', 'post_deleteUser', '\OC\Calendar\Util\UserHooks', 'delete');
//$api->connectHook();
//$api->connectJook();
//\OC_Search::registerProvider('\OCA\Calendar\SearchProvider');
//\OCP\Share::registerBackend('calendar', '\OCA\Calendar\Share\Calendar');
//\OCP\Share::registerBackend('event', '\OCA\Calendar\Share\Event');

/*
 * add a global script for calendar import
 */
//$api->addScript('fileaction');