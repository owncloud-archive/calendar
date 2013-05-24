<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//!Register hooks

//!~ for user management
\OCP\Util::connectHook('OC_User', 'post_createUser', 'OC_Calendar_Hooks', 'createUser');
\OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OC_Calendar_Hooks', 'deleteUser');

//!~ for file management
//\OCP\Util::connectHook();
//\OCP\Util::connectJook();

//!Load javascript for calendar import in files app
//\OCP\Util::addScript('calendar', 'fileactions');

//!Register backends
//!~ search
//\OC_Search::registerProvider('\OCA\Calendar\SearchProvider');

//!~ sharing
//\OCP\Share::registerBackend('calendar', '\OCA\Calendar\Share\Calendar');
//\OCP\Share::registerBackend('event', '\OCA\Calendar\Share\Event');