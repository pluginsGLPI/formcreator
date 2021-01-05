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
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

interface PluginFormcreatorQuestionParameterInterface {
   /**
    * Gets the HTML form part for the parameters
    * @param PluginFormcreatorForm $form a form used as context when displaying parameters
    * @param PluginFormcreatorQuestion $question question associated to the field, itself associated to the parameter
    * @return string HTML
    */
   public function getParameterForm(PluginFormcreatorForm $form, PluginFormcreatorQuestion $question);

   /**
    * Gets the Js selector containing the parameters to show or hide
    * @return string JS code
    */
   public function getJsShowHideSelector();

   /**
    * Gets the name of the parameter
    * @return string
    */
   public function getFieldName();

   /**
    * Gets the size of the parameter
    * Possible values are 0 for 2 table columns, or 1 for 4 table columns
    * @return integer
    */
   public function getParameterFormSize();

}