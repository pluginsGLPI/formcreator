<?php
class PluginFormcreatorMultiSelectField extends PluginFormcreatorSelectField
{
   const IS_MULTIPLE    = true;

   public function isValid($value) {
      $value = json_decode($value);
      if (is_null($value)) {
         $value = [];
      }

      // If the field is required it can't be empty
      if ($this->isRequired() && empty($value)) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;

      }
      if (!$this->isValidValue($value)) {
         return false;
      }

      return true;
   }

   private function isValidValue($value) {
      $parameters = $this->getUsedParameters();
      foreach ($parameters as $fieldname => $parameter) {
         $parameter->getFromDBByCrit([
            'plugin_formcreator_questions_id'   => $this->fields['id'],
            'fieldname'                         => $fieldname,
         ]);
      }

      // Check the field matches the format regex
      $rangeMin = $parameters['range']->getField('range_min');
      $rangeMax = $parameters['range']->getField('range_max');
      if (strlen($rangeMin) > 0 && count($value) < $rangeMin) {
         $message = sprintf(__('The following question needs of at least %d answers', 'formcreator'), $rangeMin);
         Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      if (strlen($rangeMax) > 0 && count($value) > $rangeMax) {
         $message = sprintf(__('The following question does not accept more than %d answers', 'formcreator'), $rangeMax);
         Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      return true;
   }

   public function displayField($canEdit = true) {
      if ($canEdit) {
         parent::displayField($canEdit);
      } else {
         $answer = $this->getAnswer();
         echo '<div class="form_field">';
         echo empty($answer) ? '' : implode('<br />', $answer);
         echo '</div>';
      }
   }

   public function getAnswer() {
      $return = [];
      $values = $this->getAvailableValues();
      $value  = $this->getValue();

      if (empty($value)) {
         return '';
      }

      if (is_array($value)) {
         $tab_values = $value;
      } else if (is_array(json_decode($value))) {
         $tab_values = json_decode($value);
      } else {
         $tab_values = [$value];
      }

      foreach ($tab_values as $value) {
         if (in_array($value, $values)) {
            $return[] = $value;
         }
      }
      return $return;
   }

   public static function getName() {
      return __('Multiselect', 'formcreator');
   }

   public static function getPrefs() {
      return [
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
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['multiselect'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

   public function getUsedParameters() {
      return [
         'range' => new PluginFormcreatorQuestionRange(
            $this,
            [
               'fieldName' => 'range',
               'label'     => __('Range', 'formcreator'),
               'fieldType' => ['text'],
            ]
         ),
      ];
   }
}
