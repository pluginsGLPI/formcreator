<?php
class selectField extends PluginFormcreatorField
{
   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         $rand       = mt_rand();
         $tab_values = array();
         $required   = $this->fields['required'] ? ' required' : '';
         $values     = $this->getAvailableValues();

         echo '<div class="form_field">';
         if(!empty($this->fields['values'])) {
            foreach ($values as $value) {
               if ((trim($value) != '')) $tab_values[$value] = $value;
            }

            if($this->fields['show_empty']) $tab_values = array('' => '-----') + $tab_values;
            Dropdown::showFromArray('formcreator_field_' . $this->fields['id'], $tab_values, array(
               'value'     => static::IS_MULTIPLE ? '' : $this->getValue(),
               'values'    => static::IS_MULTIPLE ? $this->getValue() : array(),
               'rand'      => $rand,
               'multiple'  => static::IS_MULTIPLE,
            ));
         }
         echo '</div>' . PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("change", function(e) {
                        var selectedValues = jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").val();
                        formcreatorChangeValueOf (' . $this->fields['id']. ', selectedValues);
                     });
                  });
               </script>';
      } else {
         echo '<div class="form_field">';
         echo nl2br($this->getAnswer());
         echo '</div>' . PHP_EOL;
      }
   }

   public function getAnswer()
   {
      $values = $this->getAvailableValues();
      $value  = $this->getValue();
      return isset($values[$value]) ? $values[$value] : $value;
   }

   public static function getName()
   {
      return __('Select', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 0,
         'show_empty'     => 1,
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
      return "tab_fields_fields['select'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
