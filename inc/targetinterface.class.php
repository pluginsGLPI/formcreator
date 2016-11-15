<?php
interface PluginFormcreatorTargetInterface
{

   public function export();

   public static function import($targetitems_id = 0, $target_data = array());

   public function save(PluginFormcreatorForm_Answer $formanswer);

}