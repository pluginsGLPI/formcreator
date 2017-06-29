<?php
class PluginFormcreatorCommon {

   static function getEnumValues($table, $field) {
      global $DB;

      $enum = array();
      if ($res = $DB->query( "SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'" )) {
         $data = $DB->fetch_array($res);
         $type = $data['Type'];
         $matches = null;
         preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
         $enum = explode("','", $matches[1]);
      }

      return $enum;
   }

   static function isNotificationEnabled() {
      global $CFG_GLPI;

      if (version_compare(GLPI_VERSION, '9.2', 'ge')) {
         $notification = $CFG_GLPI['use_notifications'];
      } else {
         $notification = $CFG_GLPI['use_mailing'];
      }

      return ($notification == '1');
   }

   static function setNotification($enable) {
      global $CFG_GLPI;

      if (version_compare(GLPI_VERSION, '9.2', 'ge')) {
         $CFG_GLPI['use_notifications'] = $enable ? '1' : '0';
      } else {
         $CFG_GLPI['use_mailing'] = $enable ? '1' : '0';
      }
   }

   static function getGlpiVersion() {
      return defined('GLPI_PREVER')
             ? GLPI_PREVER
             : GLPI_VERSION;
   }
}
