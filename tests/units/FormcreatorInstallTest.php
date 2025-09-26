<?php

/**
 * -------------------------------------------------------------------------
 * Formcreator plugin for GLPI
 * -------------------------------------------------------------------------
 *
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
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2011-2023 by the FusionInventory Development Team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/formcreator
 * @link      https://pluginsglpi.github.io/formcreator/
 * -------------------------------------------------------------------------
 */

use PHPUnit\Framework\TestCase;

class FormcreatorInstallTest extends TestCase
{
    public function testPluginCanLoad()
    {
        // Test that the plugin setup file can be loaded
        $setupFile = __DIR__ . '/../../setup.php';
        $this->assertFileExists($setupFile);
        
        // Include setup and test basic functionality
        include_once $setupFile;
        $this->assertTrue(function_exists('plugin_version_formcreator'));
    }

    public function testPluginVersion()
    {
        // Test that the plugin version is defined correctly
        include_once __DIR__ . '/../../setup.php';
        $version = plugin_version_formcreator();
        $this->assertEquals('3.0.0', $version['version']);
    }

    public function testEOLStatus()
    {
        // Test EOL status is properly set
        include_once __DIR__ . '/../../setup.php';
        $version = plugin_version_formcreator();
        $this->assertArrayHasKey('state', $version);
        $this->assertEquals('stable', $version['state']);
    }
}