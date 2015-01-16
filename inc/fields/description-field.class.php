<?php
class descriptionField extends PluginFormcreatorField
{
   public function show($canEdit = true)
   {
      echo '<div class="description_field form-group" id="form-group-field' . $this->fields['id'] . '">';
      echo nl2br(html_entity_decode($this->fields['description']));
      echo '</div>' . PHP_EOL;
      echo '<script type="text/javascript">formcreatorAddValueOf(' . $this->fields['id'] . ', "");</script>';
   }

   public function isValid($value)
   {
      return true;
   }

   public static function getName()
   {
      return __('Description');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 0,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['description'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
