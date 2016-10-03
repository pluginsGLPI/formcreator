<?php
class PluginFormcreatorFormanswer extends CommonDBChild
{
   public $dohistory  = true;
   public $usenotepad = true;
   public $usenotepadrights = true;

   static public $itemtype = "PluginFormcreatorForm";
   static public $items_id = "plugin_formcreator_forms_id";

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

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0)
   {
      return _n('Form answer', 'Form answers', $nb, 'formcreator');
   }

   /**
    * Define search options for forms
    *
    * @return Array Array of fields to show in search engine and options for each fields
    */
   public function getSearchOptions()
   {
      $tab = array(
         '1' => array(
            'table'         => $this->getTable(),
            'field'         => 'status',
            'name'          => _n('Status', 'Statuses', 1),
            'searchtype'    => array('equals', 'notequals'),
            'datatype'      => 'specific',
            'massiveaction' => false,
         ),
         '2' => array(
            'table'         => $this->getTable(),
            'field'         => 'id',
            'name'          => __('ID'),
            'searchtype'    => 'contains',
            'datatype'      => 'itemlink',
            'massiveaction' => false,
         ),
         '3' => array(
            'table'         => getTableForItemType('PluginFormcreatorForm'),
            'field'         => 'name',
            'name'          => PluginFormcreatorForm::getTypeName(1),
            'searchtype'    => 'contains',
            'datatype'      => 'string',
            'massiveaction' => false,

         ),
         '4' => array(
            'table'         => getTableForItemType('User'),
            'field'         => 'name',
            'name'          => __('Requester', 'formcreator'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
            'linkfield'     => 'requester_id',

         ),
         '5' => array(
            'table'         => getTableForItemType('User'),
            'field'         => 'name',
            'name'          => __('Validator', 'formcreator'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
            'linkfield'     => 'validator_id',

         ),
         '6' => array(
            'table'         => $this->getTable(),
            'field'         => 'request_date',
            'name'          => __('Creation date'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
         ),
      );
      return $tab;
   }

   /**
    * Define how to display a specific value in search result table
    *
    * @param  String $field   Name of the field as define in $this->getSearchOptions()
    * @param  Mixed  $values  The value as it is stored in DB
    * @param  Array  $options Options (optional)
    * @return Mixed           Value to be displayed
    */
   public static function getSpecificValueToDisplay($field, $values, array $options=array())
   {
      global $CFG_GLPI;

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'status' :
            $output = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/' . $values[$field] . '.png"
                         alt="' . __($values[$field], 'formcreator') . '" title="' . __($values[$field], 'formcreator') . '" />';
            return $output;
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * Define how to display search field for a specific type
    *
    * @since version 0.84
    *
    * @param String $field           Name of the field as define in $this->getSearchOptions()
    * @param String $name            Name attribute for the field to be posted (default '')
    * @param Array  $values          Array of all values to display in search engine (default '')
    * @param Array  $options         Options (optional)
    *
    * @return String                 Html string to be displayed for the form field
    **/
   public static function getSpecificValueToSelect($field, $name='', $values='', array $options=array())
   {
      if (!is_array($values)) {
         $values = array($field => $values);
      }
      $options['display'] = false;

      switch ($field) {
         case 'status' :
            $output  = '<select name="' . $name . '">';
            $output .=  '<option value="waiting" '
                           . (($values[$field] == 'waiting') ? ' selected ' : '') . '>'
                        . __('waiting', 'formcreator')
                        . '</option>';
            $output .=  '<option value="accepted" '
                           . (($values[$field] == 'accepted') ? ' selected ' : '') . '>'
                        . __('accepted', 'formcreator')
                        . '</option>';
            $output .=  '<option value="refused" '
                           . (($values[$field] == 'refused') ? ' selected ' : '') . '>'
                        . __('refused', 'formcreator')
                        . '</option>';
            $output .=  '</select>';

            return $output;
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }
   /**
    * Display a list of all forms on the configuration page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0)
   {
      $item->showForm($item->fields['id']);
   }

   public function defineTabs($options = array())
   {
      $ong = array();
      $this->addDefaultFormTab($ong);
      if ($this->fields['id'] > 0) {
         $this->addStandardTab('Ticket', $ong, $options);
         $this->addStandardTab('Document_Item', $ong, $options);
         $this->addStandardTab('Notepad', $ong, $options);
         $this->addStandardTab('Log', $ong, $options);
      }
      return $ong;
   }

   /**
    * Return the name of the tab for item including forms like the config page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $withtemplate
    *
    * @return String                   Name to be displayed
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate=0)
   {
      return $this->getTypeName();
   }

   public function showForm($ID, $options = array()) {
      global $DB;

      if (!isset($ID) || !$this->getFromDB($ID)) {
         Html::displayNotFoundError();
      }
      $options = array('canedit' => false);

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      $form = new PluginFormcreatorForm();
      $formId = $this->fields['plugin_formcreator_forms_id'];
      $form->getFromDB($formId);

      $canEdit = $this->fields['status'] == 'refused'
                 && $_SESSION['glpiID'] == $this->fields['requester_id'];

      $userId = $_SESSION['glpiID'];

      if ($form->fields['validation_required'] == 1) {
         // Check the user is one of the users able to validate this form answer
         $query = "SELECT *
               FROM glpi_plugin_formcreator_formvalidators
               WHERE `forms_id`='$formId' AND `users_id` = '$userId'";
         $result = $DB->query($query);
         $canValidate = ($DB->numrows($result) > 0);
      } elseif(($form->fields['validation_required'] == 2)) {
         // Check the user is member of at least one validator group fot the form answers
         if (Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
            || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST)) {
               $formId = $form->getID();
               $condition = "`glpi_groups`.`id` IN (
                  SELECT `users_id`
                  FROM `glpi_plugin_formcreator_formvalidators`
                  WHERE `forms_id` = '$formId'
               )";
               $groupList = Group_User::getUserGroups($userId, $condition);
               $canValidate = (count($groupList) > 0);
         } else {
            $canValidate = false;
         }
      } else {
         $canValidate = false;
      }

      echo '<tr><td colspan="4" class="formcreator_form form_horizontal">';

      // Form Header
      if (!empty($form->fields['content'])) {
         echo '<div class="form_header">';
         echo html_entity_decode($form->fields['content']);
         echo '</div>';
      }

      if ($this->fields['status'] == 'refused') {
         echo '<div class="refused_header">';
         echo '<div>' . nl2br($this->fields['comment']) . '</div>';
         echo '</div>';
      } elseif($this->fields['status'] == 'accepted') {
         echo '<div class="accepted_header">';
         echo '<div>';
         if (!empty($this->fields['comment'])) {
            echo nl2br($this->fields['comment']);
         } elseif($form->fields['validation_required']) {
            echo __('Form accepted by validator.', 'formcreator');
         } else {
            echo __('Form successfully saved.', 'formcreator');
         }
         echo '</div>';
         echo '</div>';
      }

      echo '<div class="form_section">';

      // Display all fields of the section
      $query_questions = "SELECT sections.`name` as section_name,
                                 questions.*,
                                 answers.`answer`
                          FROM `glpi_plugin_formcreator_questions` AS questions
                          LEFT JOIN `glpi_plugin_formcreator_answers` AS answers
                            ON answers.`plugin_formcreator_question_id` = questions.`id`
                            AND answers.`plugin_formcreator_formanwers_id` = $ID
                          INNER JOIN `glpi_plugin_formcreator_sections` as sections
                            ON questions.`plugin_formcreator_sections_id` = sections.`id`
                            AND plugin_formcreator_forms_id = ".$form->getID()."
                          GROUP BY questions.`id`
                          ORDER BY sections.`order` ASC,
                                   sections.`id` ASC,
                                   questions.`order` ASC";
      $res_questions = $DB->query($query_questions);
      $last_section = "";
      $questionsCount = $DB->numrows($res_questions);
      while ($question_line = $DB->fetch_assoc($res_questions)) {
         // Get and display current section if needed
         if ($last_section != $question_line['section_name']) {
            echo '<h2>'.$question_line['section_name'].'</h2>';
            $last_section = $question_line['section_name'];
         }

         if ($canEdit
            || ($question_line['fieldtype'] != "description"
                && $question_line['fieldtype'] != "hidden")) {
            PluginFormcreatorFields::showField($question_line, $question_line['answer'], $canEdit);
         }
      }

      echo '<script type="text/javascript">
         formcreatorShowFields();
      </script>';

      // Display submit button
      if (($this->fields['status'] == 'refused') && ($_SESSION['glpiID'] == $this->fields['requester_id'])) {
         echo '<div class="form-group line'.(($questionsCount + 1) % 2).'">';
         echo '<div class="center">';
         echo '<input type="submit" name="save_formanswer" class="submit_button" value="'.__('Save').'" />';
         echo '</div>';
         echo '</div>';

      // Display validation form
      } elseif(($this->fields['status'] == 'waiting') && $canValidate) {
         if (Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
            || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST)) {
            echo '<div class="form-group required line1">';
            echo '<label for="comment">' . __('Comment', 'formcreator') . ' <span class="red">*</span></label>';
            echo '<textarea class="form-control"
                     rows="5"
                     name="comment"
                     id="comment">' . $this->fields['comment'] . '</textarea>';
            echo '<div class="help-block">' . __('Required if refused', 'formcreator') . '</div>';
            echo '</div>';

            echo '<div class="form-group line1">';
            echo '<div class="center" style="float: left; width: 50%;">';
            echo '<input type="submit" name="refuse_formanswer" class="submit_button"
                     value="' . __('Refuse', 'formcreator') . '" onclick="return checkComment(this);" />';
            echo '</div>';
            echo '<div class="center">';
            echo '<input type="submit" name="accept_formanswer" class="submit_button" value="' . __('Accept', 'formcreator') . '" />';
            echo '</div>';
            echo '</div>';
         }
      }

      echo '<input type="hidden" name="formcreator_form" value="' . $form->getID() . '">';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '">';
      echo '<input type="hidden" name="_glpi_csrf_token" value="' . Session::getNewCSRFToken() . '">';

      echo '</div>';
//      echo '</form>';
      echo '<script type="text/javascript">
               function checkComment(field) {
                  if (document.getElementById("comment").value == "") {
                     alert("' . __('Refused comment is required!', 'formcreator') . '");
                     return false;
                  }
               }
            </script>';

      echo '</td></tr>';

      $this->showFormButtons($options);
      return true;
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForAdd($input)
   {
      $form = new PluginFormcreatorForm();
      $form->getFromDB($input['plugin_formcreator_forms_id']);
      $input['name'] = $form->getName();

      return $input;
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForUpdate($input)
   {
      return $input;
   }

   public function saveAnswers($datas)
   {
      global $DB;

      $form   = new PluginFormcreatorForm();
      $answer = new PluginFormcreatorAnswer();

      $form->getFromDB($datas['formcreator_form']);

      $formanwers_id = isset($datas['id'])
                        ?intval($datas['id'])
                        :-1;

      $query = "SELECT q.`id`, q.`fieldtype`, q.`name`, a.`id` as answer_id
                FROM glpi_plugin_formcreator_questions q
                LEFT JOIN glpi_plugin_formcreator_sections s
                  ON s.`id` = q.`plugin_formcreator_sections_id`
                LEFT JOIN `glpi_plugin_formcreator_answers` AS a
                  ON a.`plugin_formcreator_formanwers_id` = $formanwers_id
                  AND a.`plugin_formcreator_question_id` = q.`id`
                WHERE s.`plugin_formcreator_forms_id` = {$datas['formcreator_form']}";
      $result = $DB->query($query);

      // Update form answers
      if (isset($_POST['save_formanswer'])) {
         $status = $_POST['status'];
         $formAnswer = array(
            'id'                          => intval($datas['id']),
            'status'                      => $status,
            'comment'                     => isset($_POST['comment']) ? $_POST['comment'] : 'NULL',
         );
         if (isset($_POST['accept_formanswer']) || isset($_POST['refuse_formanswer'])) {
            $formAnswer['validator_id'] = $_SESSION['glpiID'];
         }
         $this->update($formAnswer);

         // Update questions answers
         if ($status == 'waiting') {
            while ($question = $DB->fetch_array($result)) {
               if ($question['fieldtype'] != 'file') {
                  $data_value = $datas['formcreator_field_' . $question['id']];
                  if (isset($data_value)) {
                     if (is_array($data_value)) {
                        foreach ($data_value as $key => $value) {
                           $data_value[$key] = $value;
                        }
                        $answer_value = json_encode($data_value);
                     } else {
                        $answer_value = $data_value;
                     }
                  } else {
                     $answer_value = '';
                  }

                  $answer->update(array(
                     'id'     => $question['answer_id'],
                     'answer' => $answer_value,
                  ));
               } elseif (isset($_FILES['formcreator_field_' . $question['id']]['tmp_name'])
                     && is_file($_FILES['formcreator_field_' . $question['id']]['tmp_name'])) {
                  $doc    = new Document();

                  $file_datas                 = array();
                  $file_datas["name"]         = $form->fields['name'] . ' - ' . $question['name'];
                  $file_datas["entities_id"]  = isset($_SESSION['glpiactive_entity'])
                                                      ? $_SESSION['glpiactive_entity']
                                                      : $form->fields['entities_id'];
                  $file_datas["is_recursive"] = $form->fields['is_recursive'];
                  Document::uploadDocument($file_datas, $_FILES['formcreator_field_' . $question['id']]);

                  if ($docID = $doc->add($file_datas)) {
                     $docID = intval($docID);
                     $table    = getTableForItemType('Document');
                     $filename = $_FILES['formcreator_field_' . $question['id']]['name'];
                     $query    = "UPDATE `$table` SET `filename` = '$filename'
                                  WHERE `id` = $docID";
                     $DB->query($query);

                     $docItem = new Document_Item();
                     $docItemId = $docItem->add(array(
                        'documents_id' => $docID,
                        'itemtype'     => __CLASS__,
                        'items_id'     => $datas['id'],
                     ));

                     $answer->update(array(
                        'id'     => $question['answer_id'],
                        'answer' => $docID,
                     ));
                  }
               }
            }
         }

      // Create new form answer object
      } else {
         // Does the form need to be validate ?
         if ($form->fields['validation_required']) {
            $status = 'waiting';
         } else {
            $status = 'accepted';
         }

         $id = $this->add(array(
            'entities_id'                 => isset($_SESSION['glpiactive_entity'])
                                                ? $_SESSION['glpiactive_entity']
                                                : $form->fields['entities_id'],
            'is_recursive'                => $form->fields['is_recursive'],
            'plugin_formcreator_forms_id' => $datas['formcreator_form'],
            'requester_id'                => isset($_SESSION['glpiID'])
                                                ? $_SESSION['glpiID']
                                                : 0,
            'validator_id'                =>  isset($datas['formcreator_validator'])
                                                ? $datas['formcreator_validator']
                                                : 0,
            'status'                      => $status,
            'request_date'                => date('Y-m-d H:i:s'),
         ));

         // Save questions answers
         while ($question = $DB->fetch_assoc($result)) {
            // If the answer is set, check if it is an array (then implode id).
            if (isset($datas[$question['id']])) {
               $question_answer = $datas[$question['id']];
               if (is_array(json_decode($question_answer))) {
                  $question_answer = json_decode($question_answer);
                  foreach ($question_answer as $key => $value) {
                     $question_answer[$key] = $value;
                  }
                  $question_answer = json_encode($question_answer);
               } else {
                  $question_answer = $question_answer;
               }
            } else {
               $question_answer = '';
            }

            $answerID = $answer->add(array(
               'plugin_formcreator_formanwers_id' => $id,
               'plugin_formcreator_question_id'   => $question['id'],
               'answer'                           => $question_answer,
            ));

            // If the question is a file field, save the file as a document
            if (($question['fieldtype'] == 'file')
                  && (isset($_FILES['formcreator_field_' . $question['id']]['tmp_name']))
                  && (is_file($_FILES['formcreator_field_' . $question['id']]['tmp_name']))) {
               $doc         = new Document();
               $file_datas                 = array();
               $file_datas["name"]         = $form->fields['name'] . ' - ' . $question['name'];
               $file_datas["entities_id"]  = isset($_SESSION['glpiactive_entity'])
                                                   ? $_SESSION['glpiactive_entity']
                                                   : $form->fields['entities_id'];
               $file_datas["is_recursive"] = $form->fields['is_recursive'];
               Document::uploadDocument($file_datas, $_FILES['formcreator_field_' . $question['id']]);

               if ($docID = $doc->add($file_datas)) {
                  $docID = intval($docID);
                  $table    = getTableForItemType('Document');
                  $filename = $_FILES['formcreator_field_' . $question['id']]['name'];
                  $query    = "UPDATE `$table` SET `filename` = '$filename'
                               WHERE `id` = $docID";
                  $DB->query($query);

                  $docItem = new Document_Item();
                  $docItemId = $docItem->add(array(
                     'documents_id' => $docID,
                     'itemtype'     => __CLASS__,
                     'items_id'     => $id,
                  ));

                  $answer->update(array(
                     'id'     => $answerID,
                     'answer' => $docID,
                  ));
               }
            }
         }
      }

      NotificationEvent::raiseEvent('plugin_formcreator_form_created', $this);

      if ($form->fields['validation_required'] || ($status == 'accepted')) {
         switch ($status) {
            case 'waiting' :
               // Notify the validator
               NotificationEvent::raiseEvent('plugin_formcreator_need_validation', $this);
               break;
            case 'refused' :
               // Notify the requester
               NotificationEvent::raiseEvent('plugin_formcreator_refused', $this);
               break;
            case 'accepted' :
               // Notify the requester
               if (!$this->generateTarget()) {
                  Session::addMessageAfterRedirect(__('Cannot generate targets!', 'formcreator'), true, ERROR);

                  $this->update(array(
                     'id'     => $this->getID(),
                     'status' => 'waiting',
                  ));
               }
               if ($form->fields['validation_required']) {
                  NotificationEvent::raiseEvent('plugin_formcreator_accepted', $this);
               }
               return false;
               break;
         }
      }

      Session::addMessageAfterRedirect(__('The form has been successfully saved!', 'formcreator'), true, INFO);
   }

   public function generateTarget()
   {
      // Get all targets
      $target_class    = new PluginFormcreatorTarget();
      $found_targets = $target_class->find('plugin_formcreator_forms_id = ' . $this->fields['plugin_formcreator_forms_id']);

      // Generate targets
      foreach($found_targets as $target) {
         $obj = new $target['itemtype'];
         $obj->getFromDB($target['items_id']);
         if (!$obj->save($this)) {
            return false;
         }
      }
      Session::addMessageAfterRedirect(__('The form has been successfully saved!', 'formcreator'), true, INFO);
      return true;
   }

   /**
    * Get entire form to be inserted into a target content
    *
    * @return String                                    Full form questions and answers to be print
    */
   public function getFullForm()
   {
      global $CFG_GLPI, $DB;

      $question_no = 0;
      $output      = '';

      if ($CFG_GLPI['use_rich_text']) {
         $output .= '<h1>' . __('Form data', 'formcreator') . '</h1>';
      } else {
         $output .= __('Form data', 'formcreator') . PHP_EOL;
         $output .= '=================';
         $output .= PHP_EOL . PHP_EOL;
      }

      // retrieve answers
      $answer = new PluginFormcreatorAnswer();
      $answers = $answer->find('`plugin_formcreator_formanwers_id` = '.$this->getID());
      $answers_values = array();
      foreach ($answers as $found_answer) {
         $answers_values[$found_answer['plugin_formcreator_question_id']] = $found_answer['answer'];
      }

      // computer all questions
      $query_questions = "SELECT sections.`name` as section_name,
                                 questions.*,
                                 answers.`answer`
                          FROM `glpi_plugin_formcreator_questions` AS questions
                          INNER JOIN `glpi_plugin_formcreator_answers` AS answers
                            ON answers.`plugin_formcreator_question_id` = questions.`id`
                            AND answers.`plugin_formcreator_formanwers_id` = ".$this->getID()."
                          INNER JOIN `glpi_plugin_formcreator_sections` as sections
                            ON questions.`plugin_formcreator_sections_id` = sections.`id`
                            AND plugin_formcreator_forms_id = ".$this->fields['plugin_formcreator_forms_id']."
                          GROUP BY questions.`id`
                          ORDER BY sections.`order` ASC,
                                sections.`id` ASC,
                                questions.`order` ASC";
      $res_questions = $DB->query($query_questions);
      $last_section = "";
      while ($question_line = $DB->fetch_assoc($res_questions)) {

         // Get and display current section if needed
         if ($last_section != $question_line['section_name']) {
            if ($GLOBALS['CFG_GLPI']['use_rich_text']) {
               $output .= '<h2>'.$question_line['section_name'].'</h2>';
            } else {
               $output .= PHP_EOL.$question_line['section_name'].PHP_EOL;
               $output .= '---------------------------------';
               $output .= PHP_EOL;
            }
            $last_section = $question_line['section_name'];
         }

         // Don't save tags in "full form"
         if ($question_line['fieldtype'] == 'tag') continue;

         if (!PluginFormcreatorFields::isVisible($question_line['id'], $answers_values)) continue;

         if ($question_line['fieldtype'] != 'file' && $question_line['fieldtype'] != 'description') {
            $question_no ++;
            $value = $question_line['answer'];
            $output_value = PluginFormcreatorFields::getValue($question_line,
                                                              $value);

            if (in_array($question_line['fieldtype'], array('checkboxes', 'multiselect'))) {
               if (is_array($value)) {
                  if ($GLOBALS['CFG_GLPI']['use_rich_text']) {
                     $output_value = '<ul>';
                     foreach ($value as $choice) {
                      $output_value .= '<li>' . $choice . '</li>';
                     }
                     $output_value .= '</ul>';
                  } else {
                     $output_value = PHP_EOL . " - " . implode(PHP_EOL . " - ", $value);
                  }
               } elseif (is_array(json_decode($value))) {
                  if ($CFG_GLPI['use_rich_text']) {
                     $value = json_decode($value);
                     $output_value = '<ul>';
                     foreach ($value as $choice) {
                      $output_value .= '<li>' . $choice . '</li>';
                     }
                     $output_value .= '</ul>';
                  } else {
                     $output_value = PHP_EOL . " - " . implode(PHP_EOL . " - ", json_decode($value));
                  }
               } else {
                  $output_value = $value;
               }
            } elseif ($question_line['fieldtype'] == 'textarea') {
               if ($CFG_GLPI['use_rich_text']) {
                  $output_value = '<br /><blockquote>' . $value . '</blockquote>';
               } else {
                  $output_value = PHP_EOL . $value;
               }
            }

            if ($GLOBALS['CFG_GLPI']['use_rich_text']) {
               $output .= '<div>';
               $output .= '<b>' . $question_no . ') ' . $question_line['name'] . ' : </b>';
               $output .= $output_value;
               $output .= '</div>';
            } else {
               $output .= $question_no . ') ' . $question_line['name'] . ' : ';
               $output .= $output_value . PHP_EOL . PHP_EOL;
            }
         }
      }

      return $output;
   }

   /**
    * Actions done after the PURGE of the item in the database
    * Delete answers
    *
    * @return nothing
   **/
   public function post_purgeItem()
   {
      global $DB;

      $table = getTableForItemType('PluginFormcreatorAnswer');
      $query = "DELETE FROM `$table` WHERE `plugin_formcreator_formanwers_id` = {$this->getID()};";
      $DB->query($query);

      // If the form was waiting for validation
      if ($this->fields['status'] == 'waiting') {
         // Notify the requester
         NotificationEvent::raiseEvent('plugin_formcreator_deleted', $this);
      }
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   public static function install(Migration $migration)
   {
      global $DB;

      $obj   = new self();
      $table = $obj->getTable();

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create questions table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `name` varchar(255) NOT NULL DEFAULT '',
                     `entities_id` int(11) NOT NULL DEFAULT '0',
                     `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                     `plugin_formcreator_forms_id` int(11) NOT NULL,
                     `requester_id` int(11) NULL,
                     `validator_id` int(11) NULL,
                     `request_date` datetime NOT NULL,
                     `status` enum('waiting', 'refused', 'accepted') NOT NULL DEFAULT 'waiting',
                     `comment` text NULL DEFAULT NULL
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci";
         $DB->query($query) or die ($DB->error());
      } else {
         /**
          * Migration of special chars from previous versions
          *
          * @since 0.85-1.2.3
          */
         $query  = "SELECT `id`, `comment`
                    FROM `$table`";
         $result = $DB->query($query);
         while ($line = $DB->fetch_array($result)) {
            $query_update = "UPDATE `$table` SET
                               `comment` = '" . plugin_formcreator_encode($line['comment']) . "'
                             WHERE `id` = " . $line['id'];
            $DB->query($query_update) or die ($DB->error());
         }

         if (!FieldExists('glpi_plugin_formcreator_formanswers', 'name')) {
            $query_update = 'ALTER TABLE `glpi_plugin_formcreator_formanswers` ADD `name` VARCHAR(255) NOT NULL AFTER `id`;';
            $DB->query($query_update) or die ($DB->error());
         }

         // valdiator_id should not be set for waiting form answers
         $query = "UPDATE glpi_plugin_formcreator_formanswers
               SET `validator_id` = '0' WHERE `status`='waiting'";
         $DB->query($query);
      }

      // Create standard search options
      $query = "INSERT IGNORE INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
               (NULL, '" . __CLASS__ . "', 2, 2, 0),
               (NULL, '" . __CLASS__ . "', 3, 3, 0),
               (NULL, '" . __CLASS__ . "', 4, 4, 0),
               (NULL, '" . __CLASS__ . "', 5, 5, 0),
               (NULL, '" . __CLASS__ . "', 6, 6, 0);";
      $DB->query($query) or die ($DB->error());

      return true;
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   public static function uninstall()
   {
      global $DB;

      $obj = new self();
      $DB->query('DROP TABLE IF EXISTS `' . $obj->getTable() . '`');

      // Delete logs of the plugin
      $DB->query("DELETE FROM `glpi_logs` WHERE itemtype = '" . __CLASS__ . "'");

      $displayPreference = new DisplayPreference();
      $displayPreference->deleteByCriteria(array('itemtype' => 'PluginFormcreatorFormanswer'));
      
      return true;
   }
}
