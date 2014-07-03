<?php

class PluginFormcreatorConfig extends CommonGLPI
{

   static function getTypeName($nb=1) {
      return __('Formcreator settings', 'formcreator');
   }

   /**
    * Check the rights to save changes in eachange settings
    *
    * @access public
    * @static
    * @return boolean
    */
   static function canCreate() {
      return Session::haveRight('config', 'w');
   }

   /**
    * Check the rights to view the eachange settings
    *
    * @access public
    * @static
    * @return boolean
    */
   static function canView() {
      return Session::haveRight('config', 'r');
   }

   function defineTabs($options=array())
   {
      $ong = array();
      $this->addStandardTab('PluginFormcreatorForm', $ong, $options);
      $this->addStandardTab('PluginFormcreatorHeader', $ong, $options);
      $this->addStandardTab('PluginFormcreatorCategory', $ong, $options);
      return $ong;
   }
}
