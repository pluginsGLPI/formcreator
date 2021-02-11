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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * A question parameter to handle a depdency to an other question. For example
 * the content og the question A is computed from the content of the question B. In
 * this case the question A has this parameter to maitnain the dependency to the
 * question B
 */
class PluginFormcreatorQuestionRange
extends PluginFormcreatorAbstractQuestionParameter
{
   use PluginFormcreatorTranslatable;

   protected $domId = 'plugin_formcreator_questionRange';

   public static function getTypeName($nb = 0) {
      return _n('Question range', 'Question ranges', $nb, 'formcreator');
   }

   public function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '4',
         'table'              => $this::getTable(),
         'field'              => 'range_min',
         'name'               => __('Minimum range', 'formcreator'),
         'datatype'           => 'integer',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this::getTable(),
         'field'              => 'range_max',
         'name'               => __('maximum range', 'formcreator'),
         'datatype'           => 'integer',
         'massiveaction'      => false,
      ];

      return $tab;
   }

   public function getParameterFormSize() {
      return 0;
   }

   public function getParameterForm(PluginFormcreatorForm $form, PluginFormcreatorQuestion $question) {
      // get the name of the HTML input field
      $name = '_parameters[' . $this->field->getFieldTypeName() . '][' . $this->fieldName . ']';

      // get the selected value in the dropdown
      $rangeMin = '';
      $rangeMax = '';
      $this->getFromDBByCrit([
         'plugin_formcreator_questions_id' => $question->getID(),
         'fieldname' => $this->fieldName,
      ]);
      if (!$this->isNewItem()) {
         $rangeMin = $this->fields['range_min'];
         $rangeMax = $this->fields['range_max'];
      }

      // build HTML code
      $selector = $this->domId;
      $out = '';
      $out.= '<td id="' . $selector . '">' . $this->label . '</td>';
      $out.= '<td>';
      $out.= '<label for="' . $name . '[range_min]" id="label_range_min">' . __('Min', 'formcreator') . '</label>&nbsp;';
      $out.= '<input type="text" name="'. $name . '[range_min]" id="range_min" class="small_text" style="width:90px;" value="'.$rangeMin.'" />';
      $out.= '&nbsp;<label for="' . $name . '[range_max]" id="label_range_max">' . __('Max', 'formcreator') . '</label>&nbsp;';
      $out.= '<input type="text" name="'. $name . '[range_max]" id="range_max" class="small_text" style="width:90px;" value="'.$rangeMax.'" />';
      $out.= '</td>';

      return $out;
   }

   public function post_getEmpty() {
      $this->fields['range_min'] = '0';
      $this->fields['range_max'] = '0';
   }

   public function getJsShowHideSelector() {
      return "#" . $this->domId;
   }

   public function prepareInputForAdd($input) {
      $input = parent::prepareInputForAdd($input);
      $input['fieldname'] = $this->fieldName;

      return $input;
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

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
      $input[$questionFk] = $containerId;

      $question = new PluginFormcreatorQuestion();
      $question->getFromDB($containerId);
      $field = PluginFormcreatorFields::getFieldInstance(
         $question->fields['fieldtype'],
         $question
      );

      $item = $field->getEmptyParameters();
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
      foreach (['range_min', 'range_max'] as $key) {
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
