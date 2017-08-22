<?php

class PluginFormcreatorUrgencyField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         echo '<div class="form_field">';
         $rand     = mt_rand();
         $required = $this->fields['required'] ? ' required' : '';
         Ticket::dropdownUrgency(array('name'     => 'formcreator_field_' . $this->fields['id'],
                  'value'    => $this->getValue(),
                  'comments' => false,
                  'rand'     => $rand)
         );
         echo '</div>' . PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("select2-selecting", function(e) {
                        formcreatorChangeValueOf (' . $this->fields['id']. ', e.val);
                     });
                  });
               </script>';
      } else {
         echo Ticket::getPriorityName($this->getValue());
      }
   }

   public function getAnswer() {
      $values = $this->getAvailableValues();
      $value  = $this->getValue();
      return in_array($value, $values) ? $value : $this->fields['default_values'];
   }

   public static function getName() {
      return __('Urgency');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['values'])) {
         $input['values'] = addslashes($input['values']);
      }
      return $input;
   }

   public static function getPrefs() {
      return array(
            'required'       => 1,
            'default_values' => 1,
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

   public function getAvailableValues() {
      return array(
            _x('urgency', 'Very high'),
            _x('urgency', 'High'),
            _x('urgency', 'Medium'),
            _x('urgency', 'Low'),
            _x('urgency', 'Very low'),
      );
   }

   public function getValue() {
      if (isset($this->fields['answer'])) {
         if (!is_array($this->fields['answer']) && is_array(json_decode($this->fields['answer']))) {
            return json_decode($this->fields['answer']);
         }
         return $this->fields['answer'];
      } else {
         return 3;
      }
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['urgency'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

}
