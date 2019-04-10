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

class PluginFormcreatorSection extends CommonDBChild implements PluginFormcreatorExportableInterface
{
   static public $itemtype = PluginFormcreatorForm::class;
   static public $items_id = "plugin_formcreator_forms_id";

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate() {
      return true;
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView() {
      return true;
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
      $export['uuid'] = plugin_formcreator_getUuid();
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

      $formFk = PluginFormcreatorForm::getForeignKeyField();

      $input[$formFk]        = $containerId;
      $input['_skip_checks'] = true;

      $item = new self;
      // Find an existing section to update, only if an UUID is available
      if (isset($input['uuid'])) {
         $sectionId = plugin_formcreator_getFromDBByField(
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
      if (!$item->isNewItem()) {
         $input['id'] = $sectionId;
         $originalId = $input['id'];
         $item->update($input);
      } else {
         $originalId = $input['id'];
         unset($input['id']);
         $sectionId = $item->add($input);
      }
      if ($sectionId === false) {
         throw new ImportFailureException();
      }

      // add the section to the linker
      if (isset($input['uuid'])) {
         $originalId = $input['uuid'];
      }
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
            PluginFormcreatorQuestion::import($linker, $question, $sectionId);
         }
      }

      return $sectionId;
   }

   public function export($remove_uuid = false) {
      global $DB;

      if ($this->isNewItem()) {
         return false;
      }

      $form_question = new PluginFormcreatorQuestion;
      $section       = $this->fields;

      // remove key and fk
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      unset($section[$formFk]);

      // get questions
      $section['_questions'] = [];
      $all_questions = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => $form_question::getTable(),
         'WHERE'  => [
            'plugin_formcreator_sections_id' => $this->getID()
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

   public function showSubForm($ID) {
      if ($ID == 0) {
         $name          = '';
         $uuid          = '';
      } else {
         $name          = $this->fields['name'];
         $uuid          = $this->fields['uuid'];
      }
      echo '<form name="form_section" method="post" action="'.static::getFormURL().'">';
      echo '<table class="tab_cadre_fixe">';
      echo '<tr>';
      echo '<th colspan="2">';
      if ($ID == 0) {
         echo  __('Add a section', 'formcreator');
      } else {
         echo  __('Edit a section', 'formcreator');
      }
      echo '</th>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td width="20%">'.__('Title').' <span style="color:red;">*</span></td>';
      echo '<td width="70%"><input type="text" name="name" style="width:100%;" value="'.$name.'" class="required"></td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td colspan="2" class="center">';
      echo '<input type="hidden" name="id" value="'.$ID.'" />';
      echo '<input type="hidden" name="uuid" value="'.$uuid.'" />';
      echo '<input type="hidden" name="plugin_formcreator_forms_id" value="'.intval($_REQUEST['form_id']).'" />';
      if ($ID == 0) {
         echo '<input type="hidden" name="add" value="1" />';
         echo '<input type="submit" name="add" class="submit_button" value="'.__('Add').'" />';
      } else {
         echo '<input type="hidden" name="update" value="1" />';
         echo '<input type="submit" name="update" class="submit_button" value="'.__('Edit').'" />';
      }
      echo '</td>';
      echo '</tr>';

      echo '</table>';
      Html::closeForm();
   }
}
