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

class PluginFormcreatorCondition extends CommonDBChild implements PluginFormcreatorExportableInterface
{
   use PluginFormcreatorExportable;

   static public $itemtype = 'itemtype';
   static public $items_id = 'items_id';

   const SHOW_RULE_ALWAYS = 1;
   const SHOW_RULE_HIDDEN = 2;
   const SHOW_RULE_SHOWN = 3;

   const SHOW_LOGIC_AND = 1;
   const SHOW_LOGIC_OR = 2;

   const SHOW_CONDITION_EQ = 1;
   const SHOW_CONDITION_NE = 2;
   const SHOW_CONDITION_LT = 3;
   const SHOW_CONDITION_GT = 4;
   const SHOW_CONDITION_LE = 5;
   const SHOW_CONDITION_GE = 6;
   const SHOW_CONDITION_QUESTION_VISIBLE = 7;
   const SHOW_CONDITION_QUESTION_INVISIBLE = 8;

   public static function getTypeName($nb = 0) {
      return _n('Condition', 'Conditions', $nb, 'formcreator');
   }


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

   public static function getEnumShowCondition() {
      return [
         self::SHOW_CONDITION_EQ => '=',
         self::SHOW_CONDITION_NE => '≠',
         self::SHOW_CONDITION_LT => '<',
         self::SHOW_CONDITION_GT => '>',
         self::SHOW_CONDITION_LE => '≤',
         self::SHOW_CONDITION_GE => '≥',
         self::SHOW_CONDITION_QUESTION_VISIBLE => __('is visible', 'formcreator'),
         self::SHOW_CONDITION_QUESTION_INVISIBLE => __('is not visible', 'formcreator'),
      ];
   }

   public function getEnumShowRule() {
      return [
         self::SHOW_RULE_ALWAYS => __('Always displayed', 'formcreator'),
         self::SHOW_RULE_HIDDEN => __('Hidden unless', 'formcreator'),
         self::SHOW_RULE_SHOWN  => __('Displayed unless', 'formcreator'),
      ];
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      // restore key and FK
      $input['items_id'] = $containerId;

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
      $linked = $linker->getObject($input['plugin_formcreator_questions_id'], PluginFormcreatorQuestion::class);
      if ($linked === false) {
         $linked = new PluginFormcreatorQuestion();
         $linked->getFromDBByCrit([
            $idKey => $input['plugin_formcreator_questions_id']
         ]);
         if ($linked->isNewItem()) {
            $linker->postpone($input[$idKey], $item->getType(), $input, $containerId);
            return false;
         }
      }
      $input['plugin_formcreator_questions_id'] = $linked->getID();

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
         throw new ImportFailureException(sprintf(__('failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
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

      unset($condition['items_id']);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      } else {
         // Convert IDs into UUIDs
         $question = new PluginFormcreatorQuestion();
         $question->getFromDB($condition['plugin_formcreator_questions_id']);
         $condition['plugin_formcreator_questions_id'] = $question->fields['uuid'];
      }
      unset($condition[$idToRemove]);

      return $condition;
   }

   /**
    * get conditions applied to an item
    *
    * @param PluginFormcreatorConditionnableInterface $item
    * @return PluginFotrmcreatorCondition[]
    */
   public function getConditionsFromItem(PluginFormcreatorConditionnableInterface $item) {
      global $DB;

      if ($item->isNewItem()) {
         return [];
      }

      $conditions = [];
      $rows = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'itemtype' => $item->getType(),
            'items_id' => $item->getID()
         ],
         'ORDER'  => 'order ASC'
      ]);
      foreach ($rows as $row) {
         $condition = new static();
         $condition->getFromDB($row['id']);
         $conditions[] = $condition;
      }

      return $conditions;
   }

   /**
    * Display HTML for conditions applied on an item
    *
    * @param PluginFormcreatorConditionnableInterface $item item where conditions applies to
    * @return void
    */
   public function showConditionsForItem(CommonDBTM $item) {
      $rand = mt_rand();

      echo '<tr>';
      echo '<td colspan="4">';
      Dropdown::showFromArray(
         'show_rule',
         $this->getEnumShowRule(),
         [
            'value'        => $item->fields['show_rule'],
            'on_change'    => 'plugin_formcreator_toggleCondition(this);',
            'rand'         => $rand,
         ]
      );
      echo '</td>';
      echo '</tr>';

      if ($item->fields['show_rule'] == PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
         return;
      }

      // Get existing conditions for the item
      $conditions = $this->getConditionsFromItem($item);
      foreach ($conditions as $condition) {
         echo $condition->getConditionHtml($item->fields);
      }
   }

   /**
    * return HTML to show a condition line for a question
    *
    * @param array $input
    *
    * @return string HTML to insert in a rendered web page
    */
    public function getConditionHtml($input) {
      if ($this->isNewItem()) {
         $this->getEmpty();
         $itemtype       = $input['itemtype'];
         $itemId         = $input['items_id'];
         $questionId     = '';
         $show_condition = self::SHOW_CONDITION_EQ;
         $show_value     = '';
         $show_logic     = '';
      } else {
         $itemtype       = $this->fields['itemtype'];
         $itemId         = $this->fields['items_id'];
         $questionId     = $this->fields['plugin_formcreator_questions_id'];
         $show_condition = $this->fields['show_condition'];
         $show_value     = $this->fields['show_value'];
         $show_logic     = $this->fields['show_logic'];
      }
      $item              = new $itemtype();

      // Get list of question in the form of the item
      if (!is_subclass_of($item, PluginFormcreatorConditionnableInterface::class)) {
         throw new Exception("$itemtype is not a " . PluginFormcreatorConditionnableInterface::class);
      }

      $rand = mt_rand();
      $html  = '';
      $html .= '<tr'
      . ' data-itemtype="' . self::class . '"'
      . ' data-id="' . $this->getID() . '"'
      . '">';
      $html .= '<td colspan="4">';
      $html .= '<div class="div_show_condition">';

      // Boolean operator
      $html.= '<div class="div_show_condition_logic">';
      $html.= Dropdown::showFromArray('_conditions[show_logic][]',
            self::getEnumShowLogic(),
            [
               'display'               => false,
               'value'                 => $show_logic,
               'display_emptychoice'   => false,
               'rand'                  => $rand,
            ]);
      $html.= '</div>';

      // dropdown of questions
      $form = new PluginFormcreatorForm();
      $questionListExclusion = [];
      switch ($itemtype) {
         case PluginFormcreatorForm::class:
            $form->getFromDB($itemId);
            break;

         case PluginFormcreatorSection::class:
            $sectionFk = PluginFormcreatorSection::getForeignKeyField();
            $questionListExclusion = [PluginFormcreatorQuestion::getTable() . '.' . $sectionFk => ['<>', $itemId]];
         case PluginFormcreatorTargetTicket::class:
         case PluginFormcreatorTargetChange::class:
            $form->getFromDB($input['plugin_formcreator_forms_id']);
            break;

         case PluginFormcreatorQuestion::class:
            if ($item->isNewID($itemId)) {
               $parentItemtype = $item::$itemtype;;
               $section = new $parentItemtype();
               $section->getFromDB($input[$parentItemtype::getForeignKeyField()]);
               $form->getFromDBBySection($section);
            } else {
               $item->getFromDB($itemId);
               $form->getFromDBByQuestion($item);
               $questionListExclusion = [PluginFormcreatorQuestion::getTable() . '.id' => ['<>', $itemId]];
            }
            break;

         default:
            throw new RuntimeException("Unsupported conditionnable");

      }
      $questionsInForm = (new PluginFormcreatorQuestion())->getQuestionsFromFormBySection($form->getID(), $questionListExclusion);
      $html.= '<div class="div_show_condition_field">';
      $html.= Dropdown::showFromArray(
         '_conditions[plugin_formcreator_questions_id][]',
         $questionsInForm, [
            'display'      => false,
            'value'        => $questionId,
            'rand'         => $rand,
         ]
      );
      $html.= '</div>';

      // Equality / inequality operator
      $html.= '<div class="div_show_condition_operator">';
      $showConditions = array_map(
         function ($item) {
            return htmlentities($item);
         },
         static::getEnumShowCondition()
      );

      $html.= Dropdown::showFromArray(
         '_conditions[show_condition][]',
         $showConditions, [
            'display'      => false,
            'value'        => $show_condition,
            'rand'         => $rand,
         ]
      );
      $html.= '</div>';

      // Value of comparison
      $html.= '<div class="div_show_condition_value">';
      $html.= Html::input('_conditions[show_value][]', [
         'class' => 'small_text',
         'size'  => '8',
         'value' => $show_value,
      ]);
      $html.= '</div>';

      // Buttons to add a new comparison or remove the curent one
      $onclick = 'plugin_formcreator_addEmptyCondition(this, \'' . $itemtype . '\', ' . $itemId . ')';
      $html.= '<div class="div_show_condition_add">';
      $html.= '<i class="fas fa-plus-circle" style="cursor: pointer;" onclick="' . $onclick . '"></i>&nbsp;</div>';

      $onclick = 'plugin_formcreator_removeNextCondition(this, \'' . $itemtype . '\', ' . $itemId . ')';
      $html.= '<div class="div_show_condition_remove">';
      $html.= '<i class="fas fa-minus-circle"  style="cursor: pointer;" onclick="' . $onclick . '"></i>&nbsp;</div>';

      $html.= '</div>';
      $html.= '</td>';
      $html.= '</tr>';

      return $html;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
   {
      $keepCriteria = [
         'itemtype' => $container->getType(),
         'items_id' => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }
}
