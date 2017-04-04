<?php

/*
 * @version $Id$
  -------------------------------------------------------------------------
  GLPI - Gestionnaire Libre de Parc Informatique
  Copyright (C) 2015-2016 Teclib'.

  http://glpi-project.org

  based on GLPI - Gestionnaire Libre de Parc Informatique
  Copyright (C) 2003-2014 by the INDEPNET Development Team.

  -------------------------------------------------------------------------

  LICENSE

  This file is part of GLPI.

  GLPI is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  GLPI is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with GLPI. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

/** @file
 * @brief
 */
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/** Referentiel Class
 * */
class PluginFormcreatorReferentielsApplications extends CommonDBTM {

    /**
     * Process all the rules collectiony
     *
     * @param input            array the input data used to check criterias (need to be clean slashes)
     * @param output           array the initial ouput array used to be manipulate by actions (need to be clean slashes)
     * @param params           array parameters for all internal functions (need to be clean slashes)
     * @param options          array options :
     *                            - condition : specific condition to limit rule list
     *                            - only_criteria : only react on specific criteria
     *
     * @return the output array updated by actions (addslashes datas)
     * */
    function referentielProcessAssignation() {
        global $DB;

        $select_condition = "SELECT * FROM glpi_plugin_formcreator_targetsconditions "
                . "WHERE plugin_formcreator_targets_id=" . $_SESSION['items_id'] . ";";
        $result_condition = $DB->query($select_condition);
        foreach ($result_condition as $rows) {
            $row_condition = $rows;
        }
        $data_base_tmp = $DB->dbdefault;
        $nature_demande = $_SESSION['answer'][$row_condition['questions']];
        $select_column_gr = "SELECT COLUMN_NAME as GLPI_objects_calumn FROM INFORMATION_SCHEMA.COLUMNS "
                . "WHERE TABLE_NAME ='" . $row_condition['objets_glpi_assign'] . "'"
                . " AND TABLE_SCHEMA='$data_base_tmp' "
                . "AND COLUMN_NAME LIKE 'gr%' OR COLUMN_NAME LIKE 'gv%'"
                . "GROUP BY GLPI_objects_calumn;";
        $result_column_gr = $DB->query($select_column_gr);
        foreach ($result_column_gr as $rows_column) {
            if ($rows_column['GLPI_objects_calumn'] == $row_condition['gr_conditions']) {
                $gr = $rows_column['GLPI_objects_calumn'];
            }
        }
        $select_column_gv = "SELECT COLUMN_NAME as GLPI_objects_calumn FROM INFORMATION_SCHEMA.COLUMNS "
                . "WHERE TABLE_NAME ='" . $row_condition['objets_glpi_assign'] . "'"
                . " AND TABLE_SCHEMA='$data_base_tmp' "
                . "AND COLUMN_NAME LIKE 'gv%' OR COLUMN_NAME LIKE 'gr%' "
                . "GROUP BY GLPI_objects_calumn;";
        $result_column_gv = $DB->query($select_column_gv);
        foreach ($result_column_gv as $rows_column) {
            if ($rows_column['GLPI_objects_calumn'] == $row_condition['gv_conditions']) {
                $gv = $rows_column['GLPI_objects_calumn'];
            }
        }
        $referentiel_gr = $row_condition['objets_glpi_assign'];
        // chercher la ligne de la'application dans la BDD
        $query_gr = "SELECT `$referentiel_gr`.*
                   FROM `$referentiel_gr`
                   WHERE `$referentiel_gr`.`id` = '" . $_SESSION['answer'][$row_condition['question_concernee_assign']] . "'";
        $result_gr = $DB->query($query_gr);
        foreach ($result_gr as $rows) {
            $group_gr = $rows[$gr];
        }
        $referentiel_gv = $row_condition['objets_glpi_observer'];
        $query_gv = "SELECT `$referentiel_gv`.*
                   FROM `$referentiel_gv`
                   WHERE `$referentiel_gv`.`id` = '" . $_SESSION['answer'][$row_condition['question_concernee_observer']] . "'";
        $result_gv = $DB->query($query_gv);
        foreach ($result_gv as $rows) {
            $group_gv = $rows[$gv];
        }
        if (!empty($group_gr)) {
            $query_groups = "SELECT glpi_groups.id
                    FROM glpi_groups
                   WHERE glpi_groups.`completename` LIKE '%$group_gr%' ORDER BY `completename`;";
            $result_group = $DB->query($query_groups);
            foreach ($result_group as $_id_group_assign) {
                $id_group_r = $_id_group_assign['id'];
                if ($result_group->num_rows > 1) {
                    break;
                }
            }
        }
        if (!empty($group_gv)) {
            $query_groups_v = "SELECT glpi_groups.id
                    FROM glpi_groups
                   WHERE glpi_groups.`completename` LIKE '%$group_gv%' ORDER BY `completename`;";
            $result_group_v = $DB->query($query_groups_v);
            foreach ($result_group_v as $_id_group_assign_v) {
                $id_group_v = $_id_group_assign_v['id'];
                if ($result_group_v->num_rows > 1) {
                    break;
                }
            }
        }

        $id_group['groupe_de_resolution'] = $id_group_r;
        $id_group['groupe_de_valition'] = $id_group_v;

        return $id_group;
    }

    function addGroupsValidation($group, $ticket) {
        global $DB;

        //$enities_id = $_SESSION['glpiactive_entity'];
        $tickets_id = $ticket['id'];
        /*$date = date("Y-m-d H:i:s");
        $status = 2;
        $users_id_recipient = $ticket['users_id_recipient'];
        unset($_SESSION['groups_validation']);
        $query_groups_users = "SELECT glpi_groups_users.users_id
                    FROM glpi_groups_users
                   WHERE glpi_groups_users.`groups_id`='$group'";
        $result_group_users = $DB->query($query_groups_users);
        foreach ($result_group_users as $users_id) {
            $user_id = $users_id['users_id'];
            // Do not auto add twice same validation
            if (!TicketValidation::alreadyExists($tickets_id, $user_id)) {
                $query_insert = "INSERT INTO `glpi_ticketvalidations` 
                            (`entities_id`,
                            `tickets_id`,
                            `users_id_validate`,
                            `comment_submission`,
                            `users_id`,
                            `submission_date`,
                            `status`)
                             VALUES ('$enities_id',
                                     '$tickets_id',
                                     '$user_id',
                                     '',
                                     '$users_id_recipient',
                                     '$date'
                                     ,'$status')";

                $DB->query($query_insert);
            }
        }*/
        // poste insert de groupe de validation pour l'affichage du groupe dans les détails du ticket
        $query_insert_group = "INSERT INTO `glpi_groups_tickets`(`tickets_id`, `groups_id`, `type`) VALUES ($tickets_id, $group, 3);";
        $DB->query($query_insert_group);

        return 0;
    }

}

?>
