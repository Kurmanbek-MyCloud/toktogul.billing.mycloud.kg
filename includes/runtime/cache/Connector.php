<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Cache_Connector {

	private static $instance = false;
	private $cache = array();

	private function __construct() {
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get($ns, $key) {
		if (isset($this->cache[$ns][$key])) {
			return $this->cache[$ns][$key];
		}
		return false;
	}

	public function set($ns, $key, $value) {
		$this->cache[$ns][$key] = $value;
	}

	public function delete($ns, $key) {
		if (isset($this->cache[$ns][$key])) {
			unset($this->cache[$ns][$key]);
		}
	}

	public function flush() {
		$this->cache = array();
	}
}
