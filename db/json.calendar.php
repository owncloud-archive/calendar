<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 * 
 * Example output:
 * ```json
 * {
 *   "displayname" : "Work",
 *   "calendarURI" : "local-work",
 *   "owner" : {
 *     "userid" : "developer42",
 *     "displayname" : "developer42"
 *   },
 *   "ctag" : 0,
 *   "url" : "https://owncloud/index.php/apps/calendar/calendars/local-work",
 *   "color" : "#000000",
 *   "order" : 0,
 *   "enabled" : true,
 *   "deleteAt" : null,
 *   "components" : {
 *     "vevent" : true,
 *     "vjournal" : false,
 *     "vtodo" : true
 *   },
 *   "timezone" : {
 *     "stdOffset" : 3600,
 *     "dstOffset" : 7200,
 *     "name" : "Europe/Berlin"
 *   },
 *   "user" : {
 *     "userid" : "developer42",
 *     "displayname" : "developer42"
 *   },
 *   "cruds" : {
 *     "create" : true,
 *     "update" : true,
 *     "delete" : true,
 *     "code" : 31,
 *     "read" : true,
 *     "share" : true
 *   }
 * }
 * ```
 */
namespace OCA\Calendar\Db;

class JSONCalendar extends JSON{

	public $calendarURI;
	public $url;
	public $user;
	public $owner;
	public $displayname;
	public $ctag;
	public $color;
	public $order;
	public $components;
	public $timezone;
	public $enabled;
	public $cruds;
	public $deleteAt;

	private $calendarObject;

	/**
	 * @brief init JSONCalendar object with data from Calendar object
	 * @param Calendar $calendar
	 */
	public function __construct(Calendar $calendar) {
		$this->properties = array(
			'displayname',
			'deleteAt',
			'enabled',
			'color',
			'ctag',
			'order',
		);
		parent::__construct($calendar);

		//some type fixes
		$this->enabled = (bool) $this->enabled;
		$this->ctag = (int) $this->ctag;
		$this->order = (int) $this->order;

		$this->calendarObject = $calendar;

		$this->setCalendarURI();
		$this->setURL();
		$this->setUser();
		$this->setOwner();		
		$this->setComponents();
		$this->setTimezone();
		$this->setCruds();
	}

	/**
	 * @brief set public calendar uri
	 */
	private function setCalendarURI() {
		$backend = $this->calendarObject->getBackend();
		$uri = $this->calendarObject->getUri();

		$this->calendarURI = strtolower($backend . '-' . $uri);
	}

	/**
	 * @brief set api url to calendar
	 */
	private function setURL() {
		$properties = array(
			'calendarId' => $this->calendarURI,
		);

		$url = \OCP\Util::linkToRoute('calendar.calendars.show', $properties);
		$this->url = \OCP\Util::linkToAbsolute('', substr($url, 1));
	}

	/**
	 * @brief set user info
	 */
	private function setUser() {
		$userId = $this->calendarObject->getUserid();
		$this->user = $this->getUserInfo($userId);
	}

	/**
	 * @brief set owner info
	 */
	private function setOwner() {
		$ownerId = $this->calendarObject->getOwnerid();
		$this->owner = $this->getUserInfo($ownerId);
	}

	/**
	 * @brief return array with user info
	 * @param string $userId
	 * @return array
	 */
	private function getUserInfo($userId=null){
		return array(
			'userid' => $userId,
			'displayname' => \OCP\User::getDisplayName($userId),
		);
	}

	/**
	 * @brief set components info
	 */
	private function setComponents() {
		$components = $this->calendarObject->getComponents();
		$components = strtoupper($components);

		$vevent = (bool) substr_count($components, ObjectType::EVENT);
		$vjournal = (bool) substr_count($components, ObjectType::JOURNAL);
		$vtodo = (bool) substr_count($components, ObjectType::TODO);

		$this->components = array(
			'vevent' => $vevent,
			'vjournal' => $vjournal,
			'vtodo' => $vtodo,
		);
	}

	/**
	 * @brief set timezone info
	 */
	private function setTimezone($timezoneId='UTC') {
		$timezoneId = $this->calendarObject->getTimezone();

		$currentYear = date('Y');
		$dateTimeZone = new \DateTimeZone($timezoneId);
		$standard = new \DateTime($currentYear . '-01-01', $dateTimeZone);
		$daylightSaving = new \DateTime($currentYear . '-07-31', $dateTimeZone);

		$standardOffset = (int) $standard->format('Z');
		$daylightSavingOffset = (int) $daylightSaving->format('Z');

		$this->timezone = array(
			'name' => $timezoneId,
			'stdOffset' => $standardOffset,
			'dstOffset' => $daylightSavingOffset
		);
	}

	/**
	 * @brief set cruds info
	 */
	private function setCruds() {
		$cruds = (int) $this->calendarObject->getCruds();
		$this->cruds = array(
			'code' => 	$cruds,
			'create' =>	(bool) ($cruds & Permissions::CREATE),
			'read' => 	(bool) ($cruds & Permissions::READ),
			'update' =>	(bool) ($cruds & Permissions::UPDATE),
			'delete' =>	(bool) ($cruds & Permissions::DELETE),
			'share' =>	(bool) ($cruds & Permissions::SHARE),
		);
	}
}