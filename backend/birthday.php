<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * Copyright (c) 2014 Thomas Tanghus <thomas@tanghus.net>
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

class Birthday extends Backend {

	public function __construct($api, $parameters){
		parent::__construct($api, 'Birthday');
	}

	public function cacheObjects($uri, $userId) {
		return false;
	}

	public function canBeEnabled() {
		return \OCP\App::isEnabled('contacts');
	}

	public function findCalendar($uri, $userId) {
		if($uri !== 'birthday') {
			throw new DoesNotExistException('');
		}

		$calendar = new Calendar();
		$calendar->setUserId($userId)
			->setOwnerId($userId)
			->setBackend($this->backend)
			->setUri('birthday')
			->setDisplayname($this->api->getTrans()->t('Birthday'))
			->setComponents(Components::EVENT)
			->setCtag(1) //sum of all addressbook ctags
			->setTimezone(new TimeZone('UTC'))
			->setCruds(Permissions::READ + Permissions::SHARE);

		return $calendar;
	}

	public function findCalendars($userId) {
		return new CalendarCollection($this->findCalendar('birthday', $userId));
	}

	public function findObject($uri, $uid, $userId) {
		//birthday uri equals uri of contact
		return null;
	}

	public function findObjects($uri, $userId) {
		return new ObjectCollection();
	}
}