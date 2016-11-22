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
   public static function import($questions_id = 0, $condition = array()) {
      $item = new self;

      $condition['plugin_formcreator_questions_id'] = $questions_id;

      if ($conditions_id = plugin_formcreator_getFromDBByField($item, 'uuid', $condition['uuid'])) {
         // add id key
         $condition['id'] = $conditions_id;

         // update condition
         $item->update($condition);
      } else {
         //create condition
         $conditions_id = $item->add($condition);
      }

      return $conditions_id;
   }

   /**
    * Export in an array all the data of the current instanciated condition
    * @return array the array with all data (with sub tables)
    */
   public function export() {
      if (!$this->getID()) {
         return false;
      }

      $condition = $this->fields;

      unset($condition['id'],
            $condition['plugin_formcreator_questions_id']);

      return $condition;
   }

}