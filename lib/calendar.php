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
 * example for an objectid:
 * database.defaultcalendar.7sm626oar9a7t5k4p4ljhlnqbk
 * 
 * Full documentation will be available on github.com/ownCloud/documentation soon
 */
namespace OCA;
class Calendar {
	//!Variables for backends
	private static $_usedBackends = array();
	private static $_setupedBackends = array();
	// available backends
	private static $_backends = array();
	
	//!Variables for calendars
	private static $allCalendarsByUser = array();
	
	//!Variables for objects
	private static $allEventsByUser = array();
	private static $allJournalsByUser = array();
	private static $allTodosByUser = array();
	
	//!Backend initialization
	/**
	 * @brief registers a backend
	 * @param $backend name of the backend
	 * @param $classname name of the class
	 * @param $arguments some arguments that might be necessary
	 * @returns void
	 *
	 * register a calendar backend
	 */
	public static function registerBackend( $backend, $classname, $arguments = array()) {
		self::$_backends[] = array('backend' => $backend, 'class' => $classname, 'arguments' => $arguments);
	}
	
	/**
	 * @brief gets available backends
	 * @returns array
	 *
	 * returns a list of all backends
	 */
	public static function getBackends() {
		return self::$_backends;
	}

	/**
	 * @brief gets used backends
	 * @returns array of backends
	 *
	 * returns the names of all used backends
	 */
	public static function getUsedBackends() {
		return array_keys(self::$_usedBackends);
	}

	/**
	 * @brief Adds the backend to the list of used backends
	 * @param $backend default: database The backend to use for calendar managment
	 * @returns true/false
	 *
	 * enables a calendar backend
	 */
	public static function useBackend( $backend = null ) {
		if(is_null($backend)) {
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
	 * @brief removes all used backends
	 * @returns void
	 * 
	 * removes all used backends
	 */
	public static function clearBackends() {
		self::$_usedBackends = array();
	}

	/**
	 * @brief initializes all registered calendar backends
	 * @return void
	 * 
	 * initializes all registered calendar backends
	 */
	public static function setupBackends() {
		//get all enabled backends
		$enabledbackends = self::getEnabledBackends();
		//setup backends
		foreach(self::$_backends as $backend) {
			$class = $backend['class'];
			$arguments = $backend['arguments'];
			if(class_exists($class) && !array_search($class,self::$_setupedBackends) && array_search($class, $enabledbackends)) {
				// make a reflection object
				$reflectionObj = new \ReflectionClass($class);

				// use Reflection to create a new instance, using the $args
				$_backend = $reflectionObj->newInstanceArgs($arguments);
				self::useBackend($_backend);
				self::$_setupedBackends[]=$backend;
			}else{
				\OCP\Util::writeLog('calendar', 'Calendar backend '.$class.' not found or not enabled', \OCP\Util::DEBUG);
			}
		}
		if(count(self::$_setupedBackends) === 0){
			\OCP\Util::writeLog('calendar', 'No backend was setup', \OCP\Util::ERROR);
		}
	}
	
	// !Backend management
	/**
	 * @brief gets enabled backends
	 * @returns array
	 *
	 * returns a list of enabled backends
	 */
	public static function getEnabledBackends(){
		//get the enabled backends saved in the database
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_backends` WHERE `visibility` = ?' );
		$result = $stmt->execute( array(1) );
		if(\OCP\DB::isError($result)){
			\OCP\Util::writeLog('calendar', __METHOD__.', An error occurred while fetching all enabled backends', \OCP\Util::ERROR);
		}
		//create empty array for all enabled backends
		$backends = array();
		while( $row = $result->fetchRow()){
			//add this backend to the array with all enabled backends
			$backends[] = $row['backendname'];
		}
		//return all enabled backends
		return $backends;
	}
	
	/**
	 * @brief disables / enables a calendar backend
	 * @param $backend string - name of the backend
	 * @param $visibility bool - visibility of the backend
	 * @returns bool
	 *
	 * disables or enables the backend with the name given in the first param
	 */
	public static function setBackendsVisibility($backend, $visibility = 0){
		//update the backend's database entry
		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*calendar_backends` SET `visibility` = ? WHERE `backendname` = ?' );
		$result = $stmt->execute( array($visibility, $backend) );
		//check if the backend's visibility was changed successfully
		if(!\OCP\DB::isError($result)){
			return true;
		}
		\OCP\Util::writeLog('calendar', __METHOD__.', An error occurred while ' . (($visibility === 0)?'disabling':'enabling') . ' the backend: ' . $backend, \OCP\Util::ERROR);
		return false; 
	}
	
	/**
	 * @brief returns the default backend
	 * @returns bool
	 *
	 * returns the name of the default backend
	 */
	public static function getDefaultBackend(){
		return \OCP\Config::getAppValue('calendar', 'defaultBackend', 'database');
	}

	/**
	 * @brief sets the default backend
	 * @returns bool
	 *
	 * sets the name of the default backend
	 */
	public static function setDefaultBackend($backend){
		\OCP\Config::setAppValue('calendar', 'defaultBackend', $backend);
		return true;
	}
	
	/**
	 * @brief installs a backend
	 * @param $backend name of the backend
	 * @returns bool
	 *
	 * installs a calendar backend
	 * ONLY CALL THIS METHOD ONCE AT ALL!
	 */
	public static function installBackend($backend){
		//create a database entry for backend
		$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*calendar_backends` (`backendname`, `visibility`) VALUES(?, ?)' );
		$result = $stmt->execute( array($backend, 0) );
		//was creating the database entry successful
		if(!\OCP\DB::isError($result)){
			return true;
		}
		\OCP\Util::writeLog('calendar', __METHOD__.', An error occurred while installing the backend: ' . $backend, \OCP\Util::ERROR);
		return false;
	}
	
	// !Calendar methods
	// !get information about calendars

	/**
	 * @brief get all calendars by a user with the userid given in the $userid parameter
	 * @param $userid string - userid of the user
	 * @param $active boolean - return enabled calendars only ?
	 * @ @param $writable boolean - return writable calendars only ?
	 * @ @param $backend mixed (array of strings / string) - return calendars of a specific backend only ?
	 *
	 * @return array of calendar object
	 *
	 * This method returns all calendars that are available for a user with the userid given in the first parameter.
	 * If you set the second parameter to true, this method will only return enabled calendars.
	 * If you set the third parameter to true, this method will only return writable calendars.
	 * If you assign a value to the fourth parameter, this method will only return calendars from the backend with the name that was assigned.
	 * 
	 * The returned array will be multidimensional.
	 * For information about the structure take a look at the findCalendarByCalendarID method
	 */
	public static function getAllCalendarsByUser($userid, $active = false, $writable = false, $useBackend = null, $hidden = false) {
		$visibility = $active?1:0;
		//search for hidden calendars as well?
		if($hidden){
			$visibility = -1;
		}
		//sql statement
		$sql = 'SELECT * FROM `*PREFIX*calendars` WHERE `userid` = ?';
		//parameters for sql statement
		$param = array($userid);
		//add sql statement to filter the calendar by it's visibility
		if($active){
			$sql .= ' AND `visibility` = ?';
			$param[] = $visibility;
		}
		//add sql statement to filter the calendar by it's writability
		if($writable){
			$sql .= ' AND `writable` = ?';
			$param[] = $writable ? 1 : 0;
		}
		if(!is_null($useBackend)) {
			//make $useBackend an array if it isn't already one yet 
			if(!is_array($useBackend)) {
				$useBackend = array($useBackend);
			}
			$sql .= ' AND ( ';
			for ($i = 0; $i < count($useBackend); $i++){
				$sql .= ' `backend` = ?';
				if($i !== count($useBackend) - 1){
					$sql .= ' OR ';
				}
				$param[] = strtolower($useBackend[$i]);
			}
			$sql .= ' )';
		}
		$sql .= ' ORDER BY `order`';
		
		$stmt = \OCP\DB::prepare( $sql );
		$result = $stmt->execute( $param );
		
		if(\OCP\DB::isError($result)){
			\OCP\Util::writeLog('calendar', __METHOD__.', An unknown database error occurred', \OCP\Util::ERROR);
		}
		
		//create empty array for all calendars
		$calendars = array();
		while( $row = $result->fetchRow()){
			//add this calendar to the calendars array
			$calendars[] = self::getCalendarObjectByCalendarCachingDBRow($row);
		}
		//return all calendars
		return $calendars;
	}
	
	public static function getAllUncachedCalendarsByUser($userid, $active = false, $writable = false, $useBackend = null) {
		$allCalendars = array();
		//generate an array with backends to use for this search
		$backends = array();
		if(is_null($useBackend)) {
			//no backends given, just use all available
			$backends = self::$_usedBackends;
		}else{
			//make $useBackend an array if it isn't one yet
			if(!is_array($useBackend)) {
				$useBackend = array($useBackend);
			}
			//check all given backends
			foreach($useBackend as $backendToCheck) {
				//does the given backend exists at all?
				if(self::doesBackendExist($backendToCheck)) {
					//add backend to array of all backends to search in
					$backends[$backendToCheck] = self::$_usedBackends[$backendToCheck];
				}
			}
		}
		//get all calendars of the backends to search in
		foreach($backends as $backendName => $backend) {
			if(array_key_exists($backendName, self::$allCalendarsByUser)){
				$allCalendarsOfBackend = self::$allCalendarsByUser[$backendName];
			}else{
				$allCalendarsOfBackend = $backend->getCalendars($userid);
				foreach($allCalendarsOfBackend as $key => $value){
					$value->add('X-OWNCLOUD-CALENADRID', $backendName . '.' . $value->__get('X-OWNCLOUD-URI'));
					$allCalendarsOfBackend[$key] = $value;
				}
				self::$allCalendarsByUser[$backendName] = $allCalendarsOfBackend;
			}
			//remove the disabled calendars if requested
			if($active) {
				$activeCalendars = array();
				//check for each calendar if it is enabled
				foreach($allCalendarsOfBackend as $calendar) {
					if(!self::isCalendarDisabled($calendar->__get('X-OWNCLOUD-CALENADRID'))) {
						$activeCalendars[] = $calendar;
					}
				}
				//overwrite old array
				$allCalendarsOfBackend = $activeCalendars;
			}
			//remove the non-writable calendars if requested
			if($writable) {
				$writableCalendars = array();
				//check for each calendar if it is writable
				foreach($allCalendarsOfBackend as $calendar) {
					if($backend->isCalendarWritableByUser($calendar->__get('X-OWNCLOUD-CALENADRID'), $userid)) {
						$writableCalendars[] = $calendar;
					}
				}
				//overwrite old array
				$allCalendarsOfBackend = $writableCalendars;
			}
			//merge both arrays
			$allCalendars = array_merge($allCalendars, $allCalendarsOfBackend);
		}
		//return all calendars that match the parameters
		return $allCalendars;
	}
	
	/**
	 * @brief get information about a calendar
	 * @param $calendarid string id of the calendar
	 * @returns mixed (object / false)
	 *
	 *  Get information about a calendar with the calendar id given in calendarid parameter
	 * 
	 */
	public static function findCalendarByCalendarID($calendarid){
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendars` WHERE `calendarid` = ?' );
		$result = $stmt->execute( array($calendarid) );
		if(\OCP\DB::isError($result)){
			\OCP\Util::writeLog('calendar', __METHOD__.', An unknown database error occurred', \OCP\Util::ERROR);
		}
		$row = $result->fetchRow();
		//check if the returned row is valid
		if(!$row){
			\OCP\Util::writeLog('calendar', __METHOD__.', Calendar with ID: ' . $calendarid . ' was not found', \OCP\Util::DEBUG);
			return false;
		}
		//return calendar object
		return self::getCalendarObjectByCalendarCachingDBRow($row);
	}
	
	public static function findUncachedCalendarByCalendarID($calendarid) {
		//get the cached calendar
		$cached = self::findCachedCalendarByCalendarID($calendarid);
		//does the calendar exist in the cache?
		if($cached && !self::isCalendarCacheOutdated($calendarid)) {
			//return calendar object if it's cached and not outdated
			return $cached;
		}
		//get the name of the backend
		$backendname = self::getBackendNameById($calendarid);
		//check if the given backend exists
		if(!self::doesBackendExist($backendname)) {
			\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $calendarid . ' does not exist or was not set up properly', \OCP\Util::ERROR);
			return false;
		}
		//get the backend object
		$backend = self::$_usedBackends[$backendname];
		//get the calendar info
		$calendar = $backend->findCalendar(self::getCalendarURIById($calendarid));
		//is the returned object a calendar?
		if($calendar instanceof \OCA\Calendar\Objects\Calendar) {
			//add calendarid as a property
			$calendar->addProperty('X-ownCloud-CalendarID', $calendarid);
			//return the calendar object
			return $calendar;
		}
		\OCP\Util::writeLog('calendar', __METHOD__.', Calendar with ID: ' . $calendarid . ' was not found', \OCP\Util::DEBUG);
		return false;
	}
	
	// !modify calendars
	
	/**
	 * @brief create a calendar
	 * @param $backendname string
	 * @param $properties array
	 * @returns boolean
	 * 
	 * Create a calendar in a specific backend using the given properties
	 */
	public static function createCalendar($backendname, $calendarobject) {
		//get the default backend if no backend was given
		if($backendname == '' || $backendname == null) {
			$backendname = self::getDefaultBackend();
		}
		//does the backend exist?
		if(!self::doesBackendExist($backendname)) {
			\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $backendname . ' does not exist or was not set up properly', \OCP\Util::ERROR);
			return false;
		}
		//is the calendar object valid?
		if(!($calendarobject instanceof \OCA\Calendar\Objects\Calendar)) {
			\OCP\Util::writeLog('calendar', __METHOD__.', No valid calendar object was submitted', \OCP\Util::ERROR);
			return false;
		}
		//get the backend object
		$backend = self::$_usedBackends[$backendname];
		//is creating calendars implemented at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_CREATE_CALENDAR)) {
			//create the calendar with some properties
			$result = $backend->createCalendar($calendarobject);
			//was creating successful?
			if($result) {
				//TODO - emit hook - //
				self::createCalendarCacheByCalendarObject($calendarobject);
				return true;
			}else{
				\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $backendname. ' failed to create a calendar', \OCP\Util::ERROR);
				return false;
			}
		}
		\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $backendname. ' does not implement OC_CALENDAR_BACKEND_CREATE_CALENDAR', \OCP\Util::DEBUG);
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
	public static function editCalendar($calendarid, $calendarobject) {
		//check if the given backend exists
		if(!self::doesBackendExist(self::getBackendNameById($calendarid))) {
			\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $calendarid . ' does not exist or was not set up properly', \OCP\Util::ERROR);
			return false;
		}
		//is the calendar object valid?
		if(!($calendarobject instanceof \OCA\Calendar\Objects\Calendar)) {
			\OCP\Util::writeLog('calendar', __METHOD__.', No valid calendar object was submitted', \OCP\Util::ERROR);
			return false;
		}
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//is editing calendars implemented in the backend at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_EDIT_CALENDAR)) {
			//edit the calendar with the new properties
			$result = $backend->editCalendar($calendarobject);
			//was editing successful?
			if($result) {
				//TODO - emit hook - //
				self::updateCalendarCacheByCalendarObject($calendarobject);
				return true;
			}else{
				\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $backendname. ' failed to edit a calendar', \OCP\Util::ERROR);
				return false;
			}
		}
		\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $backendname. ' does not implement OC_CALENDAR_BACKEND_EDIT_CALENDAR', \OCP\Util::DEBUG);
		return false;
		//TODO update cache
	}
	
	/**
	 * @brief delete a calendar
	 * @param $calendarid string
	 * @returns boolean
	 *
	 * Delete a calendar with a specific calendarid
	 */
	public static function deleteCalendar($calendarid) {
		//check if the given backend exists
		if(!self::doesBackendExist(self::getBackendNameById($calendarid))) {
			\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $calendarid . ' does not exist or was not set up properly', \OCP\Util::ERROR);
			return false;
		}
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//is deleting calendars implemented in the backend at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_DELETE_CALENDAR)) {
			//delete the calendar
			$result = $backend->deleteCalendar(self::getCalendarURIById($calendarid));
			//was deleting successful?
			if($result) {
				//TODO - emit hook - //
				self::deleteCalendarCacheByCalendarID($calendarid);
				return true;
			}
			//todo hide or clear from cache
		}
		//hide the calendar if deleting it is not available
		self::hideCalendar($calendarid);
		//TODO - emit hook - //
		//TODO - delete from  calendar cache - //
		\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $backendname. ' does not implement OC_CALENDAR_BACKEND_DELETE_CALENDAR', \OCP\Util::DEBUG);
		\OCP\Util::writeLog('calendar', __METHOD__.', ' . $calendarid . ' will be hidden', \OCP\Util::DEBUG);
		return true;
	}
	
	/**
	 * @brief touch a calendar
	 * @param $calendarid string
	 * @returns boolean
	 *
	 * Touch a calendar with a specific calendarid
	 */
	public static function touchCalendar($calendarid) {
		//check if the given backend exists
		if(!self::doesBackendExist(self::getBackendNameById($calendarid))) {
			\OCP\Util::writeLog('calendar', __METHOD__.', Backend: ' . $calendarid . ' does not exist or was not set up properly', \OCP\Util::ERROR);
			return false;
		}
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//is touching calendars implemented in the backend at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_TOUCH_CALENDAR)) {
			//touch it
			$result = $backend->touchCalendar(self::getCalendarURIById($calendarid));
			//check if touching was successful
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
	public static function mergeCalendar() {
		$numberofarguments = func_num_args();
		$mergeintocalendar = func_get_arg(0);
		//informations about the calendar all others will be merged in
		$destination = array('backendname' => self::getBackendNameById($mergeintocalendar), 'calendaruri' => self::getCalendarURIById($mergeintocalendar));
		//let's merge it
		for($i = 1; $i < $numberofarguments; $i++) {
			//get the current calendar
			$currentcalendar = func_get_arg($i);
			$origin = array('backendname' => self::getBackendNameById($currentcalendar), 'calendaruri' => self::getCalendarURIById($currentcalendar));
			//are both calendar in the same backend and does this backend support merging at all?
			if($origin['backendname'] == $destination['backendname'] && self::$_usedBackends[$destination['backendname']]->implementsActions(OC_CALENDAR_BACKEND_MERGE_CALENDAR)) {
				//yeah
				$backend->mergeCalendar($origin['calendaruri'], $destination['calendaruri']);
			}else{
				//nope, either not in the same backend or backend doesn't support merging at all
				$allobjectsofcurrentcalendar = self::allObjects($currentcalendar);
				//merge each single object
				foreach($allobjectsofcurrentcalendar as $currentobject) {
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
	
	// !Object methods
	// !get information about objects
	
	/**
	 * @brief get all objects of a calendar
	 * @param $calendarid string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function allObjects($calendarid, $type = null) {
		//validate the type param
		if(!is_null($type) && $type !== 'VEVENT' && $type !== 'VJOURNAL' && $type !== 'VTODO'){
			\OCP\Util::writeLog('calendar', __METHOD__.', Type: ' . $type. ' is no valid type', \OCP\Util::ERROR);
			return false;
		}
		//get the backend
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//get the object
		$objects = $backend->getObjects(self::getCalendarURIById($calendarid));
		//prepare objects
		for($i = 0; $i < count($objects); $i++) {
			//add objectid to event information
			$objects[$i]['objectid'] = $calendarid . '.' . $objects[$i]['uid'];
		}
		//TODO - only return objects of given type
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
	public static function allObjectsInPeriod($calendarid, $start, $end, $type = null) {
		//validate the type param
		if(!is_null($type) && $type !== 'VEVENT' && $type !== 'VJOURNAL' && $type !== 'VTODO'){
			\OCP\Util::writeLog('calendar', __METHOD__.', Type: ' . $type. ' is no valid type', \OCP\Util::ERROR);
			return false;
		}
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($calendarid)];
		//does the backend support searching for objects in a specific period at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_GET_IN_PERIOD)) {
			//yeah, it does :D
			$objects = $backend->getInPeriod(self::getCalendarURIById($calendarid), $start, $end);
		}else{
			//nope, it doesn't :(
			$allobjects = self::allObjects($calendarid);
			$objects = array();
			foreach($allobjects as $object) {
				//TODO - only put objects in the period into the objects array
			}
		}
		//prepare objects
		for($i = 0; $i < count($objects); $i++) {
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
	public static function findObject($objectid) {
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
	 * /
	public static function findObjectByUid($uid) {
		return self::findObject(self::getObjectIdByUID($uid));
	}*/
	
	// !modify objects
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function createObject($calendarid, $properties) {
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($id)];
		//does the backend support creating objects at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_CREATE_OBJECT)) {
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
	public static function editObject($objectid, $properties) {
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($objectid)];
		//does the backend support editing objects at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_CREATE_OBJECT)) {
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
	public static function deleteObject($objectid) {
		//get the backend object
		$backend = self::$_usedBackends[self::getBackendNameById($objectid)];
		//does the backend support deleting objects at all?
		if($backend->implementsActions(OC_CALENDAR_BACKEND_DELETE_OBJECT)) {
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
	public static function moveObject($objectid, $newcalendarid) {
		$oldbackend = self::getBackendNameById($objectid);
		$newbackend = self::getBackendNameById($newcalendarid);
		if($oldbackend == $newbackend && self::$_usedBackends[$oldbackend]->implementsActions(OC_CALENDAR_BACKEND_MOVE_OBJECT)) {
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
	
	// !UI stuff
	
	/**
	 * @brief merge calendar two into calendar one
	 * @param $calendarid1 string
	 * @param $calendarid2 string
	 * @returns boolean
	 *
	 * Merge calendar two into calendar one, both with a specific calendarid
	 */
	public static function setCalendarsVisibility($calendarid, $visibility) {
		//UI stuff only
	}
	
	public static function getUsersDefaultCalendar() {
		
	}
	
	public static function setCalendarsOrder() {
		
	}
	
	public static function doWhatEverYouWant() {
		
	}

	
	// !Hooks
	
	public static function onUserCreate($userid){
		self::createCalendarCacheByUserID($userid);
		self::createCalendarCacheByUserID($userid);
	}
	
	public static function onUserDelete($userid){
		self::wipeCalendarCacheByUserID($userid);
		self::wipeObjectCacheByUserID($userid);
	}
	
	public static function createCacheForAllUsers(){
		$users = \OCP\User::getUser();
		foreach($users as $user){
			self::createCalendarCacheByUserID($user);
			self::createCalendarCacheByUserID($user);
		}
		return false;
	}
	
	
	/** * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                *
	 * Implementation of all private calendar methods *
	 *                                                *
	 ** * * * * * * * * * * * * * * * * * * * * * * * */

	 // !Helper methods

	private static function getBackendNameById($id) {
		$splittedId = self::splitObjectId($id);
		return $splittedId['backend'];
	}
	
	private static function getCalendarURIById($id) {
		$splittedId = self::splitObjectId($id);
		return $splittedId['calendar'];
	}

	private static function getObjectUIDById($id) {
		$splittedId = self::splitObjectId($id);
		return $splittedId['object'];
	}
	
	private static function splitObjectId($id) {
		list($backend, $calendar, $object) = explode('.', $id);
		return array('backend' => $backend, 'calendar' => $calendar, 'object' => $object);
	}
	
	private static function getClassNameByBackendObject($backend) {
		$classname = explode('\\', get_class($backend));
		return strtolower(end($classname));
	}
	
	private static function doesBackendExist($backendname) {
		//does the given backend exists at all?
		if(array_key_exists($backendname, self::$_usedBackends)) {
			//yeah, everything is fine
			return true;
		}else{
			//nope, backend not found
			//\OCP\Util::writeLog('calendar', 'Backend with the name "' . $backendname . '" was not found', \OCP\Util::WARN);
			return false;
		}
	}
	
	
	private static function getEventObjectByObjectCachingDBRow($row){
		$event = \Sabre\VObject\Component::create('VEVENT');
		$event = self::getObjectObjectByObjectAndObjectCachingDBRow($event, $row);
		return $event;
	}
	
	private static function getJournalObjectByObjectCachingDBRow($row){
		$journal = \Sabre\VObject\Component::create('VJOURNAL');
		$journal = self::getObjectObjectByObjectAndObjectCachingDBRow($journal, $row);
		return $journal;
	}
	
	private static function getTodoObjectByObjectCachingDBRow($row){
		$todo = \Sabre\VObject\Component::create('VTODO');
		$todo = self::getObjectObjectByObjectAndObjectCachingDBRow($todo, $row);
		return $todo;
	}
	
	private static function getObjectObjectByObjectAndObjectCachingDBRow($object, $row){
		//$object->add('X-OWNCLOUD-WHATSOEVER', 42);
		return $object;
	}
	
	// !Calendar caching
	
	private static function getCalendarObjectByCalendarCachingDBRow($row){
			//create a new calendar object
			$calendar = \Sabre\VObject\Component::create('VCALENDAR');
			//split calendarid to get backend and uri
			list($backendname, $uri) = explode('.', $row['calendarid']);
			//add some informations like name of the backend, 
			$calendar->add('X-OWNCLOUD-BACKEND', $backendname);
			//color of the calendar,
			$calendar->add('X-OWNCLOUD-CALENDARCOLOR', $row['color']);
			//id of the calendar,
			$calendar->add('X-OWNCLOUD-CALENADRID', $row['calendarid']);
			//the supported components,
			$calendar->add('X-OWNCLOUD-COMPONENTS', $row['components']);
			//name of the calendar,
			$calendar->add('X-OWNCLOUD-DISPLAYNAME', $row['displayname']);
			//is the calendar editable or readonly,
			$calendar->add('X-OWNCLOUD-ISEDITABLE', $row['writable']);
			//order of the calendar,
			$calendar->add('X-OWNCLOUD-ORDER', $row['order']);
			//the calendar's timezone,
			$calendar->add('X-OWNCLOUD-TZ', $row['timezone']);
			//uri of the calendar,
			$calendar->add('X-OWNCLOUD-URI', $uri);
			//userid of the owner,
			$calendar->add('X-OWNCLOUD-USERID', $row['userid']);
			//is the calendar enabled, disabled or hidden
			$calendar->add('X-OWNCLOUD-VISIBILITY', $row['visibility']);
			//return the created calendar object
			return $calendar;
	}
	
	private static function createCalendarCacheByCalendarObject($calendarobject){
		$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*calendars` (`backend`,`calendarid`,`userid`,`displayname`,`visibility`,`ctag`,`color`,`order`,`writable`,`timezone`,`components`) VALUES(?,?,?,?,?,?,?,?,?,?,?)' );
		$result = $stmt->execute( array($calendarobject->__get('X-OWNCLOUD-BACKEND'),
										$calendarobject->__get('X-OWNCLOUD-CALENADRID'),
										$calendarobject->__get('X-OWNCLOUD-USER'),
										$calendarobject->__get('X-OWNCLOUD-DISPLAYNAME'),
										$calendarobject->__get('X-OWNCLOUD-VISIBILITY'),
										$calendarobject->__get('X-OWNCLOUD-CTAG'),
										$calendarobject->__get('X-OWNCLOUD-CALENDARCOLOR'),
										$calendarobject->__get('X-OWNCLOUD-ORDER'),
										$calendarobject->__get('X-OWNCLOUD-ISEDITABLE'),
										$calendarobject->__get('X-OWNCLOUD-TZ'),
										$calendarobject->__get('X-OWNCLOUD-COMPONENTS')) );
		if(\OCP\DB::isError($result)){
			return false;
		}
		return true;
	}
	
	private static function updateCalendarCacheByCalendarObject($calendarobject){
		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*calendars` SET `backend` = ? , `calendarid` = ? ,`userid` = ?, `displayname` = ?, `visibility` = ?, `ctag` = ?, `color` = ?, `order` = ?, `writable` = ?, `timezone` = ?, `components` = ? WHERE `calendarid` = ?' );
		$result = $stmt->execute( array($calendarobject->__get('X-OWNCLOUD-BACKEND'),
										$calendarobject->__get('X-OWNCLOUD-CALENADRID'),
										$calendarobject->__get('X-OWNCLOUD-USER'),
										$calendarobject->__get('X-OWNCLOUD-DISPLAYNAME'),
										$calendarobject->__get('X-OWNCLOUD-VISIBILITY'),
										$calendarobject->__get('X-OWNCLOUD-CTAG'),
										$calendarobject->__get('X-OWNCLOUD-CALENDARCOLOR'),
										$calendarobject->__get('X-OWNCLOUD-ORDER'),
										$calendarobject->__get('X-OWNCLOUD-ISEDITABLE'),
										$calendarobject->__get('X-OWNCLOUD-TZ'),
										$calendarobject->__get('X-OWNCLOUD-COMPONENTS'),
										!is_null($calendarobject->__get('X-OWNCLOUD-OLDCALENDARID')) ? $calendarobject->__get('X-OWNCLOUD-OLDCALENDARID') : $calendarobject->__get('X-OWNCLOUD-CALENADRID')) );
		if(\OCP\DB::isError($result)){
			return false;
		}
		return true;
	}
	
	private static function deleteCalendarCacheByCalendarID($calendarid){
		$stmt = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*calendars` WHERE `calendarid` = ?' );
		$result = $stmt->execute( array($calendarid) );
		if(\OCP\DB::isError($result)){
			return false;
		}
		return true;
	}
	
	private static function wipeCalendarCacheByUserID($userid){
		$stmt = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*calendars` WHERE `userid` = ?' );
		$result = $stmt->execute( array($userid) );
		if($result){
			return true;
		}
		return false;
	}
	
	private static function createCalendarCacheByUserID($userid){
		$calendars = self::getAllUncachedCalendarsByUser($userid);
		foreach($calendars as $calendar){
			self::createCalendarCacheByCalendarObject($calendar);
		}
		return true;
	}
	
	// !Object Cache
	
	private static function createObjectCacheByObjectObject($object){
		
	}
	
	private static function updateObjectCacheByObjectObject($object){
		
	}
	
	private static function deleteObjectCacheByObjectID($objectid){
		$stmt = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*calendar_cache` WHERE `objectid` = ?' );
		$result = $stmt->execute( array($objectid) );
		if(\OCP\DB::isError($result)){
			return false;
		}
		return true;
	}
	
	private static function wipeObjectCacheByUserID($userid){
		$stmt = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*calendar_cache` WHERE `userid` = ?' );
		$result = $stmt->execute( array($userid) );
		if(\OCP\DB::isError($result)){
			return false;
		}
		return true;
	}
	
	private static function createObjectCacheByUserID($userid){
		$objects = self::allObjects($userid);
		foreach($objects as $object){
			$backend = $object->__get('X-OWNCLOUD-BACKEND');
			$uri = $object->__get('X-OWNCLOUD-OBJECTs-CALENDAR');
			self::createObjectCacheByObjectObject($object);
		}
		return true;
	}
	
	private static function validateCalendarObject($calendarobject){
		
	}
	
	private static function validateObjectObject($objectobject){
		
	}
	
	private static function validateObjectType($type){
		
	}
	 
}