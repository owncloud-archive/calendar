<?php
/**
 * Copyright (c) 2012 Frank Karlitschek <frank @ ownCloud.org>
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 * 
 * structure of objectid 
 * backendname.calendaridentifier.uid
 * 
 * examples for an objectid:
 * database.defaultcalendar.7sm626oar9a7t5k4p4ljhlnqbk
 * 
 */
namespace OCA;
class Calendar {
	// The backends used for calendar management
	private static $_usedBackends = array();
	private static $_setupedBackends = array();
	
	// Backends available
	private static $_backends = array();

	/**
	 * @brief registers backend
	 * @param $name name of the backend
	 * @returns true/false
	 *
	 * Makes a list of backends that can be used by other modules
	 */
	public static function registerBackend( $backend, $classname, $arguments = array()) {
		self::$_backends[] = array('backend' => $backend, 'class' => $classname, 'arguments' => $arguments);
		return true;
	}

	/**
	 * @brief gets available backends
	 * @returns array of backends
	 *
	 * Returns the names of all backends.
	 */
	public static function getBackends() {
		return self::$_backends;
	}

	/**
	 * @brief gets used backends
	 * @returns array of backends
	 *
	 * Returns the names of all used backends.
	 */
	public static function getUsedBackends() {
		return array_keys(self::$_usedBackends);
	}

	/**
	 * @brief Adds the backend to the list of used backends
	 * @param $backend default: database The backend to use for calendar managment
	 * @returns true/false
	 *
	 * Set the User Authentication Module
	 */
	public static function useBackend( $backend = null ) {
		if(is_null($backend)){
			$backend = new \OCA\Calendar\Backend\Database();
		}
		if($backend instanceof \OCA\Calendar\Backend\Backend) {
			$classname = self::getClassNameByBackendObject($backend);
			self::$_usedBackends = array_merge(self::$_usedBackends, array($classname => $backend));
			return true;
		}
		throw new \Exception('Backend is no instance of OCA\Calendar\Backend\Backend');
	}

	/**
	 * remove all used backends
	 */
	public static function clearBackends() {
		self::$_usedBackends = array();
	}

	/**
	 * setup the configured backends in calendarbackends.php in the users' home directory
	 */
	public static function setupBackends() {
		//setup backends
		foreach(self::$_backends as $backend){
			$class = $backend['class'];
			$arguments = $backend['arguments'];
			if(class_exists($class) and array_search($i,self::$_setupedBackends) === false) {
				// make a reflection object
				$reflectionObj = new \ReflectionClass($class);

				// use Reflection to create a new instance, using the $args
				$_backend = $reflectionObj->newInstanceArgs($arguments);
				self::useBackend($_backend);
				$_setupedBackends[]=$backend;
			}else{
				OC_Log::write('calendar','Calendar backend '.$class.' not found.', OC_Log::ERROR);
			}
		}
	}
	
	/* ================================================================================================= */

	private static $uidMap = array();
	/**
	 * @brief get all calendars by a user with the userid given in the $userid parameter
	 * @param $userid string - userid of the user
	 * @param $active boolean - return enabled calendars only ?
	 * @ @param $writable boolean - return writable calendars only ?
	 * @ @param $backend mixed (array of strings / string) - return calendars of a specific backend only ?
	 *
	 * @return array
	 *
	 * This method returns all calendars that are available for a user with the userid given in the first parameter.
	 * If you set the second parameter to true, this method will only return enabled calendars.
	 * If you set the third parameter to true, this method will only return writable calendars.
	 * If you assign a value to the fourth parameter, this method will only return calendars from the backend with the name that was assigned.
	 * 
	 * The returned array will be multidimensional.
	 * For information about the structure take a look at the findCalendarByCalendarID method
	 */
	public static function getAllCalendarsByUser($userid, $active = false, $writable = false, $useBackend = null){
		$allCalendars = array();
		//generate an array with backends to use for this search
		$backends = array();
		if(is_null($useBackend)){
			//no backends given, just use all available
			$backends = self::$_usedBackends;
		}else{
			//make $useBackend an array if it isn't one yet
			if(!is_array($useBackend)){
				$useBackend = array($useBackend);
			}
			//check all given backends
			foreach($useBackend as $backendToCheck){
				//does the given backend exists at all?
				if(self::checkBackendExists($backendToCheck)){
					//add backend to array of all backends to search in
					$backends[] = self::$_usedBackends[$backendToCheck];
				}
			}
		}
		//get all calendars of the backends to search in
		foreach($backends as $backend){
			$allCalendarsOfBackend = $backend->getCalendars($userid);
			//remove the disabled calendars if requested
			if($active){
				$activeCalendars = array();
				//check for each calendar if it is enabled
				foreach($allCalendarsOfBackend as $calendar){
					if(!self::isCalendarDisabled($calendar['uri'])){
						$activeCalendars[] = $calendar;
					}
				}
				//overwrite old array
				$allCalendarsOfBackend = $activeCalendars;
			}
			//remove the non-writable calendars if requested
			if($writable){
				$writableCalendars = array();
				//check for each calendar if it is writable
				foreach($allCalendarsOfBackend as $calendar){
					if($backend->isCalendarWritableByUser($calendar['uri'], $userid)){
						$writableCalendars[] = $calendar;
					}
				}
				//overwrite old array
				$allCalendarsOfBackend = $writableCalendars;
			}
			//add the backendname to all uri's
			$allCalendarsOfBackend = self::addBackendNameToURIs($allCalendarsOfBackend, self::getClassNameByBackendObject($backend));
			//merge both arrays
			$allCalendars = array_merge($allCalendars, $allCalendarsOfBackend);
		}
		//return all calendars that match the parameters
		return $allCalendars;
	}
	
	/**
	 * @brief get information about a calendar
	 * @param $calendarid string id of the calendar
	 * @returns array
	 *
	 *  Get information about a calendar with the calendar id given in calendarid parameter
	 * 
	 * Structure of array that will be returned
	 * [uri => calendar's uri, 
	 *  userid => owner's uid, 
	 *  displayname => public visible display name,
	 *  ctag => current ctag,
	 *  color => calendar's color,
	 *  timezone => default timezone of calendar,
	 *  components =>  supported components]
	 */
	public static function findCalendarByCalendarID($calendarid){
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//get the calendar info
		$calendarinfo = $backend->findCalendar(self::getCalendarURIById($calendarid));
		//add the backendname to the URI
		$calendarinfo['uri'] = self::getBackendNameById($calendarid) . '.' . $calendarinfo['uri'];
		//return the calendar information 
		return $calendarinfo;
	}
	
	/**
	 * @brief create a calendar
	 * @param $backendname string
	 * @param $properties array
	 * @returns boolean
	 * 
	 * Structure of $properties array
	 * [uri => calendar's uri, 
	 *  userid => owner's uid, 
	 *  displayname => public visible display name,
	 *  color => calendar's color,
	 *  timezone => default timezone of calendar,
	 *  components =>  supported components]
	 * 
	 * Create a calendar in a specific backend using the given properties
	 */
	public static function createCalendar($backendname, $properties){
		return true;
		//check if the given backend exists
		if(!self::checkBackendExists($backendname)){
			return false;
		}
		//yeah, everything is fine
		$backend = self::$_usedBackends[$backendname];
		//is creating calendars implemented in the backend at all?
		if($backend->implementsActionss(OC_CALENDAR_BACKEND_CREATE_CALENDAR)) {
			//create the calendar with some properties
			$result = $backend->createCalendar($properties);
			//was creating successful?
			if($result) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @brief edit a calendar
	 * @param $calendarid string
	 * @param $properties array
	 * @returns boolean
	 * 
	 * For information about the structure of the properties array take a look at the createCalendar method
	 * 
	 * Edit a calendar with a specific calendarid
	 */
	public static function editCalendar($calendarid, $properties){
		//check if the given backend exists
		if(!self::checkBackendExists(self::getBackendNameById($calendarid))){
			return false;
		}
		//yeah, everything is fine
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//is editing calendars implemented in the backend at all?
		if($backend->implementsActionss(OC_CALENDAR_BACKEND_EDIT_CALENDAR)) {
			//edit the calendar with the new properties
			$result = $backend->editCalendar(self::getCalendarURIById($calendarid), $properties);
			//was editing successful?
			if($result) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @brief delete a calendar
	 * @param $calendarid string
	 * @returns boolean
	 *
	 * Delete a calendar with a specific calendarid
	 */
	public static function deleteCalendar($calendarid){
		//check if the given backend exists
		if(!self::checkBackendExists(self::getBackendNameById($calendarid))){
			return false;
		}
		//yeah, everything is fine
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//is deleting calendars implemented in the backend at all?
		if($backend->implementsActionss(OC_CALENDAR_BACKEND_DELETE_CALENDAR)) {
			//delete the calendar
			$result = $backend->deleteCalendar(self::getCalendarURIById($calendarid));
			//was deleting successful
			if($result) {
				return true;
			}
		}
		//hide the calendar if deleting it is not available
		self::hideCalendar($calendarid);
		return true;
	}
	
	/**
	 * @brief touch a calendar
	 * @param $calendarid string
	 * @returns boolean
	 *
	 * Touch a calendar with a specific calendarid
	 */
	public static function touchCalendar($calendarid){
		//check if the given backend exists
		if(!self::checkBackendExists(self::getBackendNameById($calendarid))){
			return false;
		}
		//yeah, everything is fine
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//is touching calendars implemented in the backend at all?
		if($backend->implementsActionss(OC_CALENDAR_BACKEND_TOUCH_CALENDAR)) {
			//touch it
			$result = $backend->touchCalendar(self::getCalendarURIById($calendarid));
			//was touching successful
			if($result) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @brief merge all given calendars into calendar one
	 * @param $calendarid_1 string
	 * @param $calendarid_2 string
	 * @param $calendarid_3 string
	 * @param $calendarid_4 string
	 *            ... 
	 * @returns boolean
	 *
	 * Merge all given calendars into calendar one
	 * Each one of the parameters must be a valid calendarid!
	 */
	public static function mergeCalendar(){
		$numberofarguments = func_num_args();
		$mergeintocalendar = func_get_arg(0);
		//informations about the calendar all others will be merged in
		$destination = array('backendname' => self::getBackendNameById($mergeintocalendar), 'calendaruri' => self::getCalendarURIById($mergeintocalendar));
		//let's merge it
		for($i = 1; $i < $numberofarguments; $i++){
			//get the current calendar
			$currentcalendar = func_get_arg($i);
			$origin = array('backendname' => self::getBackendNameById($currentcalendar), 'calendaruri' => self::getCalendarURIById($currentcalendar));
			//are both calendar in the same backend and does this backend support merging at all?
			if($origin['backendname'] == $destination['backendname'] && self::$_usedBackends[$destination['backendname']]->implementsActionss(OC_CALENDAR_BACKEND_MERGE_CALENDAR)){
				//yeah
				$backend->mergeCalendar($origin['calendaruri'], $destination['calendaruri']);
			}else{
				//nope, either not in the same backend or backend doesn't support merging at all
				$allobjectsofcurrentcalendar = self::allObjects($currentcalendar);
				//merge each single object
				foreach($allobjectsofcurrentcalendar as $currentobject){
					//get object information
					$object = self::findObject($currentobject);
					//create the object in the new calendar
					self::createObject($mergeintocalendar, $object);
					//delete old object
					self::deleteObject($currentobject);
				}
				//delete old calendar after all objects have been moved
				self::deleteCalendar($currentcalendar);
			}
		}
		return true;
	}
	
	/**
	 * @brief get all objects of a calendar
	 * @param $calendarid string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function allObjects($calendarid){
		//get the backend
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//get the object
		$objects = $backend->getObjects(self::getCalendarURIById($calendarid));
		//prepare objects
		for($i = 0; $i < count($objects); $i++){
			//add objectid to event information
			$objects[$i]['objectid'] = $calendarid . '.' . $objects[$i]['uid'];
		}
		//return all objects
		return $objects;
	}
	
	/**
	 * @brief get all objects of a calendar in a specific period
	 * @param $calendarid string
	 * @param $start DateTime Object
	 * @param $end DateTime Object
	 * @returns boolean
	 *
	 * get all object of a calendar in a specific period
	 * ! $start and $end MUST be DateTime Objects !
	 */
	public static function allObjectsInPeriod($calendarid, $start, $end){
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//does the backend support searching for objects in a specific period at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_GET_IN_PERIOD)){
			//yeah, it does :D
			$objects = $backend->getInPeriod(self::getCalendarURIById($calendarid), $start, $end);
		}else{
			//nope, it doesn't :(
			$allobjects = self::allObjects($calendarid);
			$objects = array();
			foreach($allobjects as $object){
				//TODO - only put objects in the period into the objects array
			}
		}
		//prepare objects
		for($i = 0; $i < count($objects); $i++){
			//add objectid to event information
			$objects[$i]['objectid'] = $calendarid . '.' . $objects[$i]['uid'];
		}
		//return all objects in period
		return $objects;
	}
	
	/**
	 * @brief get information about an object using it's objectid
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function findObject($objectid){
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($objectid)];
		//get the calendar info
		$object = $backend->findObject(self::getCalendarURIById($objectid), self::getObjectUIDById($objectid));
		//add the backendname to the URI
		$object['objectid'] = $objectid;
		//return the object information 
		return $object;
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function findObjectByUid($uid){
		return self::findObject(self::getObjectIdByUID($uid));
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function createObject($calendarid, $properties){
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($id)];
		//does the backend support creating objects at all?
		if($backend->implementsActionss(OC_CALENDAR_BACKEND_CREATE_OBJECT)) {
			//create it
			$result = $backend->createObject(self::getCalendarURIById($calendarid), $properties);
			//was creating the object successful
			if($result) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function editObject($objectid, $properties){
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($objectid)];
		//does the backend support editing objects at all?
		if($backend->implementsActionss(OC_CALENDAR_BACKEND_CREATE_OBJECT)) {
			//edit it
			$result = $backend->editObject(self::getCalendarURIById($objectid), self::getObjectUIDById($objectid), $properties);
			//was editing the object successful
			if($result) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function deleteObject($objectid){
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($objectid)];
		//does the backend support deleting objects at all?
		if($backend->implementsActionss(OC_CALENDAR_BACKEND_DELETE_OBJECT)) {
			//delete it
			$result = $backend->deleteObject(self::getCalendarURIById($objectid), self::getObjectUIDById($objectid));
			//was deleting the object successful
			if($result) {
				return true;
			}
		//if deleting the object is not available, just hide it
		}else{
			self::hideObject($objectid);
			return true;
		}
		return false;
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function moveObject($objectid, $newcalendarid){
		$oldbackend = self::getBackendNameById($objectid);
		$newbackend = self::getBackendNameById($newcalendarid);
		if($oldbackend == $newbackend && self::$_usedBackends[$oldbackend]->implementsActionss(OC_CALENDAR_BACKEND_MOVE_OBJECT)){
			$backend = self::$_usedBackends[$oldbackend];
			$uid = self::getObjectUIDById($objectid);
			$newcalendar = self::getCalendarURIById($newcalendarid);
			$backend->moveObject($uid, $newcalendar);
		}else{
			//TODO
			//delete old object
			//create a new one with same properties
		}
	}
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function setCalendarActive(){
		//UI stuff only
	}

		/***************************************
		**   Private methods of this class    **
		***************************************/
	
	//UIDMap
	//[key: (string) $uid -> value: (string) $objectid]
	private static $_uidmap = array();
	private static function hideObject(){
		
	}
	private static function hideCalendar(){
		
	}
	private static function isCalendarDisabled(){
		return false;
	}
	private static function getBackendNameById($id){
		$splittedId = self::splitObjectId($id);
		return $splittedId['backend'];
	}
	
	private static function getCalendarURIById($id){
		$splittedId = self::splitObjectId($id);
		return $splittedId['calendar'];
	}

	private static function getObjectUIDById($id){
		$splittedId = self::splitObjectId($id);
		return $splittedId['object'];
	}
	
	private static function splitObjectId($id){
		list($backend, $calendar, $object) = explode('.', $id);
		return array('backend' => $backend, 'calendar' => $calendar, 'object' => $object);
	}
	
	private static function getObjectIdByUID($uid){
		if(array_key_exists($uid, self::$_uidmap)){
			return self::$_uidmap[$uid];
		}
		return false;
	}
	
	private static function getClassNameByBackendObject($backend){
		$classname = explode('\\', get_class($backend));
		return strtolower(end($classname));
	}
	
	private static function addBackendNameToURIs($calendars, $backendname){
		for($i = 0;$i < count($calendars); $i++){
			$calendars[$i]['uri'] = $backendname . '.' . $calendars[$i]['uri'];
		}
		return $calendars;
	}
	
	private static function checkBackendExists($backendname){
		//does the given backend exists at all?
		if(array_key_exists($backendname, self::$_usedBackends)){
			//yeah, everything is fine
			return true;
		}else{
			//nope, backend not found
			OCP\Util::writeLog('calendar', 'Backend with the name "' . $backendname . '" was not found', OCP\Util::WARN);
			return false;
		}
	}
		/***************************************
		** All of this class' backend methods **
		***************************************/
	

}