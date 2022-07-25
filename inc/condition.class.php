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

class PluginFormcreatorCondition extends CommonDBChild implements PluginFormcreatorExportableInterface
{
   use PluginFormcreatorExportableTrait;

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
   const SHOW_CONDITION_REGEX = 9;

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

   /**
    * Undocumented function
    *
    * @return array
    */
   public static function getEnumShowLogic() : array {
      return [
         self::SHOW_LOGIC_AND => 'AND',
         self::SHOW_LOGIC_OR  => 'OR',
      ];
   }

   /**
    * Undocumented function
    *
    * @return array
    */
   public static function getEnumShowCondition() : array {
      return [
        self::SHOW_CONDITION_EQ => '=',
        self::SHOW_CONDITION_NE => '≠',
        self::SHOW_CONDITION_LT => '<',
        self::SHOW_CONDITION_GT => '>',
        self::SHOW_CONDITION_LE => '≤',
        self::SHOW_CONDITION_GE => '≥',
        self::SHOW_CONDITION_QUESTION_VISIBLE => __('is visible', 'formcreator'),
        self::SHOW_CONDITION_QUESTION_INVISIBLE => __('is not visible', 'formcreator'),
        self::SHOW_CONDITION_REGEX => __('regular expression matches', 'formcreator'),
      ];
   }

   /**
    * Get rules for conditions
    *
    * @return array
    */
   public static function getEnumShowRule(): array {
      return [
        self::SHOW_RULE_ALWAYS => __('Always displayed', 'formcreator'),
        self::SHOW_RULE_HIDDEN => __('Hidden unless', 'formcreator'),
        self::SHOW_RULE_SHOWN  => __('Displayed unless', 'formcreator'),
      ];
   }

   public static function import(PluginFormcreatorLinker $linker, array $input = [], int $containerId = 0) {
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
      /** @var CommonDBTM $linked */
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
         throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the question to the linker
      $linker->addObject($originalId, $item);

      return $itemId;
   }


   public static function countItemsToImport(array $input) : int {
      return 1;
   }

   /**
    * Export in an array all the data of the current instanciated condition
    * @param bool $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export(bool $remove_uuid = false) : array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
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
    * @param CommonDBTM $item
    * @return PluginFormcreatorCondition[]
    */
   public static function getConditionsFromItem(CommonDBTM $item) : array {
      global $DB;

      if ($item->isNewItem()) {
         return [];
      }

      $conditions = [];
      $rows = $DB->request([
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'itemtype' => $item->getType(),
            'items_id' => $item->getID()
         ],
         'ORDER'  => 'order ASC'
      ]);
      foreach ($rows as $row) {
         $condition = new static();
         $condition->getFromResultSet($row);
         $conditions[] = $condition;
      }

      return $conditions;
   }

   public static function countForItem(PluginFormcreatorConditionnableInterface $item): int {
      /** @var CommonDBTM $item */
      return (new DBUtils())->countElementsInTable(PluginFormcreatorCondition::getTable(), [
         'itemtype' => $item::getType(),
         'items_id' => $item->getID(),
      ]);
   }

   /**
    * Display HTML for conditions applied on an item
    *
    * @param PluginFormcreatorConditionnableInterface $item item where conditions applies to
    * @return void
    */
   public function showConditionsForItem(PluginFormcreatorConditionnableInterface $item) {
      echo '<tr><th class="center" colspan="4">' . __('Conditions', 'formcreator') . '</th></tr>';
      echo '<td colspan="4">';
      echo '<div class="row">';
      echo '<div class="col-12 col-sm-5 mb-3">';
      Dropdown::showFromArray(
         'show_rule',
         $item::getEnumShowRule(),
         [
            'value'        => $item->fields['show_rule'],
            'on_change'    => 'plugin_formcreator_toggleCondition(this);',
         ]
      );
      echo '<div>';
      echo "<div>&nbsp;</div>";
      echo '</div>';
      echo '</td>';
      echo '</tr>';

      if ($item->fields['show_rule'] == PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
         return;
      }

      // Get existing conditions for the item
      /** @var CommonDBTM $item */
      $conditions = self::getConditionsFromItem($item);
      foreach ($conditions as $condition) {
         echo '<tr><td colspan="4">';
         echo $condition->getConditionHtml($item);
         echo '</td></tr>';
      }
   }

   /**
    * get SQL WHERE clause to exclude questions from questions dropdown
    *
    * @param PluginFormcreatorConditionnableInterface $item
    * @return void
    */
   public static function getQuestionsExclusion(?PluginFormcreatorConditionnableInterface $item) {
      if ($item === null) {
         return [];
      }

      /** @var CommonDBTM $item */
      if ($item instanceof PluginFormcreatorForm) {
         return [];
      } else if ($item instanceof PluginFormcreatorSection) {
         if ($item->isNewItem()) {
            $formFk = PluginFormcreatorForm::getForeignKeyField();
            $sections = (new PluginFormcreatorSection())->getSectionsFromForm($item->fields[$formFk]);
            $sectionsList = [];
            foreach ($sections as $section) {
               $sectionsList[] = $section->getID();
            }
            $questionListExclusion = [];
            if (count($sectionsList) > 0) {
               $questionListExclusion[] = [
                  PluginFormcreatorSection::getForeignKeyField() => $sectionsList,
               ];
            }
            return $questionListExclusion;
         }
         $sectionFk = PluginFormcreatorSection::getForeignKeyField();
         return [PluginFormcreatorQuestion::getTable() . '.' . $sectionFk => ['<>', $item->getID()]];
      } else if ($item instanceof PluginFormcreatorQuestion) {
         if (!$item->isNewItem()) {
            return [PluginFormcreatorQuestion::getTable() . '.id' => ['<>', $item->getID()]];
         }
         return [];
      }
      if (in_array($item::getType(), PluginFormcreatorForm::getTargetTypes())) {
         // No question exclusion for targets
         return [];
      }

      throw new RuntimeException("Unsupported conditionnable");
   }

   /**
    * return HTML to show a condition line for a question
    *
    * @return string HTML to insert in a rendered web page
    */
   public function getConditionHtml(CommonDBTM $parent): string {
      $itemtype = $parent->getType();
      if (!is_subclass_of($itemtype, PluginFormcreatorConditionnableInterface::class)) {
         // security check
         throw new RuntimeException("$itemtype is not a " . PluginFormcreatorConditionnableInterface::class);
      }

      $out = TemplateRenderer::getInstance()->render('@formcreator/components/form/condition.html.twig', [
         'condition' => $this,
         'parent'    => $parent
      ]);

      return $out;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) : bool {
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
