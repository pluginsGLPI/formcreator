<?php
class PluginFormcreatorTargetTicket extends CommonDBTM
{
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
      $rand = mt_rand();

      $obj = new PluginFormcreatorTarget();
      $found = $obj->find('itemtype = "' . __CLASS__ . '" AND items_id = ' . $this->getID());
      $target = array_shift($found);

      $form = new PluginFormcreatorForm();
      $form->getFromDB($target['plugin_formcreator_forms_id']);

      echo '<form name="form_target" method="post" action="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

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
      if ($GLOBALS['CFG_GLPI']["use_rich_text"]) {
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
      Dropdown::showFromArray('due_date_rule', array(
         ''          => Dropdown::EMPTY_VALUE,
         'answer'    => __('equals to the answer to the question', 'formcreator'),
         'ticket'    => __('calculated from the ticket creation date', 'formcreator'),
         'calcul'    => __('calculated from the answer to the question', 'formcreator'),
      ), array(
         'value'     => $this->fields['due_date_rule'],
         'on_change' => 'formcreatorChangeDueDate(this.value)',
      ));

      // for each section ...
      $questions_list = array(Dropdown::EMPTY_VALUE);
      $query = "SELECT s.id, s.name
                FROM glpi_plugin_formcreator_targets t
                INNER JOIN glpi_plugin_formcreator_sections s ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = " . (int) $this->getID() . "
                ORDER BY s.order";
      $result = $GLOBALS['DB']->query($query);
      while ($section = $GLOBALS['DB']->fetch_array($result)) {
         // select all date and datetime questions
         $query2 = "SELECT q.id, q.name
                   FROM glpi_plugin_formcreator_questions q
                   INNER JOIN glpi_plugin_formcreator_sections s ON s.id = q.plugin_formcreator_sections_id
                   WHERE s.id = {$section['id']}
                   AND q.fieldtype IN ('date', 'datetime')";
         $result2 = $GLOBALS['DB']->query($query2);
         $section_questions = array();
         while ($question = $GLOBALS['DB']->fetch_array($result2)) {
            $section_questions[$question['id']] = $question['name'];
         }
         if (count($section_questions) > 0) {
            $questions_list[$section['name']] = $section_questions;
         }
      }
      // List questions
      if ($this->fields['due_date_rule'] != 'answer' && $this->fields['due_date_rule'] != 'calcul') {
         echo '<div id="due_date_questions" style="display:none">';
      } else {
         echo '<div id="due_date_questions">';
      }
      Dropdown::showFromArray('due_date_question', $questions_list, array(
         'value' => $this->fields['due_date_question']
      ));
      echo '</div>';

      if ($this->fields['due_date_rule'] != 'ticket' && $this->fields['due_date_rule'] != 'calcul') {
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
      // Due date type selection end
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
      $query = "SELECT s.id, s.name
                FROM glpi_plugin_formcreator_targets t
                INNER JOIN glpi_plugin_formcreator_sections s ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = " . (int) $this->getID() . "
                ORDER BY s.order";
      $result = $GLOBALS['DB']->query($query);
      while ($section = $GLOBALS['DB']->fetch_array($result)) {
         // select all user, group or supplier questions (GLPI Object)
         $query2 = "SELECT q.id, q.name, q.values
                   FROM glpi_plugin_formcreator_questions q
                   INNER JOIN glpi_plugin_formcreator_sections s ON s.id = q.plugin_formcreator_sections_id
                   WHERE s.id = {$section['id']}
                   AND q.fieldtype = 'glpiselect'
                   AND q.values IN ('User', 'Group', 'Supplier')";
         $result2 = $GLOBALS['DB']->query($query2);
         $section_questions_user = array();
         $section_questions_group = array();
         $section_questions_supplier = array();
         while ($question = $GLOBALS['DB']->fetch_array($result2)) {
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
         }
         $questions_user_list[$section['name']]     = $section_questions_user;
         $questions_group_list[$section['name']]    = $section_questions_group;
         $questions_supplier_list[$section['name']] = $section_questions_supplier;
      }

      // Get available questions for actors lists
      $actors = array('requester' => array(), 'observer' => array(), 'assigned' => array());
      $query = "SELECT id, actor_role, actor_type, actor_value, use_notification
                FROM glpi_plugin_formcreator_targettickets_actors
                WHERE plugin_formcreator_targettickets_id = " . $this->getID();
      $result = $GLOBALS['DB']->query($query);
      while ($actor = $GLOBALS['DB']->fetch_array($result)) {
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
      echo '<form name="form_target" id="form_add_requester" method="post" style="display:none" action="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      Dropdown::showFromArray('actor_type', array(
         ''                => Dropdown::EMPTY_VALUE,
         'creator'         => __('Form requester', 'formcreator'),
         'validator'       => __('Form validator', 'formcreator'),
         'person'          => __('Specific person', 'formcreator'),
         'question_person' => __('Person from the question', 'formcreator'),
         'group'           => __('Specific group', 'formcreator'),
         'question_group'  => __('Group from the question', 'formcreator'),
      ), array(
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

      echo '<div>';
      echo __('Email followup');
      Dropdown::showYesNo('use_notification', 1);
      echo '</div>';

      echo '<p align="center">';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
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
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo self::getDeleteImage($id);
         echo '</div>';
      }

      echo '</td>';

      // Observer
      echo '<td valign="top">';

      // => Add observer form
      echo '<form name="form_target" id="form_add_watcher" method="post" style="display:none" action="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      Dropdown::showFromArray('actor_type', array(
         ''                => Dropdown::EMPTY_VALUE,
         'creator'         => __('Form requester', 'formcreator'),
         'validator'       => __('Form validator', 'formcreator'),
         'person'          => __('Specific person', 'formcreator'),
         'question_person' => __('Person from the question', 'formcreator'),
         'group'           => __('Specific group', 'formcreator'),
         'question_group'  => __('Group from the question', 'formcreator'),
      ), array(
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
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo self::getDeleteImage($id);
         echo '</div>';
      }

      echo '</td>';

      // Assigned to
      echo '<td valign="top">';

      // => Add assigned to form
      echo '<form name="form_target" id="form_add_assigned" method="post" style="display:none" action="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      Dropdown::showFromArray('actor_type', array(
         ''                  => Dropdown::EMPTY_VALUE,
         'creator'           => __('Form requester', 'formcreator'),
         'validator'         => __('Form validator', 'formcreator'),
         'person'            => __('Specific person', 'formcreator'),
         'question_person'   => __('Person from the question', 'formcreator'),
         'group'             => __('Specific group', 'formcreator'),
         'question_group'    => __('Group from the question', 'formcreator'),
         'supplier'          => __('Specific supplier', 'formcreator'),
         'question_supplier' => __('Supplier from the question', 'formcreator'),
      ), array(
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
            case 'supplier' :
               $group = new Group();
               $group->getFromDB($values['actor_value']);
               echo $img_supplier . ' <b>' . __('Supplier') . ' </b> "' . $group->getName() . '"';
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
                LEFT JOIN $table_sections s ON q.`plugin_formcreator_sections_id` = s.`id`
                WHERE s.`plugin_formcreator_forms_id` = " . $target['plugin_formcreator_forms_id'] . "
                ORDER BY s.`order`, q.`order`";
      $result = $GLOBALS['DB']->query($query);

      $i = 0;
      while ($question = $GLOBALS['DB']->fetch_array($result)) {
         $i++;
         echo '<tr class="line' . ($i % 2) . '">';
         echo '<td colspan="2">' . $question['question'] . '</td>';
         echo '<td align="center"><code>##question_' . $question['id'] . '##</code></td>';
         echo '<td align="center"><code>##answer_' . $question['id'] . '##</code></td>';
         echo '<td align="center">' . $question['section'] . '</td>';
         echo '</tr>';
      }

      echo '</table>';
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
      // Control fields values :
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

      if ($GLOBALS['CFG_GLPI']['use_rich_text']) {
         $input['comment'] = Html::entity_decode_deep($input['comment']);
      }

      return $input;
   }

   /**
    * Save form datas to the target
    *
    * @param  PluginFormcreatorFormanswer $formanswer    Answers previously saved
    */
   public function save(PluginFormcreatorFormanswer $formanswer)
   {
      $datas   = array();
      $ticket  = new Ticket();
      $docItem = new Document_Item();
      $form    = new PluginFormcreatorForm();
      $form->getFromDB($formanswer->fields['plugin_formcreator_forms_id']);

      // Get default request type
      $query   = "SELECT id FROM `glpi_requesttypes` WHERE `name` LIKE 'Formcreator';";
      $result  = $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
      list($requesttypes_id) = $GLOBALS['DB']->fetch_array($result);

      $datas['requesttypes_id'] = $requesttypes_id;

      // Get predefined Fields
      $ttp                  = new TicketTemplatePredefinedField();
      $predefined_fields    = $ttp->getPredefinedFields($this->fields['tickettemplates_id'], true);
      $datas                = array_merge($datas, $predefined_fields);

      // Parse datas and tags
      $datas['name']                  = addslashes($this->parseTags($this->fields['name'], $formanswer));
      $datas['content']               = htmlentities($this->parseTags($this->fields['comment'], $formanswer));
      $datas['entities_id']           = (isset($_SESSION['glpiactive_entity']))
                                          ? $_SESSION['glpiactive_entity']
                                          : $form->fields['entities_id'];
      $datas['_users_id_requester']   = 0;
      $datas['_users_id_recipient']   = $_SESSION['glpiID'];
      $datas['_tickettemplates_id']   = $this->fields['tickettemplates_id'];

      // Define due date
      $answer = new PluginFormcreatorAnswer();
      $found  = $answer->find('plugin_formcreator_formanwers_id = ' . $formanswer->fields['id']
                  . ' AND plugin_formcreator_question_id = ' . $this->fields['due_date_question']);
      $date   = array_shift($found);
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

      // Select ticket actors
      $query = "SELECT id, actor_type, actor_value, use_notification
                FROM glpi_plugin_formcreator_targettickets_actors
                WHERE plugin_formcreator_targettickets_id = " . $this->getID() . "
                AND actor_role = 'requester'";
      $result = $GLOBALS['DB']->query($query);

      // If there is only one requester add it on creation, otherwize we will add them later
      if ($GLOBALS['DB']->numrows($result) == 1) {
         $actor = $GLOBALS['DB']->fetch_array($result);
         switch ($actor['actor_type']) {
            case 'creator' :
               $user_id = $formanswer->fields['requester_id'];
               break;
            case 'validator' :
               $user_id = $formanswer->fields['validator_id'];
               break;
            case 'person' :
            case 'group' :
            case 'supplier' :
               $user_id = $actor['actor_value'];
               break;
            case 'question_person' :
            case 'question_group' :
            case 'question_supplier' :
               $answer  = new PluginFormcreatorAnswer();
               $found   = $answer->find('plugin_formcreator_question_id = ' . $actor['actor_value']
                           . ' AND plugin_formcreator_formanwers_id = ' . $formanswer->fields['id']);
               $found   = array_shift($found);

               if (empty($found['answer'])) {
                  continue;
               } else {
                  $user_id = (int) $found['answer'];
               }
               break;
         }
         $datas['_users_id_requester']   = $user_id;
      }
      Toolbox::logDebug($datas);

      // Create the target ticket
      if (!$ticketID = $ticket->add($datas)) {
         return false;
      }

      // Add link between Ticket and FormAnswer
      $itemlink = new Item_Ticket();
      $itemlink->add(array(
         'itemtype'   => 'PluginFormcreatorFormanswer',
         'items_id'   => $formanswer->fields['id'],
         'tickets_id' => $ticketID,
      ));

      // Add actors to ticket
      $query = "SELECT id, actor_role, actor_type, actor_value, use_notification
                FROM glpi_plugin_formcreator_targettickets_actors
                WHERE plugin_formcreator_targettickets_id = " . $this->getID();
      $result = $GLOBALS['DB']->query($query);
      while ($actor = $GLOBALS['DB']->fetch_array($result)) {

         // If actor type is validator and if the form doesn't have a validator, continue to other actors
         if ($actor['actor_type'] == 'validator' && !$form->fields['validation_required']) continue;

         switch ($actor['actor_role']) {
            case 'requester' : $role = CommonITILActor::REQUESTER;   break;
            case 'observer' :  $role = CommonITILActor::OBSERVER;    break;
            case 'assigned' :  $role = CommonITILActor::ASSIGN;      break;
         }
         switch ($actor['actor_type']) {
            case 'creator' :
               $user_id = $formanswer->fields['requester_id'];
               break;
            case 'validator' :
               $user_id = $formanswer->fields['validator_id'];
               break;
            case 'person' :
            case 'group' :
            case 'supplier' :
               $user_id = $actor['actor_value'];
               break;
            case 'question_person' :
            case 'question_group' :
            case 'question_supplier' :
               $answer  = new PluginFormcreatorAnswer();
               $found   = $answer->find('plugin_formcreator_question_id = ' . $actor['actor_value']
                           . ' AND plugin_formcreator_formanwers_id = ' . $formanswer->fields['id']);
               $found   = array_shift($found);

               if (empty($found['answer'])) {
                  continue;
               } else {
                  $user_id = (int) $found['answer'];
               }
               break;
         }
         switch ($actor['actor_type']) {
            case 'creator' :
            case 'validator' :
            case 'person' :
            case 'question_person' :
               $obj = new Ticket_User();
               $obj->add(array(
                  'tickets_id'       => $ticketID,
                  'users_id'         => $user_id,
                  'type'             => $role,
                  'use_notification' => $actor['use_notification'],
               ));
               break;
            case 'group' :
            case 'question_group' :
               $obj = new Group_Ticket();
               $obj->add(array(
                  'tickets_id'       => $ticketID,
                  'groups_id'        => $user_id,
                  'type'             => $role,
                  'use_notification' => $actor['use_notification'],
               ));
               break;
            case 'supplier' :
            case 'question_supplier' :
               $obj = new Supplier_Ticket();
               $obj->add(array(
                  'tickets_id'       => $ticketID,
                  'suppliers_id'     => $user_id,
                  'type'             => $role,
                  'use_notification' => $actor['use_notification'],
               ));
               break;
         }
      }

      // Attach documents to ticket
      $found = $docItem->find('itemtype = "PluginFormcreatorFormanswer" AND items_id = ' . $formanswer->getID());
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
      // /!\ Followup is directly saved to the database to avoid double notification on ticket
      //     creation and add followup
      if ($form->fields['validation_required'] && $this->fields['validation_followup']) {
         $message = addslashes(__('Your form have been accepted by the validator', 'formcreator'));
         if (!empty($formanswer->fields['comment'])) {
            $message.= "\n".addslashes($formanswer->fields['comment']);
         }

        $query = "INSERT INTO `glpi_ticketfollowups` SET
                     `tickets_id` = $ticketID,
                     `date`       = NOW(),
                     `users_id`   = {$_SESSION['glpiID']},
                     `content`    = \"$message\"";
         $GLOBALS['DB']->query($query);
      }

      return true;
   }

   /**
    * Parse target content to replace TAGS like ##FULLFORM## by the values
    *
    * @param  String $content                            String to be parsed
    * @param  PluginFormcreatorFormanswer $formanswer    Formanswer object where answers are stored
    * @return String                                     Parsed string with tags replaced by form values
    */
   private function parseTags($content, PluginFormcreatorFormanswer $formanswer) {
      $content     = str_replace('##FULLFORM##', $formanswer->getFullForm(), $content);

      $section     = new PluginFormcreatorSection();
      $found     = $section->find('plugin_formcreator_forms_id = '
                                    . $formanswer->fields['plugin_formcreator_forms_id'], '`order` ASC');
      $tab_section = array();
      foreach($found as $section_item) {
         $tab_section[] = $section_item['id'];
      }

      if(!empty($tab_section)) {
         $question  = new PluginFormcreatorQuestion();
         $found = $question->find('plugin_formcreator_sections_id IN (' . implode(', ', $tab_section) . ')', '`order` ASC');
         foreach($found as $question_line) {
            $id     = $question_line['id'];
            $name   = $question_line['name'];

            $answer = new PluginFormcreatorAnswer();
            $found  = $answer->find('`plugin_formcreator_formanwers_id` = ' . $formanswer->getID()
                                    . ' AND `plugin_formcreator_question_id` = ' . $id);
            if (count($found)) {
               $datas = array_shift($found);
               $value = $datas['answer'];
            } else {
               $value = '';
            }
            $value   = PluginFormcreatorFields::getValue($question_line, $value);
            Toolbox::logDebug($value);
            if (is_array($value)) {
               if ($GLOBALS['CFG_GLPI']['use_rich_text']) {
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
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `name` varchar(255) NOT NULL DEFAULT '',
                     `tickettemplates_id` int(11) NULL DEFAULT NULL,
                     `comment` text collate utf8_unicode_ci,
                     `due_date_rule` ENUM('answer', 'ticket', 'calcul') NULL DEFAULT NULL,
                     `due_date_question` INT NULL DEFAULT NULL,
                     `due_date_value` TINYINT NULL DEFAULT NULL,
                     `due_date_period` ENUM('minute', 'hour', 'day', 'month') NULL DEFAULT NULL,
                     `validation_followup` BOOLEAN NOT NULL DEFAULT TRUE
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
      } elseif(!FieldExists($table, 'due_date_rule', false)) {
         $query = "ALTER TABLE `$table`
                     ADD `due_date_rule` ENUM('answer', 'ticket', 'calcul') NULL DEFAULT NULL,
                     ADD `due_date_question` INT NULL DEFAULT NULL,
                     ADD `due_date_value` TINYINT NULL DEFAULT NULL,
                     ADD `due_date_period` ENUM('minute', 'hour', 'day', 'month') NULL DEFAULT NULL,
                     ADD `validation_followup` BOOLEAN NOT NULL DEFAULT TRUE;";
         $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
      }

      if (!TableExists('glpi_plugin_formcreator_targettickets_actors')) {
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_targettickets_actors` (
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `plugin_formcreator_targettickets_id` int(11) NOT NULL,
                    `actor_role` enum('requester','observer','assigned') NOT NULL,
                    `actor_type` enum('creator','validator','person','question_person','group','question_group','supplier','question_supplier') NOT NULL,
                    `actor_value` int(11) DEFAULT NULL,
                    `use_notification` BOOLEAN NOT NULL DEFAULT TRUE,
                    KEY `plugin_formcreator_targettickets_id` (`plugin_formcreator_targettickets_id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
      }

      return true;
   }

   public static function uninstall()
   {
      $query = "DROP TABLE IF EXISTS `" . getTableForItemType(__CLASS__) . "`";
      return $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
   }

   private static function getDeleteImage($id) {
      $link  = ' &nbsp;<a href="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/targetticket.form.php?delete_actor=' . $id . '">';
      $link .= '<img src="../../../pics/delete.png" alt="' . __('Delete') . '" title="' . __('Delete') . '" />';
      $link .= '</a>';
      return $link;
   }
}
