<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class radioField extends Field
{
	protected $_disposition = 'V';

	public function setVertical() {
		$this->_disposition = 'V';
	}

	public function setHorizontal() {
		$this->_disposition = 'H';
	}

	public function show() {
		echo '<dl>'.PHP_EOL;
		echo "\t".'<dt>'.PHP_EOL;
		echo "\t\t".'<label for="radioField'.$this->_id.'_1"';
		if($this->_required === true) echo ' class="required"';
		echo '>';
		echo $this->_label;
		if($this->_required === true) echo ' <span class="asterisk">*</span>';
		else echo ' &nbsp;&nbsp;';
		echo '</label>'.PHP_EOL;
		echo "\t".'</dt>'.PHP_EOL;
		echo "\t".'<dd>'.PHP_EOL;
		$i=0;
		foreach($this->_value as $value) {
			$i++;
			$value = trim(str_replace('"', "'", $value));
			echo "\t\t".'<label class="radio'.$this->_disposition.'">';
			echo '<input type="radio" name="radioField'.$this->_id.'" id="radioField'.$this->_id.'_'.$i.'" value="'.$value.'"';
			if(isset($_post['radiofield'.$this->_id]) && ($_POST['radioField'.$this->_id] == $value))
				echo ' checked="checked"';
			echo ' /> ';
			echo $value;
			echo '</label>'.PHP_EOL;
		}
		echo "\t".'</dd>'.PHP_EOL;
		echo '</dl>'.PHP_EOL;
	}

	public function isValid() {
		if(($this->_required !== true) || !empty($_POST['radioField'.$this->_id])) {
			if(in_array($_POST['radioField'.$this->_id], $this->_value)) {
				return true;
			}else{
				$this->_addError('<label for="radioField'.$this->_id.'">' . TXT_ERR_FORBIDEN_RADIO_VALUE . '<span style="color:#000">'.$this->_label.'</span></label>');
				return false;
			}
		}else{
			$this->_addError('<label for="radioField'.$this->_id.'">' . TXT_ERR_EMPTY_RADIO . '<span style="color:#000">'.$this->_label.'</span></label>');
			return false;
		}
	}

	public function getPost() {
		return trim(strip_tags($_POST['radioField'.$this->_id]));
	}

   public static function getName()
   {
      return __('Radios', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['radios'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
