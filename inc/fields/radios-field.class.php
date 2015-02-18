<?php
class radiosField extends PluginFormcreatorField
{
   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         echo '<input type="hidden" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '" value="" />' . PHP_EOL;

         $values = $this->getAvailableValues();
         if(!empty($values)) {
            echo '<div class="checkbox">';
            $i = 0;
            foreach ($values as $value) {
               if ((trim($value) != '')) {
                  $i++;
                  $checked = ($this->getValue() == $value) ? ' checked' : '';
                  echo '<input type="radio" class="form-control"
                        name="formcreator_field_' . $this->fields['id'] . '"
                        id="formcreator_field_' . $this->fields['id'] . '_' . $i . '"
                        value="' . addslashes($value) . '"' . $checked . ' /> ';
                  echo '<label for="formcreator_field_' . $this->fields['id'] . '_' . $i . '">';
                  echo $value;
                  echo '</label>';
                  if($i != count($values)) echo '<br />';
               }
            }
            echo '</div>';
         }
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("input[name=\'formcreator_field_' . $this->fields['id'] . '\']").on("change", function() {
                        jQuery("input[name=\'formcreator_field_' . $this->fields['id'] . '\']").each(function() {
                           if (this.checked == true) {
                              formcreatorChangeValueOf (' . $this->fields['id'] . ', this.value);
                           }
                        });
                     });
                  });
               </script>';

      } else {
         echo $this->getAnswer();
      }
   }

   public static function getName()
   {
      return __('Radios', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
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
      return "tab_fields_fields['radios'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
