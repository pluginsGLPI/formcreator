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
use Glpi\Application\View\TemplateRenderer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorSection extends CommonDBChild implements
PluginFormcreatorExportableInterface,
PluginFormcreatorDuplicatableInterface,
PluginFormcreatorConditionnableInterface,
PluginFormcreatorTranslatableInterface
{
   use PluginFormcreatorConditionnableTrait;
   use PluginFormcreatorExportableTrait;
   use PluginFormcreatorTranslatable;

   static public $itemtype = PluginFormcreatorForm::class;
   static public $items_id = 'plugin_formcreator_forms_id';

   private $skipChecks = false;

   /**
    * Number of columns in a section
    */
   const COLUMNS = 4;

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
      return _n('Section', 'Sections', $nb, 'formcreator');
   }

   public static function getIcon() {
      return 'fas fa-edit';
   }

   /**
    * May be removed when GLPI 9.5 will  be the lowest supported version
    * workaround use if entity in WHERE when using PluginFormcreatorQuestion::dropdown
    * (while editing conditions, list of questions is empty + SQL error)
    * @see bug on GLPI #6488, might be related
    */
   public function isEntityAssign() {
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

      return $tab;
   }

   public function getForbiddenStandardMassiveAction() {
      return [
         'update', 'clone', 'add_note',
      ];
   }

   /**
    * Prepare input data for adding the section
    * Check fields values and get the order for the new section
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
   **/
   public function prepareInputForAdd($input) {
      // Control fields values :
      // - name is required
      if (!isset($input['name']) ||
         (isset($input['name']) && empty($input['name']))) {
         Session::addMessageAfterRedirect(__('The title is required', 'formcreator'), false, ERROR);
         return [];
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      // Get next order
      if ($this->useAutomaticOrdering) {
         $maxOrder = PluginFormcreatorCommon::getMax($this, [
            self::$items_id => $input[self::$items_id]
         ], 'order');
         if ($maxOrder === null) {
            $input['order'] = 1;
         } else {
            $input['order'] = $maxOrder + 1;
         }
      }

      if (!$this->skipChecks) {
         if (!$this->checkConditionSettings($input)) {
            $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
         }
      }

      return $input;
   }

   /**
    * Prepare input datas for updating the form
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
   **/
   public function prepareInputForUpdate($input) {
      // Control fields values :
      // - name is required
      if (isset($input['name'])
            && empty($input['name'])) {
         Session::addMessageAfterRedirect(__('The title is required', 'formcreator'), false, ERROR);
         return [];
      }

      // generate a uniq id
      if (!isset($input['uuid'])
            || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      if (!$this->checkConditionSettings($input)) {
         $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
      }

      return $input;
   }

   public function pre_deleteItem() {
      return (new PluginFormcreatorCondition())->deleteByCriteria([
         'itemtype' => self::class,
         'items_id' => $this->getID(),
      ]);
   }

   public function post_addItem() {
      $this->updateConditions($this->input);
   }

   public function post_updateItem($history = 1) {
      $this->updateConditions($this->input);
   }

   /**
    * Actions done after the PURGE of the item in the database
    * Reorder other sections
    *
    * @return void
   */
   public function post_purgeItem() {
      global $DB;

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $rows = $DB->request([
         'SELECT' => 'id',
         'FROM' => self::getTable(),
         'WHERE' => [
            'order' => ['>', $this->fields['order']],
            $formFk => $this->fields[$formFk]
         ],
      ]);
      foreach ($rows as $row) {
         /** @var PluginFormcreatorSection $section */
         $section = self::getById($row['id']);
         $section->update([
            'id' => $row['id'],
            'order' => $section->fields['order'] - 1,
         ]);
      }

      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $question = new PluginFormcreatorQuestion();
      $question->deleteByCriteria([$sectionFk => $this->getID()], 1);
   }

   public function duplicate(array $options = []) {
      $linker = new PluginFormcreatorLinker($options);

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $export = $this->export(true);
      $export['order'] = PluginFormcreatorCommon::getMax(
         $this,
         [$formFk => $this->fields[$formFk]],
         'order'
      ) + 1;
      $newSectionId = static::import($linker, $export, $this->fields[$formFk]);

      if ($newSectionId === false) {
         return false;
      }
      $linker->linkPostponed();

      return $newSectionId;
   }

   /**
    * Move up a section by swapping it with the previous one
    * @return boolean true on success, false otherwise
    */
   public function moveUp() {
      global $DB;

      $order         = $this->fields['order'];
      $formId        = $this->fields['plugin_formcreator_forms_id'];
      $otherItem = new static();
      $otherItem->getFromDBByRequest([
         'WHERE' => [
            'AND' => [
               'plugin_formcreator_forms_id' => $formId,
               'order'                       => ['<', $order]
            ]
         ],
         'ORDER' => ['order DESC'],
         'LIMIT' => 1
      ]);
      if ($otherItem->isNewItem()) {
         return false;
      }
      $success = true;
      $DB->beginTransaction();
      $success = $success && $this->update([
         'id'     => $this->getID(),
         'order'  => $otherItem->fields['order'],
      ]);
      $success = $success && $otherItem->update([
         'id'     => $otherItem->getID(),
         'order'  => $order,
      ]);

      if (!$success) {
         $DB->rollBack();
      } else {
         $DB->commit();
      }

      return $success;
   }

   /**
    * Move down a section by swapping it with the next one
    * @return boolean true on success, false otherwise
    */
   public function moveDown() {
      global $DB;

      $order     = $this->fields['order'];
      $formId    = $this->fields['plugin_formcreator_forms_id'];
      $otherItem = new static();
      $otherItem->getFromDBByRequest([
         'WHERE' => [
            'AND' => [
               'plugin_formcreator_forms_id' => $formId,
               'order'                       => ['>', $order]
            ]
         ],
         'ORDER' => ['order ASC'],
         'LIMIT' => 1
      ]);
      if ($otherItem->isNewItem()) {
         return false;
      }
      $success = true;
      $DB->beginTransaction();
      $success = $success && $this->update([
         'id'     => $this->getID(),
         'order'  => $otherItem->fields['order'],
      ]);
      $success = $success && $otherItem->update([
         'id'     => $otherItem->getID(),
         'order'  => $order,
      ]);

      if (!$success) {
         $DB->rollBack();
      } else {
         $DB->commit();
      }

      return $success;
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      // restore key and FK
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk]        = $containerId;

      $item = new self();
      // Find an existing section to update, only if an UUID is available
      $itemId = false;
       /** @var string $idKey key to use as ID (id or uuid) */
       $idKey = 'id';
      if (isset($input['uuid'])) {
         // Try to find an existing item to update
         $idKey = 'uuid';
         $itemId = plugin_formcreator_getFromDBByField(
           $item,
           'uuid',
           $input['uuid']
         );
      }

      // Escape text fields
      foreach (['name'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // Add or update section
      $originalId = $input[$idKey];
      $item->skipChecks = true;
      if ($itemId !== false) {
         $input['id'] = $itemId;
         $item->update($input);
      } else {
         unset($input['id']);
         $item->useAutomaticOrdering = false;
         $itemId = $item->add($input);
      }
      $item->skipChecks = false;
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the section to the linker
      $linker->addObject($originalId, $item);

      $subItems = [
         '_questions'   => PluginFormcreatorQuestion::class,
         '_conditions' => PluginFormcreatorCondition::class,
      ];
      $item->importChildrenObjects($item, $linker, $subItems, $input);

      return $itemId;
   }

   public static function countItemsToImport(array $input) : int {
      $subItems = [
         '_questions'   => PluginFormcreatorQuestion::class,
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
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      unset($export[$formFk]);

      $subItems = [
         '_questions'  => PluginFormcreatorQuestion::class,
         '_conditions' => PluginFormcreatorCondition::class,
      ];
      $export = $this->exportChildrenObjects($subItems, $export, $remove_uuid);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($export[$idToRemove]);

      return $export;
   }

   /**
    * gets all sections in a form
    * @param int $formId ID of a form
    * @return self[] sections in a form
    */
   public static function getSectionsFromForm($formId) {
      global $DB;

      $sections = [];
      $rows = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_formcreator_forms_id' => $formId
         ],
         'ORDER'  => 'order ASC'
      ]);
      foreach ($rows as $row) {
         $section = new self();
         $section->getFromDB($row['id']);
         $sections[$row['id']] = $section;
      }

      return $sections;
   }

   public function showForm($ID, $options = []) {
      $this->initForm($ID, $options);
      $options['candel'] = false;
      $options['formoptions'] = sprintf('data-itemtype="%s" data-id="%s"', self::getType(), $ID);
      $options['target'] = "javascript:;";
      TemplateRenderer::getInstance()->display('@formcreator/pages/section.html.twig', [
         'item'   => $this,
         'params' => $options,
      ]);
   }

   /**
    * Get HTML for section at design time of a form
    *
    * @return string HTML
    */
   public function getDesignHtml(): string {
      $out = TemplateRenderer::getInstance()->render('@formcreator/components/form/section_design.html.twig', [
         'item'   => $this,
      ]);
      return $out;
   }

   /**
    * Is the given row empty ?
    *
    * @return boolean true if empty
    */
   public function isRowEmpty($row) {
      // TODO: handle multiple consecutive empty rows
      $dbUtil = new DBUtils();
      $sectionFk = static::getForeignKeyField();
      $count = $dbUtil->countElementsInTable(
         PluginFormcreatorQuestion::getTable(), [
            $sectionFk => $this->getID(),
            // Items where row is the same as the current item
            'OR' => [
               'row' => $row,
            // Items where row is less than the first row of this question
            // and overlap first row of this item
               'AND' => [
                  'row' => ['<', $row],
                  // To support variable height the expressin  below should be
                  // row + height - 1
                  new QueryExpression("`row` >= " . $row),
               ],
            ],
         ]
      );

      return ($count < 1);
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

      foreach ((new PluginFormcreatorQuestion())->getQuestionsFromSection($this->getID()) as $question) {
         foreach ($question->getTranslatableStrings($options) as $type => $subStrings) {
            $strings[$type] = array_merge($strings[$type], $subStrings);
         }
      }

      $strings = $this->deduplicateTranslatable($strings);

      return $strings;
   }

   public function getDesignLabel(): string {
      $sectionId = $this->getID();
      $nb = (new DBUtils())->countElementsInTable(PluginFormcreatorCondition::getTable(), [
         'itemtype' => self::getType(),
         'items_id' => $sectionId,
      ]);
      $formId = $this->fields[PluginFormcreatorForm::getForeignKeyField()];
      $onclick = 'plugin_formcreator.showSectionForm(' . $formId . ', ' . $sectionId . ');';
      $html = '<a href="#" onclick=' . $onclick . '" data-field="name">';
      $html .= "<sup class='plugin_formcreator_conditions_count' title='" . __('Count of conditions', 'formcreator') ."'>$nb</sup>";
      $html .= '<span>';
      $html .= empty($this->fields['name']) ? '(' . $sectionId . ')' : $this->fields['name'];
      $html .= '</span>';
      $html .= '</a>';

      return $html;
   }
}
