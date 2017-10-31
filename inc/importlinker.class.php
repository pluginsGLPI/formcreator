<?php
class PluginFormcreatorImportLinker
{
   private $imported = [];

   private $postponed = [];

   /**
    *
    * @param string $uuid
    * @param CommonDBTM $object
    */
   public function addImportedObject($uuid, CommonDBTM $object) {
      if (!isset($this->imported[$object->getType()])) {
         $this->imported[$object->getType()] = [];
      }
      $this->imported[$object->getType()][$uuid] = $object;
   }

   /**
    *
    * @param string $uuid
    * @param string $itemtype
    * @param array $object
    */
   public function postponeImport($uuid, $itemtype, array $object, $relationId) {
      if (!isset($this->postponed[$itemtype])) {
         $this->postponed[$itemtype] = [];
      }
      $this->postponed[$itemtype][$uuid] = ['object' => $object, 'relationId' => $relationId];
   }

   public function importPostponed() {
      do {
         $postponedCount = 0;
         $postponedAgainCount = 0;
         foreach ($this->postponed as $itemtype => $postponedItemtypeList) {
            $postponedCount += count($postponedItemtypeList);
            $newList = $postponedItemtypeList;
            foreach ($postponedItemtypeList as $uuid => $postponedItem) {
               if ($itemtype::import($this, $postponedItem['relationId'], $postponedItem['object']) === false) {
                  $newList[$uuid] = $postponedItem;
                  $postponedAgainCount++;
               }
            }
         }

         // If no item was successfully imported,  then the import is in a deadlock and fails
         if ($postponedAgainCount > 0 && $postponedCount == $postponedAgainCount) {
            return false;
         }
      } while ($postponedCount > 0);
   }
}