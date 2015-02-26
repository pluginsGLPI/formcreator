<?php
class textField extends PluginFormcreatorField
{
	public function isValid($value)
   {
      if (!parent::isValid($value)) return false;

      $value = utf8_decode(stripcslashes($value));

      // Min range not set or text length longer than min length
      if(!empty($this->fields['range_min']) && strlen($value) < $this->fields['range_min']) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too short (minimum %d characters):', 'formcreator'), $this->fields['range_min']) . ' ' . $this->fields['name'], false, ERROR);
         return false;

      // Max range not set or text length shorter than max length
      } elseif(!empty($this->fields['range_max']) && strlen($value) > $this->fields['range_max']) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too long (maximum %d characters):', 'formcreator'), $this->fields['range_max']) . ' ' . $this->fields['name'], false, ERROR);
         return false;

      // Specific format not set or well match
      } elseif(!empty($this->fields['regex']) && !preg_match($this->fields['regex'], $value)) {
         Session::addMessageAfterRedirect(__('Specific format does not match:', 'formcreator') . ' ' . $this->fields['name'], false, ERROR);
         return false;
		}

      // All is OK
		return true;
	}

   public static function getName()
   {
      return __('Text', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 1,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['text'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
