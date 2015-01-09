<?php
include ('../../../inc/includes.php');

$currentValues  = json_decode(stripslashes($_POST['values']), true);
$questionToShow = array();

foreach ($currentValues as $id => $value) {
   $questionToShow[$id] = isVisible($id, $currentValues);
}
echo json_encode($questionToShow);

/**
 * Check if a field should be shown or not
 *
 * @param   Integer     $id         ID of the current question
 * @param   Array       $values     Array of current fields values (id => value)
 * @return  boolean                 Should be shown or not
 */
function isVisible($id, $values) {
   $conditions = array();

   $question = new PluginFormcreatorQuestion();
   $question->getFromDB($id);

   // If the field is always shown
   if ($question->fields['show_rule'] == 'always') return true;

   // Get conditions to show or hide field
   $query = "SELECT `show_logic`, `show_field`, `show_condition`, `show_value`
             FROM glpi_plugin_formcreator_questions_conditions
             WHERE `plugin_formcreator_questions_id` = $id";
   $result = $GLOBALS['DB']->query($query);
   while ($line = $GLOBALS['DB']->fetch_array($result)) {
      $conditions[] = array(
            'multiple' => in_array($question->fields['fieldtype'], array('checkboxes', 'multiselect')),
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
