<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class selectField extends Field
{
	public function show() {
		echo '<dl>'.PHP_EOL;
		echo "\t".'<dt>'.PHP_EOL;
		echo "\t\t".'<label for="selectField'.$this->_id.'"';
		if($this->_required === true) echo ' class="required"';
		echo '>';
		echo $this->_label;
		if($this->_required === true) echo ' <span class="asterisk">*</span>';
		else echo ' &nbsp;&nbsp;';
		echo '</label>'.PHP_EOL;
		echo "\t".'</dt>'.PHP_EOL;
		echo "\t".'<dd>'.PHP_EOL;
		echo "\t\t".'<select name="selectField'.$this->_id.'" id="selectField'.$this->_id.'">'.PHP_EOL;
		foreach($this->_value as $value) {
			$value = trim(str_replace('"', "'", $value));
			echo "\t\t\t".'<option value="'.$value.'"';
			if(isset($_post['selectfield'.$this->_id]) && ($_POST['selectField'.$this->_id] == $value))
				echo ' selected="selected"';
			echo '>'.$value.'</option>'.PHP_EOL;
		}
		echo "\t\t".'</select>'.PHP_EOL;
		echo "\t".'</dd>'.PHP_EOL;
		echo '</dl>'.PHP_EOL;
	}

	public function isValid() {
		if(($this->_required !== true) || !empty($_POST['selectField'.$this->_id])) {
			if(in_array($_POST['selectField'.$this->_id], $this->_value)) {
				return true;
			}else{
				$this->_addError('<label for="selectField'.$this->_id.'">' . TXT_ERR_FORBIDEN_SELECT_VALUE . '<span style="color:#000">'.$this->_label.'</span></label>');
				return false;
			}
		}else{
			$this->_addError('<label for="selectField'.$this->_id.'">' . TXT_ERR_EMPTY_SELECT . '<span style="color:#000">'.$this->_label.'</span></label>');
			return false;
		}
	}

	public function getPost() {
		return trim(strip_tags($_POST['selectField'.$this->_id]));
	}

   public static function getName()
   {
      return __('Select', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['select'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
