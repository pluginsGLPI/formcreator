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

use Glpi\Plugin\Formcreator\EOLInfo;
use PHPUnit\Framework\TestCase;

class FormcreatorEOLTest extends TestCase
{
    public function testEOLClassExists()
    {
        // Test that the EOL info class exists
        $this->assertTrue(class_exists(EOLInfo::class));
    }

    public function testEOLTemplatesExist()
    {
        // Test that EOL templates exist
        $templatesDir = __DIR__ . '/../../templates';
        $this->assertFileExists($templatesDir . '/eol_info.html.twig');
        $this->assertFileExists($templatesDir . '/migration_status.html.twig');
        $this->assertFileExists($templatesDir . '/central_eol_warning.html.twig');
    }

    public function testEOLInfoMethods()
    {
        // Test EOL info class methods
        $eolInfo = new EOLInfo();
        $this->assertTrue(method_exists($eolInfo, 'showForm'));
        $this->assertTrue(method_exists(EOLInfo::class, 'displayCentralEOLWarning'));
        $this->assertTrue(method_exists(EOLInfo::class, 'canView'));
        $this->assertTrue(method_exists(EOLInfo::class, 'getMenuName'));
        $this->assertTrue(method_exists(EOLInfo::class, 'getMenuContent'));
    }
}