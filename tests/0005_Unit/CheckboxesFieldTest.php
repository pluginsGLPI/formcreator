<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class CheckboxesFieldTest extends SuperAdminTestCase {

   public function provider() {

      $dataset = array(
            array(
                  'fields'          => array(
                        'fieldtype'       => 'checkboxes',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => '',
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'checkboxes',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => '2',
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array('2'),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'checkboxes',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "3\r\n5",
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array('3', '5'),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'checkboxes',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "3\r\n5",
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ),
                  'data'            => null,
                  'expectedValue'   => array('3', '5'),
                  'expectedIsValid' => false
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'checkboxes',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "3\r\n5\r\n6",
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ),
                  'data'            => null,
                  'expectedValue'   => array('3', '5', '6'),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'checkboxes',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ),
                  'data'            => null,
                  'expectedValue'   => array('1', '2', '3', '5', '6'),
                  'expectedIsValid' => false
            ),
      );

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldAvailableValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorCheckboxesField($fields, $data);

      $availableValues = $fieldInstance->getAvailableValues();
      $expectedAvaliableValues = explode("\r\n", $fields['values']);

      $this->assertCount(count($expectedAvaliableValues), $availableValues);

      foreach ($expectedAvaliableValues as $expectedValue) {
         $this->assertContains($expectedValue, $availableValues);
      }
   }

   /**
    * @dataProvider provider
    */
   public function testFieldValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorCheckboxesField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->assertEquals(count($expectedValue), count($value));
      foreach ($expectedValue as $expectedSubValue) {
         $this->assertTrue(in_array($expectedSubValue, $value));
      }
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorCheckboxesField($fields, $data);

      $values = json_encode(explode("\r\n", $fields['default_values']), JSON_OBJECT_AS_ARRAY);
      $isValid = $fieldInstance->isValid($values);
      $this->assertEquals($expectedValidity, $isValid);
   }

   public function testPrepareInputForSave() {
      $fields = array(
         'fieldtype'       => 'checkboxes',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
         'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
         'order'           => '1',
         'show_rule'       => 'always',
         'range_min'       => 3,
         'range_max'       => 4,
      );
      $fieldInstance = new PluginFormcreatorCheckboxesField($fields);

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