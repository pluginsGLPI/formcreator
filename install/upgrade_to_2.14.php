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
class PluginFormcreatorUpgradeTo2_14 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
       $this->migration = $migration;

       $this->addTtoToIssues();
       $this->addRights();
       $this->addPropertiesToCategories();
       $this->addTargetActorUnicity();
       $this->addTargetContract();
       $this->addEntityOption();
       $this->addMultiLevelValidation();
       $this->addFormLink();
   }

   public function addEntityOption() {
      $table = 'glpi_plugin_formcreator_entityconfigs';

      $this->migration->addField(
         $table,
         'home_page',
         'integer', [
            'after' => 'tile_design',
            'value' => '-2',
            'update' => '0',
            'condition' => 'WHERE `entities_id` = 0'
         ]
      );

      $this->migration->addField(
         $table,
         'is_category_visible',
         'integer', [
            'after' => 'home_page',
            'value' => '-2',
            'update' => '1',
            'condition' => 'WHERE `entities_id` = 0'
         ]
      );

      $this->migration->addField(
         $table,
         'is_folded_menu',
         'integer', [
            'after' => 'is_category_visible',
            'value' => '-2',
            'update' => '0',
            'condition' => 'WHERE `entities_id` = 0'
         ]
      );
   }

   public function addTtoToIssues() {
        $table = (new DBUtils())->getTableForItemType(PluginFormcreatorIssue::class);
        $this->migration->addField($table, 'time_to_own', 'timestamp', ['after' => 'users_id_recipient']);
        $this->migration->addField($table, 'time_to_resolve', 'timestamp', ['after' => 'time_to_own']);
        $this->migration->addField($table, 'internal_time_to_own', 'timestamp', ['after' => 'time_to_resolve']);
        $this->migration->addField($table, 'internal_time_to_resolve', 'timestamp', ['after' => 'internal_time_to_own']);
        $this->migration->addField($table, 'solvedate', 'timestamp', ['after' => 'internal_time_to_resolve']);
        $this->migration->addField($table, 'date', 'timestamp', ['after' => 'solvedate']);
        $this->migration->addField($table, 'takeintoaccount_delay_stat', 'int', ['after' => 'date']);

        $this->migration->addKey($table, 'time_to_own');
        $this->migration->addKey($table, 'time_to_resolve');
        $this->migration->addKey($table, 'internal_time_to_own');
        $this->migration->addKey($table, 'internal_time_to_resolve');
        $this->migration->addKey($table, 'solvedate');
        $this->migration->addKey($table, 'date');
   }

   public function addRights() {
      // Add rights
      global $DB;
      $profiles = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => Profile::getTable(),
      ]);
      foreach ($profiles as $profile) {
         $rights = ProfileRight::getProfileRights(
            $profile['id'],
            [
               Entity::$rightname,
               PluginFormcreatorForm::$rightname,
            ]
         );
         if (($rights[Entity::$rightname] & (UPDATE + CREATE + DELETE + PURGE)) == 0) {
            continue;
         }
         $right = READ + UPDATE + CREATE + DELETE + PURGE;
         ProfileRight::updateProfileRights($profile['id'], [
            PluginFormcreatorForm::$rightname => $right,
         ]);
      }
   }

   public function isResyncIssuesRequiresd() {
      return true;
   }

   public function addPropertiesToCategories() {
      global $DB;

      $table = (new DBUtils())->getTableForItemType(PluginFormcreatorCategory::class);
      $this->migration->addField($table, 'icon', 'string', ['after' => 'knowbaseitemcategories_id']);
      $this->migration->addField($table, 'icon_color', 'string', ['after' => 'icon']);
      if (!$DB->fieldExists($table, 'background_color')) {
         $this->migration->addField($table, 'background_color', 'string', ['after' => 'icon_color']);
         $this->migration->addPostQuery("UPDATE `$table` SET background_color=''");
      }
   }

   public function addTargetActorUnicity() {
      /** @var DBmysql $DB */
      global $DB;

      $table = (new DBUtils())->getTableForItemType(PluginFormcreatorTarget_Actor::class);
      $unicity = [
         'itemtype',
         'items_id',
         'actor_role',
         'actor_type',
         'actor_value'
      ];

      // Clean existing duplicates
      $DB->queryOrDie("DELETE `t1` FROM `$table` `t1`
         INNER JOIN `$table` `t2`
         WHERE
            t1.id < t2.id AND
            t1.itemtype = t2.itemtype
            AND t1.items_id = t2.items_id
            AND t1.actor_role = t2.actor_role
            AND t1.actor_type = t2.actor_type
            AND t1.actor_value = t2.actor_value"
      );

      // Set unicity
      $this->migration->addKey($table, $unicity, 'unicity', 'UNIQUE');
   }

   public function addTargetContract() {
      $table = 'glpi_plugin_formcreator_targettickets';
      $unsignedIntType = "INT UNSIGNED NOT NULL DEFAULT '0'";

      $this->migration->addField($table, 'contract_rule', 'integer', ['after' => 'location_question', 'value' => '1']);
      $this->migration->addField($table, 'contract_question', $unsignedIntType, ['after' => 'contract_rule']);
   }

   public function addMultiLevelValidation() {
      $this->migration->addField('glpi_plugin_formcreator_forms', 'validation_percent', 'integer', [
         'after' => 'validation_required',
         'value' => '100',
      ]);
      $this->migration->addField('glpi_plugin_formcreator_formanswers', 'validation_percent', 'integer', [
         'after' => 'requester_id',
         'value' => '100',
      ]);
      $this->migration->addField('glpi_plugin_formcreator_forms_validators', 'level', 'integer', [
         'after' => 'items_id',
         'value' => '1',
      ]);
   }

   public function addFormLink() {
      $table = 'glpi_plugin_formcreator_forms';

      // Change default value in DB
      $this->migration->addField($table, 'plugin_formcreator_forms_id', 'integer', ['after' => 'show_rule']);
   }
}
