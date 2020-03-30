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
 * @copyright Copyright © 2011 - 2020 Teclib'
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

   public static function getEnumShowRule() {
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

   public function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'massiveaction'      => false, // implicit field is id
         'datatype'           => 'number'
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => PluginFormcreatorSection::getTable(),
         'field'              => 'show_logic',
         'name'               => __('Show logic', 'formcreator'),
         'datatype'           => 'specific',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'show_logic',
         'name'               => __('Show logic', 'formcreator'),
         'datatype'           => 'specific',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => PluginFormcreatorQuestion::getTable(),
         'field'              => 'plugin_formcreator_questions_id',
         'name'               => _n('Question', 'Questions', 1, 'formcreator'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'show_condition',
         'name'               => __('Show condition', 'formcreator'),
         'datatype'           => 'specific',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'show_value',
         'name'               => __('Value', 'formcreator'),
         'datatype'           => 'string',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '12',
         'table'              => $this->getTable(),
         'field'              => 'uuid',
         'name'               => __('UUID', 'formcreator'),
         'datatype'           => 'string',
         'nosearch'           => true,
         'massiveaction'      => false
      ];

      return $tab;
   }

   /**
    * Define how to display search field for a specific type
    *
    * @since version 0.84
    *
    * @param String $field           Name of the field as define in $this->rawSearchOptions()
    * @param String $name            Name attribute for the field to be posted (default '')
    * @param Array  $values          Array of all values to display in search engine (default '')
    * @param Array  $options         Options (optional)
    *
    * @return String                 Html string to be displayed for the form field
    */
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'show_logic':
            if ($name == '') {
               $name = $field;
            }
            $rules = self::getEnumShowLogic();
            $options['value'] = $values[$field];
            $options['display_emptychoice'] = false;
            return Dropdown::showFromArray($name, $rules, $options);
            break;

         case 'show_rule':
            if ($name == '') {
               $name = $field;
            }
            $rules = self::getEnumShowRule();
            $options['value'] = $values[$field];
            $options['display_emptychoice'] = false;
            return Dropdown::showFromArray($name, $rules, $options);
            break;

         case 'show_condition':
            if ($name == '') {
               $name = $field;
            }
            $rules = self::getEnumShowCondition();
            $options['value'] = $values[$field];
            $options['display_emptychoice'] = false;
            return Dropdown::showFromArray($name, $rules, $options);
            break;
         }
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
    * @param CommonDBTM $item
    * @return array array of PluginFotrmcreatorCondition
    */
   public function getConditionsFromItem(CommonDBTM $item) {
      global $DB;

      if ($item->isNewItem()) {
         return [];
      }

      $conditions = [];
      $rows = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'itemtype' => get_class($item),
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
    * @param CommonDBTM $item item where conditions applies to
    * @param array      $options
    * @return void|string
    */
   public function showConditionsForItem(CommonDBTM $item, $options = []) {
      $defaultOptions = [
         'display' => true,
      ];
      $options = array_merge($defaultOptions, $options);

      $rand = mt_rand();

      $html = '<tr>';
      $html .= '<td colspan="4">';
      $html .=Dropdown::showFromArray(
         'show_rule',
         self::getEnumShowRule(),
         [
            'value'        => $item->fields['show_rule'],
            'on_change'    => 'plugin_formcreator_toggleCondition(this);',
            'rand'         => $rand,
            'display'      => false,
         ]
      );
      $html .= '</td>';
      $html .= '</tr>';

      // Get existing conditions for the item
      $conditions = $this->getConditionsFromItem($item);
      foreach ($conditions as $condition) {
         $html .= $condition->getConditionHtml($item->fields);
      }

      if (!$options['display']) {
         return $html;
      }

      echo $html;
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
         $this->fields['show_condition'] = self::SHOW_CONDITION_EQ;
         $this->fields['itemtype']       = $input['itemtype'];
         $this->fields['items_id']       = $input['items_id'];
      }
      $itemtype       = $this->fields['itemtype'];
      $item           = new $itemtype();

      // Get list of question in the form of the item
      if (!is_subclass_of($item, PluginFormcreatorConditionnableInterface::class)) {
         throw new Exception("$itemtype is not a " . PluginFormcreatorConditionnableInterface::class);
      }

      $data = [
         'so'        => [
            self::getType() => $this->searchOptions()
         ],
         'condition' => $this,
      ];

      return plugin_formcreator_render(
         'condition/showconditionforitem.html.twig',
         $data,
         ['display' => false,]
      );
   }

   public function getDropdownCondition($fieldName) {
      switch ($fieldName) {
         case PluginFormcreatorQuestion::getForeignKeyField():
            $itemtype = $this->fields['itemtype'];
            $itemId   = $this->fields['items_id'];
            $condition = [];
            $form = new PluginFormcreatorForm();
            switch ($this->fields['itemtype']) {
               case PluginFormcreatorSection::class:
                  $sectionFk = PluginFormcreatorSection::getForeignKeyField();
                  $condition = [PluginFormcreatorQuestion::getTable() . '.' . $sectionFk => ['<>', $itemId]];
                  break;

               case PluginFormcreatorQuestion::class:
                  $item = new $itemtype();
                  if ($item->isNewID($itemId)) {
                     $section = new PluginFormcreatorSection();
                     $section->getFromDB($$this->fields[PluginFormcreatorSection::getForeignKeyField()]);
                     $form->getFromDBBySection($section);
                  } else {
                     $item->getFromDB($itemId);
                     $form->getFromDBByQuestion($item);
                     $condition = [PluginFormcreatorQuestion::getTable() . '.id' => ['<>', $itemId]];
                  }
                  break;
            }
            $sections = (new PluginFormcreatorSection())->getSectionsFromForm($form->getID());
            $sectionsList = [];
            foreach ($sections as $section) {
               $sectionsList[] = $section->getID();
            }
            $condition[] = [
               PluginFormcreatorSection::getForeignKeyField() => $sectionsList,
            ];
            return $condition;
      }

      return [];
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
