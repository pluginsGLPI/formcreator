<?php

interface Field
{
	public static function isValid($field, $input);
   public static function show($field, $datas);
   public static function getName();
   public static function getPrefs();
   public static function getJSFields();
   public static function displayValue($value, $values);
}
