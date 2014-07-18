<?php

interface Field
{
	public static function isValid($field, $input);
   public static function show($field);
   public static function getName();
   public static function getJSFields();
}
