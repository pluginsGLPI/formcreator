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

class PluginFormcreatorTargetTicket extends PluginFormcreatorTargetBase
{
   public static function getTypeName($nb = 1) {
      return _n('Target ticket', 'Target tickets', $nb, 'formcreator');
   }

   protected function getItem_User() {
      return new Ticket_User();
   }

   protected function getItem_Group() {
      return new Group_Ticket();
   }

   protected function getItem_Supplier() {
      return new Supplier_Ticket();
   }

   protected function getItem_Item() {
      return new Item_Ticket();
   }

   protected function getTargetItemtypeName() {
      return Ticket::class;
   }

   public function getItem_Actor() {
      return new PluginFormcreatorTargetTicket_Actor();
   }

   protected function getCategoryFilter() {
      return "`is_request` = '1' OR `is_incident` = '1'";
   }

   /**
    * Show the Form for the adminsitrator to edit in the config page
    *
    * @param  Array  $options Optional options
    *
    * @return NULL         Nothing, just display the form
    */
   public function showForm($options=[]) {
      global $CFG_GLPI, $DB;

      $rand = mt_rand();

      $obj = new PluginFormcreatorTarget();
      $found = $obj->find("itemtype = '" . __CLASS__ . "' AND items_id = " . $this->getID());
      $target = array_shift($found);

      $form = new PluginFormcreatorForm();
      $form->getFromDB($target['plugin_formcreator_forms_id']);

      echo '<div class="center" style="width: 950px; margin: 0 auto;">';
      echo '<form name="form_target" method="post" action="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      // General information: name
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="2">' . __('Edit a destination', 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td width="15%"><strong>' . __('Name') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td width="85%"><input type="text" name="name" style="width:704px;" value="' . $target['name'] . '"></textarea</td>';
      echo '</tr>';

      echo '</table>';

      // Ticket information: title, template...
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="4">' . _n('Target ticket', 'Target tickets', 1, 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Ticket title', 'formcreator') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td colspan="3"><input type="text" name="title" style="width:704px;" value="' . $this->fields['name'] . '"></textarea</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Description') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="comment" style="width:700px;" rows="15">' . $this->fields['comment'] . '</textarea>';
      if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI["use_rich_text"]) {
         Html::initEditorSystem('comment');
      }
      echo '</td>';
      echo '</tr>';

      $rand = mt_rand();
      $this->showDestinationEntitySetings($rand);

      echo '<tr class="line1">';
      $this->showTemplateSettins($rand);
      $this->showDueDateSettings($rand);
      echo '</tr>';

      // -------------------------------------------------------------------------------------------
      //  category of the target
      // -------------------------------------------------------------------------------------------
      $this->showCategorySettings($rand);

      // -------------------------------------------------------------------------------------------
      // Urgency selection
      // -------------------------------------------------------------------------------------------
      $this->showUrgencySettings($rand);

      // -------------------------------------------------------------------------------------------
      // Location selection
      // -------------------------------------------------------------------------------------------
      $this->showLocationSettings($rand);
      // -------------------------------------------------------------------------------------------
      //  Tags
      // -------------------------------------------------------------------------------------------
      $this->showPluginTagsSettings($rand);

      // -------------------------------------------------------------------------------------------
      //  Composite tickets
      // -------------------------------------------------------------------------------------------
      $this->showCompositeTicketSettings($rand);

      // -------------------------------------------------------------------------------------------
      //  Validation as ticket followup
      // -------------------------------------------------------------------------------------------
      if ($form->fields['validation_required']) {
         echo '<tr class="line1">';
         echo '<td colspan="4">';
         echo '<input type="hidden" name="validation_followup" value="0" />';
         echo '<input type="checkbox" name="validation_followup" id="validation_followup" value="1" ';
         if (!isset($this->fields['validation_followup']) || ($this->fields['validation_followup'] == 1)) {
            echo ' checked="checked"';
         }
         echo '/>';
         echo ' <label for="validation_followup">';
         echo __('Add validation message as first ticket followup', 'formcreator');
         echo '</label>';
         echo '</td>';
         echo '</tr>';
      }

      echo '</table>';

      // Buttons
      echo '<table class="tab_cadre_fixe">';

      echo '<tr class="line1">';
      echo '<td colspan="5" class="center">';
      echo '<input type="reset" name="reset" class="submit_button" value="' . __('Cancel', 'formcreator') . '"
               onclick="document.location = \'form.form.php?id=' . $target['plugin_formcreator_forms_id'] . '\'" /> &nbsp; ';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="submit" name="update" class="submit_button" value="' . __('Save') . '" />';
      echo '</td>';
      echo '</tr>';

      echo '</table>';
      Html::closeForm();

      // Get available questions for actors lists
      $questions_user_list     = [Dropdown::EMPTY_VALUE];
      $questions_group_list    = [Dropdown::EMPTY_VALUE];
      $questions_supplier_list = [Dropdown::EMPTY_VALUE];
      $questions_actors_list   = [Dropdown::EMPTY_VALUE];
      $query = "SELECT s.id, s.name
                FROM glpi_plugin_formcreator_targets t
                INNER JOIN glpi_plugin_formcreator_sections s
                  ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = ".$this->getID()."
                ORDER BY s.order";
      $result = $DB->query($query);
      while ($section = $DB->fetch_array($result)) {
         // select all user, group or supplier questions (GLPI Object)
         $query2 = "SELECT q.id, q.name, q.fieldtype, q.values
                   FROM glpi_plugin_formcreator_questions q
                   INNER JOIN glpi_plugin_formcreator_sections s
                     ON s.id = q.plugin_formcreator_sections_id
                   WHERE s.id = {$section['id']}
                   AND ((q.fieldtype = 'glpiselect'
                     AND q.values IN ('User', 'Group', 'Supplier'))
                     OR (q.fieldtype = 'actor'))";
         $result2 = $DB->query($query2);
         $section_questions_user       = [];
         $section_questions_group      = [];
         $section_questions_supplier   = [];
         $section_questions_actors     = [];
         while ($question = $DB->fetch_array($result2)) {
            if ($question['fieldtype'] == 'glpiselect') {
               switch ($question['values']) {
                  case 'User' :
                     $section_questions_user[$question['id']] = $question['name'];
                     break;
                  case 'Group' :
                     $section_questions_group[$question['id']] = $question['name'];
                     break;
                  case 'Supplier' :
                     $section_questions_supplier[$question['id']] = $question['name'];
                     break;
               }
            } else if ($question['fieldtype'] == 'actor') {
               $section_questions_actors[$question['id']] = $question['name'];
            }
         }
         $questions_user_list[$section['name']]     = $section_questions_user;
         $questions_group_list[$section['name']]    = $section_questions_group;
         $questions_supplier_list[$section['name']] = $section_questions_supplier;
         $questions_actors_list[$section['name']]   = $section_questions_actors;
      }

      // Get available questions for actors lists
      $actors = ['requester' => [], 'observer' => [], 'assigned' => []];
      $query = "SELECT id, actor_role, actor_type, actor_value, use_notification
                FROM glpi_plugin_formcreator_targettickets_actors
                WHERE plugin_formcreator_targettickets_id = " . $this->getID();
      $result = $DB->query($query);
      while ($actor = $DB->fetch_array($result)) {
         $actors[$actor['actor_role']][$actor['id']] = [
            'actor_type'       => $actor['actor_type'],
            'actor_value'      => $actor['actor_value'],
            'use_notification' => $actor['use_notification'],
         ];
      }

      $img_user     = '<img src="../../../pics/users.png" alt="' . __('User') . '" title="' . __('User') . '" width="20" />';
      $img_group    = '<img src="../../../pics/groupes.png" alt="' . __('Group') . '" title="' . __('Group') . '" width="20" />';
      $img_supplier = '<img src="../../../pics/supplier.png" alt="' . __('Supplier') . '" title="' . __('Supplier') . '" width="20" />';
      $img_mail     = '<img src="../pics/email.png" alt="' . __('Yes') . '" title="' . __('Email followup') . ' ' . __('Yes') . '" />';
      $img_nomail   = '<img src="../pics/email-no.png" alt="' . __('No') . '" title="' . __('Email followup') . ' ' . __('No') . '" />';

      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="3">' . __('Ticket actors', 'formcreator') . '</th></tr>';

      echo '<tr>';

      echo '<th width="33%">';
      echo _n('Requester', 'Requesters', 1) . ' &nbsp;';
      echo '<img title="Ajouter" alt="Ajouter" onclick="displayRequesterForm()" class="pointer"
               id="btn_add_requester" src="../../../pics/add_dropdown.png">';
      echo '<img title="Annuler" alt="Annuler" onclick="hideRequesterForm()" class="pointer"
               id="btn_cancel_requester" src="../../../pics/delete.png" style="display:none">';
      echo '</th>';

      echo '<th width="34%">';
      echo _n('Watcher', 'Watchers', 1) . ' &nbsp;';
      echo '<img title="Ajouter" alt="Ajouter" onclick="displayWatcherForm()" class="pointer"
               id="btn_add_watcher" src="../../../pics/add_dropdown.png">';
      echo '<img title="Annuler" alt="Annuler" onclick="hideWatcherForm()" class="pointer"
               id="btn_cancel_watcher" src="../../../pics/delete.png" style="display:none">';
      echo '</th>';

      echo '<th width="33%">';
      echo __('Assigned to') . ' &nbsp;';
      echo '<img title="Ajouter" alt="Ajouter" onclick="displayAssignedForm()" class="pointer"
               id="btn_add_assigned" src="../../../pics/add_dropdown.png">';
      echo '<img title="Annuler" alt="Annuler" onclick="hideAssignedForm()" class="pointer"
               id="btn_cancel_assigned" src="../../../pics/delete.png" style="display:none">';
      echo '</th>';

      echo '</tr>';

      echo '<tr>';

      // Requester
      echo '<td valign="top">';

      // => Add requester form
      echo '<form name="form_target" id="form_add_requester" method="post" style="display:none" action="'
           . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      $dropdownItems = ['' => Dropdown::EMPTY_VALUE] + PluginFormcreatorTargetTicket_Actor::getEnumActorType();
      unset($dropdownItems['supplier']);
      unset($dropdownItems['question_supplier']);
      Dropdown::showFromArray(
         'actor_type',
         $dropdownItems,
         ['on_change' => 'formcreatorChangeActorRequester(this.value)']
      );

      echo '<div id="block_requester_user" style="display:none">';
      User::dropdown([
         'name' => 'actor_value_person',
         'right' => 'all',
         'all'   => 0,
      ]);
      echo '</div>';

      echo '<div id="block_requester_group" style="display:none">';
      Group::dropdown([
         'name' => 'actor_value_group',
      ]);
      echo '</div>';

      echo '<div id="block_requester_question_user" style="display:none">';
      Dropdown::showFromArray('actor_value_question_person', $questions_user_list, [
         'value' => $this->fields['due_date_question'],
      ]);
      echo '</div>';

      echo '<div id="block_requester_question_group" style="display:none">';
      Dropdown::showFromArray('actor_value_question_group', $questions_group_list, [
         'value' => $this->fields['due_date_question'],
      ]);
      echo '</div>';

      echo '<div id="block_requester_question_actors" style="display:none">';
      Dropdown::showFromArray('actor_value_question_actors', $questions_actors_list, [
            'value' => $this->fields['due_date_question'],
      ]);
      echo '</div>';

      echo '<div>';
      echo __('Email followup');
      Dropdown::showYesNo('use_notification', 1);
      echo '</div>';

      echo '<p align="center">';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="hidden" name="uuid" value="' . $target['uuid'] . '" />';
      echo '<input type="hidden" name="actor_role" value="requester" />';
      echo '<input type="submit" value="' . __('Add') . '" class="submit_button" />';
      echo '</p>';

      echo "<hr>";

      Html::closeForm();

      // => List of saved requesters
      foreach ($actors['requester'] as $id => $values) {
         echo '<div>';
         switch ($values['actor_type']) {
            case 'creator' :
               echo $img_user . ' <b>' . __('Form requester', 'formcreator') . '</b>';
               break;
            case 'validator' :
               echo $img_user . ' <b>' . __('Form validator', 'formcreator') . '</b>';
               break;
            case 'person' :
               $user = new User();
               $user->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('User') . ' </b> "' . $user->getName() . '"';
               break;
            case 'question_person' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Person from the question', 'formcreator')
                  . '</b> "' . $question->getName() . '"';
               break;
            case 'group' :
               $group = new Group();
               $group->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Group') . ' </b> "' . $group->getName() . '"';
               break;
            case 'question_group' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the question', 'formcreator')
                  . '</b> "' . $question->getName() . '"';
               break;
            case 'question_actors':
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Actors from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo self::getDeleteImage($id);
         echo '</div>';
      }

      echo '</td>';

      // Observer
      echo '<td valign="top">';

      // => Add observer form
      echo '<form name="form_target" id="form_add_watcher" method="post" style="display:none" action="'
           . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      $dropdownItems = [''  => Dropdown::EMPTY_VALUE] + PluginFormcreatorTargetTicket_Actor::getEnumActorType();
      unset($dropdownItems['supplier']);
      unset($dropdownItems['question_supplier']);
      Dropdown::showFromArray(
         'actor_type',
         $dropdownItems,
         ['on_change' => 'formcreatorChangeActorWatcher(this.value)']
      );

      echo '<div id="block_watcher_user" style="display:none">';
      User::dropdown([
         'name' => 'actor_value_person',
         'right' => 'all',
         'all'   => 0,
      ]);
      echo '</div>';

      echo '<div id="block_watcher_group" style="display:none">';
      Group::dropdown([
         'name' => 'actor_value_group',
      ]);
      echo '</div>';

      echo '<div id="block_watcher_question_user" style="display:none">';
      Dropdown::showFromArray('actor_value_question_person', $questions_user_list, [
         'value' => $this->fields['due_date_question'],
      ]);
      echo '</div>';

      echo '<div id="block_watcher_question_group" style="display:none">';
      Dropdown::showFromArray('actor_value_question_group', $questions_group_list, [
         'value' => $this->fields['due_date_question'],
      ]);
      echo '</div>';

      echo '<div id="block_watcher_question_actors" style="display:none">';
      Dropdown::showFromArray('actor_value_question_actors', $questions_actors_list, [
         'value' => $this->fields['due_date_question'],
      ]);
      echo '</div>';

      echo '<div>';
      echo __('Email followup');
      Dropdown::showYesNo('use_notification', 1);
      echo '</div>';

      echo '<p align="center">';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="hidden" name="actor_role" value="observer" />';
      echo '<input type="submit" value="' . __('Add') . '" class="submit_button" />';
      echo '</p>';

      echo "<hr>";

      Html::closeForm();

      // => List of saved observers
      foreach ($actors['observer'] as $id => $values) {
         echo '<div>';
         switch ($values['actor_type']) {
            case 'creator' :
               echo $img_user . ' <b>' . __('Form requester', 'formcreator') . '</b>';
               break;
            case 'validator' :
               echo $img_user . ' <b>' . __('Form validator', 'formcreator') . '</b>';
               break;
            case 'person' :
               $user = new User();
               $user->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('User') . ' </b> "' . $user->getName() . '"';
               break;
            case 'question_person' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Person from the question', 'formcreator')
                  . '</b> "' . $question->getName() . '"';
               break;
            case 'group' :
               $group = new Group();
               $group->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Group') . ' </b> "' . $group->getName() . '"';
               break;
            case 'question_group' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the question', 'formcreator')
                  . '</b> "' . $question->getName() . '"';
               break;
            case 'question_actors' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Actors from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo self::getDeleteImage($id);
         echo '</div>';
      }

      echo '</td>';

      // Assigned to
      echo '<td valign="top">';

      // => Add assigned to form
      echo '<form name="form_target" id="form_add_assigned" method="post" style="display:none" action="'
            . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      $dropdownItems = [''  => Dropdown::EMPTY_VALUE] + PluginFormcreatorTargetTicket_Actor::getEnumActorType();
      Dropdown::showFromArray(
         'actor_type',
         $dropdownItems,
         ['on_change' => 'formcreatorChangeActorAssigned(this.value)']
      );

      echo '<div id="block_assigned_user" style="display:none">';
      User::dropdown([
         'name' => 'actor_value_person',
         'right' => 'all',
         'all'   => 0,
      ]);
      echo '</div>';

      echo '<div id="block_assigned_group" style="display:none">';
      Group::dropdown([
         'name' => 'actor_value_group',
      ]);
      echo '</div>';

      echo '<div id="block_assigned_supplier" style="display:none">';
      Supplier::dropdown([
         'name' => 'actor_value_supplier',
      ]);
      echo '</div>';

      echo '<div id="block_assigned_question_user" style="display:none">';
      Dropdown::showFromArray('actor_value_question_person', $questions_user_list, [
         'value' => $this->fields['due_date_question'],
      ]);
      echo '</div>';

      echo '<div id="block_assigned_question_group" style="display:none">';
      Dropdown::showFromArray('actor_value_question_group', $questions_group_list, array(
         'value' => $this->fields['due_date_question'],
      ));
      echo '</div>';

      echo '<div id="block_assigned_question_actors" style="display:none">';
      Dropdown::showFromArray('actor_value_question_actors', $questions_actors_list, array(
            'value' => $this->fields['due_date_question'],
      ));
      echo '</div>';

      echo '<div id="block_assigned_question_supplier" style="display:none">';
      Dropdown::showFromArray('actor_value_question_supplier', $questions_supplier_list, array(
         'value' => $this->fields['due_date_question'],
      ));
      echo '</div>';

      echo '<div>';
      echo __('Email followup');
      Dropdown::showYesNo('use_notification', 1);
      echo '</div>';

      echo '<p align="center">';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="hidden" name="actor_role" value="assigned" />';
      echo '<input type="submit" value="' . __('Add') . '" class="submit_button" />';
      echo '</p>';

      echo "<hr>";

      Html::closeForm();

      // => List of saved assigned to
      foreach ($actors['assigned'] as $id => $values) {
         echo '<div>';
         switch ($values['actor_type']) {
            case 'creator' :
               echo $img_user . ' <b>' . __('Form requester', 'formcreator') . '</b>';
               break;
            case 'validator' :
               echo $img_user . ' <b>' . __('Form validator', 'formcreator') . '</b>';
               break;
            case 'person' :
               $user = new User();
               $user->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('User') . ' </b> "' . $user->getName() . '"';
               break;
            case 'question_person' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Person from the question', 'formcreator')
                  . '</b> "' . $question->getName() . '"';
               break;
            case 'group' :
               $group = new Group();
               $group->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Group') . ' </b> "' . $group->getName() . '"';
               break;
            case 'question_group' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the question', 'formcreator')
                  . '</b> "' . $question->getName() . '"';
               break;
            case 'question_actors' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Actors from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case 'supplier' :
               $supplier = new Supplier();
               $supplier->getFromDB($values['actor_value']);
               echo $img_supplier . ' <b>' . __('Supplier') . ' </b> "' . $supplier->getName() . '"';
               break;
            case 'question_supplier' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_supplier . ' <b>' . __('Supplier from the question', 'formcreator')
                  . '</b> "' . $question->getName() . '"';
               break;
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo self::getDeleteImage($id);
         echo '</div>';
      }

      echo '</td>';

      echo '</tr>';

      echo '</table>';

      // List of available tags
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="5">' . __('List of available tags') . '</th></tr>';
      echo '<tr>';
      echo '<th width="40%" colspan="2">' . _n('Question', 'Questions', 1, 'formcreator') . '</th>';
      echo '<th width="20%">' . __('Title') . '</th>';
      echo '<th width="20%">' . _n('Answer', 'Answers', 1, 'formcreator') . '</th>';
      echo '<th width="20%">' . _n('Section', 'Sections', 1, 'formcreator') . '</th>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td colspan="2"><strong>' . __('Full form', 'formcreator') . '</strong></td>';
      echo '<td align="center"><code>-</code></td>';
      echo '<td align="center"><code><strong>##FULLFORM##</strong></code></td>';
      echo '<td align="center">-</td>';
      echo '</tr>';

      $table_questions = getTableForItemType('PluginFormcreatorQuestion');
      $table_sections  = getTableForItemType('PluginFormcreatorSection');
      $query = "SELECT q.`id`, q.`name` AS question, s.`name` AS section
                FROM $table_questions q
                LEFT JOIN $table_sections s
                  ON q.`plugin_formcreator_sections_id` = s.`id`
                WHERE s.`plugin_formcreator_forms_id` = " . $target['plugin_formcreator_forms_id'] . "
                ORDER BY s.`order`, q.`order`";
      $result = $DB->query($query);

      $i = 0;
      while ($question = $DB->fetch_array($result)) {
         $i++;
         echo '<tr class="line' . ($i % 2) . '">';
         echo '<td colspan="2">' . $question['question'] . '</td>';
         echo '<td align="center"><code>##question_' . $question['id'] . '##</code></td>';
         echo '<td align="center"><code>##answer_' . $question['id'] . '##</code></td>';
         echo '<td align="center">' . $question['section'] . '</td>';
         echo '</tr>';
      }

      echo '</table>';
      echo '</div>';
   }

   /**
    * Show settings to handle composite tickets
    * @param string $rand
    */
   protected function showCompositeTicketSettings($rand) {
      echo '<tr class="line1">';
      echo '<td>';
      echo __('Link to an other ticket', 'formcreator');
      echo "<span class='fa fa-plus pointer' onClick=\"".Html::jsShow("plugin_formcreator_linked_items$rand")."\"
             title=\"".__s('Add')."\"><span class='sr-only'>" . __s('Add') . "</span></span>";

      echo '</td>';
      echo '<td colspan="3">';
      echo '<div style="display: none" id="plugin_formcreator_linked_items' . $rand . '">';
      Ticket_Ticket::dropdownLinks('_linktype');
      $elements = [
         'PluginFormcreatorTargetTicket'  => __('An other destination of this form', 'formcreator'),
         'Ticket'                         => __('An existing ticket', 'formcreator'),
      ];
      Dropdown::showFromArray('_link_itemtype', $elements, [
         'on_change' => 'updateCompositePeerType()',
         'rand'      => $rand,
      ]);
      $script = <<<EOS
      function updateCompositePeerType() {
         if ($('#dropdown__link_itemtype$rand').val() == 'Ticket') {
            $('#plugin_formcreator_link_ticket').show();
            $('#plugin_formcreator_link_target').hide();
         } else {
            $('#plugin_formcreator_link_ticket').hide();
            $('#plugin_formcreator_link_target').show();
         }
      }
      updateCompositePeerType();
EOS;
      echo Html::scriptBlock($script);
      // get already linked items
      $targetTicketId = $this->getID();
      $item_targetTicket = new PluginFormcreatorItem_TargetTicket();
      $rows = $item_targetTicket->find("`plugin_formcreator_targettickets_id` = '$targetTicketId'");
      $excludedTargetTicketsIds = [];
      $excludedTicketIds = [];
      foreach ($rows as $row) {
         switch ($row['itemtype']) {
            case PluginFormcreatorTargetTicket::getType():
               $excludedTargetTicketsIds[] = $row['items_id'];
               break;

            case Ticket::getType():
               $excludedTicketIds[] = $row['items_id'];
               break;
         }
      }

      echo '<span id="plugin_formcreator_link_ticket">';
      $linkparam = [
         'name'        => '_link_tickets_id',
         'used'        => $excludedTicketIds,
         'displaywith' => ['id'],
         'display'     => false
      ];
      echo Ticket::dropdown($linkparam);
      echo '</span>';

      // dropdown of target tickets
      echo '<span id="plugin_formcreator_link_target">';
      $excludedTargetTicketsIds[] = $this->getID();
      echo PluginFormcreatorTargetTicket::dropdown([
         'name' => '_link_targettickets_id',
         'rand' => $rand,
         'display' => false,
         'used'   => $excludedTargetTicketsIds,
         'condition' => "`id` IN (
            SELECT `items_id` FROM `glpi_plugin_formcreator_targets` AS `t1`
            WHERE `plugin_formcreator_forms_id` = (
               SELECT `plugin_formcreator_forms_id` FROM `glpi_plugin_formcreator_targets` AS `t2`
               WHERE `t2`.`itemtype` = 'PluginFormcreatorTargetTicket' AND `t2`.`items_id` = '$targetTicketId'
            )
            AND `t1`.`itemtype` = 'PluginFormcreatorTargetTicket')",
      ]);
      echo '</span>';
      echo '</div>';

      // show already linked items
      foreach ($rows as $row) {
         $icons = '&nbsp;'.Html::getSimpleForm(PluginFormcreatorItem_TargetTicket::getFormURL(), 'purge',
               _x('button', 'Delete permanently'),
               ['id'                => $row['id']],
               'fa-times-circle');
               $itemtype = $row['itemtype'];
               $item = new $itemtype();
               $item->getFromDB($row['items_id']);
         switch ($itemtype) {
            case Ticket::getType():
               //TODO: when merge of https://github.com/glpi-project/glpi/pull/2840 (this is a BC)
               //echo Ticket_Ticket::getLinkName($row['link']);
               echo PluginFormcreatorCommon::getLinkName($row['link']);
               echo ' ';
               echo $itemtype::getTypeName();
               echo ' ';
               echo '<span style="font-weight:bold">' . $item->getField('name') . '</span>';
               echo ' ';
               echo $icons;
               break;

            case PluginFormcreatorTargetTicket::getType():
               // TODO: when merge of https://github.com/glpi-project/glpi/pull/2840 (this is a BC)
               //echo Ticket_Ticket::getLinkName($row['link']);
               echo PluginFormcreatorCommon::getLinkName($row['link']);
               echo ' ';
               echo $itemtype::getTypeName();
               echo ' ';
               echo '<span style="font-weight:bold">' . $item->getField('name') . '</span>';
               echo ' ';
               echo $icons;
               break;
         }
         echo '<br>';
      }

      echo '</td>';
      echo '</tr>';
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
    * Prepare input datas for updating the target ticket
    *
    * @param array $input datas used to add the item
    *
    * @return array the modified $input array
   **/
   public function prepareInputForUpdate($input) {
      global $CFG_GLPI;

      // Control fields values :
      if (!isset($input['_skip_checks'])
          || !$input['_skip_checks']) {
         // - name is required
         if (empty($input['title'])) {
            Session::addMessageAfterRedirect(__('The title cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }

         // - comment is required
         if (empty($input['comment'])) {
            Session::addMessageAfterRedirect(__('The description cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }

         if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text']) {
            $input['comment'] = Html::entity_decode_deep($input['comment']);
         }

         switch ($input['destination_entity']) {
            case 'specific' :
               $input['destination_entity_value'] = $input['_destination_entity_value_specific'];
               break;
            case 'user' :
               $input['destination_entity_value'] = $input['_destination_entity_value_user'];
               break;
            case 'entity' :
               $input['destination_entity_value'] = $input['_destination_entity_value_entity'];
               break;
            default :
               $input['destination_entity_value'] = 'NULL';
               break;
         }

         switch ($input['urgency_rule']) {
            case 'answer':
               $input['urgency_question'] = $input['_urgency_question'];
               break;
            case 'specific':
               $input['urgency_question'] = $input['_urgency_specific'];
               break;
            default:
               $input['urgency_question'] = '0';
         }

         switch ($input['category_rule']) {
            case 'answer':
               $input['category_question'] = $input['_category_question'];
               break;
            case 'specific':
               $input['category_question'] = $input['_category_specific'];
               break;
            default:
               $input['category_question'] = '0';
         }

         switch ($input['location_rule']) {
            case 'answer':
               $input['location_question'] = $input['_location_question'];
               break;
            case 'specific':
               $input['location_question'] = $input['_location_specific'];
               break;
            default:
               $input['location_question'] = '0';
         }

         $plugin = new Plugin();
         if ($plugin->isInstalled('tag') && $plugin->isActivated('tag')) {
            $input['tag_questions'] = (!empty($input['_tag_questions']))
                                       ? implode(',', $input['_tag_questions'])
                                       : '';
            $input['tag_specifics'] = (!empty($input['_tag_specifics']))
                                       ? implode(',', $input['_tag_specifics'])
                                       : '';
         }
      }

      if (isset($input['_linktype']) && isset($input['_link_itemtype'])) {
         $input = $this->saveLinkedItem($input);
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      $target = new PluginFormcreatorTarget();
      $found  = $target->find('items_id = ' . $this->getID());
      $found  = array_shift($found);
      $target->update(['id' => $found['id'], 'name' => $input['name']]);
      $input['name'] = $input['title'];
      unset($input['title']);

      return $input;
   }

   /**
    * Save links to other items for composite tickets
    * @param array $input form data
    *
    * @return array
    */
   private function saveLinkedItem($input) {
      // Check link type is valid
      $linktype = (int) $input['_linktype'];
      if ($linktype < Ticket_Ticket::LINK_TO || $linktype > Ticket_Ticket::PARENT_OF) {
         Session::addMessageAfterRedirect(__('Invalid link type', 'formcreator'), false, ERROR);
         return [];
      }

      // Check itemtype
      $itemtype = $input['_link_itemtype'];
      switch ($itemtype) {
         case Ticket::getType():
            $itemId = (int) $input['_link_tickets_id'];
            break;

         case PluginFormcreatorTargetTicket::getType():
            $itemId = (int) $input['_link_targettickets_id'];
            break;

         default:
            Session::addMessageAfterRedirect(__('Invalid linked item type', 'formcreator'), false, ERROR);
            return [];
      }
      $item = new $itemtype();

      // Check an id was provided (if not, then the fields were not populated)
      if ($item::isNewID($itemId)) {
         // nothing to do
         return $input;
      }

      // Check item exists
      if (!$item->getFromDB($itemId)) {
         Session::addMessageAfterRedirect(__('Linked item does not exists', 'formcreator'), false, ERROR);
         return [];
      }

      $item_targetTicket = new PluginFormcreatorItem_TargetTicket();
      $item_targetTicket->add([
         'plugin_formcreator_targettickets_id'  => $this->getID(),
         'link'                                 => $linktype,
         'itemtype'                             => $itemtype,
         'items_id'                             => $itemId,
      ]);

      if ($item_targetTicket->isNewItem()) {
         Session::addMessageAfterRedirect(__('Failed to link the item', 'formcreator'), false, ERROR);
      }

      return $input;
   }

   public function pre_deleteItem() {
      // delete actors related to this instance
      $targetTicketActor = new PluginFormcreatorTargetTicket_Actor();
      $success = $targetTicketActor->deleteByCriteria([
         'plugin_formcreator_targettickets_id' => $this->getID(),
      ]);

      // delete targets linked to this instance
      $item_targetTicket = new PluginFormcreatorItem_TargetTicket();
      $success |= $item_targetTicket->deleteByCriteria([
         'plugin_formcreator_targettickets_id'  => $this->getID(),
      ]);
      $success |= $item_targetTicket->deleteByCriteria([
         'itemtype'  => $this->getID(),
         'items_id'  => $this->getID(),
      ]);

      return $success;
   }

   /**
    * Save form data to the target
    *
    * @param  PluginFormcreatorForm_Answer $formanswer    Answers previously saved
    *
    * @return Ticket|false Generated ticket if success, null otherwise
    */
   public function save(PluginFormcreatorForm_Answer $formanswer) {
      global $DB, $CFG_GLPI;

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

      $data = Ticket::getDefaultValues();
      $ticket  = new Ticket();
      $form    = $formanswer->getForm();
      $answer  = new PluginFormcreatorAnswer();

      $data['requesttypes_id'] = PluginFormcreatorCommon::getFormcreatorRequestTypeId();

      // Get predefined Fields
      $ttp                  = new TicketTemplatePredefinedField();
      $predefined_fields    = $ttp->getPredefinedFields($this->fields['tickettemplates_id'], true);

      if (isset($predefined_fields['_users_id_requester'])) {
         $this->addActor('requester', $predefined_fields['_users_id_requester'], true);
         unset($predefined_fields['_users_id_requester']);
      }
      if (isset($predefined_fields['_users_id_observer'])) {
         $this->addActor('observer', $predefined_fields['_users_id_observer'], true);
         unset($predefined_fields['_users_id_observer']);
      }
      if (isset($predefined_fields['_users_id_assign'])) {
         $this->addActor('assigned', $predefined_fields['_users_id_assign'], true);
         unset($predefined_fields['_users_id_assign']);
      }

      if (isset($predefined_fields['_groups_id_requester'])) {
         $this->addGroupActor('assigned', $predefined_fields['_groups_id_requester']);
         unset($predefined_fields['_groups_id_requester']);
      }
      if (isset($predefined_fields['_groups_id_observer'])) {
         $this->addGroupActor('observer', $predefined_fields['_groups_id_observer']);
         unset($predefined_fields['_groups_id_observer']);
      }
      if (isset($predefined_fields['_groups_id_assign'])) {
         $this->addGroupActor('assigned', $predefined_fields['_groups_id_assign']);
         unset($predefined_fields['_groups_id_assign']);
      }

      $data                = array_merge($data, $predefined_fields);

      // Parse data
      // TODO: generate instances of all answers of the form and use them for the fullform computation
      //       and the computation from a admin-defined target ticket template
      $data['name'] = addslashes($this->fields['name']);
      $data['name'] = $this->parseTags($data['name'], $formanswer);

      $data['content'] = addslashes($this->fields['comment']);
      $data['content'] = str_replace("\r\n", '\r\n', $data['content']);
      if (strpos($data['content'], '##FULLFORM##') !== false) {
         $data['content'] = str_replace('##FULLFORM##', $formanswer->getFullForm(), $data['content']);
      }
      if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text']) {
         // replace HTML P tags with DIV tags
         $data['content'] = str_replace(['<p>', '</p>'], ['<div>', '</div>'], $data['content']);
      }

      $data['content'] = $this->parseTags($data['content'], $formanswer);
      if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text']) {
         $data['content'] = htmlentities($data['content']);
      }
      $data['_users_id_recipient'] = $_SESSION['glpiID'];
      $data['_tickettemplates_id'] = $this->fields['tickettemplates_id'];

      $this->prepareActors($form, $formanswer);

      if (count($this->requesters['_users_id_requester']) == 0) {
         $this->addActor('requester', $formanswer->fields['requester_id'], true);
         $requesters_id = $formanswer->fields['requester_id'];
      } else if (count($this->requesters['_users_id_requester']) >= 1) {
         if ($this->requesters['_users_id_requester'][0] == 0) {
            $this->addActor('requester', $formanswer->fields['requester_id'], true);
            $requesters_id = $formanswer->fields['requester_id'];
         } else {
            $requesters_id = $this->requesters['_users_id_requester'][0];
         }

         // If only one requester, revert array of requesters into a scalar
         // This is needed to process business rule affecting location of a ticket with the location of the user
         if (count($this->requesters['_users_id_requester']) == 1) {
            $this->requesters['_users_id_requester'] = array_pop($this->requesters['_users_id_requester']);
         }
      }

      // Computation of the entity
      switch ($this->fields['destination_entity']) {
         // Requester's entity
         case 'current' :
            $data['entities_id'] = $formanswer->getField('entities_id');
            break;

         case 'requester' :
            $userObj = new User();
            $userObj->getFromDB($requesters_id);
            $data['entities_id'] = $userObj->fields['entities_id'];
            break;

         // Requester's first dynamic entity
         case 'requester_dynamic_first' :
            $order_entities = "`glpi_profiles`.`name` ASC";
         case 'requester_dynamic_last' :
            if (!isset($order_entities)) {
               $order_entities = "`glpi_profiles`.`name` DESC";
            }
            $query_entities = "SELECT `glpi_profiles_users`.`entities_id`
                      FROM `glpi_profiles_users`
                      LEFT JOIN `glpi_profiles`
                        ON `glpi_profiles`.`id` = `glpi_profiles_users`.`profiles_id`
                      WHERE `glpi_profiles_users`.`users_id` = $requesters_id
                     ORDER BY `glpi_profiles_users`.`is_dynamic` DESC, $order_entities";
            $res_entities = $DB->query($query_entities);
            $data_entities = [];
            while ($entity = $DB->fetch_array($res_entities)) {
               $data_entities[] = $entity;
            }
            $first_entity = array_shift($data_entities);
            $data['entities_id'] = $first_entity['entities_id'];
            break;

         // Specific entity
         case 'specific' :
            $data['entities_id'] = $this->fields['destination_entity_value'];
            break;

         // The form entity
         case 'form' :
            $data['entities_id'] = $form->fields['entities_id'];
            break;

         // The validator entity
         case 'validator' :
            $userObj = new User();
            $userObj->getFromDB($formanswer->fields['users_id_validator']);
            $data['entities_id'] = $userObj->fields['entities_id'];
            break;

         // Default entity of a user from the answer of a user's type question
         case 'user' :
            $found   = $answer->find('plugin_formcreator_forms_answers_id = '.$formanswer->fields['id'].
                                     ' AND plugin_formcreator_questions_id = '.$this->fields['destination_entity_value']);
            $user    = array_shift($found);
            $user_id = $user['answer'];

            if ($user_id > 0) {
               $userObj = new User();
               $userObj->getFromDB($user_id);
               $data['entities_id'] = $userObj->fields['entities_id'];
            } else {
               $data['entities_id'] = 0;
            }
            break;

         // Entity from the answer of an entity's type question
         case 'entity' :
            $found  = $answer->find('plugin_formcreator_forms_answers_id = '.$formanswer->fields['id'].
                                    ' AND plugin_formcreator_questions_id = '.$this->fields['destination_entity_value']);
            $entity = array_shift($found);

            $data['entities_id'] = $entity['answer'];
            break;

         // Requester current entity
         default :
            $data['entities_id'] = 0;
            break;
      }

      $data = $this->setTargetDueDate($data, $formanswer);
      $data = $this->setTargetUrgency($data, $formanswer);
      $data = $this->setTargetCategory($data, $formanswer);
      $data = $this->setTargetLocation($data, $formanswer);

      // There is always at least one requester
      $data = $this->requesters + $data;

      // Overwrite default actors only if populated
      if (count($this->observers['_users_id_observer']) > 0) {
         $data = $this->observers + $data;
      }
      if (count($this->assigned['_users_id_assign']) > 0) {
         $data = $this->assigned + $data;
      }
      if (count($this->assignedSuppliers['_suppliers_id_assign']) > 0) {
         $data = $this->assignedSuppliers + $data;
      }
      if (count($this->requesterGroups['_groups_id_requester']) > 0) {
         $data = $this->requesterGroups + $data;
      }
      if (count($this->observerGroups['_groups_id_observer']) > 0) {
         $data = $this->observerGroups + $data;
      }
      if (count($this->assignedGroups['_groups_id_assign']) > 0) {
         $data = $this->assignedGroups + $data;
      }

      // Create the target ticket
      if (!$ticketID = $ticket->add($data)) {
         return false;
      }

      // Add tag if presents
      $plugin = new Plugin();
      if ($plugin->isActivated('tag')) {

         $tagObj = new PluginTagTagItem();
         $tags   = [];

         // Add question tags
         if (($this->fields['tag_type'] == 'questions'
               || $this->fields['tag_type'] == 'questions_and_specific'
               || $this->fields['tag_type'] == 'questions_or_specific')
            && (!empty($this->fields['tag_questions']))) {

            $query = "SELECT answer
                      FROM `glpi_plugin_formcreator_answers`
                      WHERE `plugin_formcreator_forms_answers_id` = " . $formanswer->fields['id'] . "
                      AND `plugin_formcreator_questions_id` IN (" . $this->fields['tag_questions'] . ")";
            $result = $DB->query($query);
            while ($line = $DB->fetch_array($result)) {
               $tab = json_decode($line['answer']);
               if (is_array($tab)) {
                  $tags = array_merge($tags, $tab);
               }
            }
         }

         // Add specific tags
         if ($this->fields['tag_type'] == 'specifics'
             || $this->fields['tag_type'] == 'questions_and_specific'
             || ($this->fields['tag_type'] == 'questions_or_specific' && empty($tags))
             && (!empty($this->fields['tag_specifics']))) {

            $tags = array_merge($tags, explode(',', $this->fields['tag_specifics']));
         }

         $tags = array_unique($tags);

         // Save tags in DB
         foreach ($tags as $tag) {
            $tagObj->add([
               'plugin_tag_tags_id' => $tag,
               'items_id'           => $ticketID,
               'itemtype'           => Ticket::class,
            ]);
         }
      }

      // Add link between Ticket and FormAnswer
      $itemlink = $this->getItem_Item();
      $itemlink->add([
         'itemtype'   => 'PluginFormcreatorForm_Answer',
         'items_id'   => $formanswer->fields['id'],
         'tickets_id' => $ticketID,
      ]);

      $this->attachDocument($formanswer->getID(), Ticket::class, $ticketID);

      // Attach validation message as first ticket followup if validation is required and
      // if is set in ticket target configuration
      if ($form->fields['validation_required'] && $this->fields['validation_followup']) {
         $message = addslashes(__('Your form has been accepted by the validator', 'formcreator'));
         if (!empty($formanswer->fields['comment'])) {
            $message.= "\n".addslashes($formanswer->fields['comment']);
         }

         // Disable email notification when adding a followup
         $use_mailing = PluginFormcreatorCommon::isNotificationEnabled();
         PluginFormcreatorCommon::setNotification(false);

         $ticketFollowup = new TicketFollowup();
         $ticketFollowup->add([
           'tickets_id'       => $ticketID,
           'date'             => $_SESSION['glpi_currenttime'],
           'users_id'         => $_SESSION['glpiID'],
           'content'          => $message
         ]);

         // Restore mail notification setting
         PluginFormcreatorCommon::setNotification($use_mailing);
      }

      return $ticket;
   }

   protected function setTargetCategory($data, $formanswer) {
      switch ($this->fields['category_rule']) {
         case 'answer':
            $answer  = new PluginFormcreatorAnswer();
            $formAnswerId = $formanswer->fields['id'];
            $categoryQuestion = $this->fields['category_question'];
            $found  = $answer->find("`plugin_formcreator_forms_answers_id` = '$formAnswerId'
                  AND `plugin_formcreator_questions_id` = '$categoryQuestion'");
            $category = array_shift($found);
            $category = $category['answer'];
            break;
         case 'specific':
            $category = $this->fields['category_question'];
            break;
         default:
            $category = null;
      }
      if ($category !== null) {
         $data['itilcategories_id'] = $category;
      }

      return $data;
   }

   protected function setTargetDueDate($data, $formanswer) {
      $answer  = new PluginFormcreatorAnswer();
      if ($this->fields['due_date_question'] !== null) {
         $found  = $answer->find('`plugin_formcreator_forms_answers_id` = '.$formanswer->fields['id'].
               ' AND `plugin_formcreator_questions_id` = '.$this->fields['due_date_question']);
         $date   = array_shift($found);
      } else {
         $date = null;
      }
      $str    = "+" . $this->fields['due_date_value'] . " " . $this->fields['due_date_period'];

      switch ($this->fields['due_date_rule']) {
         case 'answer':
            $due_date = $date['answer'];
            break;
         case 'ticket':
            $due_date = date('Y-m-d H:i:s', strtotime($str));
            break;
         case 'calcul':
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

   protected function setTargetUrgency($data, $formanswer) {
      switch ($this->fields['urgency_rule']) {
         case 'answer':
            $answer  = new PluginFormcreatorAnswer();
            $formAnswerId = $formanswer->fields['id'];
            $urgencyQuestion = $this->fields['urgency_question'];
            $found  = $answer->find("`plugin_formcreator_forms_answers_id` = '$formAnswerId'
                  AND `plugin_formcreator_questions_id` = '$urgencyQuestion'");
            $urgency = array_shift($found);
            $urgency = $urgency['answer'];
            break;
         case 'specific':
            $urgency = $this->fields['urgency_question'];
            break;
         default:
            $urgency = null;
      }
      if (!is_null($urgency)) {
         $data['urgency'] = $urgency;
      }

      return $data;
   }

   protected function setTargetLocation($data, $formanswer) {
      switch ($this->fields['location_rule']) {
         case 'answer':
            $answer  = new PluginFormcreatorAnswer();
            $formAnswerId = $formanswer->fields['id'];
            $locationQuestion = $this->fields['location_question'];
            $found  = $answer->find("`plugin_formcreator_forms_answers_id` = '$formAnswerId'
                  AND `plugin_formcreator_questions_id` = '$locationQuestion'");
            $location = array_shift($found);
            $location = $location['answer'];
            break;
         case 'specific':
            $location = $this->fields['location_question'];
            break;
         default:
            $location = null;
      }
      if (!is_null($location)) {
         $data['locations_id'] = $location;
      }

      return $data;
   }

   private static function getDeleteImage($id) {
      global $CFG_GLPI;

      $link  = ' &nbsp;<a href="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php?delete_actor=' . $id . '">';
      $link .= '<img src="../../../pics/delete.png" alt="' . __('Delete') . '" title="' . __('Delete') . '" />';
      $link .= '</a>';
      return $link;
   }

   /**
    * Import a form's targetticket into the db
    * @see PluginFormcreatorTarget::import
    *
    * @param  integer $targetitems_id  current id
    * @param  array   $target_data the targetticket data (match the targetticket table)
    * @return integer the targetticket's id
    */
   public static function import($targetitems_id = 0, $target_data = []) {
      $item = new self;

      $target_data['_skip_checks'] = true;
      $target_data['id'] = $targetitems_id;

      // convert question uuid into id
      $targetTicket = new PluginFormcreatorTargetTicket();
      $targetTicket->getFromDB($targetitems_id);
      $formId        = $targetTicket->getForm()->getID();
      $section       = new PluginFormcreatorSection();
      $found_section = $section->find("plugin_formcreator_forms_id = '$formId'",
            "`order` ASC");
      $tab_section = [];
      foreach ($found_section as $section_item) {
         $tab_section[] = $section_item['id'];
      }

      if (!empty($tab_section)) {
         $sectionList = "'" . implode(', ', $tab_section) . "'";
         $question = new PluginFormcreatorQuestion();
         $rows = $question->find("`plugin_formcreator_sections_id` IN ($sectionList)", "`order` ASC");
         foreach ($rows as $id => $question_line) {
            $uuid  = $question_line['uuid'];

            $content = $target_data['title'];
            $content = str_replace("##question_$uuid##", "##question_$id##", $content);
            $content = str_replace("##answer_$uuid##", "##answer_$id##", $content);
            $target_data['name'] = $content;

            $content = $target_data['comment'];
            $content = str_replace("##question_$uuid##", "##question_$id##", $content);
            $content = str_replace("##answer_$uuid##", "##answer_$id##", $content);
            $target_data['comment'] = $content;
         }
      }

      // update target ticket
      $item->update($target_data);

      if ($targetitems_id) {
         if (isset($target_data['_actors'])) {
            foreach ($target_data['_actors'] as $actor) {
               PluginFormcreatorTargetTicket_Actor::import($targetitems_id, $actor);
            }
         }
         if (isset($target_data['_ticket_relations'])) {
            foreach ($target_data['_ticket_relations'] as $ticketLink) {
               PluginFormcreatorItem_TargetTicket::import($targetitems_id, $ticketLink);
            }
         }
      }

      return $targetitems_id;
   }

   /**
    * Export in an array all the data of the current instanciated targetticket
    * @return array the array with all data (with sub tables)
    */
   public function export() {
      if (!$this->getID()) {
         return false;
      }

      $target_data = $this->fields;

      // replace dropdown ids
      if ($target_data['tickettemplates_id'] > 0) {
         $target_data['_tickettemplate']
            = Dropdown::getDropdownName('glpi_tickettemplates',
                                        $target_data['tickettemplates_id']);
      }

      // convert questions ID into uuid for ticket description
      $formId        = $this->getForm()->getID();
      $section       = new PluginFormcreatorSection();
      $found_section = $section->find("plugin_formcreator_forms_id = '$formId'",
            "`order` ASC");
      $tab_section = [];
      foreach ($found_section as $section_item) {
         $tab_section[] = $section_item['id'];
      }

      if (!empty($tab_section)) {
         $sectionList = "'" . implode(', ', $tab_section) . "'";
         $question = new PluginFormcreatorQuestion();
         $rows = $question->find("`plugin_formcreator_sections_id` IN ($sectionList)", "`order` ASC");
         foreach ($rows as $id => $question_line) {
            $uuid  = $question_line['uuid'];

            $content = $target_data['name'];
            $content = str_replace("##question_$id##", "##question_$uuid##", $content);
            $content = str_replace("##answer_$id##", "##answer_$uuid##", $content);
            $target_data['name'] = $content;

            $content = $target_data['comment'];
            $content = str_replace("##question_$id##", "##question_$uuid##", $content);
            $content = str_replace("##answer_$id##", "##answer_$uuid##", $content);
            $target_data['comment'] = $content;
         }
      }

      // get data from ticket relations
      $target_data['_ticket_relations'] = [];
      $target_ticketLink = new PluginFormcreatorItem_TargetTicket();
      $targetTicketId = $target_data['id'];
      $all_ticketLinks = $target_ticketLink->find("`plugin_formcreator_targettickets_id` = '$targetTicketId'");
      foreach ($all_ticketLinks as $ticketLink) {
         $target_ticketLink->getFromDB($ticketLink['id']);
         $target_data['_ticket_relations'][] = $target_ticketLink->export();
      }

      // remove key and fk
      unset($target_data['id'],
            $target_data['tickettemplates_id']);

      $target_data['title'] = $target_data['name'];
      unset($target_data['name']);
      return $target_data;
   }
}
