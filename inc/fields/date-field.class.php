<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class dateField extends Field
{
   public function show() {
      echo '<dl>'.PHP_EOL;
      echo "\t".'<dt>'.PHP_EOL;
      echo "\t\t".'<label for="dateField'.$this->_id.'"';
      if($this->_required === true) echo ' class="required"';
      echo '>';
      echo $this->_label;
      if($this->_required === true) echo ' <span class="asterisk">*</span>';
      else echo ' &nbsp;&nbsp;';
      echo '</label>'.PHP_EOL;
      echo "\t".'</dt>'.PHP_EOL;
      echo "\t".'<dd>'.PHP_EOL;

      if(isset($_POST['dateField'.$this->_id])) $value = $_POST['dateField'.$this->_id];
      else $value = $this->_value[0];

      echo "\t\t".'<input type="text" name="dateField'.$this->_id.'" id="dateField'.$this->_id.'" value="'.$value.'" maxlength="255" />';
      echo "\t".'</dd>'.PHP_EOL;
      echo '</dl>'.PHP_EOL;
   }

   public function isValid() {
      if(($this->_required !== true) || !empty($_POST['dateField'.$this->_id]))
         return true;
      else{
         $this->_addError('<label for="dateField'.$this->_id.'">' . TXT_ERR_EMPTY_TEXT . '<span style="color:#000">'.$this->_label.'</span></label>');
         return false;
      }
   }

   public function getPost() {
      return trim(strip_tags($_POST['dateField'.$this->_id]));
   }

   public static function getName()
   {
      return __('Date', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['date'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
