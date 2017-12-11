<?php
class PluginFormcreatorCommonTest extends SuperAdminTestCase {
   public function testGetFormcreatorRequestTypeId() {
      $requestTypeId = PluginFormcreatorCommon::getFormcreatorRequestTypeId();

      // The ID must be > 0 (aka found)
      $this->assertGreaterThan(0, $requestTypeId);
   }
}