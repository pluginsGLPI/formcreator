<?php
class PluginFormcreatorQuestion_Condition extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorQuestion";
   static public $items_id = "plugin_formcreator_questions_id";

   /**
    * Import a question's condition into the db
    * @see PluginFormcreatorQuestion::import
    *
    * @param  integer $questions_id  id of the parent question
    * @param  array   $condition the condition data (match the condition table)
    * @return integer the condition's id
    */
   public static function import($questions_id = 0, $condition = array(), $storeOnly = true) {
      static $conditionsToImport = array();

      if ($storeOnly) {
         $condition['plugin_formcreator_questions_id'] = $questions_id;

         $item = new static();
         if ($conditions_id = plugin_formcreator_getFromDBByField($item, 'uuid', $condition['uuid'])) {
            // add id key
            $condition['id'] = $conditions_id;

            // prepare update condition
            $conditionsToImport[] = $condition;
         } else {
            // prepare create condition
            $conditionsToImport[] = $condition;
         }
      } else {
         // Assumes all questions needed for the stored conditions exist
         foreach ($conditionsToImport as $condition) {
            $item = new static();
            $question = new PluginFormcreatorQuestion();
            $condition['show_field'] = plugin_formcreator_getFromDBByField($question, 'uuid', $condition['show_field']);
            if (isset($condition['id'])) {
               $item->update($condition);
            } else {
               $item->add($condition);
            }
         }
         $conditionsToImport = array();
      }
   }

   /**
    * Export in an array all the data of the current instanciated condition
    * @return array the array with all data (with sub tables)
    */
   public function export() {
      if (!$this->getID()) {
         return false;
      }

      $question = new PluginFormcreatorQuestion();
      $question->getFromDB($this->fields['show_field']);
      $condition = $this->fields;
      $condition['show_field'] = $question->getField('uuid');

      unset($condition['id'],
            $condition['plugin_formcreator_questions_id']);

      return $condition;
   }

}