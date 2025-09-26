<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - End of Life Plugin Test
 * ---------------------------------------------------------------------
 * Basic plugin functionality test.
 * ---------------------------------------------------------------------
 */

use Glpi\Plugin\Formcreator\Install;
use PHPUnit\Framework\TestCase;

/**
 * Test basic plugin functionality
 */
class PluginTest extends TestCase
{
    /**
     * Test plugin basic info
     */
    public function testPluginInfo()
    {
        // Test plugin name
        $this->assertEquals('Form Creator', plugin_formcreator_getPluginName());
        
        // Test plugin version
        $this->assertEquals('3.0.0', plugin_formcreator_getVersion());
    }

    /**
     * Test plugin setup functions exist
     */
    public function testPluginSetupFunctions()
    {
        $this->assertTrue(function_exists('plugin_init_formcreator'));
        $this->assertTrue(function_exists('plugin_version_formcreator'));
        $this->assertTrue(function_exists('plugin_formcreator_check_prerequisites'));
        $this->assertTrue(function_exists('plugin_formcreator_check_config'));
    }

    /**
     * Test that install class exists
     */
    public function testInstallClassExists()
    {
        $this->assertTrue(class_exists(Install::class));
        
        $install = new Install();
        $this->assertInstanceOf(Install::class, $install);
    }
}