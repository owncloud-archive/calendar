<?php
/**
 * Copyright (c) 2013 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Calendar;

use \OCA\AppFramework\Db\Entity;

class Item extends Entity{

	private $id;
	private $userid;
	private $backend;
	private $uri;
	private $displayname;
	private $components;
	private $ctag;
	private $timezone;
	private $color;
	private $order;
	private $enabled;
	private $writable;

	public function __construct($fromRow=null){
		if($fromRow){
			$this->fromRow($fromRow);
		}
	}


	public function getId(){
		return $this->id;
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
		$this->oder = $order;
	}

	public function setEnabled($enabled){
		$this->enabled = $enabled;
	}

	public function setWriteable($writable){
		$this->writable = $writable;
	}

}
