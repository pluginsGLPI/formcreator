<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class hiddenField extends Field
{
	public function show() {
		echo '<dl style="display: none">'.PHP_EOL;
		echo "\t".'<dt>'.PHP_EOL;
		echo "\t\t".'<label for="hiddenField'.$this->_id.'"';
		if($this->_required === true) echo ' class="required"';
		echo '>';
		echo $this->_label;
		if($this->_required === true) echo ' <span class="asterisk">*</span>';
		echo '</label>'.PHP_EOL;
		echo "\t".'</dt>'.PHP_EOL;
		echo "\t".'<dd>'.PHP_EOL;
		echo "\t\t".'<input type="hidden" name="hiddenField'.$this->_id.'" id="hiddenField'.$this->_id.'" value="'.$this->_value[0].'" />';
		echo "\t".'</dd>'.PHP_EOL;
		echo '</dl>'.PHP_EOL;
	}

	public function isValid() {
		if(($this->_required !== true) || !empty($_POST['hiddenField'.$this->_id]))
			return true;
		else return false;
	}

	public function getPost() {
		return trim(strip_tags($_POST['hiddenField'.$this->_id]));
	}

   public static function getName()
   {
      return __('Hidden field', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 0,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 0,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['hidden'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
