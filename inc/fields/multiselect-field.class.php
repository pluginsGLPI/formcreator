<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class multiSelectField extends Field
{
	public function show() {
		echo '<dl>'.PHP_EOL;
		echo "\t".'<dt>'.PHP_EOL;
		echo "\t\t".'<label for="multiSelectField'.$this->_id.'"';
		if($this->_required === true) echo ' class="required"';
		echo '>';
		echo $this->_label;
		if($this->_required === true) echo ' <span class="asterisk">*</span>';
		else echo ' &nbsp;&nbsp;';
		echo '</label>'.PHP_EOL;
		echo "\t".'</dt>'.PHP_EOL;
		echo "\t".'<dd>'.PHP_EOL;
		echo "\t\t".'<select name="multiSelectField'.$this->_id.'[]" id="multiSelectField'.$this->_id.'" multiple="multiple" size="4">'.PHP_EOL;
		foreach($this->_value as $value) {
			$value = trim(str_replace('"', "'", $value));
			echo "\t\t\t".'<option value="'.$value.'"';
			if(isset($_post['multiselectfield'.$this->_id]) && (in_array($value, $_POST['multiSelectField'.$this->_id])))
				echo ' selected="selected"';
			echo '>'.$value.'</option>'.PHP_EOL;
		}
		echo "\t\t".'</select>'.PHP_EOL;
		echo "\t".'</dd>'.PHP_EOL;
		echo '</dl>'.PHP_EOL;
	}

	public function isValid() {
		if(($this->_required !== true) || !empty($_POST['multiSelectField'.$this->_id])) {
			foreach($_POST['multiSelectField'.$this->_id] as $value) {
				if(!in_array($value, $this->_value)) {
					$this->_addError('<label for="multiSelectField'.$this->_id.'">' . TXT_ERR_FORBIDEN_MULTISELECT_VALUE . '<span style="color:#000">'.$this->_label.'</span></label>');
					return false;
				}
			}
			return true;
		}else{
			$this->_addError('<label for="multiSelectField'.$this->_id.'">' . TXT_ERR_EMPTY_MULTISELECT . '<span style="color:#000">'.$this->_label.'</span></label>');
			return false;
		}
	}

	public function getPost() {
		$values = '<br />';
		foreach($_POST['multiSelectField'.$this->_id] as $value) {
			$values .= trim(strip_tags($value)).'<br />'.PHP_EOL;
		}
		return $values;
	}

   public static function getName()
   {
      return __('Multiselect', 'formcreator');
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
      return "tab_fields_fields['multiselect'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
