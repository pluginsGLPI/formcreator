<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - End of Life Functionality Test
 * ---------------------------------------------------------------------
 * Tests for EOL-specific functionality like info display.
 * ---------------------------------------------------------------------
 */

use Glpi\Plugin\Formcreator\EOLInfo;
use PHPUnit\Framework\TestCase;

/**
 * Test EOL-specific functionality
 */
class EOLTest extends TestCase
{
    /**
     * Test EOL info display methods
     */
    public function testEOLInfoDisplay()
    {
        // Test that the EOL info class can show forms
        $eolInfo = new EOLInfo();
        
        // Should be able to call showForm without errors
        ob_start();
        $eolInfo->showForm();
        $output = ob_get_clean();
        
        // Should produce some output
        $this->assertNotEmpty($output);
    }

    /**
     * Test EOL menu integration
     */
    public function testEOLMenuIntegration()
    {
        // Test that menu content is properly defined
        $menuContent = EOLInfo::getMenuContent();
        
        $this->assertIsArray($menuContent);
        $this->assertArrayHasKey('title', $menuContent);
        $this->assertArrayHasKey('page', $menuContent);
        $this->assertArrayHasKey('icon', $menuContent);
    }

    /**
     * Test that EOL pages are accessible
     */
    public function testEOLPagesAccessible()
    {
        // Test that EOL info page exists
        $eolInfoPage = dirname(__DIR__) . '/front/eol_info.php';
        $this->assertFileExists($eolInfoPage);
        
        // Test that migration status page exists  
        $migrationPage = dirname(__DIR__) . '/front/migration_status.php';
        $this->assertFileExists($migrationPage);
    }

    /**
     * Test that templates exist
     */
    public function testEOLTemplatesExist()
    {
        $templatesDir = dirname(__DIR__) . '/templates';
        
        // Test template directory exists
        $this->assertDirectoryExists($templatesDir);
        
        // Test specific templates exist
        $this->assertFileExists($templatesDir . '/eol_info.html.twig');
        $this->assertFileExists($templatesDir . '/migration_status.html.twig');
        $this->assertFileExists($templatesDir . '/central_eol_warning.html.twig');
    }

    /**
     * Test plugin constants are defined
     */
    public function testPluginConstants()
    {
        $this->assertTrue(defined('PLUGIN_FORMCREATOR_VERSION'));
        $this->assertTrue(defined('PLUGIN_FORMCREATOR_SCHEMA_VERSION'));
        $this->assertEquals('3.0.0', PLUGIN_FORMCREATOR_VERSION);
        $this->assertEquals('3.0.0', PLUGIN_FORMCREATOR_SCHEMA_VERSION);
    }
}