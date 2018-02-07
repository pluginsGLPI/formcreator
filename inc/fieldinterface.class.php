<?php
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

interface PluginFormcreatorFieldInterface
{
   /**
    * gets the localized name of the field
    * @return string
    */
   public static function getName();

   public static function getPrefs();
   public static function getJSFields();

   /**
    * Transform input to properly save it in the database
    *
    * @param array $input data to transform before save
    *
    * @return array|false input data to save or false if data is rejected
    */
   public function prepareQuestionInputForSave($input);

   /**
    * Gets the parameters of the field
    * @return PluginFormcreatorQuestionParameter[]
    */
   public function getUsedParameters();

   /**
    * Gets the name of the field type
    *
    * @return string
    */
   public function getFieldTypeName();

   /**
    * Adds parameters of the field into the database
    *
    * @param PluginFormcreatorQuestion $question question of the field
    * @param array $input data of parameters
    */
   public function addParameters(PluginFormcreatorQuestion $question, array $input);

   /**
    * Updates parameters of the field into the database
    *
    * @param PluginFormcreatorQuestion $question question of the field
    * @param array $input data of parameters
    */
   public function updateParameters(PluginFormcreatorQuestion $question, array $input);

   /**
    * Deletes all parameters of the field applied to the question
    *
    * @param PluginFormcreatorQuestion $question
    *
    * @return boolean true if success, false otherwise
    */
   public function deleteParameters(PluginFormcreatorQuestion $question);
}
