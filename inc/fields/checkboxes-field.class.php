<?php
class checkboxesField extends PluginFormcreatorField
{
   const IS_MULTIPLE    = true;
   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         echo '<input type="hidden" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '" value="" />' . PHP_EOL;

         if(!empty($this->getAvailableValues())) {
            echo '<div class="checkbox">';
            $i = 0;
            foreach ($this->getAvailableValues() as $value) {
               if ((trim($value) != '')) {
                  $i++;
                  $checked = (!empty($this->getValue()) && in_array($value, $this->getValue())) ? ' checked' : '';
                  echo '<input type="checkbox" class="form-control"
                        name="formcreator_field_' . $this->fields['id'] . '[]"
                        id="formcreator_field_' . $this->fields['id'] . '_' . $i . '"
                        value="' . addslashes($value) . '"' . $checked . ' /> ';
                  echo '<label for="formcreator_field_' . $this->fields['id'] . '_' . $i . '">';
                  echo $value;
                  echo '</label>';
                  if($i != count($this->getAvailableValues())) echo '<br />';
               }
            }
            echo '</div>';
         }
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("input[name=\'formcreator_field_' . $this->fields['id']. '[]\']").on("change", function() {
                        var tab_values = new Array();
                        jQuery("input[name=\'formcreator_field_' . $this->fields['id']. '[]\']").each(function() {
                           if (this.checked == true) {
                              tab_values.push(this.value);
                           }
                        });
                        formcreatorChangeValueOf (' . $this->fields['id']. ', tab_values);
                     });
                  });
               </script>';

      } else {
         if (!empty($this->getAnswer())) {
            if (is_array($this->getAnswer())) {
               echo implode(', ', $this->getAnswer());
            } elseif (is_array(json_decode($this->getAnswer()))) {
               echo implode(', ', json_decode($this->getAnswer()));
            } else {
               echo $this->getAnswer();
            }
         } else {
            echo '';
         }
      }
   }

	public function isValid($value)
   {
      $value = json_decode($value);
      if (is_null($value)) $value = array();

      // If the field is required it can't be empty
      if ($this->isRequired() && empty($value)) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;

      // Min range not set or number of selected item lower than min
      } elseif (!empty($this->fields['range_min']) && (count($value) < $this->fields['range_min'])) {
         $message = sprintf(__('The following question needs of at least %d answers', 'formcreator'), $this->fields['range_min']);
         Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
         return false;

      // Max range not set or number of selected item greater than max
      } elseif (!empty($this->fields['range_max']) && (count($value) > $this->fields['range_max'])) {
         $message = sprintf(__('The following question does not accept more than %d answers', 'formcreator'), $this->fields['range_max']);
         Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
         return false;

      // All is OK
      } else {
         return true;
      }
	}

   public static function getName()
   {
      return __('Checkboxes', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 1,
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
      return "tab_fields_fields['checkboxes'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
