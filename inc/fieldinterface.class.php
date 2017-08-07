<?php

interface PluginFormcreatorFieldInterface
{
   public static function getName();
   public static function getPrefs();
   public static function getJSFields();
   public function prepareQuestionInputForSave($input);
}
