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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

function plugin_formcreator_update_2_6_4() {
   global $DB;

   // Remove abusive encoding in target changes
   $table = 'glpi_plugin_formcreator_questions';
   $request = [
      'FROM' => $table,
   ];
   foreach ($DB->request($request) as $row) {
      $name = Toolbox::addslashes_deep(html_entity_decode($row['description'], ENT_QUOTES|ENT_HTML5));
      $id = $row['id'];
      $DB->query("UPDATE `$table` SET `description`='$name' WHERE `id` = '$id'");
      switch ($row['fieldtype']) {
         case 'checkboxes':
         case 'multiselect':
         case 'radios':
         case 'select':
            $defaultValues = Toolbox::addslashes_deep(html_entity_decode($row['default_values'], ENT_QUOTES|ENT_HTML5));
            $values = Toolbox::addslashes_deep(html_entity_decode($row['values'], ENT_QUOTES|ENT_HTML5));
            $DB->query("UPDATE `$table` SET `default_values`='$defaultValues', `values`='$values' WHERE `id` = '$id'");
            break;

         case 'hidden':
         case 'text':
         case 'textarea':
         case 'urgency':
            $defaultValues = Toolbox::addslashes_deep(html_entity_decode($row['default_values'], ENT_QUOTES|ENT_HTML5));
            $DB->query("UPDATE `$table` SET `default_values`='$defaultValues' WHERE `id` = '$id'");
            break;

         case 'ldapselect':
            $values = Toolbox::addslashes_deep(html_entity_decode($row['values'], ENT_QUOTES|ENT_HTML5));
            $DB->query("UPDATE `$table` SET `values`='$values' WHERE `id` = '$id'");
            break;
      }
   }

      // Remove abusive encoding in target changes
      $table = 'glpi_plugin_formcreator_forms';
      $request = [
         'FROM' => $table,
      ];
      foreach ($DB->request($request) as $row) {
         $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
         $description = Toolbox::addslashes_deep(html_entity_decode($row['description'], ENT_QUOTES|ENT_HTML5));
         $content = Toolbox::addslashes_deep(html_entity_decode($row['content'], ENT_QUOTES|ENT_HTML5));
         $id = $row['id'];
         $DB->query("UPDATE `$table` SET `name`='$name', `description`='$description', `content`='$content' WHERE `id` = '$id'");
      }
}