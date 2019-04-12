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

class PluginFormcreatorQuestion_Condition extends CommonDBChild implements PluginFormcreatorExportableInterface
{
   static public $itemtype = PluginFormcreatorQuestion::class;
   static public $items_id = 'plugin_formcreator_questions_id';

   const SHOW_LOGIC_AND = 1;
   const SHOW_LOGIC_OR = 2;

   const SHOW_CONDITION_EQ = 1;
   const SHOW_CONDITION_NE = 2;
   const SHOW_CONDITION_LT = 3;
   const SHOW_CONDITION_GT = 4;
   const SHOW_CONDITION_LE = 5;
   const SHOW_CONDITION_GE = 6;

   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid'])
            || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public static function getEnumShowLogic() {
      return [
         self::SHOW_LOGIC_AND => 'AND',
         self::SHOW_LOGIC_OR  => 'OR',
      ];
   }

   public function getEnumShowCondition() {
      return [
         self::SHOW_CONDITION_EQ => '=',
         self::SHOW_CONDITION_NE => '≠',
         self::SHOW_CONDITION_LT => '<',
         self::SHOW_CONDITION_GT => '>',
         self::SHOW_CONDITION_LE => '≤',
         self::SHOW_CONDITION_GE => '≥',
      ];
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException('UUID or ID is mandatory');
      }

      $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
      $input[$questionFk] = $containerId;

      $item = new self();
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
      foreach (['show_value'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // set ID for linked objects
      $linked = $linker->getObject($input['show_field'], PluginFormcreatorQuestion::class);
      if ($linked === false) {
         $linker->postpone($input[$idKey], $item->getType(), $input, $containerId);
         return false;
      }
      $input['show_field'] = $linked->getID();

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
         throw new ImportFailureException('failed to add or update the item');
      }

      // add the question to the linker
      $linker->addObject($originalId, $item);

      return $itemId;
   }

   /**
    * Export in an array all the data of the current instanciated condition
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if ($this->isNewItem()) {
         return false;
      }

      $condition = $this->fields;

      $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
      unset($condition[$questionFk]);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      } else {
         // Convert IDs into UUIDs
         $question = new PluginFormcreatorQuestion();
         $question->getFromDB($condition['show_field']);
         $condition['show_field'] = $question->fields['uuid'];
      }
      unset($condition[$idToRemove]);

      return $condition;
   }

   /**
    * get show / hide conditions for a question
    *
    * @param int $questionId
    * @return array
    */
   public function getConditionsFromQuestion($questionId) {
      global $DB;

      $questionConditions = [];
      $rows = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_formcreator_questions_id' => $questionId
         ],
         'ORDER'  => 'order ASC'
      ]);
      foreach ($rows as $row) {
         $questionCondition = new static();
         $questionCondition->getFromDB($row['id']);
         $questionConditions[] = $questionCondition;
      }

      return $questionConditions;
   }

   /**
    *
    * return HTML to show a condition line for a question
    *
    * @param integer $formId ID of the form of the condition
    * @param integer $questionId ID of the question (or 0 for a new question)
    * @param string $isFirst true if this is the first condition in all conditions applied to a question
    *
    * @return string
    */
   public function getConditionHtml($form_id, $questionId = 0, $isFirst = false) {
      global $CFG_GLPI;

      if ($this->isNewItem()) {
         $show_field       = '';
         $show_condition   = PluginFormcreatorQuestion_Condition::SHOW_CONDITION_EQ;
         $show_value       = '';
         $show_logic       = '';
      } else {
         $show_field       = $this->fields['show_field'];
         $show_condition   = $this->fields['show_condition'];
         $show_value       = $this->fields['show_value'];
         $show_logic       = $this->fields['show_logic'];
         $questionId       = $this->fields['plugin_formcreator_questions_id'];
      }
      $rand = mt_rand();

      $question = new PluginFormcreatorQuestion();
      $questionsInForm = $question->getQuestionsFromForm($form_id);
      $questions_tab = [];
      foreach ($questionsInForm as $question) {
         if (strlen($question->getField('name')) > 30) {
            $questions_tab[$question->getID()] = substr($question->getField('name'),
                  0,
                  strrpos(substr($question->getField('name'), 0, 30), ' ')) . '...';
         } else {
            $questions_tab[$question->getID()] = $question->getField('name');
         }
      }

      $html = '';
      $html.= '<tr class="plugin_formcreator_logicRow">';
      $html.= '<td colspan="4">';
      $html.= '<div class="div_show_condition">';

      $showLogic = $isFirst ? 'style="display: none"' : '';
      $html.= '<div class="div_show_condition_logic"' . $showLogic . '>';
      $html.= Dropdown::showFromArray('show_logic[]',
            PluginFormcreatorQuestion_Condition::getEnumShowLogic(),
            [
               'display'               => false,
               'value'                 => $show_logic,
               'display_emptychoice'   => false,
               'rand'                  => $rand,
            ]);
      $html.= '</div>';
      $html.= '<div class="div_show_condition_field">';
      $html.= Dropdown::showFromArray('show_field[]', $questions_tab, [
         'display'      => false,
         'used'         => [$questionId => ''],
         'value'        => $show_field,
         'rand'         => $rand,
      ]);
      $html.= '</div>';

      $html.= '<div class="div_show_condition_operator">';
      $showConditions = array_map(
         function ($item) {
            return htmlentities($item);
         },
         PluginFormcreatorQuestion_Condition::getEnumShowCondition()
      );

      $html.= Dropdown::showFromArray(
         'show_condition[]',
         $showConditions, [
            'display'      => false,
            'value'        => $show_condition,
            'rand'         => $rand,
         ]
      );
      $html.= '</div>';
      $html.= '<div class="div_show_condition_value">';
      $html.= '<input type="text" name="show_value[]" id="show_value" class="small_text"'
              .'value="'. $show_value . '" size="8">';
      $html.= '</div>';
      $html.= '<div class="div_show_condition_add">';
      $html.= '<img src="../../../pics/plus.png" onclick="plugin_formcreator_addEmptyCondition(this)"/>&nbsp;</div>';
      $html.= '<div class="div_show_condition_remove">';
      $html.= '<img src="../../../pics/moins.png" onclick="plugin_formcreator_removeNextCondition(this)"/></div>';
      $html.= '</div>';
      $html.= '</td>';
      $html.= '</tr>';

      return $html;
   }
}
