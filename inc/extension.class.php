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

/**
 * PluginFormcreatorExtension Class
 * 
 * */
class PluginFormcreatorExtension {

    //Empty value displayed in a dropdown
    const EMPTY_VALUE = '-----';

    /**
     * Create Ajax dropdown to clean JS
     *
     * @param $name
     * @param $field_id   string   id of the dom element
     * @param $url        string   URL to get datas
     * @param $params     array    of parameters
     *            must contains :
     *                   - 'value'     : default value selected
     *                   - 'valuename' : default name of selected value
     *
     * @since version 0.85.
     *
     * @return String
     * */
    static function jsAjaxDropdown($name, $field_id, $url, $params = array()) {
        global $CFG_GLPI;

        if (!isset($params['value'])) {
            $value = 0;
        } else {
            $value = $params['value'];
        }
        if (!isset($params['value'])) {
            $valuename = Dropdown::EMPTY_VALUE;
        } else {
            $valuename = $params['valuename'];
        }
        $on_change = '';
        if (isset($params["on_change"])) {
            $on_change = $params["on_change"];
            unset($params["on_change"]);
        }
        $width = '80%';
        if (isset($params["width"])) {
            $width = $params["width"];
            unset($params["width"]);
        }
        unset($params['value']);
        unset($params['valuename']);

        $options = array('value' => $value, 'id' => $field_id);
        if (!empty($params['specific_tags'])) {
            foreach ($params['specific_tags'] as $tag => $val) {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
                $options[$tag] = $val;
            }
        }

        $output = Html::hidden($name, $options);

        $js .= " $('#$field_id').select2({
                        width: '$width',
                        minimumInputLength: 0,
                        quietMillis: 100,
                        dropdownAutoWidth: true,
                        minimumResultsForSearch: " . $CFG_GLPI['ajax_limit_count'] . ",
                        closeOnSelect: false,
                        ajax: {
                           url: '$url',
                           dataType: 'json',
                           type: 'POST',
                           data: function (term, page) {
                              return { ";
        foreach ($params as $key => $val) {
            // Specific boolean case
            if (is_bool($val)) {
                $js .= "$key: " . ($val ? 1 : 0) . ",\n";
            } else {
                $js .= "$key: " . json_encode($val) . ",\n";
            }
        }
        // Affichage des bonne valeurs
        if (isset($_SESSION['js'])) {
            $js .= $_SESSION['js']['name'] . ":" . $_SESSION['js']['value'] . ",\n";
        }
        $js .= "               searchText: term,
                                 page_limit: " . $CFG_GLPI['dropdown_max'] . ", // page size
                                 page: page, // page number
                              };
                           },
                           results: function (data, page) {
//                               var more = (page * " . $CFG_GLPI['dropdown_max'] . ") < data.total;
//                               alert(data.count+' '+" . $CFG_GLPI['dropdown_max'] . ");
                              var more = (data.count >= " . $CFG_GLPI['dropdown_max'] . ");
                              return {results: data.results, more: more};
//                               return {results: data.results};
                           }
                        },
                        initSelection: function (element, callback) {
                           var id=$(element).val();
                           var defaultid = '$value';
                           if (id !== '') {
                              // No ajax call for first item
                              if (id === defaultid) {
                                var data = {id: " . json_encode($value) . ",
                                          text: " . json_encode($valuename) . "};
                                 callback(data);
                              } else {
                                 $.ajax('$url', {
                                 data: {";
        foreach ($params as $key => $val) {
            $js .= "$key: " . json_encode($val) . ",\n";
        }
        // Affichage des bonne valeurs
        if (isset($_SESSION['js'])) {
            $js .= $_SESSION['js']['name'] . ":" . $_SESSION['js']['value'] . ",\n";
        }
        $js .= "            _one_id: id},
                                 dataType: 'json',
                                 type: 'POST',
                                 }).done(function(data) { callback(data); });
                              }
                           }

                        },
                        formatResult: function(result, container, query, escapeMarkup) {
                           container.attr('title', result.title);
                           var markup=[];
                           window.Select2.util.markMatch(result.text, query.term, markup, escapeMarkup);
                           if (result.level) {
                              var a='';
                              var i=result.level;
                              while (i>1) {
                                 a = a+'&nbsp;&nbsp;&nbsp;';
                                 i=i-1;
                              }
                              return a+'&raquo;'+markup.join('');
                           }
                           return markup.join('');
                        }

                     });";
        if (!empty($on_change)) {
            $js .= " $('#$field_id').on('change', function(e) {" .
                    stripslashes($on_change) . "});";
        }

        $output .= Html::scriptBlock($js);
        return $output;
    }

    /**
     * Print out an HTML "<select>" for a dropdown with preselected value
     *
     * @param $itemtype        itemtype used for create dropdown
     * @param $options   array of possible options:
     *    - name                 : string / name of the select (default is depending itemtype)
     *    - value                : integer / preselected value (default -1)
     *    - comments             : boolean / is the comments displayed near the dropdown (default true)
     *    - toadd                : array / array of specific values to add at the begining
     *    - entity               : integer or array / restrict to a defined entity or array of entities
     *                                                (default -1 : no restriction)
     *    - entity_sons          : boolean / if entity restrict specified auto select its sons
     *                                       only available if entity is a single value not an array
     *                                       (default false)
     *    - toupdate             : array / Update a specific item on select change on dropdown
     *                                     (need value_fieldname, to_update,
     *                                      url (see Ajax::updateItemOnSelectEvent for information)
     *                                      and may have moreparams)
     *    - used                 : array / Already used items ID: not to display in dropdown
     *                                    (default empty)
     *    - on_change            : string / value to transmit to "onChange"
     *    - rand                 : integer / already computed rand value
     *    - condition            : string / aditional SQL condition to limit display
     *    - displaywith          : array / array of field to display with request
     *    - emptylabel           : Empty choice's label (default self::EMPTY_VALUE)
     *    - display_emptychoice  : Display emptychoice ? (default true)
     *    - display              : boolean / display or get string (default true)
     *    - width                : specific width needed (default auto adaptive)
     *    - permit_select_parent : boolean / for tree dropdown permit to see parent items
     *                                       not available by default (default false)
     *    - specific_tags        : array of HTML5 tags to add the the field
     *    - url                  : url of the ajax php code which should return the json data to show in
     *                                       the dropdown
     *
     * @return boolean : false if error and random id if OK
     * */
    static function show($itemtype, $options = array()) {
        global $DB, $CFG_GLPI;

        //verifier si c'est un champs de type Objet GLPI application
        if (isset($_SESSION['js'])) {
            $itemtype = 'PluginFormcreatorReferentielsApplications';
        }

        if ($itemtype && !($item = getItemForItemtype($itemtype))) {
            return false;
        }

        $table = $item->getTable();

        $params['name'] = $item->getForeignKeyField();
        $params['value'] = (($itemtype == 'Entity') ? $_SESSION['glpiactive_entity'] : '');
        $params['comments'] = true;
        $params['entity'] = -1;
        $params['entity_sons'] = false;
        $params['toupdate'] = '';
        $params['width'] = '';
        $params['used'] = array();
        $params['toadd'] = array();
        $params['on_change'] = '';
        $params['condition'] = '';
        $params['rand'] = mt_rand();
        $params['displaywith'] = array();
        //Parameters about choice 0
        //Empty choice's label
        $params['emptylabel'] = self::EMPTY_VALUE;
        //Display emptychoice ?
        $params['display_emptychoice'] = ($itemtype != 'Entity');
        $params['display'] = true;
        $params['permit_select_parent'] = false;
        $params['addicon'] = true;
        $params['specific_tags'] = array();
        $params['url'] = $CFG_GLPI['root_doc'] . "/plugins/formcreator/ajax/getDropdownValue.php";


        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }
        $output = '';
        $name = $params['emptylabel'];
        $comment = "";

        // Check default value for dropdown : need to be a numeric
        if ((strlen($params['value']) == 0) || !is_numeric($params['value']) && $params['value'] != 'mygroups') {
            $params['value'] = 0;
        }

        if (isset($params['toadd'][$params['value']])) {
            $name = $params['toadd'][$params['value']];
        } else if (($params['value'] > 0) || (($itemtype == "Entity") && ($params['value'] >= 0))) {
            $tmpname = Dropdown::getDropdownName($table, $params['value'], 1);

            if ($tmpname["name"] != "&nbsp;") {
                $name = $tmpname["name"];
                $comment = $tmpname["comment"];
            }
        }

        // Manage entity_sons
        if (!($params['entity'] < 0) && $params['entity_sons']) {
            if (is_array($params['entity'])) {
                // translation not needed - only for debug
                $output .= "entity_sons options is not available with entity option as array";
            } else {
                $params['entity'] = getSonsOf('glpi_entities', $params['entity']);
            }
        }


        $field_id = Html::cleanId("dropdown_" . $params['name'] . $params['rand']);

        // Manage condition
        if (!empty($params['condition'])) {
            $params['condition'] = static::addNewCondition($params['condition']);
        }

        if (!$item instanceof CommonTreeDropdown) {
            $name = Toolbox::unclean_cross_side_scripting_deep($name);
        }
        $p = array('value' => $params['value'],
            'valuename' => $name,
            'width' => $params['width'],
            'itemtype' => $itemtype,
            'display_emptychoice' => $params['display_emptychoice'],
            'displaywith' => $params['displaywith'],
            'emptylabel' => $params['emptylabel'],
            'condition' => $params['condition'],
            'used' => $params['used'],
            'toadd' => $params['toadd'],
            'entity_restrict' => (is_array($params['entity']) ? json_encode(array_values($params['entity'])) : $params['entity']),
            'on_change' => $params['on_change'],
            'permit_select_parent' => $params['permit_select_parent'],
            'specific_tags' => $params['specific_tags'],
        );



        $output = "<span class='no-wrap'>";
        $output .= PluginFormcreatorExtension::jsAjaxDropdown($params['name'], $field_id, $params['url'], $p);
        // Display comment
        if ($params['comments']) {
            $comment_id = Html::cleanId("comment_" . $params['name'] . $params['rand']);
            $link_id = Html::cleanId("comment_link_" . $params['name'] . $params['rand']);
            $options_tooltip = array('contentid' => $comment_id,
                'linkid' => $link_id,
                'display' => false);

            if ($item->canView()) {
                if ($params['value'] && $item->getFromDB($params['value']) && $item->canViewItem()) {
                    $options_tooltip['link'] = $item->getLinkURL();
                } else {
                    $options_tooltip['link'] = $item->getSearchURL();
                }
                $options_tooltip['linktarget'] = '_blank';
            }

            $output .= "&nbsp;" . Html::showToolTip($comment, $options_tooltip);

            if (($item instanceof CommonDropdown) && $item->canCreate() && !isset($_REQUEST['_in_modal']) && $params['addicon']) {

                $output .= "<img alt='' title=\"" . __s('Add') . "\" src='" . $CFG_GLPI["root_doc"] .
                        "/pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'
                            onClick=\"" . Html::jsGetElementbyID('add_dropdown' . $params['rand']) . ".dialog('open');\">";
                $output .= Ajax::createIframeModalWindow('add_dropdown' . $params['rand'], $item->getFormURL(), array('display' => false));
            }
            // Display specific Links
            if ($itemtype == "Supplier") {
                if ($item->getFromDB($params['value'])) {
                    $output .= $item->getLinks();
                }
            }

            if (($itemtype == 'ITILCategory') && Session::haveRight('knowbase', READ)) {

                if ($params['value'] && $item->getFromDB($params['value'])) {
                    $output .= '&nbsp;' . $item->getLinks();
                }
            }
            $paramscomment = array('value' => '__VALUE__',
                'table' => $table);
            if ($item->canView()) {
                $paramscomment['withlink'] = $link_id;
            }

            $output .= Ajax::updateItemOnSelectEvent($field_id, $comment_id, $CFG_GLPI["root_doc"] . "/ajax/comments.php", $paramscomment, false);
        }
        $output .= Ajax::commonDropdownUpdateItem($params, false);
        if ($params['display']) {
            echo $output;
            return $params['rand'];
        }
        $output .= "</span>";
        return $output;
    }

    /**
     * Print out an HTML "<select>" for a dropdown
     *
     * This should be overloaded in Class
     *
     * @param $options   array of possible options:
     * Parameters which could be used in options array :
     *    - name : string / name of the select (default is depending itemtype)
     *    - value : integer / preselected value (default 0)
     *    - comments : boolean / is the comments displayed near the dropdown (default true)
     *    - entity : integer or array / restrict to a defined entity or array of entities
     *                   (default -1 : no restriction)
     *    - toupdate : array / Update a specific item on select change on dropdown
     *                   (need value_fieldname, to_update, url (see Ajax::updateItemOnSelectEvent for information)
     *                   and may have moreparams)
     *    - used : array / Already used items ID: not to display in dropdown (default empty)
     *
     * @return nothing display the dropdown
     * */
    static function dropdown($options = array()) {
        /// TODO try to revert usage : Dropdown::show calling this function
        /// TODO use this function instead of Dropdown::show
        return PluginFormcreatorExtension::show(get_called_class(), $options);
    }

    static function generationTicketControl($target) {
        /// TODO try to revert usage : Dropdown::show calling this function
        /// TODO use this function instead of Dropdown::show
        $select_conf = "SELECT * FROM glpi_plugin_formcreator_targettickets_actors "
                . "WHERE plugin_formcreator_targettickets_id=" . $_SESSION['items_id'] . ";";

        $result_conf = $GLOBALS['DB']->query($select_conf);
        foreach ($result_conf as $row_conf) {
            if ($row_conf['actor_role'] == 'assigned' && $row_conf['actor_type'] == 'group' && !empty($row_conf['actor_value']) && $row_conf['actor_value'] != "0") {
                $_SESSION['referentiel_actif']['groupe_specific'] = $row_conf['actor_value'];
            }
        }
        $select = "SELECT * FROM glpi_plugin_formcreator_targetsconditions "
                . "WHERE plugin_formcreator_targets_id=" . $target['items_id'] . ";";
        $result = $GLOBALS['DB']->query($select);

        if ($result->num_rows > 0) {
            $row = $GLOBALS['DB']->fetch_array($result);
            $_SESSION['referentiel_actif']['condition_generation'] = $row['condition_generation'];
            $_SESSION['referentiel_actif']['objets_glpi_gv'] = $row['objets_glpi_observer'];
            $_SESSION['referentiel_actif']['objets_glpi_gr'] = $row['objets_glpi_assign'];
            $_SESSION['referentiel_actif']['gr_conditions'] = $row['gr_conditions'];
            $_SESSION['referentiel_actif']['gv_conditions'] = $row['gv_conditions'];
            $_SESSION['referentiel_actif']['slas'] = html_entity_decode($row['slas']);
            $_SESSION['referentiel_actif']['categorie'] = html_entity_decode($row['categorie']);
            $condition = true;
            if ($row['condition_generation'] == 'questions') {
                if (!empty($row['gr_conditions'])) {
                    $select_assign_gr = "SELECT " . $row['gr_conditions'] . " FROM " . $row['objets_glpi_assign']
                            . " WHERE id='" . $_SESSION['answer'][$row['question_concernee_assign']] . "';";
                    $result_assign_gr = $GLOBALS['DB']->query($select_assign_gr);
                    if ($result_assign_gr->num_rows > 0) {
                        $row_assign_gr = $GLOBALS['DB']->fetch_array($result_assign_gr);
                        if ($row_assign_gr[$row['gr_conditions']] == 'N/A' /* || $row_assign_gr[$row['gr_conditions']] == '' || empty($row_assign_gr[$row['gr_conditions']]) */) {
                            $condition = false;
                        } /* else {
                          // controler si le groupe présent dans le référentiel existe dans la BDD
                          $group_gr = $row_assign_gr[$row['gr_conditions']];
                          $query_groups = "SELECT glpi_groups.id
                          FROM glpi_groups
                          WHERE glpi_groups.`completename` LIKE '%$group_gr%' ORDER BY `completename`;";
                          $result_group = $GLOBALS['DB']->query($query_groups);
                          if ($result_group->num_rows == 0) {
                          return false;
                          }
                          } */
                    }
                }
                if (!empty($row['questions'])) {
                    switch ($row['conditions']) {
                        case 'est':
                            if (strtolower(html_entity_decode($_SESSION['answer'][$row['questions']])) != strtolower($row['valeur'])) {
                                $condition = FALSE;
                            }
                            break;
                        case 'n_est_pas':
                            if (strtolower(html_entity_decode($_SESSION['answer'][$row['questions']])) == strtolower($row['valeur'])) {
                                $condition = FALSE;
                            }
                            break;
                        case 'contient':
                            if (strpos(strtolower($_SESSION['answer'][$row['questions']]), strtolower($row['valeur'])) === FALSE) {
                                $condition = FALSE;
                            }
                            break;
                        case 'ne_contient_pas':
                            if (strpos(strtolower($_SESSION['answer'][$row['questions']]), strtolower($row['valeur']))) {
                                $condition = FALSE;
                            }
                            break;
                        case 'commence_par':
                            if (substr(strtolower($_SESSION['answer'][$row['questions']]), 0, strlen($row['valeur'])) !== strtolower($row['valeur'])) {
                                $condition = FALSE;
                            }
                            break;
                        case 'termine_par':
                            $longueur = strlen($_SESSION['answer'][$row['questions']]) - strlen($row['valeur']);
                            if (substr(strtolower($_SESSION['answer'][$row['questions']]), $longueur, strlen($_SESSION['answer'][$row['questions']])) !== strtolower($row['valeur'])) {
                                $condition = FALSE;
                            }
                            break;
                    }
                } else {
                    $condition = TRUE;
                }
            } elseif ($row['condition_generation'] == 'ne_pas_generer') {
                $condition = FALSE;
            } else {
                if (!empty($row['gr_conditions'])) {
                    $select_assign_gr = "SELECT " . $row['gr_conditions'] . " FROM " . $row['objets_glpi_assign']
                            . " WHERE id='" . $_SESSION['answer'][$row['question_concernee_assign']] . "';";
                    $result_assign_gr = $GLOBALS['DB']->query($select_assign_gr);
                    if ($result_assign_gr->num_rows > 0) {
                        $row_assign_gr = $GLOBALS['DB']->fetch_array($result_assign_gr);
                        if ($row_assign_gr[$row['gr_conditions']] == 'N/A' /* || $row_assign_gr[$row['gr_conditions']] == '' || empty($row_assign_gr[$row['gr_conditions']]) */) {
                            $condition = false;
                        } /* else {
                          // controler si le groupe présent dans le référentiel existe dans la BDD
                          $group_gr = $row_assign_gr[$row['gr_conditions']];
                          $query_groups = "SELECT glpi_groups.id
                          FROM glpi_groups
                          WHERE glpi_groups.`completename` LIKE '%$group_gr%' ORDER BY `completename`;";
                          $result_group = $GLOBALS['DB']->query($query_groups);
                          if ($result_group->num_rows == 0) {
                          return false;
                          }
                          } */
                    }
                }
            }
        } else {
            $condition = true;
        }
        return $condition;
    }

}
