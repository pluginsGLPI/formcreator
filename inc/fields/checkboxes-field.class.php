<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class checkboxesField extends Field
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
		echo "\t\t".'<label for="checkboxField'.$this->_id.'_1"';
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
			echo "\t\t".'<label class="checkbox'.$this->_disposition.'">';
			echo '<input type="checkbox" name="checkboxField'.$this->_id.'[]" id="checkboxField'.$this->_id.'_'.$i.'" value="'.$value.'"';
			if(isset($_post['checkboxfield'.$this->_id]) && (in_array($value, $_POST['checkboxField'.$this->_id])))
				echo ' checked="checked"';
			echo ' /> ';
			echo $value;
			echo '</label>'.PHP_EOL;
		}
		echo "\t".'</dd>'.PHP_EOL;
		echo '</dl>'.PHP_EOL;
	}

	public function isValid() {
		if(($this->_required !== true) || !empty($_POST['checkboxField'.$this->_id])) {
			foreach($_POST['checkboxField'.$this->_id] as $value) {
				if(!in_array($value, $this->_value)) {
					$this->_addError('<label for="checkboxField'.$this->_id.'">' . TXT_ERR_FORBIDEN_CHECKBOX_VALUE . '<span style="color:#000">'.$this->_label.'</span></label>');
					return false;
				}
			}
			return true;
		}else{
			$this->_addError('<label for="checkboxField'.$this->_id.'">' . TXT_ERR_EMPTY_CHECKBOX . '<span style="color:#000">'.$this->_label.'</span></label>');
			return false;
		}
	}

	public function getPost() {
		$values = '<br />';
		foreach($_POST['checkboxField'.$this->_id] as $value) {
			$values .= trim(strip_tags($value)).'<br />'.PHP_EOL;
		}
		return $values;
	}

   public static function getName()
   {
      return __('Checkboxes', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['checkboxes'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
