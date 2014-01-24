<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backend;

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Db\Mapper;
use \OCA\Calendar\AppFramework\Db\DoesNotExistException;
use \OCA\Calendar\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\Object;
use \OCA\Calendar\Db\ObjectType;

use \OCA\Calendar\Db\Permissions;

class WebCal extends Backend {

	public function cacheCalendars($userId) {
		return true;
	}

	public function cacheObjects($calendarURI, $userId) {
		return true;
	}

	public function canStoreColor() {
		return false;
	}

	public function canStoreComponents() {
		return false;
	}

	public function canStoreDisplayname() {
		return false;
	}

	public function canStoreEnabled() {
		return false;
	}

	public function canStoreOrder() {
		return false;
	}

	public function getDisplayName() {
		return $this->api->getTrans()->t('WebCal-Backend');
	}

	public function getDescription() {
		return $this->api->getTrans()->t('The webcal-backend is a readonly backend that displays remote calendar files.');
	}
}