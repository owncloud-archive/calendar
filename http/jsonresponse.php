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
use \OCA\Calendar\Http\IResponse;

class JSONResponse extends Response {

	protected $data;

	/**
	 * @param array|object $data the object or array that should be transformed
	 * @param int $statusCode the Http status code, defaults to 200
	 */
	public function __construct(&$data=null, $statusCode=null) {
		$this->data = $data;

		if($statusCode === null) {
			if($data instanceof IResponse) {
				$statusCode = HTTP::STATUS_OK;
			} else {
				$statusCode = HTTP::STATUS_NO_CONTENT;
			}
		}

		$this->setStatus($statusCode);
		$this->addHeader('X-Content-Type-Options', 'nosniff');
		$this->addHeader('Content-type', 'application/json; charset=utf-8');
	}

	/**
	 * Returns the rendered json
	 * @return string the rendered json
	 */
	public function render(){
		if($this->data instanceof IResponse) {
			return $this->data->serialize();
		} else {
			return '';
		}
	}
}