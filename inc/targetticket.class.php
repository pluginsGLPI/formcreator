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
      if(!$this->getFromDB($options['id'])) {
         Html::displayNotFoundError();
      }

      $obj = new PluginFormcreatorTarget();
      $founded = $obj->find('itemtype = "' . __CLASS__ . '" AND items_id = ' . $this->getID());
      $target = array_shift($founded);

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
      echo '<td width="70%" colspan="4"><textarea name="comment" style="width:646px;" rows="15">' . $this->fields['comment'] . '</textarea></td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td width="20%">' . _n('Ticket template', 'Ticket templates', 1) . '</td>';
      echo '<td width="70%" colspan="4">';
      Dropdown::show('TicketTemplate', array(
         'name'  => 'tickettemplates_id',
         'value' => $this->fields['tickettemplates_id']
      ));
      echo '</td>';
      echo '</tr>';

      echo '<tr><td colspan="5">&nbsp;</td></tr>';

      echo '<tr><th colspan="5">' . __('List of available tags') . '</th></tr>';
      echo '<tr>';
      echo '<th width="40%" colspan="2">' . _n('Question', 'Questions', 1, 'formcreator') . '</th>';
      echo '<th width="20%">' . __('Title') . '</th>';
      echo '<th width="20%">' . __('Answer', 'formcreator') . '</th>';
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

      echo '<tr class="line' . (++$i % 2) . '"><td colspan="5">&nbsp;</td></tr>';

      echo '<tr class="line' . (++$i % 2) . '">';
      echo '<td colspan="5" class="center">';
      echo '<input type="reset" name="reset" class="submit_button" value="' . __('Cancel', 'formcreator') . '"
               onclick="document.location = \'form.form.php?id=' . $target['plugin_formcreator_forms_id'] . '\'" /> &nbsp; ';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="submit" name="update" class="submit_button" value="' . __('Save') . '" />';
      echo '</td>';
      echo '</tr>';

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
      $input['name'] = $input['title'];

      return $input;
   }


   public function save($form, $input)
   {
      $datas   = array();
      $ticket  = new Ticket();
      $docItem = new Document_Item();

      // Get default request type
      $query   = "SELECT id FROM `glpi_requesttypes` WHERE `name` LIKE 'Formcreator';";
      $result  = $GLOBALS['DB']->query($query) or die ($DB->error());
      list($requesttypes_id) = $GLOBALS['DB']->fetch_array($result);

      $datas['requesttypes_id'] = $requesttypes_id;

      // Get predefined Fields
      $ttp                  = new TicketTemplatePredefinedField();
      $predefined_fields    = $ttp->getPredefinedFields($this->fields['tickettemplates_id'], true);
      $datas                = array_merge($datas, $predefined_fields);

      $datas['name']        = $this->fields['name'];
      $datas['content']     = $this->parseContent($form, $input);
      $datas['entities_id'] = (isset($_SESSION['glpiactive_entity']))
                              ? $_SESSION['glpiactive_entity']
                              : $form->fields['entities_id'];

      $ticketID = $ticket->add($datas);

      if(!empty($_SESSION['formcreator_documents'])) {
         foreach ($_SESSION['formcreator_documents'] as $docID) {
            $docItem->add(array(
               'documents_id' => $docID,
               'itemtype'     => 'Ticket',
               'items_id'     => $ticketID
            ));
         }
      }
   }

   private function parseContent($form, $input) {
      $content     = $this->fields['comment'];
      $content     = str_replace('##FULLFORM##', $form->getFullForm($input), $content);

      $section     = new PluginFormcreatorSection();
      $founded     = $section->find('plugin_formcreator_forms_id = ' . $form->getID(), '`order` ASC');
      $tab_section = array();
      foreach($founded as $section_item) {
         $tab_section[] = $section_item['id'];
      }

      if(!empty($tab_section)) {
         $question  = new PluginFormcreatorQuestion();
         $founded = $question->find('plugin_formcreator_sections_id IN (' . implode(', ', $tab_section) . ')', '`order` ASC');
         foreach($founded as $question_line) {
            $id        = $question_line['id'];
            $name      = $question_line['name'];
            $value     = isset($input['formcreator_field_' . $id]) ? $input['formcreator_field_' . $id] : '';
            $value     = PluginFormcreatorFields::getValue($question_line, $value);

            $content   = str_replace('##question_' . $id . '##', $name, $content);
            $content   = str_replace('##answer_' . $id . '##', $value, $content);
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
                     `comment` text collate utf8_unicode_ci
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
      }

      return true;
   }

   public static function uninstall()
   {
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      return $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
   }
}
