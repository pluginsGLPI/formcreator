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
                        'rand'     => $rand];

            switch ($itemtype) {
               case User::class:
                  $dparams['right'] = 'all';
                  break;

               case ITILCategory::class:
                  $dparams['condition'] = '1';
                  if (isset ($_SESSION['glpiactiveprofile']['interface'])
                     && $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {
                     $dparams['condition'] .= " AND `is_helpdeskvisible` = '1'";
                  }
                  switch ($decodedValues['show_ticket_categories']) {
                     case 'request':
                        $dparams['condition'] .= " AND `is_request` = '1'";
                        break;
                     case 'incident':
                        $dparams['condition'] .= " AND `is_incident` = '1'";
                        break;
                     case 'both':
                        $dparams['condition'] .= " AND `is_incident` = '1' OR `is_request` = '1' ";
                        break;
                     case 'change':
                        $dparams['condition'] .= " AND `is_change` = '1'";
                        break;
                     case 'all':
                        $dparams['condition'] .= " AND `is_change` = '1' OR `is_incident` = '1' OR  `is_request` = '1'";
                        break;
                  }
                  if (isset($decodedValues['show_ticket_categories_depth'])
                     && $decodedValues['show_ticket_categories_depth'] > 0) {
                     $dparams['condition'] .= " AND `level` <= '" . $decodedValues['show_ticket_categories_depth'] . "'";
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
                     }
                     if ($DB->fieldExists($itemtype::getTable(), $groupFk)
                        && !$canViewAllHardware && count($groups) > 0) {
                        $groups = implode("', '", $groups);
                        $dparams['condition'] .= " OR `$groupFk` IN ('$groups')";
                     }
                  }
            }

            $itemtype::dropdown($dparams);
         }
         echo PHP_EOL;
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeDropdown('$fieldName', '$rand');
         });");
      } else {
         echo $this->value;
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

   public function getValueForTargetText() {
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
      return [];;
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

   private function getMyGroups($userID) {
      global $DB;

      // from Item_Ticket::dropdownMyDevices()
      $DbUtil = new DbUtils();
      $group_where = "";
      $query       = "SELECT `glpi_groups_users`.`groups_id`, `glpi_groups`.`name`
                        FROM `glpi_groups_users`
                        LEFT JOIN `glpi_groups`
                        ON (`glpi_groups`.`id` = `glpi_groups_users`.`groups_id`)
                        WHERE `glpi_groups_users`.`users_id` = '$userID' " .
                              $DbUtil->getEntitiesRestrictRequest(
                                 "AND",
                                 "glpi_groups",
                                 "",
                                 $_SESSION['glpiactive_entity'],
                                 $_SESSION['glpiactive_entity_recursive']);
      $result  = $DB->query($query);

      $first   = true;
      $devices = [];
      if ($DB->numrows($result) === 0) {
         return [];
      }
      while ($data = $DB->fetch_assoc($result)) {
         if ($first) {
            $first = false;
         } else {
            $group_where .= " OR ";
         }
         $a_groups                     = getAncestorsOf("glpi_groups", $data["groups_id"]);
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
      if (is_a($dropdown, CommonTreeDropdown::class)) {
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
      if (is_a($dropdown, CommonTreeDropdown::class)) {
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
      if (!is_string($input[$key])) {
         return false;
      }

       $this->value = $input[$key];
       return true;
   }

   public function isAnonymousFormCompatible() {
      return false;
   }
}
