<?php
/**
 * LICENSE
 *
 * Copyright © 2011-2018 Teclib'
 *
 * This file is part of Formcreator Plugin for GLPI.
 *
 * Formcreator is a plugin that allow creation of custom, easy to access forms
 * for users when they want to create one or more GLPI tickets.
 *
 * Formcreator Plugin for GLPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2018 Teclib
 * @license   GPLv2 https://www.gnu.org/licenses/gpl2.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ------------------------------------------------------------------------------
 */

function plugin_formcreator_update_2_6_1() {
   global $DB;

   // decode html entities in name of questions
   $request = [
      'SELECT' => ['glpi_plugin_formcreator_answers.*'],
      'FROM' => 'glpi_plugin_formcreator_answers',
      'INNER JOIN' => ['glpi_plugin_formcreator_questions' => [
         'FKEY' => [
            'glpi_plugin_formcreator_answers' => 'plugin_formcreator_questions_id',
            'glpi_plugin_formcreator_questions' => 'id'
         ]
      ]],
      'WHERE' => ['fieldtype' => 'textarea']
   ];
   foreach ($DB->request($request) as $row) {
      $answer = Toolbox::addslashes_deep(html_entity_decode($row['answer'], ENT_QUOTES|ENT_HTML5));
      $id = $row['id'];
      $DB->query("UPDATE `glpi_plugin_formcreator_answers` SET `answer`='$answer' WHERE `id` = '$id'");
   }

   $request = [
      'FROM' => 'glpi_plugin_formcreator_questions',
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
      $id = $row['id'];
      $DB->query("UPDATE `glpi_plugin_formcreator_questions` SET `name`='$name' WHERE `id` = '$id'");
   }
}