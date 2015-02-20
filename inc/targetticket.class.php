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

      echo '<form name="form_target" method="post" action="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="5">' . __('Edit a destination', 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td width="20%"><strong>' . __('Name') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td width="70%" colspan="4"><input type="text" name="name" style="width:650px;" value="' . $target['name'] . '"></textarea</td>';
      echo '</tr>';

      echo '<tr><td colspan="5">&nbsp;</td></tr>';

      echo '<tr><th colspan="5">' . _n('Target ticket', 'Target tickets', 1, 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td width="20%"><strong>' . __('Ticket title', 'formcreator') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td width="70%" colspan="4"><input type="text" name="title" style="width:650px;" value="' . $this->fields['name'] . '"></textarea</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td width="20%"><strong>' . __('Description') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td width="70%" colspan="4">';
      echo '<textarea name="comment" id="comment' . $rand . '" style="width:646px;" rows="15">' . $this->fields['comment'] . '</textarea>';
      if ($GLOBALS['CFG_GLPI']["use_rich_text"]) {
         Html::initEditorSystem('comment', $rand);
      }
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td>' . _n('Ticket template', 'Ticket templates', 1) . '</td>';
      echo '<td>';
      Dropdown::show('TicketTemplate', array(
         'name'  => 'tickettemplates_id',
         'value' => $this->fields['tickettemplates_id']
      ));
      echo '</td>';
      echo '<td>' . __('Due date') . '</td>';
      echo '<td colspan="2">';

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
                LEFT JOIN glpi_plugin_formcreator_sections s ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = " . (int) $this->getID() . "
                ORDER BY s.order";
      $result = $GLOBALS['DB']->query($query);
      while ($section = $GLOBALS['DB']->fetch_array($result)) {
         // select all date and datetime questions
         $query2 = "SELECT q.id, q.name
                   FROM glpi_plugin_formcreator_questions q
                   LEFT JOIN glpi_plugin_formcreator_sections s ON s.id = q.plugin_formcreator_sections_id
                   WHERE s.id = {$section['id']}
                   AND q.fieldtype IN ('date', 'datetime')";
         $result2 = $GLOBALS['DB']->query($query2);
         $section_questions = array();
         while ($question = $GLOBALS['DB']->fetch_array($result2)) {
            $section_questions[$question['id']] = $question['name'];
         }
         if (count($section_questions > 0)) {
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
         'value' => 1,
         'min'   => -30,
         'max'   => 30
      ), array(
         'value' => $this->fields['due_date_period']
      ));
      Dropdown::showFromArray('due_date_period', array(
         'minute' => _n('Minute', 'Minutes', Session::getPluralNumber()),
         'hour'   => _n('Hour', 'Hours', Session::getPluralNumber()),
         'day'    => _n('Day', 'Days', Session::getPluralNumber()),
         'month'  => _n('Month', 'Month', Session::getPluralNumber()),
      ), array(
         'value' => $this->fields['due_date_period']
      ));
      echo '</div>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td colspan="5" class="center">';
      echo '<input type="reset" name="reset" class="submit_button" value="' . __('Cancel', 'formcreator') . '"
               onclick="document.location = \'form.form.php?id=' . $target['plugin_formcreator_forms_id'] . '\'" /> &nbsp; ';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="submit" name="update" class="submit_button" value="' . __('Save') . '" />';
      echo '</td>';
      echo '</tr>';
      echo '<tr class="line1"><td colspan="5">&nbsp;</td></tr>';

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
      Html::closeForm();
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
      $input['name']    = htmlentities($input['title']);

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
      $datas['name']                  = $this->parseTags($this->fields['name'], $formanswer);
      $datas['content']               = $this->parseTags($this->fields['comment'], $formanswer);
      $datas['entities_id']           = (isset($_SESSION['glpiactive_entity']))
                                          ? $_SESSION['glpiactive_entity']
                                          : $form->fields['entities_id'];
      $datas['_users_id_requester']   = $formanswer->fields['requester_id'];
      $datas['_users_id_recipient']   = $formanswer->fields['requester_id'];
      $datas['_users_id_lastupdater'] = Session::getLoginUserID();

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

      // Create the target ticket
      $ticketID = $ticket->add($datas);
      $found  = $docItem->find('itemtype = "PluginFormcreatorFormanswer" AND items_id = ' . $formanswer->getID());

      // Attach documents to ticket
      if(count($found) > 0) {
         foreach ($found as $document) {
            $docItem->add(array(
               'documents_id' => $document['documents_id'],
               'itemtype'     => 'Ticket',
               'items_id'     => $ticketID
            ));
         }
      }
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
                     `due_date_period` ENUM('minute', 'hour', 'day', 'month') NULL DEFAULT NULL
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
      } elseif(!FieldExists($table, 'due_date_rule', false)) {
         $query = "ALTER TABLE `$table`
                     ADD `due_date_rule` ENUM('answer', 'ticket', 'calcul') NULL DEFAULT NULL,
                     ADD `due_date_question` INT NULL DEFAULT NULL,
                     ADD `due_date_value` TINYINT NULL DEFAULT NULL,
                     ADD `due_date_period` ENUM('minute', 'hour', 'day', 'month') NULL DEFAULT NULL;";
         $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
      }

      return true;
   }

   public static function uninstall()
   {
      $query = "DROP TABLE IF EXISTS `" . getTableForItemType(__CLASS__) . "`";
      return $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
   }
}
