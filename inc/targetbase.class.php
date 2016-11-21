<?php
abstract class PluginFormcreatorTargetBase extends CommonDBTM
{

   abstract public function export();

   abstract public static function import($targetitems_id = 0, $target_data = array());

   abstract public function save(PluginFormcreatorForm_Answer $formanswer);

   /*
    *
    */
   public function getForm() {
      $targetItemId = $this->getID();
      $targetItemtype = static::getType();

      $target = new PluginFormcreatorTarget();
      if (!$target->getFromDBByQuery("WHERE `itemtype` = '$targetItemtype' AND `items_id` = '$targetItemId'")) {
         return null;
      } else {
         $form = new PluginFormcreatorForm();
         if (!$form->getFromDB($target->getField('plugin_formcreator_forms_id'))) {
            return null;
         }
         return $form;
      }

      return null;
   }
}