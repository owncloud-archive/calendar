<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

abstract class Entity extends use \OCA\Calendar\AppFramework\Db\Entity {

	public function isValid();


	public function fromVObject();


	public function getVObject();
}