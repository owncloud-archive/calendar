<?php
/**
 * Copyright (c) 2013 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Controller;

use OCA\AppFramework\DoesNotExistException as DoesNotExistException;
use OCA\AppFramework\RedirectResponse as RedirectResponse;

use OCA\Calendar\Exception\NoBackendSetup as NoBackendSetup;

class Backend extends \OCA\AppFramework\Controller {
	//! vars
	//associative array with $backendname => $backendobject
	private $backends;


	//!constructor
	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 * @param ItemMapper $itemMapper: an itemwrapper instance
	 */
	public function __construct($api, $request, $itemMapper){
		//Last call for all Backends
		OCP\Util::emitHook('OC_Calendar', 'preInitBackends');
		//setup all registered and enabled backends
		$this->setupBackends();
		//call parent's constructor
		parent::__construct($api, $request);
		$this->itemMapper = $itemMapper;
	}


	//!initialization
	private function setupBackends() {
		$enabledbackends = $this->getEnabledBackends();
		foreach($enabledbackends as $backend) {
			$class = $backend['class'];
			$arguments = $backend['arguments'];
			if(class_exists( $class ) && !in_array( $class , $this->backends )) {
				// create a reflection object
				$reflectionObj = new \ReflectionClass($class);
				// use Reflection to create a new instance, using the $args
				$api = $reflectionObj->newInstanceArgs($arguments);
				$backend->registerAPI($api);
				array_push($this->backends, array($class => $backend));
			}else{
				if(!class_exists($class)){
					\OCP\Util::writeLog('calendar', 'Calendar backend '.$class.' was not found', \OCP\Util::DEBUG);
					//disable backend if it does not exist anymore
					$this->disableBackend($backend);
				}elseif(in_array( $class , $this->backends ){
					\OCP\Util::writeLog('calendar', 'Backend '.$class.' already initialized. Please check if there are any multiple db entries for this backend.', \OCP\Util::DEBUG);
					//remove all db entries for this backend and make a clean install
					$this->uninstallBackend($backend);
					$this->installBackend($backend);
				}else{
					\OCP\Util::writeLog('calendar', 'Calendar backend '.$class.' was not setup due to an unknown error', \OCP\Util::DEBUG);
				}
			}
		}
		if(count($this->backends) === 0){
			throw new \NoBackendSetup('No Backend was setup');
		}
	}


	/**
	 * @brief removes all used backends
	 * @returns void
	 * 
	 * removes all initialized backends
	 */
	private function clearBackends() {
		$this->backends = array();
	}


	//!backend management
	/**
	 * @brief gets all backends
	 * @returns array of backend objects
	 * 
	 * gets all backends
	 */
	public function getAllBackends(){
		return $this->itemMapper->findAll();
	}


	/**
	 * @brief gets all enabled backends
	 * @returns array of backend objects
	 * 
	 * gets all enabled backends
	 */
	public function getEnabledBackends(){
		return $this->itemMapper->findWhereEnabledIs(1);
	}


	/**
	 * @brief gets the default backend
	 * @returns array of backend objects
	 * 
	 * gets all enabled backends
	 */
	public function getDefaultBackend(){
		$backend = \OCP\Config::getAppValue('calendar', 'defaultBackend', 'database');
		return $this->itemMapper->findByName($backend);
		
	}


	/**
	 * @brief sets default backend
	 * @returns void
	 * 
	 * sets default backend
	 */
	public function setDefaultBackend($backend){
		\OCP\Config::setAppValue('calendar', 'defaultBackend', $backend->getBackend());
	}


	/**
	 * @brief disables a backend
	 * @returns void
	 * 
	 * disables a backend
	 */
	public function disableBackend($backend){
		$backend->setEnabled(0);
		$this->itemMapper->update($backend);
	}


	/**
	 * @brief enables a backend
	 * @returns void
	 * 
	 * enables
	 */
	public function enableBackend($backend){
		$backend->setEnabled(1);
		$this->itemMapper->update($backend);
	}


	/**
	 * @brief installs a backend
	 * @returns array of backend objects
	 * 
	 * installs a backend
	 */
	public function installBackend($backend){
		//just to be sure it's enabled ;)
		$backend->setEnabled(1);
		$this->itemMapper->create($backend);
	}


	/**
	 * @brief uninstalls a backend
	 * @returns array of backend objects
	 * 
	 * uninstalls a backend
	 */
	public function uninstallBackend($backend){
		$this->itemMapper->delete($backend);
	}


	/**
	 * @brief returns the number initialized backends
	 * @returns integer
	 * 
	 * number of backends
	 */
	public function getNumOfBackends(){
		return (int) count($this->backends);
	}
}