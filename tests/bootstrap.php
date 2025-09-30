<?php

/**
 *
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
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @copyright Copyright (C) 2011-2023 by the FusionInventory Development Team.
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * @link      https://github.com/pluginsGLPI/formcreator
 * ---------------------------------------------------------------------
 */

use Glpi\Application\Environment;
use Glpi\Kernel\Kernel;

/** @var array $CFG_GLPI */
/** @var array $PLUGIN_HOOKS */
global $CFG_GLPI, $PLUGIN_HOOKS;

define('TU_USER', 'glpi');
define('TU_PASS', 'glpi');

// Fix path to vendor/autoload.php
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

// Fix paths to GLPI test classes
include_once dirname(__DIR__, 3) . '/phpunit/GLPITestCase.php';
include_once dirname(__DIR__, 3) . '/phpunit/DbTestCase.php';

$kernel = new Kernel(Environment::TESTING->value);
$kernel->boot();

// Load plugin classes
$plugin_root = dirname(__DIR__);
$plugin_name = basename($plugin_root);

// Plugin is expected in inc/ directory
$inc_dir = $plugin_root . DIRECTORY_SEPARATOR . 'inc';
if (is_dir($inc_dir)) {
   foreach (glob($inc_dir . '/*.class.php') as $class_file) {
       require_once $class_file;
   }
}

// Plugin hook file
$hook_file = $plugin_root . DIRECTORY_SEPARATOR . 'hook.php';
if (file_exists($hook_file)) {
    require_once $hook_file;
}

// Plugin setup file
$setup_file = $plugin_root . DIRECTORY_SEPARATOR . 'setup.php';
if (file_exists($setup_file)) {
    require_once $setup_file;
}

// The autoloader is already defined in setup.php, just make sure it's registered
if (function_exists('plugin_formcreator_autoload')) {
    spl_autoload_register('plugin_formcreator_autoload');
}
