<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Backend;

use \OC\AppFramework\Core\API;
use \OC\AppFramework\Db\Mapper;
use \OC\AppFramework\Db\DoesNotExistException;
use \OC\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\Object;
use \OCA\Calendar\Db\ObjectType;

use \OCA\Calendar\Db\Permissions;

class Anniversary extends Backend {

	private $calendarURI;

	public function __construct($api, $parameters){
		parent::__construct($api, 'Anniversary');
		$this->calendarURI = 'anniversary';
	}

	public function cacheCalendars($userId) {
		return false;
	}

	public function cacheObjects($uri, $userId) {
		return false;
	}

	public function findCalendar($uri, $userId) {
		if($uri !== $this->calendarURI) {
			throw new DoesNotExistException();
		}

		$calendar = new Calendar();
		$calendar->setUserId($userId)
				 ->setOwnerId($userId)
				 ->setBackend($this->backend)
				 ->setUri($this->calendarURI)
				 ->setDisplayname()
				 ->setComponents(Components::EVENT)
				 ->setCtag()
				 ->setTimezone(new TimeZone('UTC'))
				 ->setCruds(Permissions::READ + Permissions::SHARE);

		return $calendar;
	}

	public function findCalendars($userId) {
		return array(
			$this->findCalendar($this->calendarURI, $userId)
		);
	}

	public function findObject($uri, $uid, $userId) {
		return null;
	}

	public function findObjects($uri, $userId) {
		return null;
	}
}