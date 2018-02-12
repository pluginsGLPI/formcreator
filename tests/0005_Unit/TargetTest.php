<?php
class TargetTest extends SuperAdminTestCase {

   public function addUpdateFormProvider() {
      return [
         [
            'input' => [
               'name' => '',
               'itemtype' => PluginFormcreatorTargetTicket::class
            ],
            'expected' => false,
         ],
         [
            'input' => [
               'name' => 'should fail',
               'itemtype' => ''
            ],
            'expected' => false,
         ],
         [
            'input' => [
               'name' => 'should pass',
               'itemtype' => PluginFormcreatorTargetTicket::class
            ],
            'expected' => true,
         ],
         [
            'input' => [
               'name' => 'être ou ne pas être',
               'itemtype' => PluginFormcreatorTargetTicket::class
            ],
            'expected' => true,
         ],
         [
            'input' => [
               'name' => 'test d\\\'apostrophe',
               'itemtype' => PluginFormcreatorTargetTicket::class
            ],
            'expected' => true,
         ],
      ];
   }

   /**
    * @dataProvider addUpdateFormProvider
    * @param array $input
    * @param boolean $expected
    */
   public function testPrepareInputForAdd($input, $expected) {
      $target = new PluginFormcreatorTarget();
      $output = $target->prepareInputForAdd($input);
      if ($expected === false) {
         $this->assertCount(0, $output);
      } else {
         $this->assertEquals($input['name'], $output['name']);
         $this->assertArrayHasKey('uuid', $output);
      }
   }
}
