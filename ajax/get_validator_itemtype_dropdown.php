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
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use Glpi\Application\View\TemplateRenderer;

include ('../../../inc/includes.php');

// Check if plugin is activated...
if (!Plugin::isPluginActive('formcreator')) {
   http_response_code(404);
   die();
}

if (!Session::haveRight(PluginFormcreatorForm::$rightname, UPDATE)) {
    http_response_code(403);
    die();
}

if (!isset($_POST['itemtype'])) {
    http_response_code(400);
    die();
}

TemplateRenderer::getInstance()->display('@formcreator/components/form/form_validator.validator_type.html.twig', [
    'type' => $_POST['itemtype'],
]);
