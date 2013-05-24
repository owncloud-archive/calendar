<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//register admin settings
\OCP\App::registerAdmin('calendar', 'admin/settings');

//initialize a l10n object
$l10n = new OC_L10N('calendar');

//add the navigation entry
OCP\App::addNavigationEntry( array(
	//id of the calendar navigation entry
	'id' => 'calendar',
	//order of the calendar in the menu
	'order' => 10,
	//link to calendar app
	'href' => \OC_Helper::linkToRoute('calendar_index'),
	//icon of calendar app
	'icon' => OCP\Util::imagePath( 'calendar', 'calendar.svg' ),
	//localize "calendar"
	'name' => \OC_L10N::get('calendar')->t('Calendar')
));