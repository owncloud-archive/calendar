<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
define('DEBUG', TRUE);

//bootstrap the calendar
require_once \OC_App::getAppPath('calendar') . '/appinfo/bootstrap.php';
//register admin settings
\OCP\App::registerAdmin('calendar', 'admin/settings');
//initialize a l10n object
$l10n = new OC_L10N('calendar');
//add the navigation entry
OCP\App::addNavigationEntry( array(
  'id' => 'calendar_index',
  'order' => 10,
  'href' => \OC_Helper::linkToRoute('calendar_index'),
  'icon' => OCP\Util::imagePath( 'calendar', 'icon.svg' ),
  'name' => $l10n->t('Calendar')));