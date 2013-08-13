<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar;

use OCA\Calendar\AppFramework\Core\API;

require_once(__DIR__ . '/consts.php');;

$api = new API('calendar');
//add the navigation entry
$api->addNavigationEntry( array(
	'id' => 'calendar',
	'order' => 10,
	'href' => \OC_Helper::linkToRoute('calendar.view.index'),
	'icon' => \OCP\Util::imagePath( 'calendar', 'calendar.svg' ),
	'name' => \OC_L10N::get('calendar')->t('Calendar')
));

//register for cron job
$api->addRegularTask('OCA\Calendar\Cron', 'run');
//register admin settings
$api->registerAdmin('admin/settings');
//register for hooks
$api->connectHook('OC_User', 'post_createUser', '\OC\Calendar\Util\UserHooks', 'create');
$api->connectHook('OC_User', 'post_deleteUser', '\OC\Calendar\Util\UserHooks', 'delete');
//$api->connectHook();
//$api->connectJook();
//javascript for importing calendars in the files app
//$api->addScript('fileaction');
//search
//\OC_Search::registerProvider('\OCA\Calendar\SearchProvider');

//sharing
//\OCP\Share::registerBackend('calendar', '\OCA\Calendar\Share\Calendar');
//\OCP\Share::registerBackend('event', '\OCA\Calendar\Share\Event');
