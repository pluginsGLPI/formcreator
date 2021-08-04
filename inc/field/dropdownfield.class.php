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
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace GlpiPlugin\Formcreator\Field;

use PluginFormcreatorAbstractField;
use PluginFormcreatorForm;
use Html;
use Toolbox;
use Session;
use DBUtils;
use Dropdown;
use CommonITILObject;
use CommonTreeDropdown;
use ITILCategory;
use Entity;
use User;
use Group;
use Group_User;
use Ticket;
use Search;
use SLA;
use SLM;
use OLA;
use GlpiPlugin\Formcreator\Exception\ComparisonException;

class DropdownField extends PluginFormcreatorAbstractField
{

   const ENTITY_RESTRICT_USER = 1;
   const ENTITY_RESTRICT_FORM = 2;
   const ENTITY_RESTRICT_BOTH = 3;

   public function getEnumEntityRestriction() {
      return [
         self::ENTITY_RESTRICT_USER =>  __('User', 'formcreator'),
         self::ENTITY_RESTRICT_FORM =>  __('Form', 'formcreator'),
         self::ENTITY_RESTRICT_BOTH =>  __('User and form', 'formcreator'),
      ];
   }

   public function isPrerequisites(): bool {
      $itemtype = $this->getSubItemtype();

      return class_exists($itemtype);
   }

   public function getDesignSpecializationField(): array {
      $rand = mt_rand();

      $label = '<label for="dropdown_dropdown_values' . $rand . '" id="label_dropdown_values">';
      $label .= _n('Dropdown', 'Dropdowns', 1);
      $label .= '</label>';

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      if ($decodedValues === null) {
         $itemtype = $this->question->fields['values'];
      } else {
         $itemtype = $decodedValues['itemtype'];
      }

      $root = $decodedValues['show_tree_root'] ?? Dropdown::EMPTY_VALUE;
      $maxDepth = $decodedValues['show_tree_depth'] ?? Dropdown::EMPTY_VALUE;
      $selectableRoot = $decodedValues['selectable_tree_root'] ?? '0';

      $optgroup = Dropdown::getStandardDropdownItemTypes();

      $optgroup[__('Service levels')] = [
         SLA::getType() => __("SLA", "formcreator"),
         OLA::getType() => __("OLA", "formcreator"),
      ];

      $field = '<div id="dropdown_values_field">';
      $field .= Dropdown::showFromArray('dropdown_values', $optgroup, [
         'value'               => $itemtype,
         'rand'                => $rand,
         'on_change'           => 'plugin_formcreator_changeDropdownItemtype("' . $rand . '");',
         'display_emptychoice' => true,
         'display'             => false,
         'specific_tags' => [
            'data-type'     => __CLASS__,
            'data-itemtype' => $itemtype
         ],
      ]);

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values' . $rand . '">';
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
      $additions .= '<label for="dropdown_show_ticket_categories' . $rand . '" id="label_show_ticket_categories">';
      $additions .= __('Show ticket categories', 'formcreator');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $ticketCategoriesOptions = [
         'request'  => __('Request categories', 'formcreator'),
         'incident' => __('Incident categories', 'formcreator'),
         'both'     => __('Request categories', 'formcreator') . " + " . __('Incident categories', 'formcreator'),
         'change'   => __('Change categories', 'formcreator'),
         'all'      => __('All'),
      ];
      $additions .= Dropdown::showFromArray('show_ticket_categories', $ticketCategoriesOptions, [
         'rand'  => $rand,
         'value' => isset($decodedValues['show_ticket_categories'])
            ? $decodedValues['show_ticket_categories']
            : 'both',
         'display' => false,
      ]);
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= "<input id='commonTreeDropdownRoot' type='hidden' value='$root'>";
      $additions .= "<input id='commonTreeDropdownMaxDepth' type='hidden' value='$maxDepth'>";
      $additions .= "<input id='commonTreeDropdownSelectableRoot' type='hidden' value='$selectableRoot'>";
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '</tr>';

      $additions .= '<tr class="plugin_formcreator_question_specific plugin_formcreator_dropdown">';
      // This row will be generated by an AJAX request
      $additions .= '</tr>';
      $additions .= Html::scriptBlock("plugin_formcreator_changeDropdownItemtype($rand);");

      $additions .= $this->getEntityRestrictSettiing();

      // Service level specific
      $additions .= '<tr class="plugin_formcreator_question_specific plugin_formcreator_dropdown_service_level">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_show_service_level_types' . $rand . '" id="label_show_service_level_types">';
      $additions .= __('Type', 'formcreator');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $serviceLevelTypes = [
         SLM::TTO  => __('Time to own', 'formcreator'),
         SLM::TTR  => __('Time to resolve', 'formcreator'),
      ];
      $additions .= dropdown::showFromArray('show_service_level_types', $serviceLevelTypes, [
         'rand'  => $rand,
         'value' => isset($decodedValues['show_service_level_types'])
            ? $decodedValues['show_service_level_types']
            : SLM::TTO,
         'display' => false,
      ]);
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</tr>';

      $common = parent::getDesignSpecializationField();
      $additions .= $common['additions'];

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => true,
         'may_be_required' => true,
      ];
   }

   public function buildParams($rand = null) {
      global $DB, $CFG_GLPI;

      $id        = $this->question->getID();
      $fieldName = 'formcreator_field_' . $id;
      $itemtype = $this->getSubItemtype();

      $form = new PluginFormcreatorForm();
      $form->getFromDBByQuestion($this->getQuestion());
      $dparams = [
         'name'     => $fieldName,
         'value'    => $this->value,
         'display'  => false,
         'comments' => false,
         'entity'   => $this->getEntityRestriction(),
         //'entity_sons' => (bool) $form->isRecursive(),
         'displaywith' => ['id'],
      ];

      if ($rand !== null) {
         $dparams['rand'] = $rand;
      }

      $dparams_cond_crit = [];
      $decodedValues = json_decode(
         $this->question->fields['values'],
         JSON_OBJECT_AS_ARRAY
      );

      switch ($itemtype) {
         case SLA::class:
         case OLA::class:
            // Apply service level type if defined
            if (isset($decodedValues['show_service_level_types'])) {
               $dparams_cond_crit['type'] = $decodedValues['show_service_level_types'];
            }
            break;

         case Entity::class:
         case Document::class:
            unset($dparams['entity']);

         case User::class:
            $dparams['right'] = 'all';
            break;

         case ITILCategory::class:
            if (Session::getCurrentInterface() == 'helpdesk') {
               $dparams_cond_crit['is_helpdeskvisible'] = 1;
            }
            switch ($decodedValues['show_ticket_categories']) {
               case 'request':
                  $dparams_cond_crit['is_request'] = 1;
                  break;
               case 'incident':
                  $dparams_cond_crit['is_incident'] = 1;
                  break;
               case 'both':
                  $dparams_cond_crit['OR'] = [
                     'is_incident' => 1,
                     'is_request'  => 1,
                  ];
                  break;
               case 'change':
                  $dparams_cond_crit['is_change'] = 1;
                  break;
               case 'all':
                  $dparams_cond_crit['OR'] = [
                     'is_change'   => 1,
                     'is_incident' => 1,
                     'is_request'  => 1,
                  ];
                  break;
            }
            break;

         default:
            $assignableToTicket = in_array($itemtype, $CFG_GLPI['ticket_types']);
            if (Session::getLoginUserID()) {
               // Restrict assignable types to current profile's settings
               $assignableToTicket = CommonITILObject::isPossibleToAssignType($itemtype);
            }
            if ($assignableToTicket) {
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
                  && !$canViewAllHardware && $canViewMyHardware
               ) {
                  $userId = Session::getLoginUserID();
                  $dparams_cond_crit[$userFk] = $userId;
               }
               if ($DB->fieldExists($itemtype::getTable(), $groupFk)
                  && !$canViewAllHardware && count($groups) > 0
               ) {
                  $dparams_cond_crit = [
                     'OR' => [
                        $groupFk => $groups,
                     ] + $dparams_cond_crit
                  ];
               }
               // Check if helpdesk availability is fine tunable on a per item basis
               if ($DB->fieldExists($itemtype::getTable(), 'is_helpdesk_visible')) {
                  $dparams_cond_crit[] = [
                     'is_helpdesk_visible' => '1',
                  ];
               }
            }
      }

      // Set specific root if defined (CommonTreeDropdown)
      $baseLevel = 0;
      if (isset($decodedValues['show_tree_root'])
         && (int) $decodedValues['show_tree_root'] > 0
      ) {
         $sons = (new DBUtils)->getSonsOf(
            $itemtype::getTable(),
            $decodedValues['show_tree_root']
         );
         if (!isset($decodedValues['selectable_tree_root']) || $decodedValues['selectable_tree_root'] == '0') {
            unset($sons[$decodedValues['show_tree_root']]);
         }

         $dparams_cond_crit[$itemtype::getTable() . '.id'] = $sons;
         $rootItem = new $itemtype();
         if ($rootItem->getFromDB($decodedValues['show_tree_root'])) {
            $baseLevel = $rootItem->fields['level'];
         }

      }

      // Apply max depth if defined (CommonTreeDropdown)
      if (isset($decodedValues['show_tree_depth'])
         && $decodedValues['show_tree_depth'] > 0
      ) {
         $dparams_cond_crit['level'] = ['<=', $decodedValues['show_tree_depth'] + $baseLevel];
      }

      $dparams['condition'] = $dparams_cond_crit;

      $dparams['display_emptychoice'] = false;
      if ($itemtype != Entity::class) {
         $dparams['display_emptychoice'] = ($this->question->fields['show_empty'] !== '0');
      } else {
         if ($this->question->fields['show_empty'] !== '0') {
            $dparams['toadd'] = [
               -1 => Dropdown::EMPTY_VALUE,
            ];
         }
      }

      $emptyItem = new $itemtype();
      $emptyItem->getEmpty();
      if (isset($emptyItem->fields['serial'])) {
         $dparams['displaywith'][] = 'serial';
      }
      if (isset($emptyItem->fields['otherserial'])) {
         $dparams['displaywith'][] = 'otherserial';
      }

      return $dparams;
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      $itemtype = $this->getSubItemtype();
      if (!$canEdit) {
         $item = new $itemtype();
         $value = '';
         if ($item->getFromDB($this->value)) {
            $column = 'name';
            if ($item instanceof CommonTreeDropdown) {
               $column = 'completename';
            }
            $value = $item->fields[$column];
         }

         return $value;
      }

      $html        = '';
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      if (!empty($this->question->fields['values'])) {
         $dparams = $this->buildParams($rand);
         $dparams['display'] = false;
         $dparams['_idor_token'] = Session::getNewIDORToken($itemtype);
         $html .= $itemtype::dropdown($dparams);
      }
      $html .= PHP_EOL;
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeDropdown('$fieldName', '$rand');
      });");

      return $html;
   }

   public function serializeValue(): string {
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

   public function getValueForDesign(): string {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function getValueForTargetText($domain, $richText): ?string {
      $DbUtil = new DbUtils();
      $itemtype = $this->getSubItemtype();
      if ($itemtype == User::class) {
         $value = (new DBUtils())->getUserName($this->value);
      } else {
         $value = Dropdown::getDropdownName($DbUtil->getTableForItemType($itemtype), $this->value);
      }
      return $value;
   }

   public function moveUploads() {
   }

   public function getDocumentsForTarget(): array {
      return [];
   }

   public static function getName(): string {
      return _n('Dropdown', 'Dropdowns', 1);
   }

   public function isValid(): bool {
      // If the field is required it can't be empty
      $itemtype = json_decode($this->question->fields['values'], true);
      if ($itemtype === null) {
         $itemtype = $this->question->fields['values'];
      } else {
         $itemtype = $itemtype['itemtype'];
      }
      $dropdown = new $itemtype();
      if ($this->isRequired() && $dropdown->isNewId($this->value)) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR
         );
         return false;
      }

      // All is OK
      return $this->isValidValue($this->value);
   }

   public function isValidValue($value): bool {
      if ($value == '0') {
         return true;
      }
      $itemtype = json_decode($this->question->fields['values'], true);
      if ($itemtype === null) {
         $itemtype = $this->question->fields['values'];
      } else {
         $itemtype = $itemtype['itemtype'];
      }
      $dropdown = new $itemtype();

      $isValid = $dropdown->getFromDB($value);

      if (!$isValid) {
         Session::addMessageAfterRedirect(
            __('Invalid value for ', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR
         );
      }

      return $isValid;
   }

   public function prepareQuestionInputForSave($input) {
      if (!isset($input['dropdown_values']) || empty($input['dropdown_values'])) {
         Session::addMessageAfterRedirect(
            sprintf(__('The field value is required: %s', 'formcreator'), $input['name']),
            false,
            ERROR
         );
         return [];
      }
      $allowedDropdownValues = [];
      $stdtypes = Dropdown::getStandardDropdownItemTypes();
      foreach ($stdtypes as $categoryOfTypes) {
         $allowedDropdownValues = array_merge($allowedDropdownValues, array_keys($categoryOfTypes));
      }
      $allowedDropdownValues[] = SLA::getType();
      $allowedDropdownValues[] = OLA::getType();

      if (!in_array($input['dropdown_values'], $allowedDropdownValues)) {
         Session::addMessageAfterRedirect(
            sprintf(__('Invalid dropdown type: %s', 'formcreator'), $input['name']),
            false,
            ERROR
         );
         return [];
      }
      $itemtype = $input['dropdown_values'];
      $input['values'] = [
         'itemtype' => $itemtype,
      ];

      // Params for CommonTreeDropdown fields
      if (is_a($itemtype, CommonTreeDropdown::class, true)) {
         // Specific param for ITILCategory
         if ($itemtype == ITILCategory::class) {
            // Set default for depth setting
            if (!isset($input['show_ticket_categories'])) {
               $input['show_ticket_categories'] = 'all';
            }
            $input['values']['show_ticket_categories'] = $input['show_ticket_categories'];
         }

         // Set default for depth setting
         $input['values']['show_tree_depth'] = (string) (int) ($input['show_tree_depth'] ?? '-1');
         $input['values']['show_tree_root'] = ($input['show_tree_root'] ?? '');
         $input['values']['selectable_tree_root'] = ($input['selectable_tree_root'] ?? '0');
      } else if ($input['dropdown_values'] == SLA::getType()
         || $input['dropdown_values'] == OLA::getType()
      ) {
         $input['values']['show_service_level_types'] = $input['show_service_level_types'];
         unset($input['show_service_level_types']);
      }

      // Params for entity restrictables itemtypes
      $itemtype = $input['dropdown_values'];
      if ((new $itemtype)->isEntityAssign()) {
         $input['values']['entity_restrict'] = $input['entity_restrict'] ?? self::ENTITY_RESTRICT_FORM;
      }
      unset($input['entity_restrict']);

      $input['values'] = json_encode($input['values']);

      $input['default_values'] = isset($input['dropdown_default_value']) ? $input['dropdown_default_value'] : '';
      unset($input['dropdown_default_value']);
      unset($input['show_ticket_categories']);
      unset($input['show_tree_depth']);
      unset($input['show_tree_root']);
      unset($input['selectable_tree_root']);
      unset($input['dropdown_values']);

      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public static function canRequire(): bool {
      return true;
   }

   /**
    * get groups of the current user
    *
    * @param int $userID
    * @return array
    */
   private function getMyGroups($userID) {
      global $DB;

      // from Item_Ticket::dropdownMyDevices()
      $dbUtil = new DbUtils();
      $groupUserTable = Group_User::getTable();
      $groupTable = Group::getTable();
      $groupFk = Group::getForeignKeyField();
      $result = $DB->request([
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
            $groupUserTable . '.users_id' => $userID,
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

   public function equals($value): bool {
      $value = html_entity_decode($value);
      $itemtype = json_decode($this->question->fields['values'], true);
      $itemtype = $itemtype['itemtype'];
      $dropdown = new $itemtype();
      if ($dropdown->isNewId($this->value)) {
         return ($value === '');
      }
      if (!$dropdown->getFromDB($this->value)) {
         throw new ComparisonException('Item not found for comparison');
      }
      if ($dropdown instanceof CommonTreeDropdown) {
         $name = $dropdown->getField($dropdown->getCompleteNameField());
      } else {
         $name = $dropdown->getField($dropdown->getNameField());
      }
      return $name == $value;
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      $value = html_entity_decode($value);
      $itemtype = $this->question->fields['values'];
      $dropdown = new $itemtype();
      if (!$dropdown->getFromDB($this->value)) {
         throw new ComparisonException('Item not found for comparison');
      }
      if ($dropdown instanceof CommonTreeDropdown) {
         $name = $dropdown->getField($dropdown->getCompleteNameField());
      } else {
         $name = $dropdown->getField($dropdown->getNameField());
      }
      return $name > $value;
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      $value = html_entity_decode($value);
      $itemtype = $this->getSubItemtype($this->question->fields['values']);
      $dropdown = new $itemtype();
      if (!$dropdown->getFromDB($this->value)) {
         throw new ComparisonException('Item not found for comparison');
      }
      if ($dropdown instanceof CommonTreeDropdown) {
         $fieldValue = $dropdown->getField($dropdown->getCompleteNameField());
      } else {
         $fieldValue = $dropdown->getField($dropdown->getNameField());
      }
      return preg_match($value, Toolbox::stripslashes_deep($fieldValue)) ? true : false;
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
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

   public function isAnonymousFormCompatible(): bool {
      return false;
   }

   /**
    * Check for object properties placeholder to commpute.
    * The expected format is ##answer_X.search_option_english_label##
    *
    * We use search option to be able to access data that may be outside
    * the given object in a generic way (e.g. email adresses for user,
    * this is data that is not stored in the user table. The searchOption
    * will give us the details on how to retrieve it).
    *
    * We also have a direct link between each searchOptions and their
    * labels that also us to use the label in the placeholder.
    * The user only need to look at his search menu to find the available
    * fields.
    *
    * Since we look for a searchOption by its name, it is not impossible
    * to find duplicates (they will usually by in differents groups in the
    * search dropdown).
    * For now we will use the first result.
    * An improvement would be to allow the user to specify the group in
    * the placeholder :
    * ##answer_X.search_option_group.search_option_english_label##
    * If not specified, search_option_group would be the default "common"
    * group.
    *
    * @param PluginFormCreatorAnswer   $answer
    * @param string                    $content
    *
    * @return string
    */
   public function parseObjectProperties(
      $answer,
      $content
   ) {
      global $TRANSLATE;

      // This feature is not available for TagField
      if (static::class == TagField::class) {
         return $content;
      }

      // Get ID from question
      // $questionID = $question->fields['id'];
      $questionID = $this->getQuestion()->getID();

      // We need english locale to search searchOptions by name
      $oldLocale = $TRANSLATE->getLocale();
      $TRANSLATE->setLocale("en_GB");

      // Load target item from DB
      // $itemtype = $question->getField('values');
      $itemtype = $this->question->fields['values'];

      // Itemtype is stored in plaintext for GlpiselectField and in
      // json for DropdownField
      $json = json_decode($itemtype);

      if ($json) {
         $itemtype = $json->itemtype;
      }

      // Safe check
      if (empty($itemtype) || !class_exists($itemtype)) {
         return $content;
      }

      $item = new $itemtype;
      $item->getFromDB($answer);

      // Search for placeholders
      $matches = [];
      $regex = "/##answer_$questionID\.(?<property>[a-zA-Z0-9_.]+)##/";
      preg_match_all($regex, $content, $matches);

      // For each placeholder found
      foreach ($matches["property"] as $property) {
         $placeholder = "##answer_$questionID.$property##";
         // Convert Property_Name to Property Name
         $property = str_replace("_", " ", $property);
         $searchOption = $item->getSearchOptionByField("name", $property);

         // Execute search
         $data = Search::prepareDatasForSearch(get_class($item), [
            'criteria' => [
               [
                  'field'      => $searchOption['id'],
                  'searchtype' => "contains",
                  'value'      => "",
               ],
               [
                  'field'      => 2,
                  'searchtype' => "equals",
                  'value'      => $answer,
               ]
            ]
         ]);
         Search::constructSQL($data);
         Search::constructData($data);

         // Handle search result, there may be multiple values
         $propertyValue = "";
         foreach ($data['data']['rows'] as $row) {
            $targetKey = get_class($item) . "_" . $searchOption['id'];
            // Add each result
            for ($i = 0; $i < $row[$targetKey]['count']; $i++) {
               $propertyValue .= $row[$targetKey][$i]['name'];
               if ($i + 1 < $row[$targetKey]['count']) {
                  $propertyValue .= ", ";
               }
            }
         }

         // Replace placeholder in content
         $content = str_replace(
            $placeholder,
            Toolbox::addslashes_deep($propertyValue),
            $content
         );
      }
      // Put the old locales on succes or if an expection was thrown
      $TRANSLATE->setLocale($oldLocale);
      return $content;
   }

   public function getHtmlIcon() {
      return '<i class="fas fa-caret-square-down" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return true;
   }

   /**
    * Get the itemtype of the item to show
    *
    * @return string
    */
   public function getSubItemtype() {
      return self::getSubItemtypeForValues($this->question->fields['values']);
   }

   /**
    * Get the itemtype of the item to show for the given values
    *
    * @param string $values json or raw string
    *
    * @return string
    */
   public static function getSubItemtypeForValues($values) {
      $decodedValues = json_decode($values, JSON_OBJECT_AS_ARRAY);
      if ($decodedValues === null) {
         return $values;
      }

      return $decodedValues['itemtype'];
   }

   /**
    * get HTML code to show entity restriction policy
    * @return string HTML code
    */
   protected function getEntityRestrictSettiing() {
      $restrictionPolicy = self::ENTITY_RESTRICT_FORM;
      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      if (isset($decodedValues['entity_restrict'])) {
         $restrictionPolicy = $decodedValues['entity_restrict'];
      }

      $html = '';

      $html .= '<tr class="plugin_formcreator_question_specific plugin_formcreator_entity_assignable">';
      $html .= '<td>';
      $html .= '<label for="entity_restrict">' . __('Entity restriction', 'formcreator') . '</label>';
      $html .= '</td>';
      $html .= '<td>';
      $settings = $this->getEnumEntityRestriction();
      $html .= Dropdown::showFromArray(
         'entity_restrict',
         $settings,
         ['display' => false, 'value' => $restrictionPolicy]
      );
      $html .= '</td>';
      $html .= '</tr>';
      return $html;
   }

   /**
    * Get the entity restriction for item availability in the field
    *
    * @return void
    */
   protected function getEntityRestriction() {
      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $restrictionPolicy = self::ENTITY_RESTRICT_FORM;
      if (isset($decodedValues['entity_restrict'])) {
         $restrictionPolicy = $decodedValues['entity_restrict'];
      }

      switch ($restrictionPolicy) {
         case self::ENTITY_RESTRICT_FORM:
            $form = new PluginFormcreatorForm();
            $form->getFromDBByQuestion($this->getQuestion());
            $formEntities = [$form->fields['entities_id']];
            if ($form->fields['is_recursive']) {
               $formEntities = $formEntities + (new DBUtils())->getSonsof(Entity::getTable(), $form->fields['entities_id']);
            }
            return $formEntities;
            break;

         case self::ENTITY_RESTRICT_BOTH:
            $form = new PluginFormcreatorForm();
            $form->getFromDBByQuestion($this->getQuestion());
            $formEntities = [$form->fields['entities_id']];
            if ($form->fields['is_recursive']) {
               $formEntities = $formEntities + (new DBUtils())->getSonsof(Entity::getTable(), $form->fields['entities_id']);
            }
            // If no entityes are in common, the result will be empty
            return array_intersect_key($_SESSION['glpiactiveentities'], $formEntities);
            break;
      }

      return $_SESSION['glpiactiveentities'];
   }
}
