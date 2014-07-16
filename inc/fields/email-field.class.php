<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class emailField extends Field
{
	public function show() {
		echo '<dl>'.PHP_EOL;
		echo "\t".'<dt>'.PHP_EOL;
		echo "\t\t".'<label for="emailField'.$this->_id.'"';
		if($this->_required === true) echo ' class="required"';
		echo '>';
		echo $this->_label;
		if($this->_required === true) echo ' <span class="asterisk">*</span>';
		else echo ' &nbsp;&nbsp;';
		echo '</label>'.PHP_EOL;
		echo "\t".'</dt>'.PHP_EOL;
		echo "\t".'<dd>'.PHP_EOL;

		if(isset($_POST['emailField'.$this->_id])) $value = $_POST['emailField'.$this->_id];
		else $value = $this->_value[0];

		echo "\t\t".'<input type="text" name="emailField'.$this->_id.'" id="emailField'.$this->_id.'" value="'.$value.'" maxlength="255" />';
		echo "\t".'</dd>'.PHP_EOL;
		echo '</dl>'.PHP_EOL;
	}

	public function isValid() {
		$regExp = '#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#';
		if(($this->_required !== true) || !empty($_POST['emailField'.$this->_id])) {
			if(preg_match($regExp, $_POST['emailField'.$this->_id])) {
  				list($username, $domaine) = explode('@', $_POST['emailField'.$this->_id]);
				if(function_exists('checkdnsrr') && (!checkdnsrr($domaine, "MX"))) {
					$this->_addError('<label for="emailField'.$this->_id.'">' . TXT_ERR_BAD_EMAIL_DOMAIN . '</label>');
					return false;
				}else return true;
			}else{
				$this->_addError('<label for="emailField'.$this->_id.'">' . TXT_ERR_BAD_EMAIL_FORMAT . '</label>');
				return false;
			}
		}else{
			$this->_addError('<label for="emailField'.$this->_id.'">' . TXT_ERR_EMPTY_EMAIL . '</label>');
			return false;
		}
	}

	public function getPost() {
		return '<a href="mailto:'.trim(strip_tags($_POST['emailField'.$this->_id])).'">'.trim(strip_tags($_POST['emailField'.$this->_id])).'</a>';
	}

   public static function getName()
   {
      return __('E-mail', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['email'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
