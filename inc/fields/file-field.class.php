<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class fileField extends Field
{
	public function show() {
		echo '<dl>'.PHP_EOL;
		echo "\t".'<dt>'.PHP_EOL;
		echo "\t\t".'<label for="fileField'.$this->_id.'"';
		if($this->_required === true) echo ' class="required"';
		echo '>';
		echo $this->_label;
		if($this->_required === true) echo ' <span class="asterisk">*</span>';
		echo '</label>'.PHP_EOL;
		echo "\t".'</dt>'.PHP_EOL;
		echo "\t".'<dd>'.PHP_EOL;
		echo "\t\t".'<input type="file" name="fileField'.$this->_id.'" id="fileField'.$this->_id.'" />';
		echo "\t".'</dd>'.PHP_EOL;
		echo '</dl>'.PHP_EOL;
	}

	public function isValid() {
		if(($this->_required !== true) || is_file($_FILES['fileField'.$this->_id]['tmp_name']))
			return true;
		else{
			$this->_addError('<label for="fileField'.$this->_id.'">' . TXT_ERR_EMPTY_FILE . '<span style="color:#000">'.$this->_label.'</span></label>');
			return false;
		}
	}

	public function getPost() {
		move_uploaded_file($_FILES['fileField'.$this->_id]['tmp_name'], FILE_PATH.'/'.$_FILES['fileField'.$this->_id]['name']);
		return $_FILES['fileField'.$this->_id]['name'];
	}

   public static function getName()
   {
      return __('File', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 0,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['file'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
