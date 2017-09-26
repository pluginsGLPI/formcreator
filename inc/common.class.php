<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorCommon {

   static function getEnumValues($table, $field) {
      global $DB;

      $enum = [];
      if ($res = $DB->query( "SHOW COLUMNS FROM `$table` WHERE Field = '$field'" )) {
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
      $notification = $CFG_GLPI['use_notifications'];

      return ($notification == '1');
   }

   static function setNotification($enable) {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = $enable ? '1' : '0';
   }

   static function getGlpiVersion() {
      return defined('GLPI_PREVER')
             ? GLPI_PREVER
             : GLPI_VERSION;
   }

   /**
    * Get Link Name
    *
    * @param integer $value    Current value
    * @param boolean $inverted Whether to invert label
    *
    * @return string
    **/
   static function getLinkName($value, $inverted = false) {
      $tmp = [];

      if (!$inverted) {
         $tmp[Ticket_Ticket::LINK_TO]        = __('Linked to');
         $tmp[Ticket_Ticket::DUPLICATE_WITH] = __('Duplicates');
         $tmp[Ticket_Ticket::SON_OF]         = __('Son of');
         $tmp[Ticket_Ticket::PARENT_OF]      = __('Parent of');
      } else {
         $tmp[Ticket_Ticket::LINK_TO]        = __('Linked to');
         $tmp[Ticket_Ticket::DUPLICATE_WITH] = __('Duplicated by');
         $tmp[Ticket_Ticket::SON_OF]         = __('Parent of');
         $tmp[Ticket_Ticket::PARENT_OF]      = __('Son of');
      }

      if (isset($tmp[$value])) {
         return $tmp[$value];
      }
      return NOT_AVAILABLE;
   }

}
