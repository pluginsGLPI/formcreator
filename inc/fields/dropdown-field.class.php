<?php
class dropdownField extends PluginFormcreatorField
{
   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         $rand     = mt_rand();
         $required = $this->fields['required'] ? ' required' : '';

         echo '<div class="form_field">';
         if(!empty($this->fields['values'])) {
            if ($this->fields['values'] == 'User') {
               User::dropdown(array(
                  'name'                => 'formcreator_field_' . $this->fields['id'],
                  'value'               => $this->getValue(),
                  'comments'            => false,
                  'right'               => 'all',
                  'display_emptychoice' => $this->fields['show_empty'],
                  'rand'                => $rand,
               ));
            } else {
               Dropdown::show($this->fields['values'], array(
                  'name'                => 'formcreator_field_' . $this->fields['id'],
                  'value'               => $this->getValue(),
                  'comments'            => false,
                  'display_emptychoice' => $this->fields['show_empty'],
                  'rand'                => $rand,
               ));
            }
         }
         echo '</div>' . PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("select2-selecting", function(e) {
                        formcreatorChangeValueOf (' . $this->fields['id']. ', e.val);
                     });
                  });
               </script>';
      } else {
         echo $this->getAnswer();
      }
   }

   public function getAnswer()
   {
      if ($this->fields['values'] == 'User') {
          return getUserName($this->getValue());
       } else {
          return Dropdown::getDropdownName(getTableForItemType($this->fields['values']), $this->getValue());
       }
   }

   public static function getName()
   {
      return _n('Dropdown', 'Dropdowns', 1);
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 1,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['dropdown'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
