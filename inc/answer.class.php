<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorAnswer extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorForm_Answer";
   static public $items_id = "plugin_formcreator_forms_answers_id";

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate() {
      return true;
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView() {
      return true;
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Answer', 'Answers', $nb, 'formcreator');
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
   **/
   public function prepareInputForAdd($input) {
      global $DB;

      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         if (is_array($value)) {
            foreach ($value as $key2 => $value2) {
               $input[$key][$key2] = plugin_formcreator_encode($value2, false);
            }
         } else if (is_array(json_decode($value))) {
            //$value = json_decode($value);
            //foreach ($value as $key2 => $value2) {
               //$value[$key2] = plugin_formcreator_encode($value2, false);
            //}
            //$input[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            $input[$key] = $DB->escape($value);
         } else {
            $input[$key] = $value;
         }
      }

      return $input;
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
   **/
   public function prepareInputForUpdate($input) {
      return $this->prepareInputForAdd($input);
   }
}
