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

   // decode html entities in name of targets
   $request = [
      'FROM' => 'glpi_plugin_formcreator_forms_targets',
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = Toolbox::addslashes_deep(Html::entity_decode_deep($row['name']));
      $DB->query("UPDATE `glpi_plugin_formcreator_forms_targets`
                  SET `name`='$name',
                  WHERE `id` = '$id'");
   }

   // decode html entities in answers
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
      $answer = Toolbox::addslashes_deep(html_entity_decode($row['answer']));
      $id = $row['id'];
      $DB->query("UPDATE `glpi_plugin_formcreator_answers` SET `answer`='$answer' WHERE `id` = '$id'");
   }

   // decode html entities in name, description and content of forms
   $request = [
      'FROM' => 'glpi_plugin_formcreator_forms',
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = addslashes(Html::entity_decode_deep($row['name']));
      $description = addslashes(Html::entity_decode_deep($row['description']));
      // have only HTML special chars encoded
      $content = addslashes(htmlspecialchars(Html::entity_decode_deep($row['description'])));
      $DB->query("UPDATE `glpi_plugin_formcreator_forms`
                  SET `name`='$name',
                  `description`='$description',
                  `content`='$content'
                  WHERE `id` = '$id'");
   }

   // decode html entities in name of form answers
   $request = [
      'FROM' => 'glpi_plugin_formcreator_forms_answers',
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = addslashes(Html::entity_decode_deep($row['name']));
      $DB->query("UPDATE `glpi_plugin_formcreator_forms_answers`
                  SET `name`='$name',
                  WHERE `id` = '$id'");
   }

   // Force update of issues to remove abusive encoding
   //PluginFormcreatorIssue::cronSyncIssues(new CronTask());
}