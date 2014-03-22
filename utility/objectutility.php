<?php
/**
 * Copyright (c) 2014 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Utility;

class ObjectUtility extends Utility{

	public static function randomURI() {
		return substr(md5(rand().time().rand()),rand(0,11),20);
	}

}