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
use PluginFormcreatorForm;
use Dropdown;
use DbUtils;
use Plugin;
use Session;
use PluginFormcreatorFormAnswer;
use Html;
use OperatingSystem;
use PluginFieldsDropdown;
use PluginFieldsField;
use Toolbox;
use User;

use GlpiPlugin\Formcreator\Exception\ComparisonException;
use Glpi\Application\View\TemplateRenderer;
class FieldsField extends PluginFormcreatorAbstractField
{

   /** @var PluginFieldsField $field */
   public $field = null;

   /**
    * Get the additional field object lined to this formcreator field
    *
    * @return null|PluginFieldsField
    */
   public function getField(): ?PluginFieldsField {
      if ($this->field === null && isset($this->question->fields['values'])) {
         $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
         $field_name = $decodedValues['dropdown_fields_field'] ?? '';
         $fieldObj = new PluginFieldsField();
         if ($fieldObj->getFromDBByCrit(['name' => $field_name])) {
            $this->field = $fieldObj;
         }
      }
      return $this->field;
   }

   public function isPrerequisites(): bool {
      return (new Plugin())->isActivated('fields');
   }

   public static function getFieldsFromBlock($block_id): array {

      global $DB;
      $optgroup = [];

      if ($block_id == 0) {
         return $optgroup;
      }

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

   public function getBlocks(): array {
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

      $itemtypes = PluginFormcreatorForm::getTargetTypes();
      $itemtypesCriteria = [];
      foreach ($itemtypes as $targetType) {
         $itemtype = $targetType::getTargetItemtypeName();
         $itemtypesCriteria[] = [
            'itemtypes' => ['LIKE', '%\"'.$itemtype.'\"%']
         ];
      }
      $request = [
         'SELECT' => ['id', 'label'],
         'FROM'   => PluginFieldsContainer::getTable(),
         'WHERE'  => [
            'AND' => [
               'OR' => $itemtypesCriteria,
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

   public function showForm(array $options): void {
      if (!\Plugin::isPluginActive('fields')) {
         $options['error'] = __('Warning: Additional Fields plugin is disabled or missing', 'formcreator');
         $template = '@formcreator/field/undefinedfield.html.twig';
         TemplateRenderer::getInstance()->display($template, [
            'item' => $this->question,
            'params' => $options,
         ]);
         return;
      }

      $template = '@formcreator/field/' . $this->question->fields['fieldtype'] . 'field.html.twig';
      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $this->question->fields['_block_id'] = $decodedValues['blocks_field'] ?? 0;
      $this->question->fields['_block_list'] = $this->getBlocks();
      $this->question->fields['_drodpdown_block_label'] = __("Block", "fields");

      $this->question->fields['_field_name'] = $decodedValues['dropdown_fields_field'] ?? '';
      $this->question->fields['_field_list'] = FieldsField::getFieldsFromBlock($this->question->fields['_block_id']);
      $this->question->fields['_drodpdown_field_label'] =  __("Field", "fields");

      TemplateRenderer::getInstance()->display($template, [
         'item' => $this->question,
         'params' => $options,
      ]);
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      // Plugin field not available
      if (!(new Plugin())->isActivated('fields')) {
         return '';
      }

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $blocks_field = $decodedValues['blocks_field'] ?? '';

      $id           = $this->question->getID();
      $fieldName    = 'formcreator_field_' . $id;

      $html         = '';
      $html .= $this->prepareHtmlField($fieldName, $canEdit, $this->value);
      $html .= Html::hidden('c_id', ['value' => $blocks_field]);
      return $html;
   }

   public function prepareHtmlField($fieldName, $canedit = true, $value = '') {

      if ($this->getField() === null || empty($this->getField()->fields)) {
         return false;
      }

      $rand         = mt_rand();
      $dropdown_type = $this->getField()->fields['type'];

      //compute default values
      if (!empty($value)) {
         $field['value'] = $value;
      } else {
         //get default value
         if ($this->getField()->fields['default_value'] !== "") {
            $value = $this->getField()->fields['default_value'];
            // shortcut for date/datetime
            if (in_array($this->getField()->fields['type'], ['date', 'datetime'])
                  && $value == 'now') {
               $value = $_SESSION["glpi_currenttime"];
            }
         }
         $field['value'] = $value;
      }

      $dropdown_matches = [];
      if (preg_match('/^dropdown-(?<class>.+)$/i', $dropdown_type, $dropdown_matches)
         && isset($dropdown_matches['class']) && class_exists($dropdown_matches['class'])
      ) {
         $dropdown_type = 'dropdown_extend';
         $dropdown_class = $dropdown_matches['class'];
      }

      $html = "";
      $readonly = ($this->getField()->fields['is_readonly'] || !$canedit);
      $this->question->fields['required'] = $this->getField()->fields['mandatory'];

      switch ($dropdown_type) {
         case 'number':
         case 'text':
            $value = Html::cleanInputText($value);
            if ($canedit && !$readonly) {

               $html .= Html::input($fieldName, ['value' => $value]);
               $html .= Html::scriptBlock("$(function() {
                  pluginFormcreatorInitializeField('$fieldName', '$rand');
               });");
            } else {
               $html.= $value;
            }
            break;
         case 'url':
            $value = Html::cleanInputText($value);
            if ($canedit && !$readonly) {
               $html.= Html::input($fieldName, ['value' => $value]);
               if ($value != '') {
                  $html .= "<a target=\"_blank\" href=\"$value\">" . __('show', 'fields') . "</a>";
               }
            } else {
               $html .= "<a target=\"_blank\" href=\"$value\">$value</a>";
            }

            $html .= Html::scriptBlock("$(function() {
               pluginFormcreatorInitializeField('$fieldName', '$rand');
            });");
            break;
         case 'textarea':
            if ($canedit && !$readonly) {
               $html.= Html::textarea([
                  'name'    => $fieldName,
                  'value'   => $value,
                  'cols'    => 45,
                  'rows'    => 4,
                  'display' => false,
               ]);
               // This JS function intercepts tinyMCE creation then must be executed before end of page load
               $html .= Html::scriptBlock("
                  pluginFormcreatorInitializeTextarea('$fieldName', '$rand');
               ");
            } else {
               $html.= nl2br($value);
            }
            break;
         case 'dropdown':
            if ($canedit && !$readonly) {
               if (strpos($this->getField()->fields['name'], "dropdowns_id") !== false) {
                  $dropdown_itemtype = getItemTypeForTable(
                                       getTableNameForForeignKeyField($this->getField()->fields['name']));
               } else {
                  $dropdown_itemtype = PluginFieldsDropdown::getClassname($this->getField()->fields['name']);
               }
               $html.= Dropdown::show($dropdown_itemtype,
                                       ['value'   => $value,
                                       'name' => $fieldName,
                                       'entity'  => $_SESSION['glpiactiveentities'],
                                       'display' => false]);
            } else {
               $dropdown_table = "glpi_plugin_fields_".$this->getField()->fields['name']."dropdowns";
               $html.= Dropdown::getDropdownName($dropdown_table, $value);
            }

            $html .= Html::scriptBlock("$(function() {
               pluginFormcreatorInitializeDropdown('$fieldName', '$rand');
            });");

            break;
         case 'yesno':
            if ($canedit && !$readonly) {
               $html.= Dropdown::showYesNo($fieldName, $value, -1, ['display' => false]);
            } else {
               $html.= Dropdown::getYesNo($value);
            }
            $html .= Html::scriptBlock("$(function() {
               pluginFormcreatorInitializeDropdown('$fieldName', '$rand');
            });");
            break;
         case 'date':
            if ($canedit && !$readonly) {
               $html.= Html::showDateField($fieldName, ['value'   => $value,
                                                            'display' => false]);
               $html .= Html::scriptBlock("$(function() {
                  pluginFormcreatorInitializeDate('$fieldName', '$rand');
               });");
            } else {
               $html.= Html::convDate($value);
            }
            break;
         case 'datetime':
            if ($canedit && !$readonly) {
               $html.= Html::showDateTimeField($fieldName, ['value'   => $value,
                                                                  'display' => false]);
               $html .= Html::scriptBlock("$(function() {
                  pluginFormcreatorInitializeTime('$fieldName', '$rand');
               });");
            } else {
               $html.= Html::convDateTime($value);
            }
            break;
         case 'dropdownuser':

            if ($canedit && !$readonly) {
               $html.= User::dropdown(['name'      => $fieldName,
                                       'value'     => $value,
                                       'entity'    => -1,
                                       'right'     => 'all',
                                       'display'   => false,
                                       'condition' => ['is_active' => 1, 'is_deleted' => 0]]);
               $html .= Html::scriptBlock("$(function() {
                  pluginFormcreatorInitializeDropdown('$fieldName', '$rand');
               });");
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
               $html.= OperatingSystem::dropdown(['name'      => $fieldName,
                                       'value'     => $value,
                                       'entity'    => -1,
                                       'right'     => 'all',
                                       'display'   => false]);
               $html .= Html::scriptBlock("$(function() {
                  pluginFormcreatorInitializeDropdown('$fieldName', '$rand');
               });");
            } else {
               $os = new OperatingSystem();
               $os->getFromDB($value);
               $html.= $os->fields['name'];
            }
         case 'dropdown_extend':
            $itemtype = $dropdown_class;

            if ($canedit && !$readonly) {
               $html.= $itemtype::dropdown(['name'      => $fieldName,
                                       'value'     => $value,
                                       'entity'    => -1,
                                       'right'     => 'all',
                                       'display'   => false]);
               $html .= Html::scriptBlock("$(function() {
                  pluginFormcreatorInitializeDropdown('$fieldName', '$rand');
               });");
            } else {
               $dropdown_field = new $itemtype();
               $dropdown_field->getFromDB($value);
               $html.= $dropdown_field->fields['name'];
            }
            break;
         default:
            $html .= sprintf(__('Field \'%1$s\' type not implemented yet!', 'formcreator'), $dropdown_type);
      }

      unset($_SESSION['plugin']['fields']['values_sent']);
      return $html;
   }

   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string {
      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = $value;
   }

   public function getValueForDesign(): string {
      return "";
   }

   public function getValueForTargetText($domain, $richText): ?string {

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $original_fields = new PluginFieldsField();
      $value = "";

      //load native field
      if ($original_fields->getFromDBByCrit([
         'name' => $decodedValues['dropdown_fields_field']
      ])) {
         //switch type compute table to load dropdown value
         $dropdown_table = null;
         if ($original_fields->fields['type'] == 'dropdown') {
            $dropdown_table = "glpi_plugin_fields_" . $decodedValues['dropdown_fields_field'] . "dropdowns";
         } else if ($original_fields->fields['type'] == 'dropdownuser') {
            $dropdown_table = getTableForItemType("User");
         } else if ($original_fields->fields['type'] == 'dropdownoperatingsystems') {
            $dropdown_table = getTableForItemType("OperatingSystem");
         }

         if ($dropdown_table != null) {
            $value = Dropdown::getDropdownName($dropdown_table, $this->value);
         } else {
            //manage yesno type
            if ($original_fields->fields['type'] == "yesno") {
               $value = Dropdown::getYesNo($this->value);
            } else {
               $value = $this->value;
            }
         }
      }

      return $value;
   }

   public function getValueForApi(): string {
      return "";
   }

   public function isValidValue($value): bool {

      if (is_null($this->getField())) {
         return false;
      }

      //check data type for input number / url
      $valid = true;
      if ($this->getField()->fields['type'] == 'number' && !empty($this->value) && !is_numeric($this->value)) {
         $number_errors[] = $this->getField()->fields['label'];
         $valid = false;
      } else if ($this->getField()->fields['type'] == 'url' && !empty($this->value)) {
         if (filter_var($this->value, FILTER_VALIDATE_URL) === false) {
            $url_errors[] = $this->getField()->fields['label'];
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

      if (!$valid) {
         return false;
      }

      //All is OK
      return true;
   }

   public function isValid(): bool {
      if (!is_null($this->getField())) {
         // If the field is required it can't be empty
         if ($this->getField()->fields['mandatory'] && $this->value == '') {
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

      $input['values'] = [];
      $input['values']['dropdown_fields_field'] = $input['dropdown_fields_field'];
      $input['values']['blocks_field'] = $input['blocks_field'];
      unset($input['dropdown_fields_field']);
      unset($input['blocks_field']);

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
      return false;
   }

   public function computeValueForComparaison(): string {

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $original_fields = new PluginFieldsField();
      $value = '';

      //load native field
      if ($original_fields->getFromDBByCrit([
         'name' => $decodedValues['dropdown_fields_field']
      ])) {
         //switch type compute table to load dropdown value
         $dropdown_itemtype = null;
         if ($original_fields->fields['type'] == 'dropdown') {
            $dropdown_itemtype = getItemTypeForTable("glpi_plugin_fields_" . $decodedValues['dropdown_fields_field'] . "dropdowns");
         } else if ($original_fields->fields['type'] == 'dropdownuser') {
            $dropdown_itemtype = "User";
         } else if ($original_fields->fields['type'] == 'dropdownoperatingsystems') {
            $dropdown_itemtype = "OperatingSystem";
         }

         if ($dropdown_itemtype != null) {
            $item = new $dropdown_itemtype();
            if ($item->isNewId($this->value)) {
               $value = '';
            }
            if (!$item->getFromDB($this->value)) {
               throw new ComparisonException('Item not found for comparison');
            }
            $value = $item->getField($item->getNameField());
         } else {
            //manage yesno type
            if ($original_fields->fields['type'] == "yesno") {
               $value = Dropdown::getYesNo($this->value);
            } else {
               $value = $this->value;
            }
         }
      }

      return $value;
   }

   public function equals($value): bool {
      $internal_value = $this->computeValueForComparaison();
      return Toolbox::stripslashes_deep($internal_value) == $value;
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      return Toolbox::stripslashes_deep($this->value) > $value;
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      $internal_value = $this->computeValueForComparaison();
      return preg_match($value, Toolbox::stripslashes_deep($internal_value)) ? true : false;
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

   public function isAnonymousFormCompatible(): bool {
      return true;
   }

}
