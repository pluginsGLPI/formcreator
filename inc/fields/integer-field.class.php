<?php
class integerField extends PluginFormcreatorField
{
   public function isValid($value)
   {
      if (!parent::isValid($value)) return false;

      // Not a number
      if (!ctype_digit($value)) {
         Session::addMessageAfterRedirect(__('This is not an integer:', 'formcreator') . ' ' . $field['name'], false, ERROR);
         return false;

      // Min range not set or text length longer than min length
      } elseif (!empty($field['range_min']) && ($value < $field['range_min'])) {
         $message = sprintf(__('The following number must be greater than %d:', 'formcreator'), $field['range_min']);
         Session::addMessageAfterRedirect($message . ' ' . $field['name'], false, ERROR);
         return false;

      // Max range not set or text length shorter than max length
      } elseif (!empty($field['range_max']) && ($value > $field['range_max'])) {
         $message = sprintf(__('The following number must be lower than %d:', 'formcreator'), $field['range_max']);
         Session::addMessageAfterRedirect($message . ' ' . $field['name'], false, ERROR);
         return false;

      // Specific format not set or well match
      } elseif (!empty($field['regex']) && !preg_match($field['regex'], $value)) {
         Session::addMessageAfterRedirect(__('Specific format does not match:', 'formcreator') . ' ' . $field['name'], false, ERROR);
         return false;

      // All is OK
      } else {
         return true;
      }
   }

   public static function getName()
   {
      return __('Integer', 'formcreator');
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
      return "tab_fields_fields['integer'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
