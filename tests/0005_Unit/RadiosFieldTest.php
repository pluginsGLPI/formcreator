<?php
class RadiossFieldTest extends SuperAdminTestCase {
   public function testPrepareInputForSave() {
      $fields = array(
         'fieldtype'       => 'radios',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
         'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
         'order'           => '1',
         'show_rule'       => 'always',
         'range_min'       => 3,
         'range_max'       => 4,
      );
      $fieldInstance = new PluginFormcreatorRadiosField($fields);

      // Test a value is mandatory
      $input = [
         'values'          => "",
         'name'            => 'foo',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->assertEquals(0, count($out));

      // Test accented chars are kept
      $input = [
         'values'          => "éè\r\nsomething else",
         'default_values'  => "éè",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->assertEquals("éè\r\nsomething else", $out['values']);
      $this->assertEquals("éè", $out['default_values']);

      // Test values are trimmed
      $input = [
         'values'          => ' something \r\n  something else  ',
         'default_values'  => " something      ",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->assertEquals('something\r\nsomething else', $out['values']);
      $this->assertEquals("something", $out['default_values']);
   }
}