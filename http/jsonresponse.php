<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Http;

use \OCA\Calendar\AppFramework\Http\Response;
use \OCA\Calendar\AppFramework\Http\Http;

class JSONResponse extends Response {

	protected $data;

	/**
	 * @param array|object $data the object or array that should be transformed
	 * @param int $statusCode the Http status code, defaults to 200
	 */
	public function __construct($data=array(), $statusCode=Http::STATUS_OK) {
		$this->data = $data;
		$this->setStatus($statusCode);
		$this->addHeader('X-Content-Type-Options', 'nosniff');
		$this->addHeader('Content-type', 'application/json; charset=utf-8');
	}

	/**
	 * Returns the rendered json
	 * @return string the rendered json
	 */
	public function render(){
		if(is_array($this->data)) {
			return json_encode($this->data);
		} else {
			return $this->data;
		}
	}
}