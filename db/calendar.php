<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\AppFramework\Db\Entity;

class Calendar extends Entity{

	public $userid;
	public $backend;
	public $uri;
	public $displayname;
	public $components;
	public $ctag;
	public $timezone;
	public $color;
	public $order;
	public $enabled;
	public $writable;

	public function __construct($fromRow=null){
		if($fromRow){
			$this->fromRow($fromRow);
		}
	}

	public function getUserid(){
		return $this->userid;
	}

	public function getBackend(){
		return $this->backend;
	}
	
	public function getURI(){
		return $this->uri;
	}

	public function getDisplayname(){
		return $this->displayname;
	}

	public function getComponents(){
		return $this->components;
	}
	
	public function getCTag(){
		return $this->ctag;
	}

	public function getTimezone(){
		return $this->timezone;
	}

	public function getColor(){
		return $this->color;
	}
	
	public function getOrder(){
		return $this->order;
	}

	public function getEnabled(){
		return $this->enabled;
	}

	public function getWritable(){
		return $this->vritable;
	}


	public function setId($id){
		$this->id = $id;
	}

	public function setUserid($userid){
		$this->userid = $userid;
	}

	public function setBackend($backend){
		$this->backend = $backend;
	}

	public function setURI($uri){
		$this->uri = $uri;
	}

	public function setDisplayname($displayname){
		$this->displayname = $displayname;
	}

	public function setComponents($components){
		$this->components = $components;
	}

	public function setCTag($ctag){
		$this->ctag = $ctag;
	}

	public function setTimezone($timezone){
		$this->timezone = $timezone;
	}

	public function setColor($color){
		$this->color = $color;
	}

	public function setOrder($oder){
		$this->order = $order;
	}

	public function setEnabled($enabled){
		$this->enabled = $enabled;
	}

	public function setWriteable($writable){
		$this->writable = $writable;
	}

}
