<?php
/**
 * Copyright (c) 2013 Georg Ehrke <oc.list@georgehrke.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Calendar\Db;

use \OCA\AppFramework\Db\Entity;

class Object extends Entity{

	private $id;
	private $userid;
	private $backend;
	private $uri;
	private $uid;
	private $type;
	private $startdate;
	private $enddate;
	private $timezone;
	private $repeating;
	private $summary;
	private $calendardata;
	private $lastmodified;

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

	public function getUID(){
		return $this->uid;
	}

	public function getType(){
		return $this->type;
	}
	
	public function getStartdate(){
		return $this->startdate;
	}
	
	public function getEnddate(){
		return this->enddate;
	}

	public function getTimezone(){
		return $this->timezone;
	}

	public function getRepeating(){
		return $this->repeating;
	}
	
	public function getSummary(){
		return $this->summary;
	}

	public function getCalendardata(){
		return $this->enabled;
	}

	public function getLastmodified(){
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

	public function setUID($uid){
		$this->uid = $uid;
	}

	public function setType($type){
		$this->type = $type;
	}

	public function setStartdate($startdate){
		$this->startdate = $startdate;
	}

	public function setEnddate($enddate){
		$this->enddate = $enddate;
	}

	public function setTimezone($timezone){
		$this->timezone = $timezone;
	}

	public function setRepeating($repeating){
		$this->repeating = $repeating;
	}

	public function setCalendardata($calendardata){
		$this->calendardata = $calendardata;
	}

	public function setLastmodified($lastmodified){
		$this->lastmodified = $lastmodified;
	}

}
