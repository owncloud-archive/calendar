<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\BusinessLayer;

use \OCA\Calendar\AppFramework\Core\API;
use \OCA\Calendar\AppFramework\Db\Mapper;
use \OCA\Calendar\AppFramework\Db\DoesNotExistException;

abstract class BusinessLayer {

	protected $api;
	protected $backends;

	public function __construct(API $api,
								BackendBusinessLayer $backends){
		$this->api = $api;
		$this->backends = $backends;
	}

	/**
	 * get backend and real uri from public uri
	 * public uri: backend-uri; e.g. local-work
	 * backend: backend; e.g. local for standard database backend
	 * real uri: uri; e.g. work
	 * @param string $uri public uri
	 * @throws BusinessLayerException if uri is empty
	 * @throws BusinessLayerException if uri is not valid
	 */
	final protected function splitCalendarURI($calendarId) {
		$split = CalendarUtility::splitURI($calendarId);

		if($split[0] === false || $split[1] === false) {
			throw new BusinessLayerException('calendar uri is not valid');
		}

		return $split;
	}

	/**
	 * get backend and real calendar uri and real object uri from public uri
	 * public uri: backend-uri; e.g. local-work
	 * backend: backend; e.g. local for standard database backend
	 * real uri: uri; e.g. work
	 * @param string $uri public uri
	 * @throws BusinessLayerException if uri is empty
	 * @throws BusinessLayerException if uri is not valid
	 * @throws DoesNotImplementException if backend does not implement searched implementation
	 */
	final protected function splitObjectURI($objectURI=null) {
		$split = ObjectUtility::splitURI($objectURI);

		if($split[0] === false || $split[1] === false || $split[2] === false) {
			throw new BusinessLayerException('object uri is not valid');
		}

		return $split;
	}

	/**
	 * check if a backend does implement smth
	 * @param string $backend - id of backend
	 * @param string $implementations 
	 * @return boolean
	 * @throws BusinessLayerException if backend does not exist
	 * @throws BusinessLayerException if backend is disabled
	 * @throws DoesNotImplementException if backend does not implement searched implementation
	 */
	final protected function checkBackendSupports($backend, $implementation) {
		$isSupported = $this->backends->find($backend)->api->implementsActions($implementation);

		if(!$isSupported) {
			throw new DoesNotImplementException('Backend (' . $backend . ') does not implement "' . $implementation . '".');
		}

		return true;
	}

	/**
	 * check if a backend exists
	 * @param string $backend - id of backend
	 * @return boolean
	 * @throws BusinessLayerException if backend does not exist
	 * @throws BusinessLayerException if multiple backends exist for the given backend id
	 */
	final protected function checkBackendExists($backend) {
		$this->backends->find($backend);
		return true;
	}

	/**
	 * check if a backend is enabled
	 * @param string $backend - id of backend
	 * @return boolean
	 * @throws BusinessLayerException if backend does not exist
	 * @throws BusinessLayerException if multiple backends exist for the given backend id
	 * @throws BusinessLayerException if backend is disabled
	 */
	final protected function checkBackendEnabled($backend) {
		$backend = $this->backends->find($backend);

		if($backend->getEnabled() !== true) {
			throw new BusinessLayerException('Backend ("' . $backend . '") is not enabled.');
		}

		return true;
	}
}