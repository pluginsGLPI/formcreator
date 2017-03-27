<?php
class ActorFieldTest extends SuperAdminTestCase {

   public function provider() {

      $user = new User();
      $user->getFromDBbyName('glpi');
      $userId = $user->getID();
      $dataset = array(
            array(
                  'fields'          => array(
                        'fieldtype'       => 'actor',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => '',
                        'values'          => '',
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'actor',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => '',
                        'values'          => 'glpi',
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'actor',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => 'nonexistent',
                        'values'          => '',
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => false
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'actor',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => 'email@incomplete',
                        'values'          => '',
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => false
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'actor',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => 'email@something.com',
                        'values'          => '',
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'actor',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => $userId . ',email@something.com',
                        'values'          => '',
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'actor',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => $userId . ',email@something.com,nonexistent',
                        'values'          => '',
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => false
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'actor',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => $userId . ',email@something.com,email@incomplete',
                        'values'          => '',
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => false
            ),
      );

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorActorField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->assertEquals(count($expectedValue), count($value));
      foreach ($expectedValue as $expectedSubValue) {
         if (!empty($expectedSubValue)) {
            $this->assertTrue(in_array($expectedSubValue, $value));
         }
      }
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorActorField($fields, $data);

      $values = $fields['default_values'];
      $isValid = $fieldInstance->isValid($values);
      $this->assertEquals($expectedValidity, $isValid);
   }
}
