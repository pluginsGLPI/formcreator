<?php

abstract class Field
{
	protected $_id;
	protected $_label;
	protected $_value;
	protected $_required;
	protected $_errors = array();

	abstract public function isValid();
   abstract public function getPost();
   abstract public static function getName();

	public function __construct($id = 0, $label = '', $required = false) {
		$this->_id = $id;
		$this->_label = $label;
		$this->_required = $required;
	}

	public function setLabel($label) {
		$this->_label = $label;
	}

	public function getLabel() {
		return $this->_label;
	}

	public function addValue($value) {
		$this->_value[] = trim($value);
	}

	public function getValues() {
		return $this->_value;
	}

	public function getLastError() {
		return array_pop($this->_errors);
	}

	protected function _addError($error) {
		return array_push($this->_errors, $error);
	}
}
