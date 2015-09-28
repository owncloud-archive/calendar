<?php
/**
 * Copyright (c) 2011, 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * Copyright (c) 2015 Felix Tiede <info at pc-tiede dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::checkAppEnabled('contacts');
OCP\JSON::success(array('birthdays' => OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'contactbirthdays')));
