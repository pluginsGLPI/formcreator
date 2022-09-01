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

use GlpiPlugin\Formcreator\Exception\ImportFailureException;
use GlpiPlugin\Formcreator\Exception\ExportFailureException;
use GlpiPlugin\Formcreator\Field\UndefinedField;
use Glpi\Application\View\TemplateRenderer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorQuestion extends CommonDBChild implements
PluginFormcreatorExportableInterface,
PluginFormcreatorDuplicatableInterface,
PluginFormcreatorConditionnableInterface,
PluginFormcreatorTranslatableInterface
{
   use PluginFormcreatorConditionnableTrait;
   use PluginFormcreatorExportableTrait;
   use PluginFormcreatorTranslatable;

   static public $itemtype = PluginFormcreatorSection::class;
   static public $items_id = 'plugin_formcreator_sections_id';

   /** @var PluginFormcreatorFieldInterface|null $field a field describing the question denpending on its field type  */
   private ?PluginFormcreatorFieldInterface $field = null;

   private $skipChecks = false;

   public static function getEnumShowRule() : array {
      return PluginFormcreatorCondition::getEnumShowRule();
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Question', 'Questions', $nb, 'formcreator');
   }

   public static function getIcon() {
      return 'fas fa-edit';
   }

   function addMessageOnAddAction() {}
   function addMessageOnUpdateAction() {}
   function addMessageOnDeleteAction() {}
   function addMessageOnPurgeAction() {}

   /**
    * Return the name of the tab for item including forms like the config page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $withtemplate
    *
    * @return String                   Name to be displayed
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      global $DB;

      if ($item instanceof PluginFormcreatorForm) {
         $number      = 0;
         $found       = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => PluginFormcreatorSection::getTable(),
            'WHERE'  => [
               'plugin_formcreator_forms_id' => $item->getID()
            ]
         ]);
         $tab_section = [];
         foreach ($found as $section_item) {
            $tab_section[] = $section_item['id'];
         }

         if (!empty($tab_section)) {
            $count = $DB->request([
               'COUNT' => 'cpt',
               'FROM'  => self::getTable(),
               'WHERE' => [
                  'plugin_formcreator_sections_id' => $tab_section
               ]
            ])->current();
            $number = $count['cpt'];
         }
         return self::createTabEntry(self::getTypeName($number), $number);
      }
      return '';
   }

   /**
    * Display a list of all form sections and questions
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Form Item)
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item instanceof PluginFormcreatorForm) {
         static::showForForm($item, $withtemplate);
      }
   }

   /**
    * May be removed when GLPI 9.5 will  be the lowest supported version
    * workaround use if entity in WHERE when using PluginFormcreatorQuestion::dropdown
    * (while editing conditions, list of questions is empty + SQL error)
    * @see bug on GLPI #6488, might be related
    */
   function isEntityAssign() {
      return false;
   }

   public function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '2',
         'table'              => $this::getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'datatype'           => 'integer',
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this::getTable(),
         'field'              => 'description',
         'name'               => __('Description'),
         'datatype'           => 'text',
         'massiveaction'      => false
      ];

      return $tab;
   }

   public function getForbiddenStandardMassiveAction() {
      return [
         'update', 'clone', 'add_note',
      ];
   }

   public static function showForForm(CommonDBTM $item, $withtemplate = '') {
      $options = [
         'candel'      => false,
         'formoptions' => sprintf('data-itemtype="%s" data-id="%s"', $item::getType(), $item->getID()),
      ];
      TemplateRenderer::getInstance()->display('@formcreator/pages/question_for_form.html.twig', [
         'item'   => $item,
         'params' => $options,
         'parent' => $item,
      ]);

      return true;
   }

   /**
    * Get the HTML for the question in form designer
    *
    * @return string
    */
   public function getDesignHtml() : string {
      if ($this->isNewItem()) {
         return '';
      }

      $html = '';

      $questionId = $this->getID();
      $sectionId = $this->fields[PluginFormcreatorSection::getForeignKeyField()];
      $fieldType = PluginFormcreatorFields::getFieldClassname($this->fields['fieldtype']);
      /** @var PluginFormcreatorFieldInterface $field */
      $field = new $fieldType($this);

      $html .= '<div class="grid-stack-item"'
      . ' data-itemtype="' . self::class . '"'
      . ' data-id="'.$questionId.'"'
      . '>';

      $html .= '<div class="grid-stack-item-content">';

      // Question name
      $html .= $field->getHtmlIcon() . '&nbsp;';
      $onclick = 'plugin_formcreator.showQuestionForm(' . $sectionId . ', ' . $questionId . ');';
      $html .= '<a href="javascript:' . $onclick . '" data-field="name">';
      // Show count of conditions
      $nb = (new DBUtils())->countElementsInTable(PluginFormcreatorCondition::getTable(), [
         'itemtype' => PluginFormcreatorQuestion::getType(),
         'items_id' => $this->getID(),
      ]);
      $html .= "<sup class='plugin_formcreator_conditions_count' title='" . __('Count of conditions', 'formcreator') ."'>$nb</sup>";
      $html .= empty($this->fields['name']) ? '(' . $questionId . ')' : $this->fields['name'];
      $html .= '</a>';

      // Delete the question
      $html .= "<span class='form_control pointer'>";
      $html .= '<i class="far fa-trash-alt"
               onclick="plugin_formcreator.deleteQuestion(this)"></i> ';
      $html .= "</span>";

      // Clone the question
      $html .= "<span class='form_control pointer'>";
      $html .= '<i class="far fa-clone"
               onclick="plugin_formcreator.duplicateQuestion(this)"></i> ';
      $html .= "</span>";

      // Toggle mandatory for the question
      if ($fieldType::canRequire()) {
         $html .= "<span class='form_control pointer'>";
         $required = ($this->fields['required'] == '0') ? 'far fa-circle' : 'far fa-check-circle';
         $html .= '<i class="' . $required .'"
                  onclick="plugin_formcreator.toggleRequired(this)"></i> ';
         $html .= "</span>";
      }

      $html .= '</div>'; // grid stack item content

      $html .= '</div>'; // grid stack item

      return $html;
   }

   /**
    * Get the HTML to display the question for a requester
    * @param string  $domain  Translation domain of the form
    * @param boolean $canEdit Can the requester edit the field of the question ?
    * @param PluginFormcreatorFormAnswer $value   Values all fields of the form
    * @param bool $isVisible is the question visible by default ?
    *
    * @return string
    */
   public function getRenderedHtml($domain, $canEdit = true, ?PluginFormcreatorFormAnswer $form_answer = null, $isVisible = true): string {
      if ($this->isNewItem()) {
         return '';
      }

      $html = '';

      $field = $this->getSubField();
      if (!$field->isPrerequisites()) {
         return '';
      }

      $field->setFormAnswer($form_answer);

      $required = ($this->fields['required']) ? ' required' : '';
      $x = $this->fields['col'];
      $width = $this->fields['width'];
      $hiddenAttribute = $isVisible ? '' : 'hidden=""';
      $html .= '<div'
         . ' gs-x="' . $x . '"'
         . ' gs-w="' . $width . '"'
         . ' data-itemtype="' . self::class . '"'
         . ' data-id="' . $this->getID() . '"'
         . " $hiddenAttribute"
         . ' >';
      $html .= '<div class="grid-stack-item-content form-group mb-3 ' . $required . '" id="form-group-field-' . $this->getID() . '">';
      $html .= $field->show($domain, $canEdit);
      $html .= '</div>';
      $html .= '</div>';

      return $html;
   }

   /**
    * Validate form fields before add or update a question
    * @param  array $input Datas used to add the item
    * @return array        The modified $input array
    */
   private function checkBeforeSave($input) : array {
      // Control fields values :
      // - name is required
      if (isset($input['name'])) {
         if (empty($input['name'])) {
            Session::addMessageAfterRedirect(__('The title is required', 'formcreator'), false, ERROR);
            return [];
         }
      }

      // - field type is required
      if (isset($input['fieldtype'])
          && empty($input['fieldtype'])) {
         Session::addMessageAfterRedirect(__('The field type is required', 'formcreator'), false, ERROR);
         return [];
      }

      // - section is required
      if (isset($input['plugin_formcreator_sections_id'])
          && empty($input['plugin_formcreator_sections_id'])) {
         Session::addMessageAfterRedirect(__('The section is required', 'formcreator'), false, ERROR);
         return [];
      }

      if (!isset($input['fieldtype'])) {
         $input['fieldtype'] = $this->fields['fieldtype'];
      }
      $this->loadField($input['fieldtype']);
      if ($this->field === null) {
         Session::addMessageAfterRedirect(
            // TRANS: $%1$s is a type of field, %2$s is the label of a question
            sprintf(
               __('Field type %1$s is not available for question %2$s.', 'formcreator'),
               $input['fieldtype'],
               $input['name']
               ),
            false,
            ERROR
         );
         return [];
      }
      // - field type is compatible with accessibility of the form
      $form = PluginFormcreatorCommon::getForm();
      $section = PluginFormcreatorSection::getById($input[PluginFormcreatorSection::getForeignKeyField()]);
      $form = PluginFormcreatorForm::getByItem($section);
      if ($form->isPublicAccess() && !$this->field->isPublicFormCompatible()) {
         Session::addMessageAfterRedirect(__('This type of question is not compatible with public forms.', 'formcreator'), false, ERROR);
         return [];
      }

      // Check the parameters are provided
      $parameters = $this->field->getEmptyParameters();
      if (count($parameters) > 0) {
         if (!isset($input['_parameters'][$input['fieldtype']])) {
            // This should not happen
            Session::addMessageAfterRedirect(__('This type of question requires parameters', 'formcreator'), false, ERROR);
            return [];
         }
         foreach ($parameters as $parameter) {
            if (!isset($input['_parameters'][$input['fieldtype']][$parameter->getFieldName()])) {
               // This should not happen
               Session::addMessageAfterRedirect(__('A parameter is missing for this question type', 'formcreator'), false, ERROR);
               return [];
            }
         }
      }

      $input = $this->field->prepareQuestionInputForSave($input);
      if ($input === false || !is_array($input)) {
         // Invalid data
         return [];
      }

      if (isset($input['_conditions']) && !$this->checkConditions($input['_conditions'])) {
         return [];
      }

      // Might need to merge $this->fields and $input, $input having precedence
      // over $this->fields
      //$input['default_values'] = $this->field->serializeValue($formanswer);

      return $input;
   }

   /**
    * Prepare input data for adding the question
    * Check fields values and get the order for the new question
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
    */
   public function prepareInputForAdd($input) {
      if (!$this->skipChecks) {
         $input = $this->checkBeforeSave($input);

         if (!$this->checkConditionSettings($input)) {
            $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
         }
      }
      if (count($input) === 0) {
         return [];
      }

      // Compute default position
      if (!isset($input['col'])) {
         $input['col'] = 0;
      }
      if (!isset($input['width'])) {
         $input['width'] = PluginFormcreatorSection::COLUMNS - $input['col'];
      }
      // Get next row
      if ($this->useAutomaticOrdering) {
         $maxRow = PluginFormcreatorCommon::getMax($this, [
            self::$items_id => $input[self::$items_id]
         ], 'row');
         if ($maxRow === null) {
            $input['row'] = 0;
         } else {
            $input['row'] = $maxRow + 1;
         }
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   /**
    * Prepare input data for adding the question
    * Check fields values and get the order for the new question
    *
    * @param array $input data used to add the item
    *
    * @array return the modified $input array
    */
   public function prepareInputForUpdate($input) {
      // global $DB;

      if (!$this->skipChecks) {
         if (!isset($input['plugin_formcreator_sections_id'])) {
            $input['plugin_formcreator_sections_id'] = $this->fields['plugin_formcreator_sections_id'];
         }

         $input = $this->checkBeforeSave($input);

         if (!$this->checkConditionSettings($input)) {
            $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
         }
      }

      if (!is_array($input) || count($input) == 0) {
         return false;
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         if (!isset($this->fields['uuid']) && $this->fields['uuid'] != $input['uuid']) {
            $input['uuid'] = plugin_formcreator_getUuid();
         }
      }

      return $input;
   }

   /**
    * Update size or position of the question
    * @param array $input
    * @return bool false on error
    */
   public function change($input): bool {
      $x = $this->fields['col'];
      $y = $this->fields['row'];
      $width = $this->fields['width'];
      $height = 1;

      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      if (isset($input['x'])) {
         if ($input['x'] < 0) {
            return false;
         }
         if ($input['x'] > PluginFormcreatorSection::COLUMNS - 1) {
            return false;
         }
         $x = $input['x'];
      }

      if (isset($input['y'])) {
         if ($input['y'] < 0) {
            return false;
         }
         $maxRow = 1 + PluginFormcreatorCommon::getMax(
            $this, [
               $sectionFk => $this->fields[$sectionFk]
            ],
            'row'
         );
         if ($input['y'] > $maxRow) {
            return false;
         }
         $y = $input['y'];
      }

      if (isset($input['width'])) {
         if ($input['width'] <= 0) {
            return false;
         }
         if ($input['width'] > (PluginFormcreatorSection::COLUMNS - $x)) {
            return false;
         }
         $width = $input['width'];
      }

      if (isset($input['height'])) {
         if ($input['height'] <= 0) {
            return false;
         }
         if ($input['height'] > 1) {
            return false;
         }
         $height = $input['height'];
      }

      if (isset($input[$sectionFk])) {
         $section = new PluginFormcreatorSection();
         if (!$section->getFromDB($input[$sectionFk])) {
            return false;
         }
      }

      $this->skipChecks = true;
      $input2 = [
         'id'     => $this->getID(),
         'col'      => $x,
         'row'      => $y,
         'width'  => $width,
         'height' => $height,
      ];
      if (isset($input[$sectionFk])) {
         $input2[$sectionFk] = $input[$sectionFk];
      }
      $success = $this->update($input2);
      $this->skipChecks = false;

      return $success;
   }

   /**
    * set or reset the required flag
    *
    * @param bool $isRequired
    * @return bool true if success, false otherwise
    */
   public function setRequired($isRequired): bool {
      $this->skipChecks = true;
      $success = $this->update([
         'id'           => $this->getID(),
         'required'     => $isRequired,
      ]);
      $this->skipChecks = false;

      return $success;
   }

   /**
    * Adds or updates parameters of the question
    * @param array $input parameters
    */
   public function updateParameters($input) {
      // The question instance has a field type
      if (!isset($this->fields['fieldtype'])) {
         return;
      }
      $fieldType = $this->fields['fieldtype'];

      // The fieldtype may change
      if (isset($input['fieldtype'])) {
         $fieldType = $input['fieldtype'];
      }

      $this->loadField($fieldType);
      $this->field->updateParameters($this, $input);
   }

   public function pre_deleteItem() {
      $success = (new PluginFormcreatorCondition())->deleteByCriteria([
         'itemtype' => self::class,
         'items_id' => $this->getID(),
      ]);
      if (!$success) {
         return false;
      }

      $this->loadField($this->fields['fieldtype']);
      return $this->field->deleteParameters($this);
   }

   public function post_addItem() {
      $this->input = $this->addFiles(
         $this->input,
         [
            'force_update'  => true,
            'content_field' => 'description',
            'name'          => 'description',
         ]
      );

      if ($this->input['fieldtype'] == 'textarea') {
         $this->input = $this->addFiles(
            $this->input,
            [
               'force_update'  => true,
               'content_field' => 'default_values',
               'name'          => 'default_values',
            ]
         );
      }

      $this->updateConditions($this->input);
      if (!$this->skipChecks) {
         $this->updateParameters($this->input);
      }
   }

   public function post_updateItem($history = 1) {
      $this->input = $this->addFiles(
         $this->input,
         [
            'force_update'  => true,
            'content_field' => 'description',
            'name'          => 'description',
         ]
      );

      if (($this->input['fieldtype'] ?? $this->fields['fieldtype']) == 'textarea') {
         $this->input = $this->addFiles(
            $this->input,
            [
               'force_update'  => true,
               'content_field' => 'default_values',
               'name'          => 'default_values',
            ]
         );
      }

      $this->updateConditions($this->input);
      if (!$this->skipChecks) {
         $this->updateParameters($this->input);
      }
   }

   /**
    * Actions done after the PURGE of the item in the database
    * Reorder other questions
    *
    * @return void
    */
   public function post_purgeItem() {
      global $DB;

      $table = self::getTable();
      $condition_table = PluginFormcreatorCondition::getTable();

      // Move up questions under this one, if row is empty
      // TODO: handle multiple consecutive empty rows
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $section = new PluginFormcreatorSection();
      $section->getFromDB($this->fields[$sectionFk]);
      if ($section->isRowEmpty($this->fields['row'])) {
         // Rows of the item are empty
         $row = $this->fields['row'];
         $DB->update(
            $table,
            [
               'row' => new QueryExpression('`row` - 1')
            ],
            [
              'row' => ['>', $row],
              $sectionFk => $this->fields[$sectionFk]
            ]
         );
      }

      // Always show questions with conditional display on the question being deleted
      $questionId = $this->fields['id'];
      $DB->update(
         $table,
         [
            'show_rule' => PluginFormcreatorCondition::SHOW_RULE_ALWAYS
         ],
         [
            'id' => new QuerySubQuery([
               'SELECT' => self::getForeignKeyField(),
               'FROM' => $condition_table,
               'WHERE' => ['plugin_formcreator_questions_id' => $questionId]
            ])
         ]
      );

      $DB->delete(
         $condition_table,
         [
            'OR' => [
               self::getForeignKeyField() => $questionId,
               'plugin_formcreator_questions_id' => $questionId
            ]
         ]
      );
   }

   public function showForm($ID, $options = []) {
      $options['candel'] = false;
      $options['target'] = "javascript:;";
      $options['formoptions'] = sprintf('onsubmit="plugin_formcreator.submitQuestion(this)" data-itemtype="%s" data-id="%s"', self::getType(), $this->getID());

      $template = '@formcreator/field/undefinedfield.html.twig';
      if (!$this->loadField($this->fields['fieldtype'])) {
         TemplateRenderer::getInstance()->display($template, [
            'item' => $this,
            'params' => $options,
         ]);
         return true;
      }

      $this->field->showForm($options);

      return true;
   }

   /**
    * Show a question type dropdown
    *
    * @param string $name
    * @param array $options
    * @return void
    */
   public static function dropdownQuestionType(string $name, array $options): void {
      $fieldtypes = PluginFormcreatorFields::getNames();
      $options['on_change'] = "plugin_formcreator.changeQuestionType(this)";
      Dropdown::showFromArray($name, $fieldtypes, $options);
   }

   public function duplicate(array $options = []) {
      $linker = new PluginFormcreatorLinker($options);

      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $export = $this->export(true);

      // Amend some data (used when duplicating a question from the form designer UI)
      if (isset($options['fields'])) {
         foreach ($options['fields'] as $key => $value) {
            if ($value === null) {
               unset($export[$key]);
               continue;
            }
            $export[$key] = $value;
         }
      }
      $newQuestionId = static::import($linker, $export, $this->fields[$sectionFk]);

      if ($newQuestionId === false) {
         return false;
      }
      $linker->linkPostponed();

      return $newQuestionId;
   }

   public static function import(PluginFormcreatorLinker $linker, array $input = [], int $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      // restore key and FK
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $input[$sectionFk] = $containerId;

      $item = new self();
      // Find an existing question to update, only if an UUID is available
      $itemId = false;
      /** @var string $idKey key to use as ID (id or uuid) */
      $idKey = 'id';
      if (isset($input['uuid'])) {
         $idKey = 'uuid';
         $itemId = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }

      // escape text fields
      foreach (['name', 'description', 'default_values', 'values'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // Add or update question
      $originalId = $input[$idKey];
      $item->skipChecks = true;
      if ($itemId !== false) {
         $input['id'] = $itemId;
         $item->field = PluginFormcreatorFields::getFieldInstance(
            $input['fieldtype'],
            $item
         );
         $item->update($input);
      } else {
         $item->useAutomaticOrdering = false;
         unset($input['id']);
         $itemId = $item->add($input);
      }
      $item->skipChecks = false;
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the question to the linker
      $linker->addObject($originalId, $item);

      // Import conditions
      if (isset($input['_conditions'])) {
         foreach ($input['_conditions'] as $condition) {
            PluginFormcreatorCondition::import($linker, $condition, $itemId);
         }
      }

      // Import parameters
      $field = PluginFormcreatorFields::getFieldInstance(
         $input['fieldtype'],
         $item
      );
      if (isset($input['_parameters'])) {
         $parameters = $field->getParameters();
         foreach ($parameters as $fieldName => $parameter) {
            if (is_array($input['_parameters'][$input['fieldtype']][$fieldName])) {
               /** @var PluginFormcreatorExportableInterface $parameter */
               $parameter::import($linker, $input['_parameters'][$input['fieldtype']][$fieldName], $itemId);
            } else {
               // Import data incomplete, parameter not defined
               // Adding an empty parameter (assuming the question is actually added or updated in DB)
               $parameterInput = $parameter->fields;
               $parameterInput['plugin_formcreator_questions_id'] = $itemId;
               unset($parameterInput['id']);
               $parameter->add($parameterInput);
            }
         }
      }

      return $itemId;
   }

   public static function countItemsToImport(array $input) : int {
      // TODO: need improvement to handle parameters
      $subItems = [
         '_conditions' => PluginFormcreatorCondition::class,
      ];

      return 1 + self::countChildren($input, $subItems);
   }

   public function export(bool $remove_uuid = false) : array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
      }

      $export = $this->fields;

      // remove key and fk
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      unset($export[$sectionFk]);

      // get question conditions
      $export['_conditions'] = [];
      $all_conditions = PluginFormcreatorCondition::getConditionsFromItem($this);
      foreach ($all_conditions as $condition) {
         $export['_conditions'][] = $condition->export($remove_uuid);
      }

      // get question parameters
      $export['_parameters'] = [];
      $this->loadField($this->fields['fieldtype']);
      $parameters = $this->field->getParameters();
      foreach ($parameters as $fieldname => $parameter) {
         $export['_parameters'][$this->fields['fieldtype']][$fieldname] = $parameter->export($remove_uuid);
      }

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($export[$idToRemove]);

      return $export;
   }

   /**
    * return array of question objects belonging to a form
    * @param int $formId
    * @param array $crit array for the WHERE clause
    * @return PluginFormcreatorQuestion[]
    */
   public static function getQuestionsFromForm($formId, $crit = []) {
      global $DB;

      $table_question = PluginFormcreatorQuestion::getTable();
      $table_section  = PluginFormcreatorSection::getTable();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $result = $DB->request([
         'SELECT' => "$table_question.*",
         'FROM' => $table_question,
         'LEFT JOIN' => [
            $table_section => [
               'FKEY' => [
                  $table_question => $sectionFk,
                  $table_section => 'id',
               ],
            ],
         ],
         'WHERE' => [
            'AND' => [$formFk => $formId] + $crit,
         ],
         'ORDER' => [
            "$table_section.order",
            "$table_question.row",
            "$table_question.col",
         ]
      ]);

      $questions = [];
      foreach ($result as $row) {
         $question = new self();
         $question->getFromDB($row['id']);
         $questions[$row['id']] = $question;
      }

      return $questions;
   }

   /**
    * Gets questions belonging to a section
    *
    * @param int $sectionId
    *
    * @return PluginFormcreatorQuestion[]
    */
   public static function getQuestionsFromSection($sectionId) {
      global $DB;

      $questions = [];
      $rows = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_formcreator_sections_id' => $sectionId
         ],
         'ORDER'  => ['row ASC', 'col ASC']
      ]);
      foreach ($rows as $row) {
            $question = new self();
            $question->getFromDB($row['id']);
            $questions[$row['id']] = $question;
      }

      return $questions;
   }

   /**
    * get questions of a form grouped by section name and filtered by criteria
    *
    * @param PluginFormcreatorForm $form
    * @param array $crit additional slection criterias criterias
    * @return array 1st level is the section name, 2nd level is id and name of the question
    */
   public static function getQuestionsFromFormBySection($form, $crit = []) {
      global $DB;

      if ($form->isNewItem()) {
         return [];
      }

      $questionTable = PluginFormcreatorQuestion::getTable();
      $sectionTable  = PluginFormcreatorSection::getTable();
      $sectionFk     = PluginFormcreatorSection::getForeignKeyField();
      $formFk        = PluginFormcreatorForm::getForeignKeyField();
      $result = $DB->request([
         'SELECT' => [
            $questionTable => ['id as qid', 'name as qname'],
            $sectionTable => ['id as sid', 'name as sname'],
         ],
         'FROM' => $questionTable,
         'LEFT JOIN' => [
            $sectionTable => [
               'FKEY' => [
                  $questionTable => $sectionFk,
                  $sectionTable => 'id',
               ],
            ],
         ],
         'WHERE' => [
            'AND' => [$formFk => $form->getID()] + $crit,
         ],
         'ORDER' => [
            "$sectionTable.order",
            "$questionTable.row",
            "$questionTable.col",
         ]
      ]);

      $items = [];
      foreach ($result as $question) {
         $sectionName = $question['sname'];
         if ($sectionName == '') {
            $sectionName = '(' . $question['sid'] . ')';
         }
         if (!isset($items[$sectionName])) {
            $items[$sectionName] = [];
         }
         $items[$sectionName][$question['qid']] = $question['qname'];
      }

      return $items;
   }

   /**
    * Show or return a dropdown to select a question among those of the given form
    *
    * @param PluginFormcreatorForm $form
    * @param array $crit
    * @param string $name
    * @param string $value
    * @param array $options
    * @return string|int HTML output or random id
    */
   public static function dropdownForForm($form, $crit, $name, $value = null, $options = []) {
      if (isset($crit['used']) && count($crit['used']) == 0) {
         unset($crit['used']);
      }
      $items = self::getQuestionsFromFormBySection($form, $crit);
      $options = $options + [
         'display' => $options['display'] ?? true,
      ];
      if ($value !== null) {
         $options['value'] = $value;
      }
      $output = Dropdown::showFromArray($name, $items, $options);

      return $output;
   }

   /**
    * Get linked data (conditions, regexes or ranges) for a question
    *
    * @param string    $table   target table containing the needed data (
    *                           condition, range or regex)
    * @param int|array $id      a single id or an array of ids
    * @return array
    */
   public static function getQuestionDataById($table, $id) {
      global $DB;

      $validTargets = [
         \PluginFormcreatorCondition::getTable(),
         \PluginFormcreatorQuestionRegex::getTable(),
         \PluginFormcreatorQuestionRange::getTable(),
      ];

      if (array_search($table, $validTargets) === false) {
         throw new \InvalidArgumentException("Invalid target ('$table')");
      }

      return iterator_to_array($DB->request([
         'FROM' => $table,
         'WHERE' => [
            "plugin_formcreator_questions_id" => $id
         ]
      ]));
   }

   /**
    * load instance of field associated to the question
    *
    * @return bool true on sucess, false otherwise
    */
   private function loadField($fieldType): bool {
      if (!$this->field === null) {
         return false;
      }
      $this->field = PluginFormcreatorFields::getFieldInstance($fieldType, $this);
      if ($this->field === null) {
         return false;
      }
      return true;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) : bool {
      $keepCriteria = [
         self::$items_id => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }

   /**
    * Get the field object representing the question
    * @return PluginFormcreatorFieldInterface|null
    */
   public function getSubField(): ?PluginFormcreatorFieldInterface {
      if ($this->isNewItem()) {
         return null;
      }

      if ($this->field === null) {
         $this->field = PluginFormcreatorFields::getFieldInstance(
            $this->fields['fieldtype'],
            $this
         );
      }

      return $this->field;
   }

   public function getTranslatableStrings(array $options = []) : array {
      $strings = [
         'itemlink' => [],
         'string'   => [],
         'text'     => [],
      ];

      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if one of is_translated and is_untranslated is false
      ];
      $options = array_merge($params, $options);

      $strings = $this->getMyTranslatableStrings($options);

      // get translatable strings from field
      $this->loadField($this->fields['fieldtype']);

      foreach ($this->field->getTranslatableStrings($options) as $type => $subStrings) {
         $strings[$type] = array_merge($strings[$type], $subStrings);
      }

      $strings = $this->deduplicateTranslatable($strings);

      return $strings;
   }

   /**
    * Show a dropdown of dropdown itemtypes (ITIL categories, locations, ...)
    *
    * @param string $name
    * @param array $options
    * @return void
    */
   public static function dropdownDropdownSubType(string $name, array $options = []): void {
      $optgroup = Dropdown::getStandardDropdownItemTypes();
      $optgroup[__('Service levels')] = [
         SLA::getType() => __("SLA", "formcreator"),
         OLA::getType() => __("OLA", "formcreator"),
      ];

      $itemtype = is_subclass_of($options['value'], CommonDBTM::class) ? $options['value'] : '';
      Dropdown::showFromArray($name, $optgroup, [
         'value'               => $itemtype,
         'display_emptychoice' => true,
         'display'             => true,
         'specific_tags' => [
            'data-type'     => \GlpiPlugin\Formcreator\Field\DropdownField::class,
            'data-itemtype' => $itemtype
         ],
      ] + $options);
   }

   public static function dropdownObjectSubType(string $name, array $options = []): void {
      $optgroup = [
         __("Assets") => [
            Computer::class           => Computer::getTypeName(2),
            Monitor::class            => Monitor::getTypeName(2),
            Software::class           => Software::getTypeName(2),
            NetworkEquipment::class   => Networkequipment::getTypeName(2),
            Peripheral::class         => Peripheral::getTypeName(2),
            Printer::class            => Printer::getTypeName(2),
            CartridgeItem::class      => CartridgeItem::getTypeName(2),
            ConsumableItem::class     => ConsumableItem::getTypeName(2),
            Phone::class              => Phone::getTypeName(2),
            Line::class               => Line::getTypeName(2),
            PassiveDCEquipment::class => PassiveDCEquipment::getTypeName(2),
            Appliance::class          => Appliance::getTypeName(2),
         ],
         __("Assistance") => [
            Ticket::class             => Ticket::getTypeName(2),
            Problem::class            => Problem::getTypeName(2),
            TicketRecurrent::class    => TicketRecurrent::getTypeName(2)
         ],
         __("Management") => [
            Budget::class             => Budget::getTypeName(2),
            Supplier::class           => Supplier::getTypeName(2),
            Contact::class            => Contact::getTypeName(2),
            Contract::class           => Contract::getTypeName(2),
            Document::class           => Document::getTypeName(2),
            Project::class            => Project::getTypeName(2),
            Certificate::class        => Certificate::getTypeName(2)
         ],
         __("Tools") => [
            Reminder::class           => __("Notes"),
            RSSFeed::class            => __("RSS feed")
         ],
         __("Administration") => [
            User::class               => User::getTypeName(2),
            Group::class              => Group::getTypeName(2),
            Entity::class             => Entity::getTypeName(2),
            Profile::class            => Profile::getTypeName(2)
         ],
      ];
      if ((new Plugin())->isActivated('appliances')) {
         $optgroup[__("Assets")][PluginAppliancesAppliance::class] = PluginAppliancesAppliance::getTypeName(2) . ' (' . _n('Plugin', 'Plugins', 1) . ')';
      }
      if ((new Plugin())->isActivated('databases')) {
         $optgroup[__("Assets")][PluginDatabasesDatabase::class] = PluginDatabasesDatabase::getTypeName(2) . ' (' . _n('Plugin', 'Plugins', 1) . ')';
      }

      // Get additional itemtypes from plugins
      $additionalTypes = Plugin::doHookFunction('formcreator_get_glpi_object_types', []);
      // Cleanup data from plugins
      $cleanedAditionalTypes = [];
      foreach ($additionalTypes as $groupName => $itemtypes) {
         if (!is_string($groupName)) {
            continue;
         }
         $cleanedAditionalTypes[$groupName] = [];
         foreach ($itemtypes as $itemtype => $typeName) {
            if (!class_exists($itemtype)) {
               continue;
            }
            if (array_search($itemtype, $cleanedAditionalTypes[$groupName])) {
               continue;
            }
            $cleanedAditionalTypes[$groupName][$itemtype] = $typeName;
         }
      }
      // Merge new itemtypes to predefined ones
      $optgroup = array_merge_recursive($optgroup, $cleanedAditionalTypes);

      $itemtype = is_subclass_of($options['value'], CommonDBTM::class) ? $options['value'] : '';
      Dropdown::showFromArray($name, $optgroup, [
         'value'               => $itemtype,
         'display_emptychoice' => true,
         'display'             => true,
         'specific_tags' => [
            'data-type'     => \GlpiPlugin\Formcreator\Field\GlpiselectField::class,
            'data-itemtype' => $itemtype
         ],
      ] + $options);
   }
}
