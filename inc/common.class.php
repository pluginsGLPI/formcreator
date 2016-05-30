<?php
class PluginFormcreatorCommon {

   static function getEnumValues($table, $field) {
      global $DB;

      $enum = array();
      if ($res = $DB->query( "SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'" )) {
         $data = $DB->fetch_array($res);
         $type = $data[0];
         preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
         $enum = explode("','", $matches[1]);
      }

      return $enum;
   }