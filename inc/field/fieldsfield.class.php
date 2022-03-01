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

use PluginFieldsContainer;
use PluginFormcreatorAbstractField;
use Dropdown;
use DbUtils;
use Plugin;
use Ticket;
use Change;
use Problem;
use Session;
use Html;
use OperatingSystem;
use PluginFieldsDropdown;
use PluginFieldsField;
use PluginFormcreatorQuestion;
use User;

class FieldsField extends PluginFormcreatorAbstractField
{

   /** @var PluginFieldsField $field */
   public $field = null;

   /**
    *
    * @param array $question PluginFormcreatorQuestion instance
    */
    public function __construct(PluginFormcreatorQuestion $question) {
      $this->question  = $question;

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $field_name = $decodedValues['dropdown_fields_field'] ?? '';
      $fieldObj = new PluginFieldsField();
      if ($fieldObj->getFromDBByCrit(['name' => $field_name])){
         $this->field  = $fieldObj;
      }
   }

   public function isPrerequisites(): bool {
      return  (!(new Plugin())->isActivated('field'));
   }

   public static function getFieldsFromBlock($block_id): array {
      global $DB;
      $optgroup = [];

      // Get all fields for block (except header)
      $request = [
         'SELECT' => ['name', 'label'],
         'FROM'   => PluginFieldsField::getTable(),
         'WHERE'  => [
            'AND' => [
               'plugin_fields_containers_id'      => $block_id,
               'is_active' => true,
               ['NOT' => ['type' => 'header']]
            ]
         ]
      ];

      $iterator = $DB->request($request);
      foreach ($iterator as $row) {
         $optgroup[$row['name']] = $row['label'];
      }

      return $optgroup;
   }

   public function getBlocks() {
      global $DB;
      $optgroup = [];

      // Get all block for :
      // Ticket / Change / Problem
      // Activated
      // Type -> dom
      $dbUtils = new DbUtils();
      $entityRestrict = $dbUtils->getEntitiesRestrictCriteria(PluginFieldsContainer::getTable(), "", "", true, false);
      if (count($entityRestrict)) {
         $entityRestrict = [$entityRestrict];
      }

      $request = [
         'SELECT' => ['id', 'label'],
         'FROM'   => PluginFieldsContainer::getTable(),
         'WHERE'  => [
            'AND' => [
               'OR' => [
                  ['itemtypes' => ['LIKE', '%\"'.Ticket::getType().'\"%']],
                  ['itemtypes' => ['LIKE', '%\"'.Change::getType().'\"%']],
                  ['itemtypes' => ['LIKE', '%\"'.Problem::getType().'\"%']],
               ],
               'type'      => 'dom',
               'is_active' => true,
               ]
               + $entityRestrict,
         ]
      ];

      $iterator = $DB->request($request);
      foreach ($iterator as $row) {
         $optgroup[$row['id']] = $row['label'];
      }

      return $optgroup;
   }

   public function getDesignSpecializationField(): array {

      if ((new Plugin())->isActivated('field')) {
         //Plugin PluginFieldsContainer not available
         $label = '';
         $field = '';
         $additions = '';

         return [
            'label' => $label,
            'field' => $field,
            'additions' => $additions,
            'may_be_empty' => false,
            'may_be_required' => static::canRequire(),
         ];
      }

      $rand = mt_rand();
      $label = '<label for="dropdown_blocks_field' . $rand . '" id="label_dropdown_values">';
      $label .= __("Block", "fields");
      $label .= '</label>';

      $optgroup = $this->getBlocks();
      $itemtype = $this->question->fields['itemtype'];

      array_unshift($optgroup, '---');
      $field = Dropdown::showFromArray('blocks_field', $optgroup, [
         'value'     => $itemtype,
         'rand'      => $rand,
         'on_change' => 'plugin_formcreator_changePluginFieldBlock();',
         'display'   => false,
      ]);

      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_fields_field' . $rand . '">';
      $additions .= __("Field", "fields");
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td id="dropdown_fields_field">';
      $additions .= '</td>';
      $additions .= '</tr>';
      $additions .= Html::scriptBlock("plugin_formcreator_changePluginFieldBlock($rand);");

      $additions .= '<tr class="plugin_formcreator_question_specific plugin_formcreator_dropdown">';
      // This row will be generated by an AJAX request
      $additions .= '</tr>';

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => false,
         'may_be_required' => static::canRequire(),
      ];
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      if ((new Plugin())->isActivated('field')) {
         // Plugin field not available
         return '';
      }

      $html         = '';
      $html .= $this->prepareHtmlField( $canEdit, $this->value);
      $html .= Html::hidden('c_id', ['value' => $this->question->fields['itemtype']]);
      return $html;
   }

   function prepareHtmlField($canedit = true, $value = '') {

      if (empty($this->field->fields)) {
         return false;
      }

      //compute default values
      if(!empty($value)){
         $field['value'] = $value;
      } else {
         //get default value
         if ($this->field->fields['default_value'] !== "") {
            $value = $this->field->fields['default_value'];
            // shortcut for date/datetime
            if (in_array($this->field->fields['type'], ['date', 'datetime'])
                  && $value == 'now') {
               $value = $_SESSION["glpi_currenttime"];
            }
         }
         $field['value'] = $value;
      }

      $html = "";
      $readonly = ($this->field->fields['is_readonly'] || !$canedit);
      $this->question->fields['required'] = $this->field->fields['mandatory'];

      switch ($this->field->fields['type']) {
         case 'number':
         case 'text':
            $value = Html::cleanInputText($value);
            if ($canedit && !$readonly) {
               $html.= Html::input($this->field->fields['name'], ['value' => $value]);
            } else {
               $html.= $value;
            }
            break;
         case 'url':
            $value = Html::cleanInputText($value);
            if ($canedit && !$readonly) {
               $html.= Html::input($this->field->fields['name'], ['value' => $value]);
               if ($value != '') {
                  $html .= "<a target=\"_blank\" href=\"$value\">" . __('show', 'fields') . "</a>";
               }
            } else {
               $html .= "<a target=\"_blank\" href=\"$value\">$value</a>";
            }
            break;
         case 'textarea':
            if ($canedit && !$readonly) {
               $html.= Html::textarea([
                  'name'    => $this->field->fields['name'],
                  'value'   => $value,
                  'cols'    => 45,
                  'rows'    => 4,
                  'display' => false,
               ]);
            } else {
               $html.= nl2br($value);
            }
            break;
         case 'dropdown':
            if ($canedit && !$readonly) {
               if (strpos($this->field->fields['name'], "dropdowns_id") !== false) {
                  $dropdown_itemtype = getItemTypeForTable(
                                       getTableNameForForeignKeyField($this->field->fields['name']));
               } else {
                  $dropdown_itemtype = PluginFieldsDropdown::getClassname($this->field->fields['name']);
               }
               $html.= Dropdown::show($dropdown_itemtype,
                                       ['value'   => $value,
                                       'entity'  => $_SESSION['glpiactiveentities'],
                                       'display' => false]);
            } else {
               $dropdown_table = "glpi_plugin_fields_".$this->field->fields['name']."dropdowns";
               $html.= Dropdown::getDropdownName($dropdown_table, $value);
            }

            break;
         case 'yesno':
            if ($canedit && !$readonly) {
               $html.= Dropdown::showYesNo($this->field->fields['name'], $value, -1, ['display' => false]);
            } else {
               $html.= Dropdown::getYesNo($value);
            }
            break;
         case 'date':
            if ($canedit && !$readonly) {
               $html.= Html::showDateField($this->field->fields['name'], ['value'   => $value,
                                                            'display' => false]);
            } else {
               $html.= Html::convDate($value);
            }
            break;
         case 'datetime':
            if ($canedit && !$readonly) {
               $html.= Html::showDateTimeField($this->field->fields['name'], ['value'   => $value,
                                                                  'display' => false]);
            } else {
               $html.= Html::convDateTime($value);
            }
            break;
         case 'dropdownuser':

            if ($canedit && !$readonly) {
               $html.= User::dropdown(['name'      => $this->field->fields['name'],
                                       'value'     => $value,
                                       'entity'    => -1,
                                       'right'     => 'all',
                                       'display'   => false,
                                       'condition' => ['is_active' => 1, 'is_deleted' => 0]]);
            } else {
               $showuserlink = 0;
               if (Session::haveRight('user', READ)) {
                  $showuserlink = 1;
               }
               $html.= getUserName($value, $showuserlink);
            }
            break;
         case 'dropdownoperatingsystems':
            if ($canedit && !$readonly) {
               $html.= OperatingSystem::dropdown(['name'      => $this->field->fields['name'],
                                       'value'     => $value,
                                       'entity'    => -1,
                                       'right'     => 'all',
                                       'display'   => false//,
                                       /*'condition' => 'is_active=1 && is_deleted=0'*/]);
            } else {
               $os = new OperatingSystem();
               $os->getFromDB($value);
               $html.= $os->fields['name'];
            }
         }

      unset($_SESSION['plugin']['fields']['values_sent']);
      return $html;
   }

   public function serializeValue(): string {
      return $this->value;
   }

   public function getRawValue() {
      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = $value;
   }

   public function getValueForDesign(): string {
      return "";
   }

   public function getValueForTargetText($domain, $richText): ?string {
      return "";
   }

   public function getValueForApi(): string{
      return "";
   }

   public function isValidValue($value): bool {

      if (!is_null($this->field)){

         //check data type for input number / url
         $valid = true;
         if ($this->field->fields['type'] == 'number' && !empty($this->value) && !is_numeric($this->value)) {
            $number_errors[] = $this->field->fields['label'];
            $valid = false;
         } else if ($this->field->fields['type'] == 'url' && !empty($this->value)) {
            if (filter_var($this->value, FILTER_VALIDATE_URL) === false) {
               $url_errors[] = $this->field->fields['label'];
               $valid = false;
            }
         }

         if (!empty($number_errors)) {
            Session::AddMessageAfterRedirect(__("Some numeric fields contains non numeric values", "fields").
                                             " : ".implode(', ', $number_errors), false, ERROR);
         }

         if (!empty($url_errors)) {
            Session::AddMessageAfterRedirect(__("Some URL fields contains invalid links", "fields").
                                             " : ".implode(', ', $url_errors), false, ERROR);
         }

         if(!$valid) {
            return false;
         }

         //All is OK
         return true;
      }
   }

   public function isValid(): bool {
      if (!is_null($this->field)){
         // If the field is required it can't be empty
         if ($this->field->fields['mandatory'] && $this->value == '') {
            Session::addMessageAfterRedirect(
               __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
               false,
               ERROR
            );
            return false;
         }
         return $this->isValidValue($this->value);
      }

      // All is OK
      return true;
   }

   public function moveUploads() {

   }

   public function getDocumentsForTarget(): array {
      return [];
   }

   public function prepareQuestionInputForSave($input) {
      if (!isset($input['blocks_field']) || empty($input['blocks_field'])) {
         Session::addMessageAfterRedirect(
            __('The field value is required:', 'formcreator') . ' ' . $input['name'],
            false,
            ERROR
         );
         return [];
      }

      $itemtype = $input['blocks_field'];
      $input['itemtype'] = $itemtype;
      $input['values'] = [];

      $input['values']['dropdown_fields_field'] = $input['dropdown_fields_field'] ;
      unset($input['dropdown_fields_field']);

      $input['values'] = json_encode($input['values']);

      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $field_name = $decodedValues['dropdown_fields_field'] ?? '';
      $dropdown_field_name = "plugin_fields_" . $decodedValues['dropdown_fields_field'] . "dropdowns_id" ?? '';
      $value = '';

      if (isset($input[$field_name])) {
         $value = $input[$field_name];
      } else if (isset($input[$dropdown_field_name])) {
         $value = $input[$dropdown_field_name];
      } else {
         $key = 'formcreator_field_' . $this->question->getID();
         if (isset($input[$key])) {
            $value = $input[$key];
         }
      }

      $this->value = $value;
      return true;
   }

   public static function getName(): string {
      return __("Additionnal fields", "fields");
   }

   public static function canRequire(): bool {
      return true;
   }

   public function equals($value): bool {
      return true;
   }

   public function notEquals($value): bool {
      return true;
   }

   public function greaterThan($value): bool {
      return true;
   }

   public function lessThan($value): bool {
      return true;
   }

   public function regex($value): bool {
      return true;
   }

   public function isPublicFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon() {
      return '<i class="fa-fw fas fa-tasks" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return true;
   }

}
