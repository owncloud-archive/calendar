<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

class ObjectType {
	const EVENT		= 1;
	const JOURNAL	= 2;
	const TODO		= 4;
	const ALL		= 7;
}