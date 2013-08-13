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

class Birthday extends Backend {

	public function __construct($api, $parameters){
		parent::__construct($api, 'Birthday');
	}

	/**
	 * @brief returns whether or not a calendar should be cached
	 * @param string $uri
	 * @param string $userId
	 * @returns boolean
	 * @throws DoesNotExistException if uri does not exist
	 * 
	 * This method returns a boolen. true if the calendar should be cached, false if the calendar shouldn't be cached
	 * This method is mandatory!
	 */
	public function cacheCalendar($uri, $userId) {
		$this->findCalendar($uri, $userId);
		return false;
	}

	/**
	 * @brief returns information about calendar $uri of the user $userId
	 * @param string $uri
	 * @param string $userId
	 * @returns array with \OCA\Calendar\Db\Calendar object
	 * @throws DoesNotExistException if uri does not exist
	 * @throws MultipleObjectsReturnedException if multiple calendars exist
	 * 
	 * This method returns an array of \OCA\Calendar\Db\Calendar object.
	 * This method is mandatory!
	 */
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

	/**
	 * @brief returns all calendars of the user $userId
	 * @param string $userId
	 * @returns array with \OCA\Calendar\Db\Calendar objects
	 * @throws DoesNotExistException if uri does not exist
	 * 
	 * This method returns an array of \OCA\Calendar\Db\Object objects.
	 * This method is mandatory!
	 */
	public function findCalendars($userId) {
		return array($this->findCalendar('birthdays', $userId));
	}

	/**
	 * @brief returns information about the object (event/journal/todo) with the uid $uid in the calendar $uri of the user $userId 
	 * @param string $uri
	 * @param string $uid
	 * @param string $userid
	 * @returns \OCA\Calendar\Db\Object object
	 * @throws DoesNotExistException if uri does not exist
	 * @throws DoesNotExistException if uid does not exist
	 *
	 * This method returns an \OCA\Calendar\Db\Object object.
	 * This method is mandatory!
	 */
	public function findObject($uri, $uid, $userId) {
		$calendar = $this->findCalendar($uri, $userId);
		return $this->objectMapper->find($uid, $calendar->getId());
	}

	/**
	 * @brief returns all objects in the calendar $uri of the user $userId
	 * @param string $uri
	 * @param string $userId
	 * @returns array with \OCA\Calendar\Db\Object objects
	 * @throws DoesNotExistException if uri does not exist
	 * 
	 * This method returns an array of \OCA\Calendar\Db\Object objects.
	 * This method is mandatory!
	 */
	public function findObjects($uri, $userId) {
		$calendar = $this->findCalendar($uri, $userId);
		return $this->objectMapper->findAll($calendar->getId());
	}
}