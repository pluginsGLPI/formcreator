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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorDropdownField extends PluginFormcreatorField
{
   public function isPrerequisites() {
      return true;
   }

   public function getDesignSpecializationField() {
      $rand = mt_rand();

      $label = '<label for="dropdown_dropdown_values'.$rand.'" id="label_dropdown_values">';
      $label .= _n('Dropdown', 'Dropdowns', 1);
      $label .= '</label>';

      $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
      if ($decodedValues === null) {
         $itemtype = $this->fields['values'];
      } else {
         $itemtype = $decodedValues['itemtype'];
      }
      $optgroup = Dropdown::getStandardDropdownItemTypes();
      array_unshift($optgroup, '---');
      $field = '<div id="dropdown_values_field">';
      $field .= Dropdown::showFromArray('dropdown_values', $optgroup, [
         'value'     => $itemtype,
         'rand'      => $rand,
         'on_change' => 'plugin_formcreator_changeDropdownItemtype("' . $rand . '");',
         'display'   => false,
      ]);

      $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values'.$rand.'">';
      $additions .= __('Default values');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td id="dropdown_default_value_field">';
      $additions .= '</td>';
      $additions .= '<td></td>';
      $additions .= '<td></td>';
      $additions .= '</tr>';

      // Ticket category specific
      $additions .= '<tr class="plugin_formcreator_question_specific plugin_formcreator_dropdown_ticket">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_show_ticket_categories'.$rand.'" id="label_show_ticket_categories">';
      $additions .= __('Show ticket categories', 'formcreator');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $ticketCategoriesOptions = [
         'request'  => __('Request categories', 'formcreator'),
         'incident' => __('Incident categories', 'formcreator'),
         'both'     => __('Request categories', 'formcreator'). " + ".__('Incident categoqries', 'formcreator'),
         'change'   => __('Change'),
         'all'      => __('All'),
      ];
      $additions .= dropdown::showFromArray('show_ticket_categories', $ticketCategoriesOptions, [
         'rand'  => $rand,
         'value' => isset($decodedValues['show_ticket_categories'])
                    ? $decodedValues['show_ticket_categories']
                    : 'both',
         'display' => false,
      ]);
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_show_ticket_categories_depth'.$rand.'" id="label_show_ticket_categories_depth">';
      $additions .= __('Limit ticket categories depth', 'formcreator');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= dropdown::showNumber(
         'show_ticket_categories_depth', [
            'rand'  => $rand,
            'value' => isset($decodedValues['show_ticket_categories_depth'])
                        ? $decodedValues['show_ticket_categories_depth']
                        : 0,
            'min' => 1,
            'max' => 16,
            'toadd' => [0 => __('No limit', 'formcreator')],
            'display' => false,
         ]
      );
      $additions .= '</td>';
      $additions .= '</tr>';
      $additions .= '<tr class="plugin_formcreator_question_specific plugin_formcreator_dropdown_ticket">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_root_ticket_categories'.$rand.'" id="label_root_ticket_categories">';
      $additions .= __('ticket categories root', 'formcreator');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $decodedValue = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
      $rootValue = isset($decodedValue['show_ticket_categories_root'])
                     ? $decodedValue['show_ticket_categories_root']
                     : Dropdown::EMPTY_VALUE;
      $additions .= Dropdown::show(ITILCategory::class, [
         'name'  => 'show_ticket_categories_root',
         'value' => $rootValue,
         'rand'  => $rand,
         'display' => false,
      ]);
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '</tr>';
      $additions .= Html::scriptBlock("plugin_formcreator_changeDropdownItemtype($rand);");

      $common = $common = parent::getDesignSpecializationField();
      $additions .= $common['additions'];

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => true,
         'may_be_required' => true,
      ];
   }

   public function displayField($canEdit = true) {
      global $DB, $CFG_GLPI;

      if ($canEdit) {
         $id           = $this->fields['id'];
         $rand         = mt_rand();
         $fieldName    = 'formcreator_field_' . $id;
         $domId        = $fieldName . '_' . $rand;
         $required     = $this->fields['required'] ? ' required' : '';
         if (!empty($this->fields['values'])) {
            $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
            if ($decodedValues === null) {
               $itemtype = $this->fields['values'];
            } else {
               $itemtype = $decodedValues['itemtype'];
            }

            $dparams = ['name'     => $fieldName,
                        'value'    => $this->value,
                        'comments' => false,
                        'entity'   => $_SESSION['glpiactive_entity'],
                        'rand'     => $rand];

            $dparams_cond_crit = [];
            switch ($itemtype) {
               case Entity::class:
                  unset($dparams['entity']);

               case User::class:
                  $dparams['right'] = 'all';
                  break;

               case ITILCategory::class:
                  $dparams['condition'] = '1';
                  if (isset ($_SESSION['glpiactiveprofile']['interface'])
                     && $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {
                     $dparams['condition'] .= " AND `is_helpdeskvisible` = '1'";
                     $dparams_cond_crit['is_helpdeskvisible'] = 1;
                  }
                  switch ($decodedValues['show_ticket_categories']) {
                     case 'request':
                        $dparams['condition'] .= " AND `is_request` = '1'";
                        $dparams_cond_crit['is_request'] = 1;
                        break;
                     case 'incident':
                        $dparams['condition'] .= " AND `is_incident` = '1'";
                        $dparams_cond_crit['is_incident'] = 1;
                        break;
                     case 'both':
                        $dparams['condition'] .= " AND (`is_incident` = '1' OR `is_request` = '1')";
                        $dparams_cond_crit['OR'] = [
                           'is_incident' => 1,
                           'is_request'  => 1,
                        ];
                        break;
                     case 'change':
                        $dparams['condition'] .= " AND `is_change` = '1'";
                        $dparams_cond_crit['is_change'] = 1;
                        break;
                     case 'all':
                        $dparams['condition'] .= " AND (`is_change` = '1' OR `is_incident` = '1' OR  `is_request` = '1')";
                        $dparams_cond_crit['OR'] = [
                           'is_change'   => 1,
                           'is_incident' => 1,
                           'is_request'  => 1,
                        ];
                        break;
                  }
                  if (isset($decodedValues['show_ticket_categories_depth'])
                     && $decodedValues['show_ticket_categories_depth'] > 0) {
                     $dparams['condition'] .= " AND `level` <= '" . $decodedValues['show_ticket_categories_depth'] . "'";
                     $dparams_cond_crit['level'] = ['<=', $decodedValues['show_ticket_categories_depth']];
                  }
                  if (isset($decodedValues['show_ticket_categories_root'])
                     && (int) $decodedValues['show_ticket_categories_root'] > 0) {
                        $sons = (new DBUtils)->getSonsOf(
                           ItilCategory::getTable(),
                           $decodedValues['show_ticket_categories_root']
                        );
                        //$sons = "'" . implode("', '", $sons) . "'";
                     $dparams['condition'] .= " AND `id` IN ('" . implode("', '", $sons) . "')";
                     $dparams_cond_crit['id'] = $sons;
                  }
                  break;

               default:
                  if (in_array($itemtype, $CFG_GLPI['ticket_types'])) {
                     $userFk = User::getForeignKeyField();
                     $groupFk = Group::getForeignKeyField();
                     $canViewAllHardware = Session::haveRight('helpdesk_hardware', pow(2, Ticket::HELPDESK_ALL_HARDWARE));
                     $canViewMyHardware = Session::haveRight('helpdesk_hardware', pow(2, Ticket::HELPDESK_MY_HARDWARE));
                     $canViewGroupHardware = Session::haveRight('show_group_hardware', '1');
                     $groups = [];
                     if ($canViewGroupHardware) {
                        $groups = $this->getMyGroups(Session::getLoginUserID());
                     }
                     if ($DB->fieldExists($itemtype::getTable(), $userFk)
                        && !$canViewAllHardware && $canViewMyHardware) {
                        $userId = $_SESSION['glpiID'];
                        $dparams['condition'] = "`$userFk`='$userId'";
                        $dparams_cond_crit[$userFk] = $userId;
                     }
                     if ($DB->fieldExists($itemtype::getTable(), $groupFk)
                        && !$canViewAllHardware && count($groups) > 0) {
                        $dparams_cond_crit = [
                           'OR' => [
                              $groupFk => $groups,
                           ] + $dparams_cond_crit
                        ];
                        $groups = implode("', '", $groups);
                        $dparams['condition'] .= " OR `$groupFk` IN ('$groups')";
                     }
                  }
            }

            // TODO remove if and the above raw queries (in $dparams['condition'])
            // when 9.3/bf compat will no be needed anymore
            if (version_compare(GLPI_VERSION, "9.4", '>=')) {
               $dparams['condition'] = $dparams_cond_crit;
            }

            $itemtype::dropdown($dparams);
         }
         echo PHP_EOL;
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeDropdown('$fieldName', '$rand');
         });");
      } else {
         $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
         if ($decodedValues === null) {
            $itemtype = $this->fields['values'];
         } else {
            $itemtype = $decodedValues['itemtype'];
         }
         $item = new $itemtype();
         $value = '';
         if ($item->getFromDB($this->value)) {
            $column = 'name';
            if ($item instanceof CommonTreeDropdown) {
               $column = 'completename';
            }
            $value = $item->fields[$column];
         }
         echo $value;
      }
   }

   public function serializeValue() {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
                  ? $value
                  : '';
   }

   public function getValueForDesign() {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function getValueForTargetText($richText) {
      $DbUtil = new DbUtils();
      $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
      if (!isset($decodedValues['itemtype'])) {
         $value = Dropdown::getDropdownName($DbUtil->getTableForItemType($this->fields['values']), $this->value);
      } else {
         $value = Dropdown::getDropdownName($DbUtil->getTableForItemType($decodedValues['itemtype']), $this->value);
      }

      return Toolbox::addslashes_deep($value);
   }

   public function getDocumentsForTarget() {
      return [];
   }

   public static function getName() {
      return _n('Dropdown', 'Dropdowns', 1);
   }

   public function isValid() {
      // If the field is required it can't be empty
      $itemtype = json_decode($this->fields['values'], true);
      $itemtype = $itemtype['itemtype'];
      $dropdown = new $itemtype();
      if ($this->isRequired() && $dropdown->isNewId($this->value)) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['dropdown_values'])) {
         if (empty($input['dropdown_values'])) {
            Session::addMessageAfterRedirect(
                  __('The field value is required:', 'formcreator') . ' ' . $input['name'],
                  false,
                  ERROR);
            return [];
         }
         $allowedDropdownValues = [];
         $stdtypes = Dropdown::getStandardDropdownItemTypes();
         foreach (Dropdown::getStandardDropdownItemTypes() as $categoryOfTypes) {
            $allowedDropdownValues = array_merge($allowedDropdownValues, array_keys($categoryOfTypes));
         }
         if (!in_array($input['dropdown_values'], $allowedDropdownValues)) {
            Session::addMessageAfterRedirect(
                  __('Invalid dropdown type:', 'formcreator') . ' ' . $input['name'],
                  false,
                  ERROR);
            return [];
         }
         $input['values'] = [
            'itemtype' => $input['dropdown_values'],
         ];
         if ($input['dropdown_values'] == ITILCategory::class) {
            $input['values']['show_ticket_categories'] = $input['show_ticket_categories'];
            if ($input['show_ticket_categories_depth'] != (int) $input['show_ticket_categories_depth']) {
               $input['values']['show_ticket_categories_depth'] = 0;
            } else {
               $input['values']['show_ticket_categories_depth'] = $input['show_ticket_categories_depth'];
            }
            $input['values']['show_ticket_categories_root'] = isset($input['show_ticket_categories_root'])
                                                              ? $input['show_ticket_categories_root']
                                                              : '';
         }
         $input['values'] = json_encode($input['values']);
         unset($input['show_ticket_categories']);
         unset($input['show_ticket_categories_depth']);
         $this->value = isset($input['dropdown_default_value']) ? $input['dropdown_default_value'] : '';
      }

      return $input;
   }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 1,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['dropdown'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

   /**
    * get groups of the current user
    *
    * @param integer $userID
    * @return array
    */
   private function getMyGroups($userID) {
      global $DB;

      // from Item_Ticket::dropdownMyDevices()
      $DbUtil = new DbUtils();
      $groupUserTable = Group_User::getTable();
      $groupTable = Group::getTable();
      $groupFk = Group::getForeignKeyField();
      $request = $DB->request([
         'SELECT' => [
            $groupUserTable => [$groupFk],
            $groupTable => ['name'],
         ],
         'FROM' => $groupUserTable,
         'LEFT JOIN' => [
            $groupTable => [
               'FKEY' => [
                  $groupTable => 'id',
                  $groupUserTable => $groupFk,
               ],
            ],
         ],
         'WHERE' => [
            $groupUser . '.id' => $userID,
         ] + $dbUtil->getEntitiesRestrictCriteria(
            $groupTable,
            '',
            $_SESSION['glpiactive_entity'],
            $_SESSION['glpiactive_entity_recursive']
         )
      ]);
      if ($result->count() === 0) {
         return [];
      }
      foreach ($result as $data) {
         $a_groups                     = $dbUtil->getAncestorsOf("glpi_groups", $data["groups_id"]);
         $a_groups[$data["groups_id"]] = $data["groups_id"];
      }
      return $a_groups;
   }

   public function equals($value) {
      $value = html_entity_decode($value);
      $itemtype = json_decode($this->fields['values'], true);
      $itemtype = $itemtype['itemtype'];
      $dropdown = new $itemtype();
      if ($dropdown->isNewId($this->value)) {
         return ($value === '');
      }
      if (!$dropdown->getFromDB($this->value)) {
         throw new PluginFormcreatorComparisonException('Item not found for comparison');
      }
      if ($dropdown instanceof CommonTreeDropdown) {
         $name = $dropdown->getField($dropdown->getCompleteNameField());
      } else {
         $name = $dropdown->getField($dropdown->getNameField());
      }
      return $name == $value;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      $value = html_entity_decode($value);
      $itemtype = $this->fields['values'];
      $dropdown = new $itemtype();
      if (!$dropdown->getFromDB($this->value)) {
         throw new PluginFormcreatorComparisonException('Item not found for comparison');
      }
      if ($dropdown instanceof CommonTreeDropdown) {
         $name = $dropdown->getField($dropdown->getCompleteNameField());
      } else {
         $name = $dropdown->getField($dropdown->getNameField());
      }
      return $name > $value;
   }

   public function lessThan($value) {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function parseAnswerValues($input) {
      $key = 'formcreator_field_' . $this->fields['id'];
      if (!isset($input[$key])) {
         $input[$key] = '0';
      } else {
         if (!is_string($input[$key])) {
            return false;
         }
      }
      $this->value = $input[$key];
      return true;
   }

   public function isAnonymousFormCompatible() {
      return false;
   }
}
