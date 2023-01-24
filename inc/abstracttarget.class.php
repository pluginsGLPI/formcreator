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

use Glpi\Application\View\TemplateRenderer;
use Glpi\Toolbox\Sanitizer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

abstract class PluginFormcreatorAbstractTarget extends CommonDBChild implements
   PluginFormcreatorExportableInterface,
   PluginFormcreatorTargetInterface,
   PluginFormcreatorConditionnableInterface,
   PluginFormcreatorTranslatableInterface
{
   use PluginFormcreatorConditionnableTrait;
   use PluginFormcreatorExportableTrait;
   use PluginFormcreatorTranslatable;

   static public $itemtype = PluginFormcreatorForm::class;
   static public $items_id = 'plugin_formcreator_forms_id';

   protected $form = null;

   public $skipChecks = false;

   /**
    * Generate the target
    *
    * @param PluginFormcreatorFormAnswer $formanswer
    * @return CommonDBTM|null the generated item
    */
   abstract public function save(PluginFormcreatorFormAnswer $formanswer): ?CommonDBTM;

   /**
    * Get the class name of the target itemtype
    *
    * @return string
    */
   abstract public static function getTargetItemtypeName(): string;

   /**
    * get fields containing tags for target generation
    * the tags are replaced when target is generated
    * with label of questions and answers to questions
    *
    * @return array field names used as templates
    */
   abstract protected function getTaggableFields();

   const DESTINATION_ENTITY_CURRENT = 1;
   const DESTINATION_ENTITY_REQUESTER = 2;
   const DESTINATION_ENTITY_REQUESTER_DYN_FIRST = 3;
   const DESTINATION_ENTITY_REQUESTER_DYN_LAST = 4;
   const DESTINATION_ENTITY_FORM = 5;
   const DESTINATION_ENTITY_VALIDATOR = 6;
   const DESTINATION_ENTITY_SPECIFIC = 7;
   const DESTINATION_ENTITY_USER = 8;
   const DESTINATION_ENTITY_ENTITY = 9;

   const TARGET_TYPE_OBJECT = 1;
   const TARGET_TYPE_ACTION = 2;

   public static function getEnumDestinationEntity() {
      return [
         self::DESTINATION_ENTITY_CURRENT   => __('Current active entity', 'formcreator'),
         self::DESTINATION_ENTITY_REQUESTER => __("Default requester user's entity", 'formcreator'),
         self::DESTINATION_ENTITY_REQUESTER_DYN_FIRST => __("First dynamic requester user's entity (alphabetical)", 'formcreator'),
         self::DESTINATION_ENTITY_REQUESTER_DYN_LAST => __("Last dynamic requester user's entity (alphabetical)", 'formcreator'),
         self::DESTINATION_ENTITY_FORM      => __('The form entity', 'formcreator'),
         self::DESTINATION_ENTITY_VALIDATOR => __('Default entity of the validator', 'formcreator'),
         self::DESTINATION_ENTITY_SPECIFIC  => __('Specific entity', 'formcreator'),
         self::DESTINATION_ENTITY_USER      => __('Default entity of a user type question answer', 'formcreator'),
         self::DESTINATION_ENTITY_ENTITY    => __('From a GLPI object > Entity type question answer', 'formcreator'),
      ];
   }

   /**
    * Get conditions rules
    *
    * @return array
    */
   public static function getEnumShowRule(): array {
      return [
         PluginFormcreatorCondition::SHOW_RULE_ALWAYS => __('Always generated', 'formcreator'),
         PluginFormcreatorCondition::SHOW_RULE_HIDDEN => __('Disabled unless', 'formcreator'),
         PluginFormcreatorCondition::SHOW_RULE_SHOWN  => __('Generated unless', 'formcreator'),
      ];
   }

   public function isEntityAssign() {
      return false;
   }

   public function prepareInputForAdd($input) {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      if (!isset($input[$formFk])) {
         Session::addMessageAfterRedirect(__('A target must be associated to a form.', 'formcreator'));
         return false;
      }
      $form = PluginFormcreatorCommon::getForm();
      if (!$form->getFromDB((int) $input[$formFk])) {
         Session::addMessageAfterRedirect(__('A target must be associated to an existing form.', 'formcreator'), false, ERROR);
         return false;
      }

      if (!isset($input['name']) || strlen($input['name']) < 1) {
         Session::addMessageAfterRedirect(__('Name is required.', 'formcreator'), false, ERROR);
         return false;
      }

      if (!$this->skipChecks) {
         if (!$this->checkConditionSettings($input)) {
            $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
         }
      }

      // generate a unique id
      if (!isset($input['uuid'])
         || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public function prepareInputForUpdate($input) {
      if (!$this->skipChecks) {
         if (isset($input['name'])
            && empty($input['name'])) {
            Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }

         if (!$this->checkConditionSettings($input)) {
            $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
         }
      }

      // generate a uniq id
      if (!isset($input['uuid'])
         && empty($this->fields['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public function post_addItem() {
      $this->updateConditions($this->input);
   }

   public function post_updateItem($history = 1) {
      $this->updateConditions($this->input);
   }

   /**
    * Prepare the template of the target
    *
    * @param string $template
    * @param PluginFormcreatorFormAnswer $formAnswer form answer to render
    * @param bool $richText Disable rich text output
    * @return string without sql or html escaping
    */
   protected function prepareTemplate($template, PluginFormcreatorFormAnswer $formAnswer, $richText = false) {
      if (strpos($template, '##FULLFORM##') !== false) {
         $template = str_replace('##FULLFORM##', $formAnswer->getFullForm($richText), $template);
      }

      $extra_tags_values = Plugin::doHookFunction('formcreator_prepare_extra_tags', [
         'formanswer' => $formAnswer,
         'values' => [],
         'richtext' => $richText,
      ]);
      foreach ($extra_tags_values['values'] as $tag => $value) {
         $template = str_replace($tag, $value, $template);
      }

      if ($richText) {
         $template = str_replace(['<p>', '</p>'], ['<div>', '</div>'], $template);
      }

      return $template;
   }

   /**
    * Append fields data to input
    *
    * @param PluginFormcreatorFormanswer $formanswer the source formanswer
    * @param array $input data of the generated target
    * @return void
    */
   protected function appendFieldsData(PluginFormcreatorFormanswer $formanswer, &$input): void {
      global $DB;

      //get all PluginFormcreatorAnswer
      //from PluginFormcreatorFormanswer
      //where linked PluginFormcreatorquestion have 'fields' type
      $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
      $result = $DB->request([
         'SELECT' => ['plugin_formcreator_questions_id', 'answer'],
         'FROM' => PluginFormcreatorAnswer::getTable(). ' AS answer',
         'JOIN' => [
            PluginFormcreatorQuestion::getTable() . ' AS question' => [
               'ON' => [
                  'answer' => 'plugin_formcreator_questions_id',
                  'question' => 'id',
               ]
            ]
         ],
         'WHERE' => [
            'answer.'.$formAnswerFk => (int) $formanswer->fields['id'],
            'question.fieldtype' => 'fields'
         ],
      ]);

      foreach ($result as $line) {
         $formQuestion = PluginFormcreatorQuestion::getById($line['plugin_formcreator_questions_id']);
         $decodedValues = json_decode($formQuestion->fields['values'], JSON_OBJECT_AS_ARRAY);
         $field_name = $decodedValues['dropdown_fields_field'] ?? '';
         $blocks_field = $decodedValues['blocks_field'] ?? '';

         $field = new PluginFieldsField();
         $field->getFromDbByCrit(['name' => $field_name]);

         if ($field->fields['type'] == 'dropdown') {
            $dropdownInputName = "plugin_fields_" . $field_name . "dropdowns_id" ?? '';
            $input[$dropdownInputName] = $line['answer'];
         } else {
            $input[$field_name] = $line['answer'];
         }
         $input['c_id'] = $blocks_field;
      }
   }


      /**
    * Converts tags in template fields from ID to UUID.
    * Used for export into JSON
    *
    * @return array all fields of the object wih converted template fields
    */
   protected function convertTags($input) {
      $question = new PluginFormcreatorQuestion();
      $questions = $question->getQuestionsFromForm($this->getForm()->getID());

      $taggableFields = $this->getTaggableFields();

      // Prepare array of search / replace
      $ids = [];
      $uuids = [];
      foreach ($questions as $question) {
         $id      = $question->fields['id'];
         $uuid    = $question->fields['uuid'];
         $ids[]   = "##question_$id##";
         $uuids[] = "##question_$uuid##";
         $ids[]   = "##answer_$id##";
         $uuids[] = "##answer_$uuid##";
      }

      // Replace for each field with tags
      foreach ($taggableFields as $field) {
         $content = $this->fields[$field];
         $content = str_replace($ids, $uuids, $content);
         $content = str_replace($ids, $uuids, $content);
         $input[$field] = $content;
      }

      return $input;
   }

   protected function showConditionsSettings() {
      $condition = new PluginFormcreatorCondition();
      $condition->showConditionsForItem($this);
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
      return $this->getMyTranslatableStrings($options);
   }

      /**
    * get all target of this type for a form
    *
    * @param int $formId
    * @return array
    */
   public function getTargetsForForm($formId) {
      global $DB;

      $targets = [];
      $rows = $DB->request([
        'SELECT' => ['id'],
        'FROM'   => static::getTable(),
        'WHERE'  => [
           'plugin_formcreator_forms_id' => $formId
        ],
      ]);
      foreach ($rows as $row) {
         $target = new static();
         $target->getFromDB($row['id']);
         $targets[$row['id']] = $target;
      }

      return $targets;
   }

   final public static function showConditions(self $item) {
      $options = [];
      $item->initForm($item->getID(), $options);
      $options['candel'] = false;
      $options['formoptions'] = sprintf('data-itemtype="%s" data-id="%s"', self::getType(), $item->getID());
      TemplateRenderer::getInstance()->display('@formcreator/pages/condition_for_item.html.twig', [
         'item'   => $item,
         'params' => $options,
         'parent' => $item,
      ]);
      return true;
   }

   /**
    * get the associated form
    */
   public function getForm() {
      if ($this->form === null) {
         $form = PluginFormcreatorCommon::getForm();
         if (!$form->getFromDB($this->fields[PluginFormcreatorForm::getForeignKeyField()])) {
            return null;
         }
         $this->form = $form;
      }

      return $this->form;
   }

      /**
    * Set the entity of the target
    *
    * @param array $data input data of the target
    * @param PluginFormcreatorFormAnswer $formanswer
    * @param int $requesters_id ID of the requester of the answers
    * @return integer ID of the entity where the target must be generated
    */
   protected function setTargetEntity($data, PluginFormcreatorFormAnswer $formanswer, $requesters_id) {
      global $DB;

      $entityId = 0;
      $entityFk = Entity::getForeignKeyField();
      switch ($this->fields['destination_entity']) {
         // Requester's entity
         case self::DESTINATION_ENTITY_CURRENT :
            $entityId = $formanswer->fields[$entityFk];
            break;

         case self::DESTINATION_ENTITY_REQUESTER :
            $userObj = new User();
            $userObj->getFromDB($requesters_id);
            $entityId = $userObj->fields[$entityFk];
            break;

         // Requester's first dynamic entity
         case self::DESTINATION_ENTITY_REQUESTER_DYN_FIRST :
            $order_entities = "glpi_profiles.name ASC";
         case self::DESTINATION_ENTITY_REQUESTER_DYN_LAST :
            if (!isset($order_entities)) {
               $order_entities = "glpi_profiles.name DESC";
            }
            $profileUserTable = Profile_User::getTable();
            $profileTable = Profile::getTable();
            $profileFk  = Profile::getForeignKeyField();
            $res_entities = $DB->request([
               'SELECT' => [
                  $profileUserTable => [Entity::getForeignKeyField()]
               ],
               'FROM' => $profileUserTable,
               'LEFT JOIN' => [
                  $profileTable => [
                     'FKEY' => [
                        $profileTable => 'id',
                        $profileUserTable => $profileFk
                     ]
                  ]
               ],
               'WHERE' => [
                  "$profileUserTable.users_id" => $requesters_id,
                  'is_dynamic' => '1',
               ],
               'ORDER' => [
                  $order_entities
               ]
            ]);

            $data_entities = [];
            foreach ($res_entities as $entity) {
               $data_entities[] = $entity;
            }
            if (count($data_entities) < 1) {
               // No entity found
               break;
            }
            $first_entity = array_shift($data_entities);
            $entityId = $first_entity[$entityFk];
            break;

         // Specific entity
         case self::DESTINATION_ENTITY_SPECIFIC :
            $entityId = $this->fields['destination_entity_value'];
            break;

         // The form entity
         case self::DESTINATION_ENTITY_FORM :
            $entityId = $formanswer->getForm()->fields[$entityFk];
            break;

         // The validator entity
         case self::DESTINATION_ENTITY_VALIDATOR :
            // when a valdiator accepts a formanswer the formanswer must switch to "accepted"
            // this tells that the validator is the last one and should be used to find the entity
            $userObj = new User();
            $userObj->getFromDB($formanswer->fields['users_id_validator']);
            $entityId = $userObj->fields[$entityFk];
            break;

         // Default entity of a user from the answer of a user's type question
         case self::DESTINATION_ENTITY_USER :
            $user = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['destination_entity_value'],
               ]
            ])->current();
            $user_id = $user['answer'];

            if ($user_id > 0) {
               $userObj = new User();
               $userObj->getFromDB($user_id);
               $entityId = $userObj->fields[$entityFk];
            }
            break;

         // Entity from the answer of an entity's type question
         case self::DESTINATION_ENTITY_ENTITY :
            $entity = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['destination_entity_value'],
               ]
            ])->current();
            $entityId = $entity['answer'];
            break;
      }

      $data[$entityFk] = $entityId;
      return $data;
   }

   protected function showDestinationEntitySetings($rand) {
      echo '<tr>';
      echo '<td width="15%">' . __('Destination entity') . '</td>';
      echo '<td width="25%">';
      Dropdown::showFromArray(
         'destination_entity',
         self::getEnumDestinationEntity(),
         [
            'value'     => $this->fields['destination_entity'],
            'on_change' => "plugin_formcreator_change_entity($rand)",
            'rand'      => $rand,
         ]
      );

      echo Html::scriptBlock("plugin_formcreator_change_entity($rand)");
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="entity_specific_title" style="display: none">' . _n('Entity', 'Entities', 1) . '</span>';
      echo '<span id="entity_user_title" style="display: none">' . __('User type question', 'formcreator') . '</span>';
      echo '<span id="entity_entity_title" style="display: none">' . __('Entity type question', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';

      echo '<div id="entity_specific_value" style="display: none">';
      Entity::dropdown([
         'name' => '_destination_entity_value_specific',
         'value' => $this->fields['destination_entity_value'],
      ]);
      echo '</div>';

      echo '<div id="entity_user_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['glpiselect'],
            'itemtype'  => User::class,
         ],
         '_destination_entity_value_user',
         $this->fields['destination_entity_value']
      );
      echo '</div>';

      echo '<div id="entity_entity_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['glpiselect'],
            'itemtype'  => Entity::class,
         ],
         '_destination_entity_value_entity',
         $this->fields['destination_entity_value']
      );
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }
}
