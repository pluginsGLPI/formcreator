<?php
abstract class PluginFormcreatorField
{
   const IS_MULTIPLE = false;

   private $fields = array();

   public function __construct($question_fields)
   {
      $this->fields = $question_fields;
   }

   // public function show()
   // {

   // }

   public function displayField()
   {

   }

   public function getLabel()
   {
      return $this->fields['name'];
   }

   public function getField()
   {

   }

   public function getDefaultValues()
   {
      return $this->fields['name'];
   }

   public function getSelectedValues()
   {
      if (isset($this->fields['answer'])) {
         return $this->fields['answer'];
      } else {
         return '';
      }
   }

   public function getAnswer()
   {
      return $this->getSelectedValues();
   }

   public function getAvailableValues()
   {

   }

   public function isValid($value)
   {
      // If the field is not visible, don't check it's value
      if (!$this->isVisible()) return true;

      // If the field is required it can't be empty
      if ($this->isRequired() && empty($value)) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public function isRequired()
   {
      return ($this->isVisible() && $this->fields['required']);
   }

   /**
    * Check if a field should be shown or not
    *
    * @param   Integer     $id         ID of the current question
    * @param   Array       $values     Array of current fields values (id => value)
    * @return  boolean                 Should be shown or not
    */
   public function isVisible() {
      $conditions = array();

      // If the field is always shown
      if ($this->fields['show_rule'] == 'always') return true;

      // Get conditions to show or hide field
      $query = "SELECT `show_logic`, `show_field`, `show_condition`, `show_value`
                FROM glpi_plugin_formcreator_questions_conditions
                WHERE `plugin_formcreator_questions_id` = {$this->fields['id']}";
      $result = $GLOBALS['DB']->query($query);
      while ($line = $GLOBALS['DB']->fetch_array($result)) {
         $conditions[] = array(
               'multiple' => in_array($this->fields['fieldtype'], array('checkboxes', 'multiselect')),
               'logic'    => $line['show_logic'],
               'field'    => $line['show_field'],
               'operator' => $line['show_condition'],
               'value'    => $line['show_value']
            );
      }

      foreach ($conditions as $id => $condition) {
         if (!isset($values[$condition['field']])) return false;
         if (!isVisible($condition['field'], $values)) return false;

         if ($condition['multiple']) {
            switch ($condition['operator']) {
               case '!=' :
                  $value = is_array($values[$condition['field']])
                           ? !in_array($condition['value'], $values[$condition['field']])
                           : !in_array($condition['value'], json_decode($values[$condition['field']]));
                  break;
               case '==' :
                  $value = is_array($values[$condition['field']])
                           ? in_array($condition['value'], $values[$condition['field']])
                           : in_array($condition['value'], json_decode($values[$condition['field']]));
                   break;
               default:
                  eval('$value = "' . $condition['value'] . '" ' . $condition['operator'] . ' Array(' . $values[$condition['field']] . ');');
            }
         } else {
            eval('$value = "' . addslashes($values[$condition['field']]) . '" ' . $condition['operator'] . ' "' . addslashes($condition['value']) . '";');
         }
         switch ($condition['logic']) {
            case 'AND' :   $return &= $value; break;
            case 'OR'  :   $return |= $value; break;
            case 'XOR' :   $return ^= $value; break;
            default :      $return = $value;
         }
      }

      // If the field is hidden by default, show it if condition is true
      if ($question->fields['show_rule'] == 'hidden') {
         return $return;

      // else show it if condition is false
      } else {
         return !$return;
      }
   }

}
