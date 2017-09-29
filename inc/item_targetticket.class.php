<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorItem_TargetTicket extends CommonDBRelation
{

   static public $itemtype_1           = 'itemtype';
   static public $items_id_1           = 'items_id';
   static public $itemtype_2           = 'PluginFormcreatorTargetTicket';
   static public $items_id_2           = 'plugin_formcreator_targettickets_id';

   static public $logs_for_item_1        = false;

   /**
    * Export in an array all the data of the current instanciated form
    *
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $item_targetTicket = $this->fields;

      // remove uneeded keys
      unset($item_targetTicket['id'],
            $item_targetTicket['plugin_formcreator_targettickets_id']);

      if ($remove_uuid) {
         $item_targetTicket['uuid'] = '';
      }

      $exported = $item_targetTicket;
      if ($item_targetTicket['itemtype'] == PluginFormcreatorTargetTicket::getType()) {
         $targetTicket = new PluginFormcreatorTargetTicket();
         $targetTicket->getFromDB($item_targetTicket['items_id']);
         $exported['items_id'] = $targetTicket->getField('uuid');
      }

      return $exported;
   }

   public static function import($targetTicket_id, $item_targetTicket = [], $storeOnly = true) {
      static $relationsToImport = [];

      if ($storeOnly) {
         $item_targetTicket['plugin_formcreator_targettickets_id'] = $targetTicket_id;

         $item = new static();
         if ($item_targetTicket_id = plugin_formcreator_getFromDBByField($item, 'uuid', $item_targetTicket['uuid'])) {
            // add id key
            $item_targetTicket['id'] = $item_targetTicket_id;

            // prepare update item_targetticket
            $relationsToImport[] = $item_targetTicket;
         } else {
            // prepare create item_targetticket
            $relationsToImport[] = $item_targetTicket;
         }
      } else {
         // Assumes all target tickets needed for the stored conditions exist
         foreach ($relationsToImport as $item_targetTicket) {
            $item = new static();
            $linkedItem = new $item_targetTicket['itemtype']();
            if ($item_targetTicket['itemtype'] == PluginFormcreatorTargetTicket::getType()) {
               $item_targetTicket['items_id'] = plugin_formcreator_getFromDBByField($linkedItem, 'uuid', $item_targetTicket['items_id']);
            }
            if (isset($item_targetTicket['id'])) {
               $item->update($item_targetTicket);
            } else {
               $item->add($item_targetTicket);
            }
         }
         $relationsToImport = [];
      }
   }

   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }
}
