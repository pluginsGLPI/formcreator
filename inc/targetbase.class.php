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

abstract class PluginFormcreatorTargetBase extends CommonDBTM
{

   protected $requesters;

   protected $observers;

   protected $assigned;

   protected $assignedSuppliers;

   protected $requesterGroups;

   protected $observerGroups;

   protected $assignedGroups;

   protected $attachedDocuments = [];

   abstract public function export();

   abstract public function save(PluginFormcreatorForm_Answer $formanswer);

   abstract protected function getItem_User();

   abstract protected function getItem_Group();

   abstract protected function getItem_Supplier();

   abstract protected function getItem_Item();

   abstract protected function getTargetItemtypeName();

   abstract public function getItem_Actor();

   abstract protected function getCategoryFilter();

   static function getEnumDestinationEntity() {
      return [
         'current'   => __("Current active entity", 'formcreator'),
         'requester' => __("Default requester user's entity", 'formcreator'),
         'requester_dynamic_first' => __("First dynamic requester user's entity (alphabetical)", 'formcreator'),
         'requester_dynamic_last' => __("Last dynamic requester user's entity (alphabetical)", 'formcreator'),
         'form'      => __('The form entity', 'formcreator'),
         'validator' => __('Default entity of the validator', 'formcreator'),
         'specific'  => __('Specific entity', 'formcreator'),
         'user'      => __('Default entity of a user type question answer', 'formcreator'),
         'entity'    => __('From a GLPI object > Entity type question answer', 'formcreator'),
      ];
   }

   static function getEnumTagType() {
      return [
         'none'                   => __("None"),
         'questions'              => __('Tags from questions', 'formcreator'),
         'specifics'              => __('Specific tags', 'formcreator'),
         'questions_and_specific' => __('Tags from questions and specific tags', 'formcreator'),
         'questions_or_specific'  => __('Tags from questions or specific tags', 'formcreator')
      ];
   }

   static function getEnumDueDateRule() {
      return [
         'answer' => __('equals to the answer to the question', 'formcreator'),
         'ticket' => __('calculated from the ticket creation date', 'formcreator'),
         'calcul' => __('calculated from the answer to the question', 'formcreator'),
      ];
   }

   static function getEnumUrgencyRule() {
      return [
         'none'      => __('Urgency from template or Medium', 'formcreator'),
         'specific'  => __('Specific urgency', 'formcreator'),
         'answer'    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   static function getEnumCategoryRule() {
      return [
         'none'      => __('Category from template or none', 'formcreator'),
         'specific'  => __('Specific category', 'formcreator'),
         'answer'    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   static function getEnumLocationRule() {
      return [
         'none'      => __('Location from template or none', 'formcreator'),
         'specific'  => __('Specific location', 'formcreator'),
         'answer'    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

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

   /*
    *
    */
   public function getForm() {
      $targetItemId = $this->getID();
      $targetItemtype = static::getType();

      $target = new PluginFormcreatorTarget();
      $request = [
         'AND' => [
            'itemtype' => $targetItemtype,
            'items_id' => $targetItemId
         ]
      ];
      if (!$target->getFromDBByCrit($request)) {
         return null;
      } else {
         $form = new PluginFormcreatorForm();
         if (!$form->getFromDB($target->getField('plugin_formcreator_forms_id'))) {
            return null;
         }
         return $form;
      }

      return null;
   }

   /**
    * find all actors and prepare data for the ticket being created
    */
   protected function prepareActors(PluginFormcreatorForm $form, PluginFormcreatorForm_Answer $formanswer) {
      $targetId = $this->getID();
      $target_actor = $this->getItem_Actor();
      $foreignKey = $this->getForeignKeyField();
      $rows = $target_actor->find("`$foreignKey` = '$targetId'");

      foreach ($rows as $actor) {
         // If actor type is validator and if the form doesn't have a validator, continue to other actors
         if ($actor['actor_type'] == 'validator' && !$form->fields['validation_required']) {
            continue;
         }

         switch ($actor['actor_type']) {
            case 'creator' :
               $userIds = [$formanswer->fields['requester_id']];
               $notify  = $actor['use_notification'];
               break;
            case 'validator' :
               $userIds = [$_SESSION['glpiID']];
               $notify  = $actor['use_notification'];
               break;
            case 'person' :
            case 'group' :
            case 'supplier' :
               $userIds = [$actor['actor_value']];
               $notify  = $actor['use_notification'];
               break;
            case 'question_person' :
            case 'question_group' :
            case 'question_supplier' :
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_questions_id'     => $actorValue,
                     'plugin_formcreator_forms_answers_id' => $formanswerId
                  ]
               ]);

               if ($answer->isNewItem()) {
                  continue 2;
               } else {
                  $userIds = [$answer->getField('answer')];
               }
               $notify  = $actor['use_notification'];
               break;
            case 'question_actors':
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_questions_id'     => $actorValue,
                     'plugin_formcreator_forms_answers_id' => $formanswerId
                  ]
               ]);

               if ($answer->isNewItem()) {
                  continue 2;
               } else {
                  $userIds = array_filter(explode(',', trim($answer->getField('answer'))));
               }
               $notify = $actor['use_notification'];
               break;
         }

         switch ($actor['actor_type']) {
            case 'creator' :
            case 'validator' :
            case 'person' :
            case 'question_person' :
            case 'question_actors':
               foreach ($userIds as $userIdOrEmail) {
                  $this->addActor($actor['actor_role'], $userIdOrEmail, $notify);
               }
               break;
            case 'group' :
            case 'question_group' :
               foreach ($userIds as $groupId) {
                  $this->addGroupActor($actor['actor_role'], $groupId);
               }
               break;
            case 'supplier' :
            case 'question_supplier' :
               foreach ($userIds as $userId) {
                  $this->addActor('supplier', $userId, $notify);
               }
               break;
         }
      }
   }

   protected function addActor($role, $user, $notify) {
      if (filter_var($user, FILTER_VALIDATE_EMAIL) !== false) {
         $userId = 0;
         $alternativeEmail = $user;
      } else {
         $userId = intval($user);
         $alternativeEmail = '';
         if ($userId == '0') {
            // there is no actor
            return;
         }
      }

      switch ($role) {
         case 'requester':
            $this->requesters['_users_id_requester'][]                                    = $userId;
            $this->requesters['_users_id_requester_notif']['use_notification'][]          = ($notify == true);
            $this->requesters['_users_id_requester_notif']['alternative_email'][]         = $alternativeEmail;
            break;
         case 'observer' :
            $this->observers['_users_id_observer'][]                                      = $userId;
            $this->observers['_users_id_observer_notif']['use_notification'][]            = ($notify == true);
            $this->observers['_users_id_observer_notif']['alternative_email'][]           = $alternativeEmail;
            break;
         case 'assigned' :
            $this->assigned['_users_id_assign'][]                                         = $userId;
            $this->assigned['_users_id_assign_notif']['use_notification'][]               = ($notify == true);
            $this->assigned['_users_id_assign_notif']['alternative_email'][]              = $alternativeEmail;
            break;
         case 'supplier' :
            $this->assignedSuppliers['_suppliers_id_assign'][]                            = $userId;
            $this->assignedSuppliers['_suppliers_id_assign_notif']['use_notification'][]  = ($notify == true);
            $this->assignedSuppliers['_suppliers_id_assign_notif']['alternative_email'][] = $alternativeEmail;
            break;
      }
   }

   protected function addGroupActor($role, $group) {
      switch ($role) {
         case 'requester':
            $this->requesterGroups['_groups_id_requester'][]  = $group;
            break;
         case 'observer' :
            $this->observerGroups['_groups_id_observer'][]     = $group;
            break;
         case 'assigned' :
            $this->assignedGroups['_groups_id_assign'][]       = $group;
            break;
      }
   }

   /**
    * Attach documents of the answer to the target
    */
   protected function attachDocument($formAnswerId, $itemtype, $targetID) {
      $docItem = new Document_Item();
      if (count($this->attachedDocuments) > 0) {
         foreach ($this->attachedDocuments as $documentID => $dummy) {
            $docItem->add([
               'documents_id' => $documentID,
               'itemtype'     => $itemtype,
               'items_id'     => $targetID
            ]);
         }
      }
   }

   protected function showDestinationEntitySetings($rand) {
      global $DB;

      echo '<tr class="line1">';
      echo '<td width="15%">' . __('Destination entity') . '</td>';
      echo '<td width="25%">';
      Dropdown::showFromArray(
         'destination_entity',
         self::getEnumDestinationEntity(),
         [
            'value'     => $this->fields['destination_entity'],
            'on_change' => 'change_entity()',
            'rand'      => $rand,
         ]
      );

      $script = <<<EOS
         function change_entity() {
            $('#entity_specific_title').hide();
            $('#entity_user_title').hide();
            $('#entity_entity_title').hide();
            $('#entity_specific_value').hide();
            $('#entity_user_value').hide();
            $('#entity_entity_value').hide();

            switch($('#dropdown_destination_entity$rand').val()) {
               case 'specific' :
                  $('#entity_specific_title').show();
                  $('#entity_specific_value').show();
                  break;
               case 'user' :
                  $('#entity_user_title').show();
                  $('#entity_user_value').show();
                  break;
               case 'entity' :
                  $('#entity_entity_title').show();
                  $('#entity_entity_value').show();
                  break;
            }
         }
         change_entity();
EOS;

      echo Html::scriptBlock($script);
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
      // select all user questions (GLPI Object)
      $query2 = "SELECT q.id, q.name, q.values
                FROM glpi_plugin_formcreator_questions q
                INNER JOIN glpi_plugin_formcreator_sections s
                  ON s.id = q.plugin_formcreator_sections_id
                INNER JOIN glpi_plugin_formcreator_targets t
                  ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = ".$this->getID()."
                AND q.fieldtype = 'glpiselect'
                AND q.values = 'User'";
      $result2 = $DB->query($query2);
      $users_questions = [];
      while ($question = $DB->fetch_array($result2)) {
         $users_questions[$question['id']] = $question['name'];
      }
      Dropdown::showFromArray('_destination_entity_value_user', $users_questions, [
         'value' => $this->fields['destination_entity_value'],
      ]);
      echo '</div>';

      echo '<div id="entity_entity_value" style="display: none">';
      // select all entity questions (GLPI Object)
      $query2 = "SELECT q.id, q.name, q.values
                FROM glpi_plugin_formcreator_questions q
                INNER JOIN glpi_plugin_formcreator_sections s
                  ON s.id = q.plugin_formcreator_sections_id
                INNER JOIN glpi_plugin_formcreator_targets t
                  ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = ".$this->getID()."
                AND q.fieldtype = 'glpiselect'
                AND q.values = 'Entity'";
      $result2 = $DB->query($query2);
      $entities_questions = [];
      while ($question = $DB->fetch_array($result2)) {
         $entities_questions[$question['id']] = $question['name'];
      }
      Dropdown::showFromArray('_destination_entity_value_entity', $entities_questions, [
         'value' => $this->fields['destination_entity_value'],
      ]);
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showTemplateSettins($rand) {
      echo '<td width="15%">' . _n('Ticket template', 'Ticket templates', 1) . '</td>';
      echo '<td width="25%">';
      Dropdown::show('TicketTemplate', [
         'name'  => 'tickettemplates_id',
         'value' => $this->fields['tickettemplates_id']
      ]);
      echo '</td>';
   }

   protected  function showDueDateSettings($rand) {
      global $DB;

      echo '<td width="15%">' . __('Time to resolve') . '</td>';
      echo '<td width="45%">';

      // Due date type selection
      Dropdown::showFromArray('due_date_rule', self::getEnumDueDateRule(),
         [
            'value'     => $this->fields['due_date_rule'],
            'on_change' => 'formcreatorChangeDueDate(this.value)',
            'display_emptychoice' => true
         ]
      );

      // for each section ...
      $questions_list = [Dropdown::EMPTY_VALUE];
      $query = "SELECT s.id, s.name
                FROM glpi_plugin_formcreator_targets t
                INNER JOIN glpi_plugin_formcreator_sections s ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = " . $this->getID() . "
                ORDER BY s.order";
      $result = $DB->query($query);
      while ($section = $DB->fetch_array($result)) {
         // select all date and datetime questions
         $query2 = "SELECT q.id, q.name
         FROM glpi_plugin_formcreator_questions q
         INNER JOIN glpi_plugin_formcreator_sections s
         ON s.id = q.plugin_formcreator_sections_id
         WHERE s.id = {$section['id']}
         AND q.fieldtype IN ('date', 'datetime')";
         $result2 = $DB->query($query2);
         $section_questions = [];
         while ($question = $DB->fetch_array($result2)) {
            $section_questions[$question['id']] = $question['name'];
         }
         if (count($section_questions) > 0) {
            $questions_list[$section['name']] = $section_questions;
         }
      }
      // List questions
      if ($this->fields['due_date_rule'] != 'answer'
            && $this->fields['due_date_rule'] != 'calcul') {
         echo '<div id="due_date_questions" style="display:none">';
      } else {
         echo '<div id="due_date_questions">';
      }
      Dropdown::showFromArray('due_date_question', $questions_list, [
         'value' => $this->fields['due_date_question']
      ]);
      echo '</div>';

      if ($this->fields['due_date_rule'] != 'ticket'
            && $this->fields['due_date_rule'] != 'calcul') {
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
         'minute' => _n('Minute', 'Minutes', 2),
         'hour'   => _n('Hour', 'Hours', 2),
         'day'    => _n('Day', 'Days', 2),
         'month'  => __('Month'),
      ], [
         'value' => $this->fields['due_date_period']
      ]);
      echo '</div>';
      echo '</td>';
   }

   protected function showCategorySettings($rand) {
      global $DB;

      echo '<tr class="line0">';
      echo '<td width="15%">' . __('Ticket category', 'formcreator') . '</td>';
      echo '<td width="25%">';
      Dropdown::showFromArray(
            'category_rule',
            static::getEnumCategoryRule(),
            [
               'value'     => $this->fields['category_rule'],
               'on_change' => 'change_category()',
               'rand'      => $rand,
            ]);
      $script = <<<EOS
         function change_category() {
            $('#category_specific_title').hide();
            $('#category_specific_value').hide();
            $('#category_question_title').hide();
            $('#category_question_value').hide();

            switch($('#dropdown_category_rule$rand').val()) {
               case 'answer' :
                  $('#category_question_title').show();
                  $('#category_question_value').show();
                  break;
               case 'specific' :
                  $('#category_specific_title').show();
                  $('#category_specific_value').show();
                  break;
            }
         }
         change_category();
EOS;
      echo Html::scriptBlock($script);
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="category_specific_title" style="display: none">' . __('Category', 'formcreator') . '</span>';
      echo '<span id="category_question_title" style="display: none">' . __('Question', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';
      echo '<div id="category_question_value" style="display: none">';
      // select all user questions (GLPI Object)
      $query2 = "SELECT `q`.`id`, `q`.`name`, `q`.`values`
                FROM `glpi_plugin_formcreator_questions` `q`
                INNER JOIN `glpi_plugin_formcreator_sections` `s`
                  ON `s`.`id` = `q`.`plugin_formcreator_sections_id`
                INNER JOIN `glpi_plugin_formcreator_targets` `t`
                  ON `s`.`plugin_formcreator_forms_id` = `t`.`plugin_formcreator_forms_id`
                WHERE `t`.`items_id` = ".$this->getID()."
                AND `q`.`fieldtype` = 'dropdown'";
      $result2 = $DB->query($query2);
      $users_questions = [];
      while ($question = $DB->fetch_array($result2)) {
         $decodedValues = json_decode($question['values'], JSON_OBJECT_AS_ARRAY);
         if (isset($decodedValues['itemtype']) && $decodedValues['itemtype'] === 'ITILCategory') {
            $users_questions[$question['id']] = $question['name'];
         }
      }
      Dropdown::showFromArray('_category_question', $users_questions, [
         'value' => $this->fields['category_question'],
      ]);
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
      global $DB;

      echo '<tr class="line0">';
      echo '<td width="15%">' . __('Urgency') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('urgency_rule', static::getEnumUrgencyRule(), [
         'value'                 => $this->fields['urgency_rule'],
         'on_change'             => 'change_urgency()',
         'rand'                  => $rand
      ]);
      $script = <<<EOS
         function change_urgency() {
            $('#urgency_specific_title').hide();
            $('#urgency_specific_value').hide();
            $('#urgency_question_title').hide();
            $('#urgency_question_value').hide();

            switch($('#dropdown_urgency_rule$rand').val()) {
               case 'answer' :
                  $('#urgency_question_title').show();
                  $('#urgency_question_value').show();
                  break;
               case 'specific':
                  $('#urgency_specific_title').show();
                  $('#urgency_specific_value').show();
                  break;
            }
         }
         change_urgency();
EOS;
      echo Html::scriptBlock($script);
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
      // select all user questions (GLPI Object)
      $query2 = "SELECT q.id, q.name, q.values
                FROM glpi_plugin_formcreator_questions q
                INNER JOIN glpi_plugin_formcreator_sections s
                  ON s.id = q.plugin_formcreator_sections_id
                INNER JOIN glpi_plugin_formcreator_targets t
                  ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = ".$this->getID()."
                AND q.fieldtype = 'urgency'";
      $result2 = $DB->query($query2);
      $users_questions = [];
      while ($question = $DB->fetch_array($result2)) {
         $users_questions[$question['id']] = $question['name'];
      }
      Dropdown::showFromArray('_urgency_question', $users_questions, [
         'value' => $this->fields['urgency_question'],
      ]);
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showPluginTagsSettings($rand) {
      global $DB;

      $plugin = new Plugin();
      if ($plugin->isInstalled('tag') && $plugin->isActivated('tag')) {
         echo '<tr class="line1">';
         echo '<td width="15%">' . __('Ticket tags', 'formcreator') . '</td>';
         echo '<td width="25%">';
         Dropdown::showFromArray('tag_type', self::getEnumTagType(),
            [
               'value'     => $this->fields['tag_type'],
               'on_change' => 'change_tag_type()',
               'rand'      => $rand,
            ]
         );

         $script = <<<EOS
            function change_tag_type() {
               $('#tag_question_title').hide();
               $('#tag_specific_title').hide();
               $('#tag_question_value').hide();
               $('#tag_specific_value').hide();

               switch($('#dropdown_tag_type$rand').val()) {
                  case 'questions' :
                     $('#tag_question_title').show();
                     $('#tag_question_value').show();
                     break;
                  case 'specifics' :
                     $('#tag_specific_title').show();
                     $('#tag_specific_value').show();
                     break;
                  case 'questions_and_specific' :
                  case 'questions_or_specific' :
                     $('#tag_question_title').show();
                     $('#tag_specific_title').show();
                     $('#tag_question_value').show();
                     $('#tag_specific_value').show();
                     break;
               }
            }
            change_tag_type();
EOS;

         echo Html::scriptBlock($script);
         echo '</td>';
         echo '<td width="15%">';
         echo '<div id="tag_question_title" style="display: none">' . _n('Question', 'Questions', 2, 'formcreator') . '</div>';
         echo '<div id="tag_specific_title" style="display: none">' . __('Tags', 'tag') . '</div>';
         echo '</td>';
         echo '<td width="25%">';

         // Tag questions
         echo '<div id="tag_question_value" style="display: none">';
         $query2 = "SELECT q.id, q.name, q.values
                   FROM glpi_plugin_formcreator_questions q
                   INNER JOIN glpi_plugin_formcreator_sections s
                     ON s.id = q.plugin_formcreator_sections_id
                   INNER JOIN glpi_plugin_formcreator_targets t
                     ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                   WHERE t.items_id = ".$this->getID()."
                   AND q.fieldtype = 'tag'";
         $result2 = $DB->query($query2);
         $entities_questions = [];
         while ($question = $DB->fetch_array($result2)) {
            $entities_questions[$question['id']] = $question['name'];
         }
         Dropdown::showFromArray('_tag_questions', $entities_questions, [
            'values'   => explode(',', $this->fields['tag_questions']),
            'multiple' => true,
         ]);
         echo '</div>';

         // Specific tags
         echo '<div id="tag_specific_value" style="display: none">';

         $obj = new PluginTagTag();
         $obj->getEmpty();

         $targetItemtype = $this->getTargetItemtypeName();
         $where = "(`type_menu` LIKE '%\"$targetItemtype\"%' OR `type_menu` LIKE '0')";
         $where .= getEntitiesRestrictRequest('AND', getTableForItemType('PluginTagTag'));

         $result = $obj->find($where);
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

   /**
    * Parse target content to replace TAGS like ##FULLFORM## by the values
    *
    * @param  String $content                            String to be parsed
    * @param  PluginFormcreatorForm_Answer $formanswer   Formanswer object where answers are stored
    * @param  String                                     full form
    * @return String                                     Parsed string with tags replaced by form values
    */
   protected function parseTags($content, PluginFormcreatorForm_Answer $formanswer, $fullform = "") {
      global $DB, $CFG_GLPI;

      // retrieve answers
      $answers_values = $formanswer->getAnswers($formanswer->getID());

      $section     = new PluginFormcreatorSection();
      $sections    = $section->getSectionsFromForm($formanswer->fields['plugin_formcreator_forms_id']);
      $sectionsIdString = implode(', ', array_keys($sections));

      if (count($sections) > 0) {
         $query_questions = "SELECT `questions`.*, `answers`.`answer`
                             FROM `glpi_plugin_formcreator_questions` AS questions
                             LEFT JOIN `glpi_plugin_formcreator_answers` AS answers
                               ON `answers`.`plugin_formcreator_questions_id` = `questions`.`id`
                               AND `plugin_formcreator_forms_answers_id` = ".$formanswer->getID()."
                             WHERE `questions`.`plugin_formcreator_sections_id` IN ($sectionsIdString)
                             ORDER BY `questions`.`order` ASC";
         $res_questions = $DB->query($query_questions);
         while ($question_line = $DB->fetch_assoc($res_questions)) {
            $classname = 'PluginFormcreator'.ucfirst($question_line['fieldtype']).'Field';
            if (class_exists($classname)) {
               $fieldObject = new $classname($question_line, $question_line['answer']);
            }

            $id    = $question_line['id'];
            if (!PluginFormcreatorFields::isVisible($question_line['id'], $answers_values)) {
               $name = '';
               $value = '';
            } else {
               $name  = $question_line['name'];
               $value = $fieldObject->prepareQuestionInputForTarget($fieldObject->getValue());
            }
            if ($question_line['fieldtype'] !== 'file') {
               $content = str_replace('##question_' . $id . '##', addslashes($name), $content);
               $content = str_replace('##answer_' . $id . '##', $value, $content);
            } else {
               if (strpos($content, '##answer_' . $id . '##') !== false) {
                  $content = str_replace('##question_' . $id . '##', addslashes($name), $content);
                  if ($value !== '') {
                     $content = str_replace('##answer_' . $id . '##', __('Attached document', 'formcreator'), $content);

                     // keep the ID of the document
                     $this->attachedDocuments[$value] = true;
                  } else {
                     $content = str_replace('##answer_' . $id . '##', '', $content);
                  }
               }
            }
         }
      }

      return $content;
   }


   protected function showLocationSettings($rand) {
      global $DB;

      echo '<tr class="line0">';
      echo '<td width="15%">' . __('Location') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('location_rule', static::getEnumLocationRule(), [
         'value'                 => $this->fields['location_rule'],
         'on_change'             => 'change_location()',
         'rand'                  => $rand
      ]);
      $script = <<<JAVASCRIPT
         function change_location() {
            $('#location_specific_title').hide();
            $('#location_specific_value').hide();
            $('#location_question_title').hide();
            $('#location_question_value').hide();

            switch($('#dropdown_location_rule$rand').val()) {
               case 'answer' :
                  $('#location_question_title').show();
                  $('#location_question_value').show();
                  break;
               case 'specific':
                  $('#location_specific_title').show();
                  $('#location_specific_value').show();
                  break;
            }
         }
         change_location();
JAVASCRIPT;
      echo Html::scriptBlock($script);
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
      $query2 = "SELECT q.id, q.name, q.values
                FROM glpi_plugin_formcreator_questions q
                INNER JOIN glpi_plugin_formcreator_sections s
                  ON s.id = q.plugin_formcreator_sections_id
                INNER JOIN glpi_plugin_formcreator_targets t
                  ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = ".$this->getID()."
                AND q.fieldtype = 'dropdown'";
      $result2 = $DB->query($query2);
      $users_questions = [];
      while ($question = $DB->fetch_array($result2)) {
         $decodedValues = json_decode($question['values'], JSON_OBJECT_AS_ARRAY);
         if (isset($decodedValues['itemtype']) && $decodedValues['itemtype'] === 'Location') {
            $users_questions[$question['id']] = $question['name'];
         }
      }
      Dropdown::showFromArray('_location_question', $users_questions, [
         'value' => $this->fields['location_question'],
      ]);
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }
}
