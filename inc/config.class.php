<?php
class PluginFormcreatorConfig extends CommonGLPI
{
   /**
    * Return the name for the Itemtype
    *
    * @param  integer $nb    Number of item(s) (for single or plural)
    * @return String         Name of the item to be displayed
    */
   public static function getTypeName($nb=1) {
      return __('Formcreator settings', 'formcreator');
   }

   /**
    * Check the rights to save changes in eachange settings
    *
    * @access public
    * @static
    * @return boolean
    */
   public static function canCreate() {
      return Session::haveRight('config', 'w');
   }

   /**
    * Check the rights to view the eachange settings
    *
    * @access public
    * @static
    * @return boolean
    */
   public static function canView() {
      return Session::haveRight('config', 'r');
   }

   public function defineTabs($options=array())
   {
      $ong = array();
      $this->addStandardTab('PluginFormcreatorForm', $ong, $options);
      $this->addStandardTab('PluginFormcreatorHeader', $ong, $options);
      $this->addStandardTab('PluginFormcreatorCategory', $ong, $options);
      return $ong;
   }
}
