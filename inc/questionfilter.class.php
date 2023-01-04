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

/**
 * A question parameter to handle a depdency to an other question. For example
 * the content og the question A is computed from the content of the question B. In
 * this case the question A has this parameter to maitnain the dependency to the
 * question B
 */
class PluginFormcreatorQuestionFilter
extends PluginFormcreatorAbstractQuestionParameter
{
   use PluginFormcreatorTranslatable;

   public static function getTypeName($nb = 0) {
      return _n('Question filter', 'Question filters', $nb, 'formcreator');
   }

   public function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '4',
         'table'              => $this::getTable(),
         'field'              => 'filter',
         'name'               => __('Filter', 'formcreator'),
         'massiveaction'      => false,
      ];

      return $tab;
   }


   public function getParameterFormSize() {
      return 1;
   }

   public function getParameterForm(PluginFormcreatorQuestion $question) {
      // get the name of the HTML input field
      $name = '_parameters[' . $this->field->getFieldTypeName() . '][' . $this->fieldName . ']';

      // get the selected value in the dropdown
      $this->getFromDBByCrit([
         'plugin_formcreator_questions_id' => $question->getID(),
         'fieldname' => $this->fieldName,
      ]);

      // build HTML code
      // TODO: GLPI should be able to use predefined search criteria with very few changes
      // @see Search::showGenericSearch() which calls Search::displayCriteria passing its criterias
      // @see Search::displayCriteria() which may receives criterias in $request['criteria'] but fully ignores it

      // TODO: Improve GLPI, file templates/components/search/query_builder/main.html.twig
      //       - add support for custom buttons, to add a "apply" button.
      $criteria_backup = $_SESSION['glpisearch'][$question->fields['itemtype']]['criteria'] ?? [];
      $_SESSION['glpisearch'][$question->fields['itemtype']]['criteria'] = $this->fields['filter'];
      $out = TemplateRenderer::getInstance()->render(
         '@formcreator/questionparameter/filter.html.twig',
         [
            'item'     => $this,
            'question' => $question,
            'label'    => $this->label,
            'params'   => [
               'name'     => $name,
            ],
         ]
      );
      $_SESSION['glpisearch'][$question->fields['itemtype']]['criteria'] = $criteria_backup;
      return $out;
   }

   public function post_getEmpty() {
      $this->fields['filter'] = [];
   }

   public function post_getFromDB() {
      $this->fields['filter'] = json_decode($this->fields['filter'] ?? '[]', true);
   }

   public function pre_addInDB() {
      if (isset($this->input['filter'])) {
         $this->input['filter'] = json_encode($this->input['filter']);
      }
   }

   public function pre_updateInDB() {
      if (isset($this->fields['filter'])) {
         $this->fields['filter'] = json_encode($this->fields['filter']);
      }
   }

   public function prepareInputForAdd($input) {
      $input = parent::prepareInputForAdd($input);
      $input['fieldname'] = $this->fieldName;

      return $input;
   }

   public function post_updateItem($history = 1) {
      // filter was encoded in JSON in pre_updateInDB. Re-decode it again
      $this->fields['filter'] = json_decode($this->fields['filter'] ?? '[]', true);
   }

   private function encodeFilter() {
      $this->fields['filter'] = json_decode($this->fields['filter'] ?? '[]', true);
   }

   private function decodeFilter() {
      $this->fields['filter'] = json_decode($this->fields['filter'] ?? '[]', true);
   }

   public function getFieldName() {
      return $this->fieldName;
   }

   public function export(bool $remove_uuid = false) : array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
      }

      $parameter = $this->fields;

      $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
      unset($parameter[$questionFk]);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($parameter[$idToRemove]);

      return $parameter;
   }

   public static function import(PluginFormcreatorLinker $linker, array $input = [], int $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
      $input[$questionFk] = $containerId;

      $question = new PluginFormcreatorQuestion();
      $question->getFromDB($containerId);
      $field = $question->getSubField();

      $item = $field->getEmptyParameters();
      if (!isset($item[$input['fieldname']])) {
         throw new ImportFailureException(sprintf('Unsupported question parameter %1$s for %2$s', $input['fieldname'], static::getTypeName(1)));
      }
      $item = $item[$input['fieldname']];

      // Find an existing condition to update, only if an UUID is available
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

      // escape text fields
      foreach (['regex'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // Add or update condition
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
         throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the question to the linker
      $linker->addObject($originalId, $item);

      return $itemId;
   }

   public static function countItemsToImport($input) : int {
      return 1;
   }
}
