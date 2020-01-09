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

use GlpiPlugin\Formcreator\Exception\ImportFailureException;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorSection extends CommonDBChild implements 
PluginFormcreatorExportableInterface,
PluginFormcreatorDuplicatableInterface,
PluginFormcreatorConditionnableInterface
{
   static public $itemtype = PluginFormcreatorForm::class;
   static public $items_id = 'plugin_formcreator_forms_id';

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Section', 'Sections', $nb, 'formcreator');
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
         (isset($input['name']) && empty($input['name'])) ) {
         Session::addMessageAfterRedirect(__('The title is required', 'formcreator'), false, ERROR);
         return [];
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      // Get next order
      $formId = $input['plugin_formcreator_forms_id'];
      $maxOrder = PluginFormcreatorCommon::getMax($this, [
         "plugin_formcreator_forms_id" => $formId
      ], 'order');
      if ($maxOrder === null) {
         $input['order'] = 1;
      } else {
         $input['order'] = $maxOrder + 1;
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

      return $input;
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
      $DB->update(
         self::getTable(),
         new QueryExpression("`order` = `order` - 1"),
         [
            'order' => ['>', $this->fields['order']],
            $formFk => $this->fields[$formFk]
         ]
      );

      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $question = new PluginFormcreatorQuestion();
      $question->deleteByCriteria([$sectionFk => $this->getID()], 1);
   }

   /**
    * Duplicate a section
    *
    * @return integer|boolean ID of the new section, false otherwise
    */
   public function duplicate() {
      $linker = new PluginFormcreatorLinker();

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $export = $this->export(true);
      $newSectionId = static::import($linker, $export, $this->fields[$formFk]);

      if ($newSectionId === false) {
         return false;
      }
      $linker->linkPostponed();

      return $newSectionId;
   }

   public function moveUp() {
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
      if (!$otherItem->isNewItem()) {
         $this->update([
            'id'     => $this->getID(),
            'order'  => $otherItem->getField('order'),
         ]);
         $otherItem->update([
            'id'     => $otherItem->getID(),
            'order'  => $order,
         ]);
      }
   }

   public function moveDown() {
      $order         = $this->fields['order'];
      $formId     = $this->fields['plugin_formcreator_forms_id'];
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
      if (!$otherItem->isNewItem()) {
         $this->update([
            'id'     => $this->getID(),
            'order'  => $otherItem->getField('order'),
         ]);
         $otherItem->update([
            'id'     => $otherItem->getID(),
            'order'  => $order,
         ]);
      }
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException('UUID or ID is mandatory');
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk]        = $containerId;
      $input['_skip_checks'] = true;

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
      if ($itemId !== false) {
         $input['id'] = $itemId;
         $item->update($input);
      } else {
         unset($input['id']);
         $itemId = $item->add($input);
      }
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the section to the linker
      $linker->addObject($originalId, $item);

      if (isset($input['_questions'])) {
         // sort questions by order
         usort($input['_questions'], function ($a, $b) {
            if ($a['order'] == $b['order']) {
               return 0;
            }
            return ($a['order'] < $b['order']) ? -1 : 1;
         });

         foreach ($input['_questions'] as $question) {
            PluginFormcreatorQuestion::import($linker, $question, $itemId);
         }
      }

      return $itemId;
   }

   public function export($remove_uuid = false) {
      global $DB;

      if ($this->isNewItem()) {
         return false;
      }

      $section       = $this->fields;

      // remove key and fk
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      unset($section[$formFk]);

      // get questions
      $form_question = new PluginFormcreatorQuestion;
      $section['_questions'] = [];
      $all_questions = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => $form_question::getTable(),
         'WHERE'  => [
            self::getForeignKeyField() => $this->getID()
         ]
      ]);
      foreach ($all_questions as $question) {
         if ($form_question->getFromDB($question['id'])) {
            $section['_questions'][] = $form_question->export($remove_uuid);
         }
      }

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($section[$idToRemove]);

      return $section;
   }

   /**
    * gets all sections in a form
    * @param integer $formId ID of a form
    * @return array sections in a form
    */
   public function getSectionsFromForm($formId) {
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
      if ($ID == 0) {
         $title =  __('Add a section', 'formcreator');
      } else {
         $title =  __('Edit a section', 'formcreator');
      }
      echo '<form name="plugin_formcreator_form" method="post" action="'.static::getFormURL().'">';
      echo '<table class="tab_cadre_fixe">';
     
      echo '<tr>';
      echo '<th colspan="4">';
      echo $title;
      echo '</th>';
      echo '</tr>';

      echo '<tr>';
      echo '<td width="20%">'.__('Title').' <span style="color:red;">*</span></td>';
      echo '<td colspan="3">';
      echo Html::input('name', ['style' => 'width: calc(100% - 20px)', 'value' => $this->fields['name']]);
      echo '</td>';
      echo '</tr>';

      $form = new PluginFormcreatorForm();
      $form->getFromDBBySection($this);
      $condition = new PluginFormcreatorCondition();
      $condition->showConditionsForItem($form, $this);

      echo '<tr>';
      echo '<td colspan="4" class="center">';
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      echo Html::hidden('id', ['value' => $ID]);
      echo Html::hidden('uuid', ['value' => $this->fields['uuid']]);
      echo Html::hidden($formFk, ['value' => $this->fields[$formFk]]);
      echo '</td>';
      echo '</tr>';

      $this->showFormButtons($options + [
         'candel' => false
      ]);
      Html::closeForm();
   }

   /**
    * Get either:
    *  - section and questions of the target parent form
    *  - questions of target section
    *
    * @param int $parent target parent form
    * @param int $id target section
    * @return array
    */
   public static function getFullData($parent, $id = null) {
      global $DB;

      if ($parent) {
         $data = [];
         $data['_sections'] = iterator_to_array($DB->request([
            'FROM' => \PluginFormcreatorSection::getTable(),
            'WHERE' => ["plugin_formcreator_forms_id" => $parent]
         ]));

         $ids = [];
         foreach ($data['_sections'] as $section) {
            $ids[] = $section['id'];
         }

         if (!count($ids)) {
            $ids[] = -1;
         }

         $data = $data + \PluginFormcreatorQuestion::getFullData($ids);
      } else {
         if ($id == null) {
            throw new \InvalidArgumentException(
               "Parameter 'id' can't be null if parameter 'parent' is not specified"
            );
         }

         $data = \PluginFormcreatorQuestion::getFullData(null, $id);
      }

      return $data;
   }

   public function post_getFromDB() {
      // Set additional data for the API
      if (isAPI()) {
         $this->fields += self::getFullData(null, $this->fields['id']);
      }
   }

   /**
    * Updates the conditions of the question
    * @param array $input
    * @return boolean true if success, false otherwise
    */
    public function updateConditions($input) {
      if (!isset($input['plugin_formcreator_questions_id']) || !isset($input['show_condition'])
         || !isset($input['show_value']) || !isset($input['show_logic'])) {
         return  false;
      }

      if (!is_array($input['plugin_formcreator_questions_id']) || !is_array($input['show_condition'])
         || !is_array($input['show_value']) || !is_array($input['show_logic'])) {
         return false;
      }

      // All arrays of condition exists
      if ($input['show_rule'] == PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
         return false;
      }

      if (!(count($input['plugin_formcreator_questions_id']) == count($input['show_condition'])
            && count($input['show_value']) == count($input['show_logic'])
            && count($input['plugin_formcreator_questions_id']) == count($input['show_value']))) {
         return false;
      }

      // Delete all existing conditions for the question
      $condition = new PluginFormcreatorCondition();
      $condition->deleteByCriteria([
         'itemtype' => static::class,
         'items_id' => $input['id'],
      ]);

      // Arrays all have the same count and have at least one item
      $order = 0;
      while (count($input['plugin_formcreator_questions_id']) > 0) {
         $order++;
         $value            = array_shift($input['show_value']);
         $questionID       = (int) array_shift($input['plugin_formcreator_questions_id']);
         $showCondition    = html_entity_decode(array_shift($input['show_condition']));
         $showLogic        = array_shift($input['show_logic']);
         $condition = new PluginFormcreatorCondition();
         $condition->add([
            'itemtype'                        => static::class,
            'items_id'                        => $input['id'],
            'plugin_formcreator_questions_id' => $questionID,
            'show_condition'                  => $showCondition,
            'show_value'                      => $value,
            'show_logic'                      => $showLogic,
            'order'                           => $order,
         ]);
         if ($condition->isNewItem()) {
            return false;
         }
      }

      return true;
   }
}
