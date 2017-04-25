<?php
class PluginFormcreatorTargetChange extends CommonDBTM
{
   public static function getTypeName($nb = 1) {
      return _n('Target change', 'Target changes', $nb, 'formcreator');
   }

   protected function getItem_User() {
      return new Change_User();
   }

   protected function getItem_Group() {
      return new Change_Group();
   }

   protected function getItem_Supplier() {
      return new Change_Supplier();
   }

   protected function getItem_Item() {
      return new Change_Item();
   }

   /**
    * Show the Form edit form the the adminsitrator in the config page
    *
    * @param  Array  $options Optional options
    *
    * @return NULL         Nothing, just display the form
    */
   public function showForm($options=array()) {
      global $CFG_GLPI, $DB;

      $rand = mt_rand();

      $obj = new PluginFormcreatorTarget();
      $found = $obj->find("itemtype = '" . __CLASS__ . "' AND items_id = " . (int) $this->getID());
      $target = array_shift($found);

      $form = new PluginFormcreatorForm();
      $form->getFromDB($target['plugin_formcreator_forms_id']);

      echo '<div class="center" style="width: 950px; margin: 0 auto;">';
      echo '<form name="form_target" method="post" action="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetchange.form.php">';

      // General information : name
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="2">' . __('Edit a destination', 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td width="15%"><strong>' . __('Name') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td width="85%"><input type="text" name="name" style="width:704px;" value="' . $target['name'] . '"></textarea</td>';
      echo '</tr>';

      echo '</table>';

      // change information : title, template...
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="4">' . _n('Target change', 'Target changes', 1, 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Change title', 'formcreator') . ' <span style="color:red;">*</span></strong></td>';
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

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Impacts') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="impactcontent" style="width:700px;" rows="15">' . $this->fields['impactcontent'] . '</textarea>';
      if ($CFG_GLPI["use_rich_text"]) {
         Html::initEditorSystem('impactcontent');
      }
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Control list') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="controlistcontent" style="width:700px;" rows="15">' . $this->fields['controlistcontent'] . '</textarea>';
      if ($CFG_GLPI["use_rich_text"]) {
         Html::initEditorSystem('controlistcontent');
      }
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Deployment plan') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="rolloutplancontext" style="width:700px;" rows="15">' . $this->fields['rolloutplancontext'] . '</textarea>';
      if ($CFG_GLPI["use_rich_text"]) {
         Html::initEditorSystem('rolloutplancontext');
      }
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Backup plan') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="backoutplancontext" style="width:700px;" rows="15">' . $this->fields['backoutplancontext'] . '</textarea>';
      if ($CFG_GLPI["use_rich_text"]) {
         Html::initEditorSystem('backoutplancontext');
      }
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Checklist') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="checklistcontent" style="width:700px;" rows="15">' . $this->fields['checklistcontent'] . '</textarea>';
      if ($CFG_GLPI["use_rich_text"]) {
         Html::initEditorSystem('checklistcontent');
      }
      echo '</td>';
      echo '</tr>';

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
      echo '<td colspan="2"></td>';

      // for each section ...
      $questions_list = array(Dropdown::EMPTY_VALUE);
      $query = "SELECT s.id, s.name
                FROM glpi_plugin_formcreator_targets t
                INNER JOIN glpi_plugin_formcreator_sections s ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = " . (int) $this->getID() . "
                ORDER BY s.order";
      $result = $DB->query($query);
      while ($section = $DB->fetch_array($result)) {
         // select all date and datetime questions
         $query2 = "SELECT q.id, q.name
         FROM glpi_plugin_formcreator_questions q
         INNER JOIN glpi_plugin_formcreator_sections s ON s.id = q.plugin_formcreator_sections_id
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
                INNER JOIN glpi_plugin_formcreator_sections s ON s.id = q.plugin_formcreator_sections_id
                INNER JOIN glpi_plugin_formcreator_targets t ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = " . (int) $this->getID() . "
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
                INNER JOIN glpi_plugin_formcreator_sections s ON s.id = q.plugin_formcreator_sections_id
                INNER JOIN glpi_plugin_formcreator_targets t ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = " . (int) $this->getID() . "
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
                   INNER JOIN glpi_plugin_formcreator_sections s ON s.id = q.plugin_formcreator_sections_id
                   INNER JOIN glpi_plugin_formcreator_targets t ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                   WHERE t.items_id = " . (int) $this->getID() . "
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
      $query = "SELECT s.id, s.name
                FROM glpi_plugin_formcreator_targets t
                INNER JOIN glpi_plugin_formcreator_sections s ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = " . (int) $this->getID() . "
                ORDER BY s.order";
      $result = $DB->query($query);
      while ($section = $DB->fetch_array($result)) {
         // select all user, group or supplier questions (GLPI Object)
         $query2 = "SELECT q.id, q.name, q.values
         FROM glpi_plugin_formcreator_questions q
         INNER JOIN glpi_plugin_formcreator_sections s ON s.id = q.plugin_formcreator_sections_id
         WHERE s.id = {$section['id']}
         AND q.fieldtype = 'glpiselect'
         AND q.values IN ('User', 'Group', 'Supplier')";
         $result2 = $DB->query($query2);
         $section_questions_user = array();
         $section_questions_group = array();
         $section_questions_supplier = array();
         while ($question = $DB->fetch_array($result2)) {
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
                FROM glpi_plugin_formcreator_targetchanges_actors
                WHERE plugin_formcreator_targetchanges_id = " . $this->getID();
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
      echo '<form name="form_target" id="form_add_watcher" method="post" style="display:none" action="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

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
      echo '<form name="form_target" id="form_add_assigned" method="post" style="display:none" action="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

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
      LEFT JOIN $table_sections s ON q.`plugin_formcreator_sections_id` = s.`id`
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
   public function prepareInputForUpdate($input) {
      global $CFG_GLPI;

      // Control fields values :
      if (!isset($input['_skip_checks'])
            || !$input['_skip_checks']) {
         // - name is required
         if (empty($input['title'])) {
            Session::addMessageAfterRedirect(__('The title cannot be empty!', 'formcreator'), false, ERROR);
            return array();
         }

         // - comment is required
         if (empty($input['comment'])) {
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

   /**
    * Save form datas to the target
    *
    * @param  PluginFormcreatorForm_Answer $formanswer    Answers previously saved
    */
   public function save(PluginFormcreatorForm_Answer $formanswer) {
      global $DB, $CFG_GLPI;

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
      $ticket  = new Change();

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
      $datas                = array_merge($datas, $predefined_fields);

      // Parse datas
      $fullform = $formanswer->getFullForm();

      $datas['name']                = addslashes($this->parseTags($this->fields['name'],
            $formanswer,
            $fullform));
      $datas['content']             = htmlentities($this->parseTags($this->fields['comment'],
            $formanswer,
            $fullform));
      $datas['impactcontent']       = htmlentities($this->parseTags($this->fields['impactcontent'],
            $formanswer,
            $fullform));

      $datas['controlistcontent']   = htmlentities($this->parseTags($this->fields['controlistcontent'],
            $formanswer,
            $fullform));

      $datas['rolloutplancontent']  = htmlentities($this->parseTags($this->fields['rolloutplancontent'],
            $formanswer,
            $fullform));

      $datas['backoutplancontent']  = htmlentities($this->parseTags($this->fields['backoutplancontent'],
            $formanswer,
            $fullform));
      $datas['checklistcontent']    = htmlentities($this->parseTags($this->fields['checklistcontent'],
            $formanswer,
            $fullform));

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
            while ($entity = $DB->fetch_array($res_entities)) {
               $data_entities[] = $entity;
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
            $found   = $answer->find('plugin_formcreator_formanwers_id = '.$formanswer->fields['id'].
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
            $found  = $answer->find('plugin_formcreator_formanwers_id = '.$formanswer->fields['id'].
            ' AND plugin_formcreator_question_id = '.$this->fields['destination_entity_value']);
            $entity = array_shift($found);

            $datas['entities_id'] = (int) $entity['answer'];
            break;

            // Requester current entity
         default :
            $datas['entities_id'] = 0;
            break;
      }

      // Define due date
      if ($this->fields['due_date_question'] !== null) {
         $found  = $answer->find('plugin_formcreator_formanwers_id = '.$formanswer->fields['id'].
                                 ' AND plugin_formcreator_question_id = '.$this->fields['due_date_question']);
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

      // Create the target change
      if (!$changeID->add($datas)) {
         return false;
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
                      WHERE `plugin_formcreator_formanwers_id` = " . (int) $formanswer->fields['id'] . "
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
            'items_id'           => $changeID,
            'itemtype'           => Change::class,
            ));
         }
      }

      // Add link between Change and FormAnswer
      $itemlink = $this->getItem_Item();
      $itemlink->add(array(
            'itemtype'     => 'PluginFormcreatorForm_Answer',
            'items_id'     => $formanswer->fields['id'],
            'changess_id'  => $changeID,
      ));

      if (version_compare(GLPI_VERSION, '9.1.2', 'lt')) {
         // update ticket with actors

         // user actors
         foreach ($this->requesters['_users_id_requester'] as $index => $userId) {
            $change_user = $this->getItem_User();
            $change_user->add(array(
                  'tickets_id'         => $changeID,
                  'users_id'           => $userId,
                  'type'               => CommonITILActor::REQUESTER,
                  'use_notification'   => $this->requesters['_users_id_requester_notif']['use_notification'][$index],
                  'alternative_email'  => $this->requesters['_users_id_requester_notif']['alternative_email'][$index],
            ));
         }
         foreach ($this->observers['_users_id_observer'] as $index => $userId) {
            $change_user = $this->getItem_User();
            $change_user->add(array(
                  'tickets_id'         => $changeID,
                  'users_id'           => $userId,
                  'type'               => CommonITILActor::OBSERVER,
                  'use_notification'   => $this->observers['_users_id_observer_notif']['use_notification'][$index],
                  'alternative_email'  => $this->observers['_users_id_observer_notif']['alternative_email'][$index],
            ));
         }
         foreach ($this->assigned['_users_id_assign'] as $index => $userId) {
            $change_user = $this->getItem_User();
            $change_user->add(array(
                  'tickets_id'         => $changeID,
                  'users_id'           => $userId,
                  'type'               => CommonITILActor::ASSIGN,
                  'use_notification'   => $this->assigned['_users_id_assign_notif']['use_notification'][$index],
                  'alternative_email'  => $this->assigned['_users_id_assign_notif']['alternative_email'][$index],
            ));
         }
         foreach ($this->assignedSuppliers['_suppliers_id_assign'] as $index => $userId) {
            $change_supplier = $this->getItem_Supplier();
            $change_supplier->add(array(
                  'tickets_id'         => $changeID,
                  'users_id'           => $userId,
                  'type'               => CommonITILActor::ASSIGN,
                  'use_notification'   => $this->assigned['_suppliers_id_assign']['use_notification'][$index],
                  'alternative_email'  => $this->assigned['_suppliers_id_assign']['alternative_email'][$index],
            ));
         }

         foreach ($this->requesterGroups['_groups_id_requester'] as $index => $groupId) {
            $change_group = $this->getItem_Group();
            $change_group->add(array(
                  'tickets_id'       => $changeID,
                  'groups_id'        => $groupId,
                  'type'             => CommonITILActor::REQUESTER,
            ));
         }
         foreach ($this->observerGroups['_groups_id_observer'] as $index => $groupId) {
            $change_group = $this->getItem_Group();
            $change_group->add(array(
                  'tickets_id'       => $changeID,
                  'groups_id'        => $groupId,
                  'type'             => CommonITILActor::OBSERVER,
            ));
         }
         foreach ($this->assignedGroups['_groups_id_assign'] as $index => $groupId) {
            $change_group = $this->getItem_Group();
            $change_group->add(array(
                  'tickets_id'       => $changeID,
                  'groups_id'        => $groupId,
                  'type'             => CommonITILActor::ASSIGN,
            ));
         }
      }

      $this->attachDocument($formanswer->getID(), Change::class, $changeID);

      return true;
   }

   /**
    * Parse target content to replace TAGS like ##FULLFORM## by the values
    *
    * @param  String $content                            String to be parsed
    * @param  PluginFormcreatorForm_Answer $formanswer   Formanswer object where answers are stored
    * @param  String
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
      foreach ($found as $section_item) {
         $tab_section[] = $section_item['id'];
      }

      if (!empty($tab_section)) {
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

   private static function getDeleteImage($id) {
      global $CFG_GLPI;

      $link  = ' &nbsp;<a href="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php?delete_actor=' . $id . '">';
      $link .= '<img src="../../../pics/delete.png" alt="' . __('Delete') . '" title="' . __('Delete') . '" />';
      $link .= '</a>';
      return $link;
   }
}
