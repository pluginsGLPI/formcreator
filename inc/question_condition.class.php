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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorQuestion_Condition extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorQuestion";
   static public $items_id = "plugin_formcreator_questions_id";

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
         'AND'    => 'AND',
         'OR'     => 'OR',
      ];
   }

   /**
    * Import a question's condition into the db
    * @see PluginFormcreatorQuestion::import
    *
    * @param  integer $questions_id  id of the parent question
    * @param  array   $condition the condition data (match the condition table)
    * @param boolean  storeOnly
    *
    * @return integer the condition's id
    */
   public static function import($questions_id = 0, $condition = [], $storeOnly = true) {
      static $conditionsToImport = [];

      if ($storeOnly) {
         $condition['plugin_formcreator_questions_id'] = $questions_id;

         $item = new static();
         if ($conditions_id = plugin_formcreator_getFromDBByField($item, 'uuid', $condition['uuid'])) {
            // add id key
            $condition['id'] = $conditions_id;

            // prepare update condition
            $conditionsToImport[] = $condition;
         } else {
            // prepare create condition
            $conditionsToImport[] = $condition;
         }
      } else {
         // Assumes all questions needed for the stored conditions exist
         foreach ($conditionsToImport as $condition) {
            $item = new static();
            $question = new PluginFormcreatorQuestion();
            $condition['show_field'] = plugin_formcreator_getFromDBByField($question, 'uuid', $condition['show_field']);
            $condition['show_value'] = Toolbox::addslashes_deep($condition['show_value']);
            if (isset($condition['id'])) {
               $item->update($condition);
            } else {
               $item->add($condition);
            }
         }
         $conditionsToImport = [];
      }
   }

   /**
    * Export in an array all the data of the current instanciated condition
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $question = new PluginFormcreatorQuestion();
      $question->getFromDB($this->fields['show_field']);
      $condition = $this->fields;
      $condition['show_field'] = $question->getField('uuid');

      unset($condition['id'],
            $condition['plugin_formcreator_questions_id']);

      if ($remove_uuid) {
         $condition['uuid'] = '';
      }

      return $condition;
   }

   public function getConditionsFromQuestion($questionId) {
      $questionConditions = [];
      $rows = $this->find("`plugin_formcreator_questions_id` = '$questionId'", "`order` ASC");
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
         $show_condition   = '==';
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
         'used'         => array($questionId => ''),
         'value'        => $show_field,
         'rand'         => $rand,
      ]);
      $html.= '</div>';

      $html.= '<div class="div_show_condition_operator">';
      $html.= Dropdown::showFromArray('show_condition[]', [
         '=='           => '=',
         '!='           => '&ne;',
         '<'            => '&lt;',
         '>'            => '&gt;',
         '<='           => '&le;',
         '>='           => '&ge;',
      ], [
         'display'      => false,
         'value'        => $show_condition,
         'rand'         => $rand,
      ]);
      $html.= '</div>';
      $html.= '<div class="div_show_condition_value">';
      $html.= '<input type="text" name="show_value[]" id="show_value" class="small_text"'
              .'value="'. $show_value . '" size="8">';
      $html.= '</div>';
      $html.= '<div class="div_show_condition_add">';
      $html.= '<img src="../../../pics/plus.png" onclick="addEmptyCondition(this)"/>&nbsp;</div>';
      $html.= '<div class="div_show_condition_remove">';
      $html.= '<img src="../../../pics/moins.png" onclick="removeNextCondition(this)"/></div>';
      $html.= '</div>';
      $html.= '</td>';
      $html.= '</tr>';

      return $html;
   }
}