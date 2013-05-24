<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\BusinessLayer;

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Utility\TimeFactory;

use \OCA\Calendar\Db\Calendar;
use \OCA\Calendar\Db\CalendarCacheMapper;

class CalendarBusinessLayer {

	private $api;
	private $backends;
	private $mapper;

	public function __construct(CalendarCacheMapper $calendarMapper,
								BackendBusinessLayer $backends,
	                            API $api){
		$this->mapper = $calendarMapper;
		$this->backends = $backends;
		$this->api = $api;
	}
	
	public function find($uri, $userid) {
		$this->mapper->findByURI($calendarURI, $userId);
	}
	
	public function delete($backend, $uri) {
		$this->backends->find($backend)->api->deleteCalendar($uri);
		//ToDo validate
		$this->mapper->delete($userid, $backend, $uri);
	}
	
	public function findAll($userId) {
		return $this->mapper->findAll($userId);
	}

	private function allowNoNameTwice($calendarURI, $userId){
		$existingCalendars = $this->mapper->findByURI($calendarURI, $userId);
		if(count($existingCalendars) > 0){

			throw new BusinessLayerExistsException(
				$this->api->getTrans()->t('Can not add calendar: Exists already'));
		}
	}

	/**
	 * @throws BusinessLayerExistsException if name exists already
	 */
	public function create($userid, $backend, $uri, $displayname, $components, $ctag, $timezone, $color, $oder, $enabled, $writable) {
		$this->allowNoNameTwice($uri, $userId);

		$calendar = new Calendar();
		$calendar->setUserId($userId);
		$calendar->setBackend($backend);
		$calendar->setURI($uri);
		$calendar->setDisplayname($displayname);
		$calendar->setComponents($components);
		$calendar->setCTag($ctag);
		$calendar->setTimezone($timezone);
		$calendar->setColor($color);
		$calendar->setOrder($order);
		$calendar->setEnabled($enabled);
		$calendar->setWritable($writable);

		$this->backends->find($backend)->api->createCalendar($calendar);

		return $this->mapper->insert($folder);
	}



	/**
	 * @throws BusinessLayerExistsException if name exists already
	 * @throws BusinessLayerException if the folder does not exist
	 */
	public function update($folderId, $folderName, $userId){
		$this->allowNoNameTwice($folderName, $userId);

		$folder = $this->find($folderId, $userId);
		$folder->setName($folderName);
		$this->mapper->update($folder);
	}
	
	public function merge(){
		
	}
	
	public function touch(){
		
	}

}