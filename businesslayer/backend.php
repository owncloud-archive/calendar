<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\BusinessLayer;

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Db\DoesNotExistException;
use \OCA\Calendar\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\Calendar\Db\Backend;
use \OCA\Calendar\Db\BackendMapper;

class BackendBusinessLayer {

	private $backends;

	public function __construct(BackendMapper $backendMapper,
	                            API $api){
		$this->mapper = $backendMapper;
		$this->api = $api;
		$this->setup();
	}

	public function isEnabled($id) {
		return true;
	}

	public function find($id) {
		try {
			return $this->mapper->find($id);
		} catch(DoesNotExistException $ex){
			throw new BusinessLayerException($ex->getMessage());
		} catch(MultipleObjectsReturnedException $ex){
			throw new BusinessLayerException($ex->getMessage());
		}
	}

	public function findAll() {
		return $this->mapper->findAll();
	}

	public function findAllDisabled() {
		return $this->mapper->findWhereEnabledIs(false);
	}

	public function findAllEnabled() {
		return $this->mapper->findWhereEnabledIs(true);
	}

	public function create($backend, $classname, $arguments='', $enabled=true) {
		$this->allowNoBackendTwice($backend);
		
		$create = new Backend();
		$create->setBackend($backend);
		$create->setClassname($classname);
		$create->setArguments($arguments);
		$create->setEnabled($enabled);
		
		return $this->mapper->create($create);
	}

	public function delete($id) {
		return $this->mapper->delete($id);
	}

	public function disable($id){
		$backend = $this->findByName($id);
		$backend->setEnabled(0);
		return $this->mapper->update($backend);
	}

	public function enable($id){
		$backend = $this->findByName($id);
		$backend->setEnabled(1);
		return $this->mapper->update($backend);
	}

	public function getDefault(){
		$backend = \OCP\Config::getAppValue('calendar', 'defaultBackend', 'database');
		return $this->find($backend);
	}

	public function setDefault($backend){
		if($backend instanceof Backend){
			$backend = $backend->getBackend();
		}
		
		\OCP\Config::setAppValue('calendar', 'defaultBackend', $backend->getBackend());
		return true;
	}

	private function allowNoBackendTwice($backend){
		$existingBackends = $this->mapper->find($backend);
		if(count($existingBackends) > 0){
			throw new BusinessLayerExistsException(
				$this->api->getTrans()->t('Can not add backend: Exists already'));
		}
	}

	private function setup() {
		$this->backends = array();
		$enabledBackends = $this->findAllEnabled();
		foreach($enabledBackends as $backend) {
			$class = $backend->getClassname();
			$arguments = is_array($backend->getArguments()) ? $backend->getArguments() : array();

			if(class_exists($class) === false){
				\OCP\Util::writeLog('calendar', 'Calendar backend '.$class.' was not found', \OCP\Util::DEBUG);
				$this->disable($backend);
				continue;
			}
			
			if(in_array($class, $this->backends)){
				\OCP\Util::writeLog('calendar', 'Backend '.$class.' already initialized. Please check if there are multiple db entries for this backend.', \OCP\Util::DEBUG);
				continue;
			}
			
			$reflectionObj = new \ReflectionClass($class);
			$api = $reflectionObj->newInstanceArgs(array($this->api, $arguments));
			$backend->registerAPI($api);
			array_push($this->backends, array($class => $backend));
		}
		
		if(count($this->backends) === 0){
			//throw new BusinessLayerException('No backend was setup successfully');
		}
	}
}