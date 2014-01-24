<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
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
	protected $mapper;
	protected $backends;

	public function __construct(API $api,
								Mapper $mapper,
								BackendBusinessLayer $backends){
		$this->api = $api;
		$this->mapper = $mapper;
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
	 * @throws DoesNotImplementException if backend does not implement searched implementation
	 */
	final protected function splitPublicURI ($publicURI=null) {
		if ( $publicURI === false || $publicURI === null || $publicURI === '' ) {
			throw new BusinessLayerException('URI is empty');
		}
		if ( substr_count($publicURI, '-') === 0 ){
			throw new BusinessLayerException('URI is not valid');
		}

		list($backend, $realURI) = explode('-', $publicURI, 2);

		return array($backend, $realURI);
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
	final protected function getBackendAndRealCalendarURIAndRealObjectURIFromURI($publicURI=null) {
		if ( $publicURI === false || $publicURI === null || $publicURI === '' ) {
			throw new BusinessLayerException('URI is empty');
		}
		if ( substr_count($publicURI, '-') === 0 ){
			throw new BusinessLayerException('URI is not valid');
		}

		list($backend, $realCalendarURI, $realObjectURI) = explode('-', $publicURI, 3);

		return array($backend, $realCalendarURI, $realObjectURI);
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
			throw new DoesNotImplementException('This Backend (' . $backend . ') does not implement "' . $implementation . '".');
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