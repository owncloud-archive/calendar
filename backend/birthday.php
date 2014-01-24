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

class Birthday extends Backend {

	public function __construct($api, $parameters){
		parent::__construct($api, 'Birthday');
	}

	public function cacheCalendars($userId) {
		return true;
	}

	public function cacheObjects($uri, $userId) {
		$this->findCalendar($uri, $userId);
		return false;
	}

	public function findCalendar($uri, $userId) {
		if($uri !== 'birthday') {
			throw new DoesNotExistException();
		}
		$calendar = new Calendar( array(
			'userid' 		=> $userId,
			'backend' 		=> $this->backend,
			'uri' 			=> 'birthdays',
			'displayname'	=> $this->api->getTrans()->t('Birthdays'),
			'components'	=> ObjectType::VEVENT,
			'ctag'			=> 1,
			'timezone'		=> 'UTC',
			'writable'		=> false,
		));
	}

	public function findCalendars($userId, $limit, $offset) {
		return array($this->findCalendar('birthdays', $userId));
	}

	public function findObject($uri, $uid, $userId) {
		$calendar = $this->findCalendar($uri, $userId);
		return $this->objectMapper->find($uid, $calendar->getId());
	}

	public function findObjects($uri, $userId, $limit, $offset) {
		$calendar = $this->findCalendar($uri, $userId);
		return $this->objectMapper->findAll($calendar->getId());
	}
}