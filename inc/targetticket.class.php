<?php
class PluginFormcreatorTargetTicket extends PluginFormcreatorTargetBase
{

   protected $requesters;

   protected $observers;

   protected $assigned;

   protected $assignedSuppliers;

   protected $requesterGroups;

   protected $observerGroups;

   protected $assignedGroups;

   static function getEnumDestinationEntity() {
      return array(
         'current'   => __("Current active entity", 'formcreator'),
         'requester' => __("Default requester user's entity", 'formcreator'),
         'requester_dynamic_first' => __("First dynamic requester user's entity (alphabetical)", 'formcreator'),
         'requester_dynamic_last' => __("Last dynamic requester user's entity (alphabetical)", 'formcreator'),
         'form'      => __('The form entity', 'formcreator'),
         'validator' => __('Default entity of the validator', 'formcreator'),
         'specific'  => __('Specific entity', 'formcreator'),
         'user'      => __('Default entity of a user type question answer', 'formcreator'),
         'entity'    => __('From a GLPI object > Entity type question answer', 'formcreator'),
      );
   }

   static function getEnumTagType() {
      return array(
         'none'                   => __("None"),
         'questions'              => __('Tags from questions', 'formcreator'),
         'specifics'              => __('Specific tags', 'formcreator'),
         'questions_and_specific' => __('Tags from questions and specific tags', 'formcreator'),
         'questions_or_specific'  => __('Tags from questions or specific tags', 'formcreator')
      );
   }

   static function getEnumDueDateRule() {
      return array(
         'answer' => __('equals to the answer to the question', 'formcreator'),
         'ticket' => __('calculated from the ticket creation date', 'formcreator'),
         'calcul' => __('calculated from the answer to the question', 'formcreator'),
      );
   }

   static function getEnumUrgencyRule() {
      return array(
            'none'      => __('Urgency from template or Medium', 'formcreator'),
            'answer'    => __('Equals to the answer to the question', 'formcreator'),
      );
   }

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate()
   {
      return true;
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView()
   {
      return true;
   }

   public static function getTypeName($nb = 1)
   {
      return _n('Target ticket', 'Target tickets', $nb, 'formcreator');
   }


   /**
    * Show the Form edit form the the adminsitrator in the config page
    *
    * @param  Array  $options Optional options
    *
    * @return NULL         Nothing, just display the form
    */
   public function showForm($options=array())
   {
      global $CFG_GLPI, $DB;

      $rand = mt_rand();

      $obj = new PluginFormcreatorTarget();
      $found = $obj->find("itemtype = '" . __CLASS__ . "' AND items_id = " . $this->getID());
      $target = array_shift($found);

      $form = new PluginFormcreatorForm();
      $form->getFromDB($target['plugin_formcreator_forms_id']);

      echo '<div class="center" style="width: 950px; margin: 0 auto;">';
      echo '<form name="form_target" method="post" action="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      // General information : name
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="2">' . __('Edit a destination', 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td width="15%"><strong>' . __('Name') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td width="85%"><input type="text" name="name" style="width:704px;" value="' . $target['name'] . '"></textarea</td>';
      echo '</tr>';

      echo '</table>';

      // Ticket information : title, template...
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
      if ($CFG_GLPI["use_rich_text"]) {
         Html::initEditorSystem('comment');
      }
      echo '</td>';
      echo '</tr>';

      // Ticket Template
      echo '<tr class="line1">';
      echo '<td width="15%">' . _n('Ticket template', 'Ticket templates', 1) . '</td>';
      echo '<td width="25%">';
      Dropdown::show('TicketTemplate', array(
         'name'  => 'tickettemplates_id',
         'value' => $this->fields['tickettemplates_id']
      ));
      echo '</td>';
      echo '<td width="15%">' . __('Due date') . '</td>';
      echo '<td width="45%">';

      // -------------------------------------------------------------------------------------------
      // Due date type selection
      // -------------------------------------------------------------------------------------------
      Dropdown::showFromArray('due_date_rule', self::getEnumDueDateRule(),
         array(
            'value'     => $this->fields['due_date_rule'],
            'on_change' => 'formcreatorChangeDueDate(this.value)',
            'display_emptychoice' => true
         )
      );

      // for each section ...
      $questions_list = array(Dropdown::EMPTY_VALUE);
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
         $section_questions = array();
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
      Dropdown::showFromArray('due_date_question', $questions_list, array(
         'value' => $this->fields['due_date_question']
      ));
      echo '</div>';

      if ($this->fields['due_date_rule'] != 'ticket'
          && $this->fields['due_date_rule'] != 'calcul') {
         echo '<div id="due_date_time" style="display:none">';
      } else {
         echo '<div id="due_date_time">';
      }
      Dropdown::showNumber("due_date_value", array(
         'value' => $this->fields['due_date_value'],
         'min'   => -30,
         'max'   => 30
      ));
      Dropdown::showFromArray('due_date_period', array(
         'minute' => _n('Minute', 'Minutes', 2),
         'hour'   => _n('Hour', 'Hours', 2),
         'day'    => _n('Day', 'Days', 2),
         'month'  => __('Month'),
      ), array(
         'value' => $this->fields['due_date_period']
      ));
      echo '</div>';
      echo '</td>';
      echo '</tr>';

      // -------------------------------------------------------------------------------------------
      // Urgency selection
      // -------------------------------------------------------------------------------------------
      echo '<tr class="line0">';
      echo '<td width="15%">' . __('Urgency') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('urgency_rule', self::getEnumUrgencyRule(), array(
            'value'                 => $this->fields['urgency_rule'],
            'on_change'             => 'change_urgency()',
            'rand'                  => $rand
      ));
      $script = <<<EOS
         function change_urgency() {
            $('#urgency_specific_question').hide();
            $('#urgency_specific_value').hide();

            switch($('#dropdown_urgency_rule$rand').val()) {
               case 'answer' :
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
      echo '<span id="urgency_specific_title" style="display: none">' . __('Question', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';

      echo '<div id="urgency_specific_value" style="display: none">';
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
      $users_questions = array();
      while ($question = $DB->fetch_array($result2)) {
         $users_questions[$question['id']] = $question['name'];
      }
      Dropdown::showFromArray('_urgency_question', $users_questions, array(
            'value' => $this->fields['urgency_question'],
      ));
      echo '</div>';
      echo '</td>';
      echo '</tr>';

      // -------------------------------------------------------------------------------------------
      // Ticket Entity
      // -------------------------------------------------------------------------------------------
      echo '<tr class="line1">';
      echo '<td width="15%">' . __('Destination entity') . '</td>';
      echo '<td width="25%">';
      $rand = mt_rand();
      Dropdown::showFromArray(
         'destination_entity',
         self::getEnumDestinationEntity(),
         array(
            'value'     => $this->fields['destination_entity'],
            'on_change' => 'change_entity()',
            'rand'      => $rand,
         )
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
      Entity::dropdown(array(
         'name' => '_destination_entity_value_specific',
         'value' => $this->fields['destination_entity_value'],
      ));
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
      $users_questions = array();
      while ($question = $DB->fetch_array($result2)) {
         $users_questions[$question['id']] = $question['name'];
      }
      Dropdown::showFromArray('_destination_entity_value_user', $users_questions, array(
         'value' => $this->fields['destination_entity_value'],
      ));
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
      $entities_questions = array();
      while ($question = $DB->fetch_array($result2)) {
         $entities_questions[$question['id']] = $question['name'];
      }
      Dropdown::showFromArray('_destination_entity_value_entity', $entities_questions, array(
         'value' => $this->fields['destination_entity_value'],
      ));
      echo '</div>';

      echo '</td>';

      echo '</tr>';


      // -------------------------------------------------------------------------------------------
      //  Tags
      // -------------------------------------------------------------------------------------------
      $plugin = new Plugin();
      if ($plugin->isInstalled('tag') && $plugin->isActivated('tag')) {
         echo '<tr class="line1">';
         echo '<td width="15%">' . __('Ticket tags', 'formcreator') . '</td>';
         echo '<td width="25%">';
         $rand = mt_rand();
         Dropdown::showFromArray('tag_type', self::getEnumTagType(),
            array(
               'value'     => $this->fields['tag_type'],
               'on_change' => 'change_tag_type()',
               'rand'      => $rand,
            )
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
         $entities_questions = array();
         while ($question = $DB->fetch_array($result2)) {
            $entities_questions[$question['id']] = $question['name'];
         }
         Dropdown::showFromArray('_tag_questions', $entities_questions, array(
            'values'   => explode(',', $this->fields['tag_questions']),
            'multiple' => true,
         ));
         echo '</div>';

         // Spécific tags
         echo '<div id="tag_specific_value" style="display: none">';

         $obj = new PluginTagTag();
         $obj->getEmpty();

         $where = "(`type_menu` LIKE '%\"Ticket\"%' OR `type_menu` LIKE '0')";
         $where .= getEntitiesRestrictRequest('AND', getTableForItemType('PluginTagTag'));

         $result = $obj->find($where);
         $values = array();
         foreach ($result AS $id => $datas) {
            $values[$id] = $datas['name'];
         }

         Dropdown::showFromArray('_tag_specifics', $values, array(
            'values'   => explode(',', $this->fields['tag_specifics']),
            'comments' => false,
            'rand'     => $rand,
            'multiple' => true,
         ));
         echo '</div>';

         echo '</td>';
         echo '</tr>';
      }


      // -------------------------------------------------------------------------------------------
      //  Validation as ticket followup
      // -------------------------------------------------------------------------------------------
      if ($form->fields['validation_required']) {
         echo '<tr class="line0">';
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
      $questions_user_list     = array(Dropdown::EMPTY_VALUE);
      $questions_group_list    = array(Dropdown::EMPTY_VALUE);
      $questions_supplier_list = array(Dropdown::EMPTY_VALUE);
      $questions_actors_list   = array(Dropdown::EMPTY_VALUE);
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
                   AND (q.fieldtype = 'glpiselect'
                     AND q.values IN ('User', 'Group', 'Supplier'))
                   OR (q.fieldtype = 'actor')";
         $result2 = $DB->query($query2);
         $section_questions_user       = array();
         $section_questions_group      = array();
         $section_questions_supplier   = array();
         $section_questions_actors     = array();
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
      $actors = array('requester' => array(), 'observer' => array(), 'assigned' => array());
      $query = "SELECT id, actor_role, actor_type, actor_value, use_notification
                FROM glpi_plugin_formcreator_targettickets_actors
                WHERE plugin_formcreator_targettickets_id = " . $this->getID();
      $result = $DB->query($query);
      while ($actor = $DB->fetch_array($result)) {
         $actors[$actor['actor_role']][$actor['id']] = array(
            'actor_type'       => $actor['actor_type'],
            'actor_value'      => $actor['actor_value'],
            'use_notification' => $actor['use_notification'],
         );
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
      echo '<form name="form_target" id="form_add_requester" method="post" style="display:none" action="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      $dropdownItems = array('' => Dropdown::EMPTY_VALUE) + PluginFormcreatorTargetTicket_Actor::getEnumActorType();
      Dropdown::showFromArray('actor_type',
         $dropdownItems, array(
         'on_change'         => 'formcreatorChangeActorRequester(this.value)'
      ));

      echo '<div id="block_requester_user" style="display:none">';
      User::dropdown(array(
         'name' => 'actor_value_person',
         'right' => 'all',
         'all'   => 0,
      ));
      echo '</div>';

      echo '<div id="block_requester_group" style="display:none">';
      Group::dropdown(array(
         'name' => 'actor_value_group',
      ));
      echo '</div>';

      echo '<div id="block_requester_question_user" style="display:none">';
      Dropdown::showFromArray('actor_value_question_person', $questions_user_list, array(
         'value' => $this->fields['due_date_question'],
      ));
      echo '</div>';

      echo '<div id="block_requester_question_group" style="display:none">';
      Dropdown::showFromArray('actor_value_question_group', $questions_group_list, array(
         'value' => $this->fields['due_date_question'],
      ));
      echo '</div>';

      echo '<div id="block_requester_question_actors" style="display:none">';
      Dropdown::showFromArray('actor_value_question_actors', $questions_actors_list, array(
            'value' => $this->fields['due_date_question'],
      ));
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
      echo '<form name="form_target" id="form_add_watcher" method="post" style="display:none" action="'.
           $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      $dropdownItems = array(''  => Dropdown::EMPTY_VALUE) + PluginFormcreatorTargetTicket_Actor::getEnumActorType();
      unset($dropdownItems['supplier']);
      unset($dropdownItems['question_supplier']);
      Dropdown::showFromArray('actor_type',
         $dropdownItems, array(
         'on_change'         => 'formcreatorChangeActorWatcher(this.value)'
      ));

      echo '<div id="block_watcher_user" style="display:none">';
      User::dropdown(array(
         'name' => 'actor_value_person',
         'right' => 'all',
         'all'   => 0,
      ));
      echo '</div>';

      echo '<div id="block_watcher_group" style="display:none">';
      Group::dropdown(array(
         'name' => 'actor_value_group',
      ));
      echo '</div>';

      echo '<div id="block_watcher_question_user" style="display:none">';
      Dropdown::showFromArray('actor_value_question_person', $questions_user_list, array(
         'value' => $this->fields['due_date_question'],
      ));
      echo '</div>';

      echo '<div id="block_watcher_question_group" style="display:none">';
      Dropdown::showFromArray('actor_value_question_group', $questions_group_list, array(
         'value' => $this->fields['due_date_question'],
      ));
      echo '</div>';

      echo '<div id="block_watcher_question_actors" style="display:none">';
      Dropdown::showFromArray('actor_value_question_actors', $questions_actors_list, array(
            'value' => $this->fields['due_date_question'],
      ));
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
      echo '<form name="form_target" id="form_add_assigned" method="post" style="display:none" action="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      $dropdownItems = array(''  => Dropdown::EMPTY_VALUE) + PluginFormcreatorTargetTicket_Actor::getEnumActorType();
      unset($dropdownItems['supplier']);
      unset($dropdownItems['question_supplier']);
      Dropdown::showFromArray('actor_type',
         $dropdownItems, array(
         'on_change'         => 'formcreatorChangeActorAssigned(this.value)'
      ));

      echo '<div id="block_assigned_user" style="display:none">';
      User::dropdown(array(
         'name' => 'actor_value_person',
         'right' => 'all',
         'all'   => 0,
      ));
      echo '</div>';

      echo '<div id="block_assigned_group" style="display:none">';
      Group::dropdown(array(
         'name' => 'actor_value_group',
      ));
      echo '</div>';

      echo '<div id="block_assigned_supplier" style="display:none">';
      Supplier::dropdown(array(
         'name' => 'actor_value_supplier',
      ));
      echo '</div>';

      echo '<div id="block_assigned_question_user" style="display:none">';
      Dropdown::showFromArray('actor_value_question_person', $questions_user_list, array(
         'value' => $this->fields['due_date_question'],
      ));
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
    * Prepare input datas for updating the target ticket
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForUpdate($input)
   {
      global $CFG_GLPI;

      // Control fields values :
      if (!isset($input['_skip_checks'])
          || !$input['_skip_checks']) {
         // - name is required
         if(empty($input['title'])) {
            Session::addMessageAfterRedirect(__('The title cannot be empty!', 'formcreator'), false, ERROR);
            return array();
         }

         // - comment is required
         if(empty($input['comment'])) {
            Session::addMessageAfterRedirect(__('The description cannot be empty!', 'formcreator'), false, ERROR);
            return array();
         }

         $input['name'] = plugin_formcreator_encode($input['title']);

         if ($CFG_GLPI['use_rich_text']) {
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
            default:
               $input['urgency_question'] = '0';
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

      return $input;
   }

   public function pre_deleteItem() {
      global $DB;

      $targetTicketId = $this->getID();
      $query = "DELETE FROM `glpi_plugin_formcreator_targettickets_actors`
         WHERE `plugin_formcreator_targettickets_id` = '$targetTicketId'";
      return $DB->query($query);
   }

   /**
    * Save form datas to the target
    *
    * @param  PluginFormcreatorForm_Answer $formanswer    Answers previously saved
    */
   public function save(PluginFormcreatorForm_Answer $formanswer) {
      global $DB;

      // Prepare actors structures for creation of the ticket
      $this->requesters = array(
            '_users_id_requester'         => array(),
            '_users_id_requester_notif'   => array(
                  'use_notification'      => array(),
                  'alternative_email'     => array(),
            ),
      );
      $this->observers = array(
            '_users_id_observer'          => array(),
            '_users_id_observer_notif'    => array(
                  'use_notification'      => array(),
                  'alternative_email'     => array(),
            ),
      );
      $this->assigned = array(
            '_users_id_assign'            => array(),
            '_users_id_assign_notif'      => array(
                  'use_notification'      => array(),
                  'alternative_email'     => array(),
            ),
      );

      $this->assignedSuppliers = array(
            '_suppliers_id_assign'        => array(),
            '_suppliers_id_assign_notif'  => array(
                  'use_notification'      => array(),
                  'alternative_email'     => array(),
            )
      );

      $this->requesterGroups = array(
            '_groups_id_requester'        => array(),
      );

      $this->observerGroups = array(
            '_groups_id_observer'         => array(),
      );

      $this->assignedGroups = array(
            '_groups_id_assign'           => array(),
      );

      $datas   = array();
      $ticket  = new Ticket();
      $docItem = new Document_Item();
      $form    = new PluginFormcreatorForm();
      $answer  = new PluginFormcreatorAnswer();

      $form->getFromDB($formanswer->fields['plugin_formcreator_forms_id']);

      // Get default request type
      $query   = "SELECT id FROM `glpi_requesttypes` WHERE `name` LIKE 'Formcreator';";
      $result  = $DB->query($query) or die ($DB->error());
      list($requesttypes_id) = $DB->fetch_array($result);

      $datas['requesttypes_id'] = $requesttypes_id;

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
         $this->addGroupActor('assigned', $predefined_fields['_groups_id_observer']);
         unset($predefined_fields['_groups_id_observer']);
      }
      if (isset($predefined_fields['_groups_id_assign'])) {
         $this->addGroupActor('assigned', $predefined_fields['_groups_id_assign']);
         unset($predefined_fields['_groups_id_assign']);
      }

      $datas                = array_merge($datas, $predefined_fields);

      // Parse datas
      $fullform = $formanswer->getFullForm();
      $datas['name']                  = addslashes($this->parseTags($this->fields['name'],
                                                                    $formanswer,
                                                                    $fullform));
      $datas['content']               = htmlentities(addslashes($this->parseTags($this->fields['comment'],
                                                                      $formanswer,
                                                                      $fullform)));

      $datas['_users_id_recipient']   = $_SESSION['glpiID'];
      $datas['_tickettemplates_id']   = $this->fields['tickettemplates_id'];

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
      }

      // Computation of the entity
      switch ($this->fields['destination_entity']) {
         // Requester's entity
         case 'current' :
            $datas['entities_id'] = $_SESSION['glpiactive_entity'];
            break;
         case 'requester' :
            $userObj = new User();
            $userObj->getFromDB($requesters_id);
            $datas['entities_id'] = $userObj->fields['entities_id'];
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
            while($data_entities[] = $DB->fetch_array($res_entities)) {

            }
            $first_entity = array_shift($data_entities);
            $datas['entities_id'] = $first_entity['entities_id'];
            break;

         // Specific entity
         case 'specific' :
            $datas['entities_id'] = $this->fields['destination_entity_value'];
            break;

         // The form entity
         case 'form' :
            $datas['entities_id'] = $form->fields['entities_id'];
            break;

         // The validator entity
         case 'validator' :
            $userObj = new User();
            $userObj->getFromDB($formanswer->fields['validator_id']);
            $datas['entities_id'] = $userObj->fields['entities_id'];
            break;

         // Default entity of a user from the answer of a user's type question
         case 'user' :
            $found   = $answer->find('plugin_formcreator_forms_answers_id = '.$formanswer->fields['id'].
                                     ' AND plugin_formcreator_question_id = '.$this->fields['destination_entity_value']);
            $user    = array_shift($found);
            $user_id = $user['answer'];

            if ($user_id > 0) {
               $userObj = new User();
               $userObj->getFromDB($user_id);
               $datas['entities_id'] = $userObj->fields['entities_id'];
            } else {
               $datas['entities_id'] = 0;
            }
            break;

         // Entity from the answer of an entity's type question
         case 'entity' :
            $found  = $answer->find('plugin_formcreator_forms_answers_id = '.$formanswer->fields['id'].
                                    ' AND plugin_formcreator_question_id = '.$this->fields['destination_entity_value']);
            $entity = array_shift($found);

            $datas['entities_id'] = $entity['answer'];
            break;

         // Requester current entity
         default :
            $datas['entities_id'] = 0;
            break;
      }

      // Define due date
      if ($this->fields['due_date_question'] !== null) {
         $found  = $answer->find('`plugin_formcreator_forms_answers_id` = '.$formanswer->fields['id'].
                                 ' AND `plugin_formcreator_question_id` = '.$this->fields['due_date_question']);
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
         $datas['due_date'] = $due_date;
      }

      // Define urgency
      $formAnswerId = $formanswer->fields['id'];
      $urgencyQuestion = $this->fields['urgency_question'];
      $found  = $answer->find("`plugin_formcreator_forms_answers_id` = '$formAnswerId'
                                AND `plugin_formcreator_question_id` = '$urgencyQuestion'");
      $urgency = array_shift($found);
      switch ($this->fields['urgency_rule']) {
         case 'answer':
            $urgency = $urgency['answer'];
            break;
         default:
            $urgency = null;
      }
      if (!is_null($urgency)) {
         $datas['urgency'] = $urgency;
      }

      if (version_compare(GLPI_VERSION, '9.1.2', 'lt')) {
         $datas['_users_id_requester'] = $requesters_id;
         // Remove first requester
         array_shift($this->requesters['_users_id_requester']);
         array_shift($this->requesters['_users_id_requester_notif']['use_notification']);
         array_shift($this->requesters['_users_id_requester_notif']['alternative_email']);
         $this->requesters = array(
               '_users_id_requester'         => array(),
               '_users_id_requester_notif'   => array(
                     'use_notification'      => array(),
                     'alternative_email'     => array(),
               ),
         );

      } else {
         $datas = $this->requesters + $this->observers + $this->assigned + $this->assignedSuppliers + $datas;
         $datas = $this->requesterGroups + $this->observerGroups + $this->assignedGroups + $datas;
      }

      // Create the target ticket
      if (!$ticketID = $ticket->add($datas)) {
         return false;
      }

      if (version_compare(GLPI_VERSION, '9.1.2', 'lt')) {
         // update ticket with actors

         // user actors
         foreach ($this->requesters['_users_id_requester'] as $index => $userId) {
            $ticket_user = new Ticket_User();
            $ticket_user->add(array(
                  'tickets_id'       => $ticketID,
                  'users_id'         => $userId,
                  'type'             => CommonITILActor::REQUESTER,
                  'use_notification' => $this->requesters['_users_id_requester_notif']['use_notification'][$index],
            ));
         }
         foreach ($this->observers['_users_id_observer'] as $index => $userId) {
            $ticket_user = new Ticket_User();
            $ticket_user->add(array(
                  'tickets_id'       => $ticketID,
                  'users_id'         => $userId,
                  'type'             => CommonITILActor::OBSERVER,
                  'use_notification' => $this->observers['_users_id_observer_notif']['use_notification'][$index],
            ));
         }
         foreach ($this->assigned['_users_id_assign'] as $index => $userId) {
            $ticket_user = new Ticket_User();
            $ticket_user->add(array(
                  'tickets_id'       => $ticketID,
                  'users_id'         => $userId,
                  'type'             => CommonITILActor::ASSIGN,
                  'use_notification' => $this->assigned['_users_id_assign_notif']['use_notification'][$index],
            ));
         }
         foreach ($this->assignedSuppliers['_suppliers_id_assign'] as $index => $userId) {
            $supplier_ticket = new Supplier_Ticket();
            $supplier_ticket->add(array(
                  'tickets_id'       => $ticketID,
                  'users_id'         => $userId,
                  'type'             => CommonITILActor::ASSIGN,
                  'use_notification' => $this->assigned['_suppliers_id_assign']['use_notification'][$index],
            ));
         }

         foreach ($this->requesterGroups['_groups_id_requester'] as $index => $groupId) {
            $group_ticket = new Group_Ticket();
            $group_ticket->add(array(
                  'tickets_id'       => $ticketID,
                  'groups_id'        => $groupId,
                  'type'             => CommonITILActor::REQUESTER,
            ));
         }
         foreach ($this->observerGroups['_groups_id_observer'] as $index => $groupId) {
            $group_ticket = new Group_Ticket();
            $group_ticket->add(array(
                  'tickets_id'       => $ticketID,
                  'groups_id'        => $groupId,
                  'type'             => CommonITILActor::OBSERVER,
            ));
         }
         foreach ($this->assignedGroups['_groups_id_assign'] as $index => $groupId) {
            $group_ticket = new Group_Ticket();
            $group_ticket->add(array(
                  'tickets_id'       => $ticketID,
                  'groups_id'        => $groupId,
                  'type'             => CommonITILActor::ASSIGN,
            ));
         }
      }

      // Add tag if presents
      $plugin = new Plugin();
      if ($plugin->isInstalled('tag') && $plugin->isActivated('tag')) {

         $tagObj = new PluginTagTagItem();
         $tags   = array();

         // Add question tags
         if (($this->fields['tag_type'] == 'questions'
               || $this->fields['tag_type'] == 'questions_and_specific'
               || $this->fields['tag_type'] == 'questions_or_specific')
            && (!empty($this->fields['tag_questions']))) {

            $query = "SELECT answer
                      FROM `glpi_plugin_formcreator_answers`
                      WHERE `plugin_formcreator_forms_answers_id` = " . $formanswer->fields['id'] . "
                      AND `plugin_formcreator_question_id` IN (" . $this->fields['tag_questions'] . ")";
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
            $tagObj->add(array(
               'plugin_tag_tags_id' => $tag,
               'items_id'           => $ticketID,
               'itemtype'           => 'Ticket',
            ));
         }
      }

      // Add link between Ticket and FormAnswer
      $itemlink = new Item_Ticket();
      $itemlink->add(array(
         'itemtype'   => 'PluginFormcreatorForm_Answer',
         'items_id'   => $formanswer->fields['id'],
         'tickets_id' => $ticketID,
      ));

      // Attach documents to ticket
      $found = $docItem->find("itemtype = 'PluginFormcreatorForm_Answer'
                               AND items_id = ".$formanswer->getID());
      if(count($found) > 0) {
         foreach ($found as $document) {
            $docItem->add(array(
               'documents_id' => $document['documents_id'],
               'itemtype'     => 'Ticket',
               'items_id'     => $ticketID
            ));
         }
      }

      // Attach validation message as first ticket followup if validation is required and
      // if is set in ticket target configuration
      if ($form->fields['validation_required'] && $this->fields['validation_followup']) {
         $message = addslashes(__('Your form has been accepted by the validator', 'formcreator'));
         if (!empty($formanswer->fields['comment'])) {
            $message.= "\n".addslashes($formanswer->fields['comment']);
         }

         // Disable email notification when adding a followup
         $use_mailing = $CFG_GLPI['use_mailing'];
         $CFG_GLPI['use_mailing'] = 0;

         $ticketFollowup = new TicketFollowup();
         $ticketFollowup->add(array(
              'tickets_id'       => $ticketID,
              'date'             => $_SESSION['glpi_currenttime'],
              'users_id'         => $_SESSION['glpiID'],
              'content'          => $message
         ));

         $CFG_GLPI['use_mailing'] = $use_mailing;
      }

      return true;
   }

   /**
    * Parse target content to replace TAGS like ##FULLFORM## by the values
    *
    * @param  String $content                            String to be parsed
    * @param  PluginFormcreatorForm_Answer $formanswer    Formanswer object where answers are stored
    * @return String                                     Parsed string with tags replaced by form values
    */
   private function parseTags($content, PluginFormcreatorForm_Answer $formanswer, $fullform = "") {
      global $DB, $CFG_GLPI;

      if ($fullform == "") {
         $fullform = $formanswer->getFullForm();
      }

      $content     = str_replace('##FULLFORM##', $fullform, $content);
      $section     = new PluginFormcreatorSection();
      $found       = $section->find('plugin_formcreator_forms_id = '.$formanswer->fields['plugin_formcreator_forms_id'],
                                    '`order` ASC');
      $tab_section = array();
      foreach($found as $section_item) {
         $tab_section[] = $section_item['id'];
      }

      if(!empty($tab_section)) {
         $query_questions = "SELECT `questions`.*, `answers`.`answer`
                             FROM `glpi_plugin_formcreator_questions` AS questions
                             LEFT JOIN `glpi_plugin_formcreator_answers` AS answers
                               ON `answers`.`plugin_formcreator_question_id` = `questions`.`id`
                               AND `plugin_formcreator_forms_answers_id` = ".$formanswer->getID()."
                             WHERE `questions`.`plugin_formcreator_sections_id` IN (".implode(', ', $tab_section).")
                             ORDER BY `questions`.`order` ASC";
         $res_questions = $DB->query($query_questions);
         while ($question_line = $DB->fetch_assoc($res_questions)) {
            $id    = $question_line['id'];
            $name  = $question_line['name'];
            $value = PluginFormcreatorFields::getValue($question_line, $question_line['answer']);
            if (is_array($value)) {
               if ($CFG_GLPI['use_rich_text']) {
                  $value = '<br />' . implode('<br />', $value);
               } else {
                  $value = "\r\n" . implode("\r\n", $value);
               }
            }

            $content = str_replace('##question_' . $id . '##', $name, $content);
            $content = str_replace('##answer_' . $id . '##', $value, $content);
         }
      }

      return $content;
   }

   public static function install(Migration $migration)
   {
      global $DB;

      $enum_destination_entity = "'".implode("', '", array_keys(self::getEnumDestinationEntity()))."'";
      $enum_tag_type           = "'".implode("', '", array_keys(self::getEnumTagType()))."'";
      $enum_due_date_rule      = "'".implode("', '", array_keys(self::getEnumDueDateRule()))."'";
      $enum_urgency_rule       = "'".implode("', '", array_keys(self::getEnumUrgencyRule()))."'";

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `name` varchar(255) NOT NULL DEFAULT '',
                     `tickettemplates_id` int(11) NULL DEFAULT NULL,
                     `comment` text collate utf8_unicode_ci,
                     `due_date_rule` ENUM($enum_due_date_rule) NULL DEFAULT NULL,
                     `due_date_question` INT NULL DEFAULT NULL,
                     `due_date_value` TINYINT NULL DEFAULT NULL,
                     `due_date_period` ENUM('minute', 'hour', 'day', 'month') NULL DEFAULT NULL,
                     `urgency_rule` ENUM($enum_urgency_rule) NOT NULL DEFAULT 'none',
                     `urgency_question` int(11) NOT NULL DEFAULT '0',
                     `validation_followup` BOOLEAN NOT NULL DEFAULT TRUE,
                     `destination_entity` ENUM($enum_destination_entity) NOT NULL DEFAULT 'requester',
                     `destination_entity_value` int(11) NULL DEFAULT NULL,
                     `tag_type` ENUM($enum_tag_type) NOT NULL DEFAULT 'none',
                     `tag_questions` VARCHAR(255) NOT NULL,
                     `tag_specifics` VARCHAR(255) NOT NULL
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      } else {
         if(!FieldExists($table, 'due_date_rule', false)) {
            $query = "ALTER TABLE `$table`
                        ADD `due_date_rule` ENUM($enum_due_date_rule) NULL DEFAULT NULL,
                        ADD `due_date_question` INT NULL DEFAULT NULL,
                        ADD `due_date_value` TINYINT NULL DEFAULT NULL,
                        ADD `due_date_period` ENUM('minute', 'hour', 'day', 'month') NULL DEFAULT NULL,
                        ADD `validation_followup` BOOLEAN NOT NULL DEFAULT TRUE;";
            $DB->query($query) or die($DB->error());
         }

         // Migration to Formcreator 0.90-1.4
         if(!FieldExists($table, 'destination_entity', false)) {
            $query = "ALTER TABLE `$table`
                        ADD `destination_entity` ENUM($enum_destination_entity) NOT NULL DEFAULT 'requester',
                        ADD `destination_entity_value` int(11) NULL DEFAULT NULL;";
            $DB->query($query) or die($DB->error());
         } else {
            $current_enum_destination_entity = PluginFormcreatorCommon::getEnumValues($table, 'destination_entity');
            if (count($current_enum_destination_entity) != count(self::getEnumDestinationEntity())) {
               $query = "ALTER TABLE `$table`
                           CHANGE COLUMN `destination_entity` `destination_entity`
                           ENUM($enum_destination_entity)
                           NOT NULL DEFAULT 'requester'";
               $DB->query($query) or die($DB->error());
            }
         }

         if(!FieldExists($table, 'tag_type', false)) {
            $query = "ALTER TABLE `$table`
                         ADD `tag_type` ENUM($enum_tag_type) NOT NULL DEFAULT 'none',
                         ADD `tag_questions` VARCHAR(255) NOT NULL,
                         ADD `tag_specifics` VARCHAR(255) NOT NULL;";
            $DB->query($query) or die($DB->error());
         }

         if (!FieldExists($table, 'urgency_rule', false)) {
            $query = "ALTER TABLE `$table`
            ADD `urgency_rule` ENUM($enum_urgency_rule) NOT NULL DEFAULT 'none' AFTER `due_date_period`;";
            $DB->query($query) or die($DB->error());

         } else {
            $current_enum_destination_entity = PluginFormcreatorCommon::getEnumValues($table, 'urgency_rule');
            if (count($current_enum_destination_entity) != count(self::getEnumUrgencyRule())) {
               $query = "ALTER TABLE `$table`
               CHANGE COLUMN `urgency_rule` `urgency_rule`
               ENUM($enum_urgency_rule)
               NOT NULL DEFAULT 'none'";
               $DB->query($query) or die($DB->error());
            }
         }
         $migration->addField($table, 'urgency_question', 'integer', array('after' => 'urgency_rule'));
      }

      return true;
   }

   public static function uninstall()
   {
      global $DB;

      $query = "DROP TABLE IF EXISTS `" . getTableForItemType(__CLASS__) . "`";
      return $DB->query($query) or die($DB->error());
   }

   /**
    * find all actors and prepare data for the ticket being created
    */
   protected function prepareActors(PluginFormcreatorForm $form, PluginFormcreatorForm_Answer $formanswer) {
      $targetTicketId = $this->getID();
      $targetTicketActor = new PluginFormcreatorTargetTicket_Actor();
      $rows = $targetTicketActor->find("`plugin_formcreator_targettickets_id` = '$targetTicketId'");

      foreach ($rows as $actor) {
         // If actor type is validator and if the form doesn't have a validator, continue to other actors
         if ($actor['actor_type'] == 'validator' && !$form->fields['validation_required']) continue;

         switch ($actor['actor_type']) {
            case 'creator' :
               $userIds = array($formanswer->fields['requester_id']);
               $notify  = $actor['use_notification'];
               break;
            case 'validator' :
               $userIds = array($_SESSION['glpiID']);
               $notify  = $actor['use_notification'];
               break;
            case 'person' :
            case 'group' :
            case 'supplier' :
               $userIds = array($actor['actor_value']);
               $notify  = $actor['use_notification'];
               break;
            case 'question_person' :
            case 'question_group' :
            case 'question_supplier' :
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByQuery("WHERE `plugin_formcreator_question_id` = '$actorValue'
                                          AND `plugin_formcreator_forms_answers_id` = '$formanswerId'");

               if ($answer->isNewItem()) {
                  continue;
               } else {
                  $userIds = array($answer->getField('answer'));
               }
               $notify  = $actor['use_notification'];
               break;
            case 'question_actors':
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByQuery("WHERE `plugin_formcreator_question_id` = '$actorValue'
                                          AND `plugin_formcreator_forms_answers_id` = '$formanswerId'");

               if ($answer->isNewItem()) {
                  continue;
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
               // TODO :
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
      }

      switch ($role) {
         case 'requester':
            $this->requesters['_users_id_requester'][]                              = $userId;
            $this->requesters['_users_id_requester_notif']['use_notification'][]    = ($notify == true);
            $this->requesters['_users_id_requester_notif']['alternative_email'][]   = $alternativeEmail;
            break;
         case 'observer' :
            $this->observers['_users_id_observer'][]                                = $userId;
            $this->observers['_users_id_observer_notif']['use_notification'][]      = ($notify == true);
            $this->observers['_users_id_observer_notif']['alternative_email'][]     = $alternativeEmail;
            break;
         case 'assigned' :
            $this->assigned['_users_id_assign'][]                                   = $userId;
            $this->assigned['_users_id_assign_notif']['use_notification'][]         = ($notify == true);
            $this->assigned['_users_id_assign_notif']['alternative_email'][]        = $alternativeEmail;
            break;
         case 'supplier' :
            $this->assignedSuppliers['_suppliers_id_assign'][]                      = $userId;
            $this->assignedSuppliers['_suppliers_id_assign']['use_notification'][]  = ($notify == true);
            $this->assignedSuppliers['_suppliers_id_assign']['alternative_email'][] = $alternativeEmail;
            break;
      }
   }

   protected function addGroupActor($role, $group) {
      switch ($role) {
         case 'requester':
            $this->requesterGroupss['_groups_id_requester'][]  = $group;
            break;
         case 'observer' :
            $this->observerGroups['_groups_id_observer'][]     = $group;
            break;
         case 'assigned' :
            $this->assignedGroups['_groups_id_assign'][]       = $group;
            break;
      }
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
    * @param  integer $targettickets_id  curent id
    * @param  array   $target_data the targetticket data (match the targetticket table)
    * @return integer the targetticket's id
    */
   public static function import($targetitems_id = 0, $target_data = array()) {
      global $DB;

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
      $tab_section = array();
      foreach($found_section as $section_item) {
         $tab_section[] = $section_item['id'];
      }

      if(!empty($tab_section)) {
         $sectionList = "'" . implode(', ', $tab_section) . "'";
         $question = new PluginFormcreatorQuestion();
         $rows = $question->find("`plugin_formcreator_sections_id` IN ($sectionList)", "`order` ASC");
         foreach ($rows as $id => $question_line) {
            $uuid  = $question_line['uuid'];

            $content = $target_data['name'];
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

      if ($targetitems_id
          && isset($target_data['_actors'])) {
         foreach($target_data['_actors'] as $actor) {
            PluginFormcreatorTargetTicket_Actor::import($targetitems_id, $actor);
         }
      }

      return $targetitems_id;
   }

   /**
    * Export in an array all the data of the current instanciated targetticket
    * @return array the array with all data (with sub tables)
    */
   public function export() {
      global $DB;

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
      $tab_section = array();
      foreach($found_section as $section_item) {
         $tab_section[] = $section_item['id'];
      }

      if(!empty($tab_section)) {
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

      // remove key and fk
      unset($target_data['id'],
            $target_data['tickettemplates_id']);

      return $target_data;
   }
}
