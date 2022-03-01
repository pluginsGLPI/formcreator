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

      // generate a unique id
      if (!isset($input['uuid'])
         || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      if (!$this->checkConditionSettings($input)) {
         $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
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
      }

      // generate a uniq id
      if (!isset($input['uuid'])
         || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      if (!$this->checkConditionSettings($input)) {
         $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
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
    * @return string
    */
   protected function prepareTemplate($template, PluginFormcreatorFormAnswer $formAnswer, $richText = false) {
      if (strpos($template, '##FULLFORM##') !== false) {
         $template = str_replace('##FULLFORM##', $formAnswer->getFullForm($richText), $template);
      }

      if ($richText) {
         $template = str_replace(['<p>', '</p>'], ['<div>', '</div>'], $template);
         $template = Sanitizer::sanitize($template);
      }

      return $template;
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

   protected function showTemplateSettings() {
      $templateType = $this->getTemplateItemtypeName();
      $templateFk = $templateType::getForeignKeyField();

      echo '<td width="15%">' . $templateType::getTypeName(1) . '</td>';
      echo '<td width="25%">';
      Dropdown::show($templateType, [
         'name'  => $templateFk,
         'value' => $this->fields[$templateFk]
      ]);
      echo '</td>';
   }

   protected  function showDueDateSettings() {
      echo '<td width="15%">' . __('Time to resolve') . '</td>';
      echo '<td width="45%">';

      // Due date type selection
      Dropdown::showFromArray('due_date_rule', self::getEnumDueDateRule(),
         [
            'value'     => $this->fields['due_date_rule'],
            'on_change' => 'plugin_formcreator_formcreatorChangeDueDate(this.value)',
            'display_emptychoice' => true
         ]
      );

      $questionTable = PluginFormcreatorQuestion::getTable();
      $questions = (new PluginFormcreatorQuestion)->getQuestionsFromForm(
         $this->getForm()->getID(),
         [
            "$questionTable.fieldtype" => ['date', 'datetime'],
         ]
      );
      $questions_list = [];
      foreach ($questions as $question) {
         $questions_list[$question->getID()] = $question->fields['name'];
      }
      // List questions
      if ($this->fields['due_date_rule'] != PluginFormcreatorAbstractTarget::DUE_DATE_RULE_ANSWER
            && $this->fields['due_date_rule'] != 'calcul') {
         echo '<div id="due_date_questions" style="display:none">';
      } else {
         echo '<div id="due_date_questions">';
      }
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['date', 'datetime'],
         ],
         'due_date_question',
         $this->fields['due_date_question']
      );
      echo '</div>';

      if ($this->fields['due_date_rule'] != '2'
            && $this->fields['due_date_rule'] != '3') {
         echo '<div id="due_date_time" style="display:none">';
      } else {
         echo '<div id="due_date_time">';
      }
      Dropdown::showNumber("due_date_value", [
         'value' => $this->fields['due_date_value'],
         'min'   => -30,
         'max'   => 30
      ]);
      Dropdown::showFromArray('due_date_period', [
         PluginFormcreatorAbstractTarget::DUE_DATE_PERIOD_MINUTE => _n('Minute', 'Minutes', 2),
         PluginFormcreatorAbstractTarget::DUE_DATE_PERIOD_HOUR   => _n('Hour', 'Hours', 2),
         PluginFormcreatorAbstractTarget::DUE_DATE_PERIOD_DAY    => _n('Day', 'Days', 2),
         PluginFormcreatorAbstractTarget::DUE_DATE_PERIOD_MONTH  => _n('Month', 'Months', 2),
      ], [
         'value' => $this->fields['due_date_period']
      ]);
      echo '</div>';
      echo '</td>';
   }

   protected function showSLASettings() {
      $label = __("SLA");

      echo '<tr>';
      echo "<td width='15%'>$label</td>";
      echo '<td width="25%">';

      // Due date type selection
      Dropdown::showFromArray("sla_rule", self::getEnumSlaRule(),
         [
            'value'     => $this->fields["sla_rule"],
            'on_change' => "plugin_formcreator_formcreatorChangeSla(this.value)",
            'display_emptychoice' => true
         ]
      );
      echo '</td>';

      $display_specific = $this->fields["sla_rule"] == self::SLA_RULE_SPECIFIC;
      $display_questions = $this->fields["sla_rule"] == self::SLA_RULE_FROM_ANWSER;
      $style_specific = !$display_specific ? "style='display: none'" : "";
      $style_questions = !$display_questions ? "style='display: none'" : "";

      echo '<td width="15%">';

      echo "<span id='sla_specific_title' $style_specific>" . __('SLA (TTO/TTR)', 'formcreator') . '</span>';
      echo "<span id='sla_question_title' $style_questions>" . __('Question (TTO/TTR)', 'formcreator') . '</span>';

      echo '</td>';
      echo '<td width="25%">';

      echo "<div id='sla_specific_value' $style_specific>";
      SLA::dropdown([
         'name'      => '_sla_specific_tto',
         'value'     => $this->fields["sla_question_tto"],
         'condition' => ['type' => SLM::TTO],
      ]);
      echo "&nbsp;&nbsp;";
      SLA::dropdown([
         'name'      => '_sla_specific_ttr',
         'value'     => $this->fields["sla_question_ttr"],
         'condition' => ['type' => SLM::TTR],
      ]);
      echo '</div>';

      echo "<div id='sla_questions' $style_questions>";

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'dropdown',
            'itemtype'  => SLA::getType(),
            new QueryExpression("`values` LIKE '%\"show_service_level_types\":\"1\"%'"),
         ],
         "_sla_questions_tto",
         $this->fields["sla_question_tto"]
      );
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'dropdown',
            'itemtype'  => SLA::getType(),
            new QueryExpression("`values` LIKE '%\"show_service_level_types\":\"0\"%'"),
         ],
         "_sla_questions_ttr",
         $this->fields["sla_question_ttr"]
      );

      echo '</div>';

      echo '</td>';
      echo '</tr>';
   }

   protected function showOLASettings() {
      $label = __("OLA");

      echo '<tr>';
      echo "<td width='15%'>$label</td>";
      echo '<td width="25%">';

      // Due date type selection
      Dropdown::showFromArray("ola_rule", self::getEnumOlaRule(),
         [
            'value'     => $this->fields["ola_rule"],
            'on_change' => "plugin_formcreator_formcreatorChangeOla(this.value)",
            'display_emptychoice' => true
         ]
      );
      echo '</td>';

      $display_specific = $this->fields["ola_rule"] == self::OLA_RULE_SPECIFIC;
      $display_questions = $this->fields["ola_rule"] == self::OLA_RULE_FROM_ANWSER;
      $style_specific = !$display_specific ? "style='display: none'" : "";
      $style_questions = !$display_questions ? "style='display: none'" : "";

      echo '<td width="15%">';

      echo "<span id='ola_specific_title' $style_specific>" . __('OLA (TTO/TTR)', 'formcreator') . '</span>';
      echo "<span id='ola_question_title' $style_questions>" . __('Question (TTO/TTR)', 'formcreator') . '</span>';

      echo '</td>';
      echo '<td width="25%">';

      echo "<div id='ola_specific_value' $style_specific>";
      OLA::dropdown([
         'name'      => '_ola_specific_tto',
         'value'     => $this->fields["ola_question_tto"],
         'condition' => ['type' => SLM::TTO],
      ]);
      echo "&nbsp;&nbsp;";
      OLA::dropdown([
         'name'      => '_ola_specific_ttr',
         'value'     => $this->fields["ola_question_ttr"],
         'condition' => ['type' => SLM::TTR],
      ]);
      echo '</div>';

      echo "<div id='ola_questions' $style_questions>";

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'dropdown',
            'itemtype'  => OLA::getType(),
            new QueryExpression("`values` LIKE '%\"show_service_level_types\":\"1\"%'"),
         ],
         "_ola_questions_tto",
         $this->fields["ola_question_tto"]
      );
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'dropdown',
            'itemtype'  => OLA::getType(),
            new QueryExpression("`values` LIKE '%\"show_service_level_types\":\"0\"%'"),
         ],
         "_ola_questions_ttr",
         $this->fields["ola_question_ttr"]
      );

      echo '</div>';

      echo '</td>';
      echo '</tr>';
   }

   protected function showCategorySettings($rand) {
      echo '<tr>';
      echo '<td width="15%">' . __('Category', 'formcreator') . '</td>';
      echo '<td width="25%">';
      Dropdown::showFromArray(
         'category_rule',
         static::getEnumCategoryRule(),
         [
            'value'     => $this->fields['category_rule'],
            'on_change' => "plugin_formcreator_changeCategory($rand)",
            'rand'      => $rand,
         ]
      );
      echo Html::scriptBlock("plugin_formcreator_changeCategory($rand);");
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="category_specific_title" style="display: none">' . __('Category', 'formcreator') . '</span>';
      echo '<span id="category_question_title" style="display: none">' . __('Question', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';
      echo '<div id="category_question_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['dropdown'],
            'itemtype'  => ITILCategory::class,
         ],
         '_category_question',
         $this->fields['category_question']
      );
      echo '</div>';
      echo '<div id="category_specific_value" style="display: none">';
      ITILCategory::dropdown([
         'name'      => '_category_specific',
         'value'     => $this->fields["category_question"],
         'condition' => $this->getCategoryFilter(),
      ]);
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showUrgencySettings($rand) {
      echo '<tr>';
      echo '<td width="15%">' . __('Urgency') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('urgency_rule', static::getEnumUrgencyRule(), [
         'value'                 => $this->fields['urgency_rule'],
         'on_change'             => "plugin_formcreator_changeUrgency($rand)",
         'rand'                  => $rand
      ]);
      echo Html::scriptBlock("plugin_formcreator_changeUrgency($rand);");
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="urgency_question_title" style="display: none">' . __('Question', 'formcreator') . '</span>';
      echo '<span id="urgency_specific_title" style="display: none">' . __('Urgency ', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';

      echo '<div id="urgency_specific_value" style="display: none">';
      Ticket::dropdownUrgency([
         'name' => '_urgency_specific',
         'value' => $this->fields["urgency_question"],
      ]);
      echo '</div>';
      echo '<div id="urgency_question_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['urgency'],
         ],
         '_urgency_question',
         $this->fields['urgency_question']
      );
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showPluginTagsSettings($rand) {
      global $DB;

      $plugin = new Plugin();
      if ($plugin->isInstalled('tag') && $plugin->isActivated('tag')) {
         echo '<tr>';
         echo '<td width="15%">' . __('Ticket tags', 'formcreator') . '</td>';
         echo '<td width="25%">';
         Dropdown::showFromArray(
            'tag_type',
            self::getEnumTagType(),
            [
               'value'     => $this->fields['tag_type'],
               'on_change' => 'change_tag_type()',
               'rand'      => $rand,
            ]
         );

         $tagTypeQuestions = self::TAG_TYPE_QUESTIONS;
         $tagTypeSpecifics = self::TAG_TYPE_SPECIFICS;
         $tagTypeQuestionAndSpecific = self::TAG_TYPE_QUESTIONS_AND_SPECIFIC;
         $tagTypeQuestinOrSpecific = self::TAG_TYPE_QUESTIONS_OR_SPECIFIC;
         $script = <<<SCRIPT
            function change_tag_type() {
               $('#tag_question_title').hide();
               $('#tag_specific_title').hide();
               $('#tag_question_value').hide();
               $('#tag_specific_value').hide();

               switch($('#dropdown_tag_type$rand').val()) {
                  case '$tagTypeQuestions' :
                     $('#tag_question_title').show();
                     $('#tag_question_value').show();
                     break;
                  case '$tagTypeSpecifics' :
                     $('#tag_specific_title').show();
                     $('#tag_specific_value').show();
                     break;
                  case '$tagTypeQuestionAndSpecific' :
                  case '$tagTypeQuestinOrSpecific' :
                     $('#tag_question_title').show();
                     $('#tag_specific_title').show();
                     $('#tag_question_value').show();
                     $('#tag_specific_value').show();
                     break;
               }
            }
            change_tag_type();
SCRIPT;

         echo Html::scriptBlock($script);
         echo '</td>';
         echo '<td width="15%">';
         echo '<div id="tag_question_title" style="display: none">' . _n('Question', 'Questions', 2, 'formcreator') . '</div>';
         echo '<div id="tag_specific_title" style="display: none">' . __('Tags', 'tag') . '</div>';
         echo '</td>';
         echo '<td width="25%">';

         // Tag questions
         echo '<div id="tag_question_value" style="display: none">';
         PluginFormcreatorQuestion::dropdownForForm(
            $this->getForm(),
            [
               'fieldtype' => ['tag'],
            ],
            '_tag_questions',
            $this->fields['tag_questions'],
            [
               'multiple' => true,
            ]
         );
         echo '</div>';

         // Specific tags
         echo '<div id="tag_specific_value" style="display: none">';

         $dbUtils = new DbUtils();
         $entityRestrict = $dbUtils->getEntitiesRestrictCriteria(PluginTagTag::getTable(), "", "", true, false);
         if (count($entityRestrict)) {
            $entityRestrict = [$entityRestrict];
         }
         $result = $DB->request([
            'SELECT' => ['id', 'name'],
            'FROM'   => PluginTagTag::getTable(),
            'WHERE'  => [
               'AND' => [
                  'OR' => [
                     ['type_menu' => ['LIKE', '%"' . $this->getTargetItemtypeName() . '"%']],
                     ['type_menu' => ['LIKE', '%"0"%']],
                     ['type_menu' => ''],
                     ['type_menu' => 'NULL'],
                  ],
               ] + $entityRestrict,
            ]
         ]);
         $values = [];
         foreach ($result AS $id => $data) {
            $values[$id] = $data['name'];
         }

         Dropdown::showFromArray('_tag_specifics', $values, [
            'values'   => explode(',', $this->fields['tag_specifics']),
            'comments' => false,
            'rand'     => $rand,
            'multiple' => true,
         ]);
         echo '</div>';
         echo '</td>';
         echo '</tr>';
      }
   }

   protected function showActorsSettings() {
      global $DB;

      // Get available questions for actors lists
      $actors = [
         PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER => [],
         PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER => [],
         PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED => [],
      ];
      $result = $DB->request([
         'SELECT' => ['id', 'actor_role', 'actor_type', 'actor_value', 'use_notification'],
         'FROM' => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype' => $this->getType(),
            'items_id' => $this->getID(),
         ],
      ]);
      foreach ($result as $actor) {
         $actors[$actor['actor_role']][$actor['id']] = [
            'actor_type'       => $actor['actor_type'],
            'actor_value'      => $actor['actor_value'],
            'use_notification' => $actor['use_notification'],
         ];
      }

      echo '<table class="tab_cadre_fixe" '
      . ' data-itemtype="' . $this->getType() . '"'
      . ' data-id="' . $this->getID() . '"'
      . '>';

      echo '<tr><th class="center" colspan="3">' . __('Actors', 'formcreator') . '</th></tr>';

      echo '<tr>';
      // Requester header
      $this->showActorSettingsHeader(CommonITILActor::REQUESTER);

      // Watcher header
      $this->showActorSettingsHeader(CommonITILActor::OBSERVER);

      // Assigned header
      $this->showActorSettingsHeader(CommonITILActor::ASSIGN);
      echo '</tr>';

      echo '<tr>';
      // Requester
      $this->showActorSettingsForType(CommonITILActor::REQUESTER, $actors);

      // Observer
      $this->showActorSettingsForType(CommonITILActor::OBSERVER, $actors);

      // Assigned to
      $this->showActorSettingsForType(CommonITILActor::ASSIGN, $actors);
      echo '</tr>';

      echo '</table>';
   }

   protected function showLocationSettings($rand) {
      global $DB;

      echo '<tr>';
      echo '<td width="15%">' . __('Location') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('location_rule', static::getEnumLocationRule(), [
         'value'                 => $this->fields['location_rule'],
         'on_change'             => "plugin_formcreator_change_location($rand)",
         'rand'                  => $rand
      ]);

      echo Html::scriptBlock("plugin_formcreator_change_location($rand)");
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="location_question_title" style="display: none">' . __('Question', 'formcreator') . '</span>';
      echo '<span id="location_specific_title" style="display: none">' . __('Location ', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';

      echo '<div id="location_specific_value" style="display: none">';
      Location::dropdown([
         'name' => '_location_specific',
         'value' => $this->fields["location_question"],
      ]);
      echo '</div>';
      echo '<div id="location_question_value" style="display: none">';
      // select all user questions (GLPI Object)
      $questionTable = PluginFormcreatorQuestion::getTable();
      $sectionTable = PluginFormcreatorSection::getTable();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $result = $DB->request([
         'SELECT' => [
            $questionTable => ['id', 'name', 'values'],
            $sectionTable => ['name as sname'],
         ],
         'FROM' => $questionTable,
         'INNER JOIN' => [
            $sectionTable => [
               'FKEY' => [
                  $sectionTable => 'id',
                  $questionTable => $sectionFk
               ]
            ],
         ],
         'WHERE' => [
            "$formFk" => $this->getForm()->getID(),
            "$questionTable.fieldtype" => 'dropdown'
         ]
      ]);
      $users_questions = [];
      foreach ($result as $question) {
         $decodedValues = json_decode($question['values'], JSON_OBJECT_AS_ARRAY);
         if (isset($decodedValues['itemtype']) && $decodedValues['itemtype'] === 'Location') {
            $users_questions[$question['sname']][$question['id']] = $question['name'];
         }
      }
      Dropdown::showFromArray('_location_question', $users_questions, [
         'value' => $this->fields['location_question'],
      ]);

      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showValidationSettings($rand) {
      echo '<tr>';

      // Setting label
      echo '<td width="15%">' . __('Validation') . '</td>';

      // Possible values
      echo '<td width="45%">';
      Dropdown::showFromArray('commonitil_validation_rule', static::getEnumValidationRule(), [
         'value'     => $this->fields['commonitil_validation_rule'],
         'on_change' => "plugin_formcreator_change_validation($rand)",
         'rand'      => $rand
      ]);
      echo Html::scriptBlock("plugin_formcreator_change_validation($rand)");
      echo '</td>';

      // Hidden secondary labels, displayed according to the user main choice
      echo '<td width="15%">';

      // Read values
      $validation_rule = $this->fields['commonitil_validation_rule'];

      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP ? "" : "display: none";
      echo "<span id='commonitil_validation_specific_title' style='$display'>";
      echo __('Approver');
      echo "</span>";

      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_ANSWER_USER || $validation_rule == self::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP ? "" : "display: none";
      echo "<span id='commonitil_validation_from_question_title' style='$display'>";
      echo __('Question', 'formcreator');
      echo "</span>";

      echo '</td>';

      // Hidden secondary values, displayed according to the user main choice
      echo '<td width="25%">';

      // COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP
      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP ? "" : "display: none";
      echo "<div id='commonitil_validation_specific' style='$display'>";
      $validation_dropdown_params = [
         'name' => 'validation_specific'
      ];
      $validation_data = json_decode($this->fields['commonitil_validation_question'], true);
      if (isset($validation_data['type'])) {
         $validation_dropdown_params['users_id_validate'] = $validation_data['values'];
      }
      $validation_dropdown_params['display'] = false;
      echo CommonITILValidation::dropdownValidator($validation_dropdown_params);
      echo '</div>';

      // COMMONITIL_VALIDATION_RULE_ANSWER_USER
      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_ANSWER_USER ? "" : "display: none";
      echo "<div id='commonitil_validation_answer_user' style='$display'>";
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            new QueryExpression("`fieldtype` = 'actor' OR (`fieldtype` = 'glpiselect' AND `itemtype`='User')"),
         ],
         '_validation_from_user_question',
         $this->fields['commonitil_validation_question'],
      );
      echo '</div>';

      // COMMONITIL_VALIDATION_RULE_ANSWER_GROUP
      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP ? "" : "display: none";
      echo "<div id='commonitil_validation_answer_group' style='$display'>";
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'glpiselect',
            'itemtype'  => 'Group',
         ],
         '_validation_from_group_question',
         $this->fields['commonitil_validation_question'],
      );
      echo '</div>';

      echo '</td>';
      echo '</tr>';
   }

   /**
    * Sets the time to resolve of the target object
    *
    * @param array $data data of the target object
    * @param PluginFormcreatorFormAnswer $formanswer    Answers previously saved
    * @return array updated data of the target object
    */
   protected function setTargetDueDate($data, PluginFormcreatorFormAnswer $formanswer) {
      global $DB;

      $answer  = new PluginFormcreatorAnswer();
      if ($this->fields['due_date_question'] != 0) {
         $request = [
            'FROM' => $answer::getTable(),
            'WHERE' => [
               'AND' => [
                  $formanswer::getForeignKeyField() => $formanswer->fields['id'],
                  PluginFormcreatorQuestion::getForeignKeyField() => $this->fields['due_date_question'],
               ],
            ],
         ];
         $iterator = $DB->request($request);
         if ($iterator->count() > 0) {
            $iterator->rewind();
            $date   = $iterator->current();
         }
      } else {
         $date = null;
      }

      $period = '';
      switch ($this->fields['due_date_period']) {
         case self::DUE_DATE_PERIOD_MINUTE:
            $period = "minute";
            break;
         case self::DUE_DATE_PERIOD_HOUR:
            $period = "hour";
            break;
         case self::DUE_DATE_PERIOD_DAY:
            $period = "day";
            break;
         case self::DUE_DATE_PERIOD_MONTH:
            $period = "month";
            break;
      }
      $str    = "+" . $this->fields['due_date_value'] . " $period";

      switch ($this->fields['due_date_rule']) {
         case PluginFormcreatorAbstractTarget::DUE_DATE_RULE_ANSWER:
            $due_date = $date['answer'];
            break;
         case PluginFormcreatorAbstractTarget::DUE_DATE_RULE_TICKET:
            $due_date = date('Y-m-d H:i:s', strtotime($str));
            break;
         case PluginFormcreatorAbstractTarget::DUE_DATE_RULE_CALC:
            $due_date = date('Y-m-d H:i:s', strtotime($date['answer'] . " " . $str));
            break;
         default:
            $due_date = null;
            break;
      }
      if (!is_null($due_date)) {
         $data['time_to_resolve'] = $due_date;
      }

      return $data;
   }

   /**
    * Sets the time to resolve of the target object
    *
    * @param array $data data of the target object
    * @param PluginFormcreatorFormAnswer $formanswer    Answers previously saved
    * @return array updated data of the target object
    */
   protected function setTargetValidation(
      $data,
      PluginFormcreatorFormAnswer $formanswer
   ) {
      global $DB;

      switch ($this->fields['commonitil_validation_rule']) {
         case self::COMMONITIL_VALIDATION_RULE_NONE:
         default:
            // No action
            break;

         case self::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP:
            $validation_data = json_decode($this->fields['commonitil_validation_question'], true);

            if (!is_null($validation_data)) {
               $data['validatortype'] = $validation_data['type'];
               $data['users_id_validate'] = $validation_data['values'];
            }

            break;

         case self::COMMONITIL_VALIDATION_RULE_ANSWER_USER:
            $answers = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['commonitil_validation_question']
               ]
            ]);

            foreach ($answers as $answer) {
               // Answer may be "2" or [2], both valid json
               $answer = json_decode($answer['answer']);
               if (!is_array($answer)) {
                  $answer = [$answer];
               }
               $data['validatortype'] = 'user';
               $data['users_id_validate'] = $answer;
               break;
            }

         case self::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP:
            $answers = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['commonitil_validation_question']
               ]
            ]);

            foreach ($answers as $answer) {
               // Answer may be "2" or [2], both valid json
               $answer = json_decode($answer['answer']);
               if (!is_array($answer)) {
                  $answer = [$answer];
               }

               // Get all user in the given group
               $user_group = new Group_User();
               $user_groups = $user_group->find([
                  'groups_id' => $answer
               ]);

               // Parse values
               $values = [];
               foreach ($user_groups as $row) {
                  $values[] = $row['users_id'];
               }

               $data['validatortype'] = 'group';
               $data['users_id_validate'] = $values;
               break;
            }
      }

      return $data;
   }

   public function prepareInputForAdd($input) {
      if (isset($input['_skip_create_actors']) && $input['_skip_create_actors']) {
         $this->skipCreateActors = true;
      }

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

      if (!isset($input['target_name']) || strlen($input['target_name']) < 1) {
         $input['target_name'] = $input['name'];
      }

      // Set default content
      if (!isset($input['content']) || isset($input['content']) && empty($input['content'])) {
         $input['content'] = '##FULLFORM##';
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      if (!$this->checkConditionSettings($input)) {
         $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
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

         // - content is required
         if (isset($input['content']) && strlen($input['content']) < 1) {
            Session::addMessageAfterRedirect(__('The description cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }
      }

      if (isset($input['_skip_create_actors']) && $input['_skip_create_actors']) {
         $this->skipCreateActors = true;
      }

      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      if (isset($input['commonitil_validation_rule'])) {
         switch ($input['commonitil_validation_rule']) {
            default:
            case self::COMMONITIL_VALIDATION_RULE_NONE:
               $input['commonitil_validation_question'] = '0';
               break;

            case self::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP:
               $input['commonitil_validation_question'] = json_encode([
                  'type'   => $input['validatortype'],
                  'values' => $input['validation_specific']
               ]);
               break;

            case self::COMMONITIL_VALIDATION_RULE_ANSWER_USER:
               $input['commonitil_validation_question'] = $input['_validation_from_user_question'];
               break;

            case self::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP:
               $input['commonitil_validation_question'] = $input['_validation_from_group_question'];
               break;
         }
      }

      if (!$this->checkConditionSettings($input)) {
         $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
      }

      return $input;
   }

   public function post_addItem() {
      $this->updateConditions($this->input);
      if ($this->skipCreateActors) {
         return;
      }

      $target_actor = new PluginFormcreatorTarget_Actor();
      $target_actor->add([
         'itemtype'            => $this->getType(),
         'items_id'            => $this->getID(),
         'actor_role'          => PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER,
         'actor_type'          => PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHOR,
         'use_notification'    => '1',
      ]);
      $target_actor = new PluginFormcreatorTarget_Actor();
      $target_actor->add([
         'itemtype'            => $this->getType(),
         'items_id'            => $this->getID(),
         'actor_role'          => PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER,
         'actor_type'          => PluginFormcreatorTarget_Actor::ACTOR_TYPE_VALIDATOR,
         'use_notification'    => '1',
      ]);
   }

   public function post_updateItem($history = 1) {
      $this->updateConditions($this->input);
   }

   protected function getDeleteImage($id) {
      $formUrl = static::getFormURL();
      // $link  = ' &nbsp;<a href="' . $formUrl . '?delete_actor=' . $id . '&id=' . $this->getID() . '">';
      // $link .= '<i style="color: #000" class="fas fa-trash-alt" alt="' . __('Delete') . '" title="' . __('Delete') . '"></i>';
      // $link .= '</a>';
      $link = '<a onclick="plugin_formcreator.deleteActor(this)">';
      $link .= '<i style="color: #000" class="fas fa-trash-alt" alt="' . __('Delete') . '" title="' . __('Delete') . '"></i>';
      $link .= '</a>';
      return $link;
   }

   public function pre_purgeItem() {
      // delete actors related to this instance
      $targetItemActor = new PluginFormcreatorTarget_Actor();
      if (!$targetItemActor->deleteByCriteria(['itemtype' => $this->getType(), 'items_id' => $this->getID()])) {
         $this->input = false;
         return false;
      }

      return true;
   }

   /**
    * Prepare the template of the target
    *
    * @param string $template
    * @param PluginFormcreatorFormAnswer $formAnswer form answer to render
    * @param bool $richText Disable rich text output
    * @return string
    */
   protected function prepareTemplate($template, PluginFormcreatorFormAnswer $formAnswer, $richText = false) {
      if (strpos($template, '##FULLFORM##') !== false) {
         $template = str_replace('##FULLFORM##', $formAnswer->getFullForm($richText), $template);
      }

      if ($richText) {
         $template = str_replace(['<p>', '</p>'], ['<div>', '</div>'], $template);
         $template = Sanitizer::sanitize($template);
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
   protected function appendFieldsData(PluginFormcreatorFormanswer $formanswer, &$input) {
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
            'answer.'.$formAnswerFk => [(int) $formanswer->fields['id']],
            'question.fieldtype' => 'fields'
         ],
      ]);

      foreach ($result as $line) {
         $formQuestion = new PluginFormcreatorQuestion();
         $formQuestion->getFromDB($line['plugin_formcreator_questions_id']);

         $decodedValues = json_decode($formQuestion->fields['values'], JSON_OBJECT_AS_ARRAY);
         $field_name = $decodedValues['dropdown_fields_field'] ?? '';

         if (strpos($field_name, 'dropdown') !== false){
            $dropdownInputName = "plugin_fields_" . $field_name . "dropdowns_id" ?? '';
            $input[$dropdownInputName] = $line['answer'];
         }else {
            $input[$field_name] = $line['answer'];
         }
         $input['c_id'] = $formQuestion->fields['itemtype'];
      }
   }

   /**
    * Associate tags to the target item
    *
    * @param PluginFormcreatorFormanswer $formanswer the source formanswer
    * @param int $targetId ID of the generated target
    * @return void
    */
   protected function saveTags(PluginFormcreatorFormanswer $formanswer, $targetId) {
      global $DB;

      // Add tag if presents
      $plugin = new Plugin();
      if (!$plugin->isActivated('tag')) {
         return;
      }

      $tagObj = new PluginTagTagItem();
      $tags   = [];

      // Add question tags
      if (($this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS
            || $this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS_AND_SPECIFIC
            || $this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS_OR_SPECIFIC)
            && (!empty($this->fields['tag_questions']))) {
         $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
         $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
         $result = $DB->request([
            'SELECT' => ['plugin_formcreator_questions_id', 'answer'],
            'FROM' => PluginFormcreatorAnswer::getTable(),
            'WHERE' => [
               $formAnswerFk => [(int) $formanswer->fields['id']],
               $questionFk => explode(',', $this->fields['tag_questions'])
            ],
         ]);
         foreach ($result as $line) {
            $question = new PluginFormcreatorQuestion();
            $question->getFromDB($line['plugin_formcreator_questions_id']);
            $field = PluginFormcreatorFields::getFieldInstance(
               $question->fields['fieldtype'],
               $question
            );
            $field->deserializeValue($line['answer']);
            $tab = $field->getRawValue();
            if (is_integer($tab)) {
               $tab = [$tab];
            }
            if (is_array($tab)) {
               $tags = array_merge($tags, $tab);
            }
         }
      }

      // Add specific tags
      if ($this->fields['tag_type'] == self::TAG_TYPE_SPECIFICS
                  || $this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS_AND_SPECIFIC
                  || ($this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS_OR_SPECIFIC && empty($tags))
                  && (!empty($this->fields['tag_specifics']))) {

         $tags = array_merge($tags, explode(',', $this->fields['tag_specifics']));
      }

      $tags = array_unique($tags);

      // Save tags in DB
      foreach ($tags as $tag) {
         $tagObj->add([
            'plugin_tag_tags_id' => $tag,
            'items_id'           => $targetId,
            'itemtype'           => $this->getTargetItemtypeName(),
         ]);
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

   /**
    * Show header for actors edition
    *
    * @param int $type see CommonITILActor constants
    * @return void
    */
   protected function showActorSettingsHeader($type) {
      switch ($type) { // Values from CommonITILObject::getSearchOptionsActors()
         case CommonITILActor::REQUESTER:
            $label =  _n('Requester', 'Requesters', 1);
            $displayJSFunction = 'plugin_formcreator_displayRequesterForm()';
            $hideJSFunction = 'plugin_formcreator_hideRequesterForm()';
            $buttonAdd = 'btn_add_requester';
            $buttonCancel = 'btn_cancel_requester';
            break;
         case CommonITILActor::OBSERVER:
            $label =  _n('Watcher', 'Watchers', 1);
            $displayJSFunction = 'plugin_formcreator_displayWatcherForm()';
            $hideJSFunction = 'plugin_formcreator_hideWatcherForm()';
            $buttonAdd = 'btn_add_watcher';
            $buttonCancel = 'btn_cancel_watcher';
            break;
         case CommonITILActor::ASSIGN:
            $label =  __('Assigned to');
            $displayJSFunction = 'plugin_formcreator_displayAssignedForm()';
            $hideJSFunction = 'plugin_formcreator_hideAssignedForm()';
            $buttonAdd = 'btn_add_assigned';
            $buttonCancel = 'btn_cancel_assigned';
            break;
      }

      echo '<th width="33%">';
      echo $label . ' &nbsp;';
      echo '<i class="fas fa-plus-circle" title="' . __('Add', 'formcreator'). '" alt="' . __('Add', 'formcreator'). '" onclick="' . $displayJSFunction . '" class="pointer"
         id="' . $buttonAdd . '"></i>';
      echo '<i class="fas fa-minus-circle" title="' . __('Cancel', 'formcreator'). '" alt="' . __('Cancel', 'formcreator'). '" onclick="' . $hideJSFunction . '" class="pointer"
         id="' . $buttonCancel . '" style="display:none"></i>';
      echo '</th>';
   }

   /**
    * Show header for actors edition
    *
    * @param int $actorType see CommonITILActor constants
    * @param array $actors actors to show
    * @return void
    */
   protected function showActorSettingsForType($actorType, array $actors) {
      global $DB;

      $itemActor = new PluginFormcreatorTarget_Actor();
      $dropdownItems = ['' => Dropdown::EMPTY_VALUE] + $itemActor::getEnumActorType();

      switch ($actorType) { // Values from CommonITILObject::getSearchOptionsActors()
         case CommonITILActor::REQUESTER:
            $type = 'requester';
            unset($dropdownItems[PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER]);
            unset($dropdownItems[PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER]);
            $changeActorJSFunction = 'plugin_formcreator.changeActor("requester", this.value)';
            $actorRole = PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER;
            break;
         case CommonITILActor::OBSERVER:
            $type = 'watcher';
            $changeActorJSFunction = 'plugin_formcreator.changeActor("watcher", this.value)';
            $actorRole = PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER;
            break;
         case CommonITILActor::ASSIGN:
            $type = 'assigned';
            unset($dropdownItems[PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHORS_SUPERVISOR]);
            $changeActorJSFunction = 'plugin_formcreator.changeActor("assigned", this.value)';
            $actorRole = PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED;
            break;
      }

      echo '<td valign="top">';
      echo '<form name="form_target"'
      . ' id="form_add_' . $type . '"'
      . ' style="display:none"'
      . 'action="javascript:;"'
      . '">';
      Dropdown::showFromArray(
         'actor_type',
         $dropdownItems, [
            'on_change' => $changeActorJSFunction,
         ]
      );

      echo '<div id="block_' . $type . '_user" style="display:none">';
      User::dropdown([
         'name' => 'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_PERSON,
         'right' => 'all',
         'all'   => 0,
      ]);
      echo '</div>';

      echo '<div id="block_' . $type . '_group" style="display:none">';
      Group::dropdown([
         'name' => 'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP,
      ]);
      echo '</div>';

      echo '<div id="block_' . $type . '_question_user" style="display:none">';
      // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'OR' => [
               'AND' => [
                  'fieldtype' => ['glpiselect'],
                  'itemtype'  => User::class,
               ],
               [
                  'fieldtype' => ['email'],
               ]
            ],
         ],
         'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      echo '<div id="block_' . $type . '_question_group" style="display:none">';
      // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['glpiselect'],
            'itemtype'  => Group::class,
         ],
         'actor_value_' .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      echo '<div id="block_' . $type . '_group_from_object" style="display:none">';
      // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['glpiselect'],
         ],
         'actor_value_' .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      echo '<div id="block_' . $type . '_tech_group_from_object" style="display:none">';
      // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['glpiselect'],
         ],
         'actor_value_' .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      echo '<div id="block_' . $type . '_question_actors" style="display:none">';
       // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['actor'],
         ],
         'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      if ($actorType == CommonITILActor::ASSIGN) {
         echo '<div id="block_' . $type . '_supplier" style="display:none">';
         // find already used items
         $request = $DB->request([
            'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
            'WHERE' => [
               'itemtype'   => $this->getType(),
               'items_id'   => $this->getID(),
               'actor_role' => $actorRole,
               'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER,
            ]
         ]);
         $used = [];
         foreach ($request as $row) {
            $used[$row['actor_value']] = $row['actor_value'];
         }

         Supplier::dropdown([
            'name' => 'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER,
            'used' => $used,
         ]);
         echo '</div>';

         echo '<div id="block_' . $type . '_question_supplier" style="display:none">';
         // find already used items
         $request = $DB->request([
            'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
            'WHERE' => [
               'itemtype'   => $this->getType(),
               'items_id'   => $this->getID(),
               'actor_role' => $actorRole,
               'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER,
            ]
         ]);
         $used = [];
         foreach ($request as $row) {
            $used[$row['actor_value']] = $row['actor_value'];
         }

         PluginFormcreatorQuestion::dropdownForForm(
            $this->getForm(),
            [
               'fieldtype' => ['glpiselect'],
               'itemtype'  => Supplier::class,
            ],
            'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER,
            0,
            [
               'used' => $used,
            ]
         );
         echo '</div>';
      }

      echo '<div>';
      echo __('Email followup');
      Dropdown::showYesNo('use_notification', 1);
      echo '</div>';

      echo '<p align="center">';
      echo Html::hidden('actor_role', ['value' => $actorRole]);
      echo Html::submit(_x('button', 'Save'), ['name' => 'update_actors', 'value' => __('Add'), 'onclick' => 'plugin_formcreator.addActor(this)']);
      echo '</p>';

      echo "<hr>";

      Html::closeForm();

      $img_user     = '<i class="fas fa-user" alt="' . __('User') . '" title="' . __('User') . '" width="20"></i>';
      $img_group    = '<i class="fas fa-users" alt="' . __('Group') . '" title="' . __('Group') . '" width="20"></i>';
      $img_supplier = '<i class="fas fa-suitcase" alt="' . __('Supplier') . '" title="' . __('Supplier') . '" width="20"></i>';
      $img_mail     = '<i class="fas fa-envelope pointer"  title="' . __('Email followup') . ' ' . __('Yes') . '" width="20"></i>';
      $img_nomail   = '<i class="fas fa-envelope pointer" title="' . __('Email followup') . ' ' . __('No') . '" width="20"></i>';

      foreach ($actors[$actorRole] as $id => $values) {
         echo '<div data-itemtype="PluginFormcreatorTarget_Actor" data-id="' . $id . '">';
         switch ($values['actor_type']) {
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHOR :
               echo $img_user . ' <b>' . __('Form author', 'formcreator') . '</b>';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_VALIDATOR :
               echo $img_user . ' <b>' . __('Form validator', 'formcreator') . '</b>';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_PERSON :
               $user = new User();
               $user->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('User') . ' </b> "' . $user->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Person from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP :
               $group = new Group();
               $group->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group') . ' </b> "' . $group->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP_FROM_OBJECT:
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the object', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT:
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Tech group from the object', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS:
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Actors from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER :
               $supplier = new Supplier();
               $supplier->getFromDB($values['actor_value']);
               echo $img_supplier . ' <b>' . __('Supplier') . ' </b> "' . $supplier->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_supplier . ' <b>' . __('Supplier from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHORS_SUPERVISOR :
               echo $img_user . ' <b>' . __('Form author\'s supervisor', 'formcreator') . '</b>';
               break;
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo $this->getDeleteImage($id);
         echo '</div>';
      }

      echo '</td>';
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

   protected function initializeActors() {
      // Prepare actors structures for creation of the ticket
      $this->requesters = [
         '_users_id_requester'         => [],
         '_users_id_requester_notif'   => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ],
      ];
      $this->observers = [
         '_users_id_observer'          => [],
         '_users_id_observer_notif'    => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ],
      ];
      $this->assigned = [
         '_users_id_assign'            => [],
         '_users_id_assign_notif'      => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ],
      ];

      $this->assignedSuppliers = [
         '_suppliers_id_assign'        => [],
         '_suppliers_id_assign_notif'  => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ]
      ];

      $this->requesterGroups = [
         '_groups_id_requester'        => [],
      ];

      $this->observerGroups = [
         '_groups_id_observer'         => [],
      ];

      $this->assignedGroups = [
         '_groups_id_assign'           => [],
      ];
   }

   /**
    * Set default values for the change to create
    *
    * @param PluginFormcreatorFormAnswer $formanswer
    * @return array
    */
   public function getDefaultData(PluginFormcreatorFormAnswer $formanswer): array {
      $this->initializeActors();

      $targetItemtype = $this->getTargetItemtypeName();
      $targetTemplateFk = $targetItemtype::getForeignKeyField();

      $data = $targetItemtype::getDefaultValues();
      // Determine category early, because it is used to determine the template
      $data = $this->setTargetCategory($data, $formanswer);

      $this->fields[$targetTemplateFk] = $this->getTargetTemplate($data);

      // Get predefined Fields
      $predefinedFieldItemtype = $this->getTemplatePredefinedFieldItemtype();
      $templatePredeinedField  = new $predefinedFieldItemtype();
      $predefined_fields       = $templatePredeinedField->getPredefinedFields($this->fields[$targetTemplateFk], true);

      if (isset($predefined_fields['_users_id_requester'])) {
         $this->addActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER, $predefined_fields['_users_id_requester'], true);
         unset($predefined_fields['_users_id_requester']);
      }
      if (isset($predefined_fields['_users_id_observer'])) {
         $this->addActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER, $predefined_fields['_users_id_observer'], true);
         unset($predefined_fields['_users_id_observer']);
      }
      if (isset($predefined_fields['_users_id_assign'])) {
         $this->addActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED, $predefined_fields['_users_id_assign'], true);
         unset($predefined_fields['_users_id_assign']);
      }

      if (isset($predefined_fields['_groups_id_requester'])) {
         $this->addGroupActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER, $predefined_fields['_groups_id_requester']);
         unset($predefined_fields['_groups_id_requester']);
      }
      if (isset($predefined_fields['_groups_id_observer'])) {
         $this->addGroupActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER, $predefined_fields['_groups_id_observer']);
         unset($predefined_fields['_groups_id_observer']);
      }
      if (isset($predefined_fields['_groups_id_assign'])) {
         $this->addGroupActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED, $predefined_fields['_groups_id_assign']);
         unset($predefined_fields['_groups_id_assign']);
      }

      // Manage special values
      if (isset($predefined_fields['date']) && $predefined_fields['date'] == 'NOW') {
         $predefined_fields['date'] = $_SESSION['glpi_currenttime'];
      }

      $data = array_merge($data, $predefined_fields);
      return $data;
   }

   /**
    * get all target problems for a form
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
}
