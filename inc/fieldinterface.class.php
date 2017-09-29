<?php
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

interface PluginFormcreatorFieldInterface
{
   public static function getName();
   public static function getPrefs();
   public static function getJSFields();
   public function prepareQuestionInputForSave($input);
}
