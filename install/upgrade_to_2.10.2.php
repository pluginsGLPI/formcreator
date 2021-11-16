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
class PluginFormcreatorUpgradeTo2_10_2 {

   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;

      // Versioin 2.10.2 contains fixes on counters requiring repopulation of issues table
      $table = 'glpi_plugin_formcreator_issues';
      $DB->query("TRUNCATE `$table`");
      $this->syncIssues(new CronTask());
   }

   /**
    * This is a copy of PluginFormcreatorIssue::cronSyncIssues as it is in 2.10.2
    *
    * @param CronTask $task
    * @return void
    */
   public function syncIssues(CronTask $task) {
      global $DB;

      $task->log("Sync issues from forms answers and tickets");
      $volume = 0;

      // Request which merges tickets and formanswers
      // 1 ticket not linked to a formanswer => 1 issue which is the ticket sub_itemtype
      // 1 form_answer not linked to a ticket => 1 issue which is the formanswer sub_itemtype
      // 1 ticket linked to 1 form_answer => 1 issue which is the ticket sub_itemtype
      // several tickets linked to the same form_answer => 1 issue which is the form_answer sub_itemtype
      $query = "SELECT DISTINCT
                  NULL                            AS `id`,
                  `f`.`name`                      AS `name`,
                  CONCAT('f_',`fanswer`.`id`)     AS `display_id`,
                  `fanswer`.`id`                  AS `original_id`,
                  'PluginFormcreatorFormAnswer'   AS `sub_itemtype`,
                  `fanswer`.`status`              AS `status`,
                  `fanswer`.`request_date`        AS `date_creation`,
                  `fanswer`.`request_date`        AS `date_mod`,
                  `fanswer`.`entities_id`         AS `entities_id`,
                  `fanswer`.`is_recursive`        AS `is_recursive`,
                  `fanswer`.`requester_id`        AS `requester_id`,
                  `fanswer`.`users_id_validator`  AS `users_id_validator`,
                  `fanswer`.`groups_id_validator` AS `groups_id_validator`,
                  `fanswer`.`comment`             AS `comment`
               FROM `glpi_plugin_formcreator_formanswers` AS `fanswer`
               LEFT JOIN `glpi_plugin_formcreator_forms` AS `f`
                  ON`f`.`id` = `fanswer`.`plugin_formcreator_forms_id`
               LEFT JOIN `glpi_items_tickets` AS `itic`
                  ON `itic`.`items_id` = `fanswer`.`id`
                  AND `itic`.`itemtype` = 'PluginFormcreatorFormAnswer'
               GROUP BY `original_id`
               HAVING COUNT(`itic`.`tickets_id`) != 1
               UNION
               SELECT DISTINCT
                  NULL                          AS `id`,
                  `tic`.`name`                  AS `name`,
                  CONCAT('t_',`tic`.`id`)       AS `display_id`,
                  `tic`.`id`                    AS `original_id`,
                  'Ticket'                      AS `sub_itemtype`,
                  if(`tv`.`status` IS NULL,`tic`.`status`, if(`tv`.`status` = 2, 101, if(`tv`.`status` = 3, `tic`.`status`, 102))) AS `status`,
                  `tic`.`date`                  AS `date_creation`,
                  `tic`.`date_mod`              AS `date_mod`,
                  `tic`.`entities_id`           AS `entities_id`,
                  0                             AS `is_recursive`,
                  `tu`.`users_id`               AS `requester_id`,
                  `tv`.`users_id_validate`      AS `users_id_validator`,
                  0                             AS `groups_id_validator`,
                  `tic`.`content`               AS `comment`
               FROM `glpi_tickets` AS `tic`
               LEFT JOIN `glpi_items_tickets` AS `itic`
                  ON `itic`.`tickets_id` = `tic`.`id`
                  AND `itic`.`itemtype` = 'PluginFormcreatorFormAnswer'
               LEFT JOIN (
                  SELECT DISTINCT `users_id`, `tickets_id`
                  FROM `glpi_tickets_users` AS `tu`
                  WHERE `tu`.`type` = '"  . CommonITILActor::REQUESTER . "'
                  ORDER BY `id` ASC
               ) AS `tu` ON (`tic`.`id` = `tu`.`tickets_id`)
               LEFT JOIN `glpi_ticketvalidations` as `tv`
                  ON (`tic`.`id` = `tv`.`tickets_id`)
               WHERE `tic`.`is_deleted` = 0
               GROUP BY `original_id`
               HAVING COUNT(`itic`.`items_id`) <= 1";

      $countQuery = "SELECT COUNT(*) AS `cpt` FROM ($query) AS `issues`";
      $result = $DB->query($countQuery);
      if ($result !== false) {
         if (version_compare(GLPI_VERSION, '9.5') < 0) {
            $fa = 'fetch_assoc';
         } else {
            $fa = 'fetchAssoc';
         }
         $count = $DB->$fa($result);
         $table = 'glpi_plugin_formcreator_issues';
         if (countElementsInTable($table) != $count['cpt']) {
            if ($DB->query("TRUNCATE `$table`")) {
               $DB->query("INSERT INTO `$table` SELECT * FROM ($query) as `dt`");
               $volume = 1;
            }
         }
      }
      $task->setVolume($volume);

      return 1;
   }
}
