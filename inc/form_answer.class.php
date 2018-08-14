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

class PluginFormcreatorForm_Answer extends CommonDBChild
{
   public $dohistory  = true;
   public $usenotepad = true;
   public $usenotepadrights = true;

   static public $itemtype = "PluginFormcreatorForm";
   static public $items_id = "plugin_formcreator_forms_id";

   const SOPTION_ANSWER = 900000;

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

   public function canViewItem() {
      global $DB;

      if (!isset($_SESSION['glpiID'])) {
         return false;
      }

      if (Session::haveRight('entity', UPDATE)) {
         return true;
      }

      if ($_SESSION['glpiID'] == $this->getField('requester_id')) {
         return true;
      }

      $request = [
         'SELECT' => PluginFormcreatorForm_Validator::getTable() . '.*',
         'FROM' => $this::getTable(),
         'INNER JOIN' => [
            PluginFormcreatorForm::getTable() => [
               'FKEY' => [
                  PluginFormcreatorForm::getTable() => PluginFormcreatorForm::getIndexName(),
                  $this::getTable() => PluginFormcreatorForm::getForeignKeyField(),
               ],
            ],
            PluginFormcreatorForm_Validator::getTable() => [
               'FKEY' => [
                  PluginFormcreatorForm::getTable() => PluginFormcreatorForm::getIndexName(),
                  PluginFormcreatorForm_Validator::getTable() => PluginFormcreatorForm::getForeignKeyField()
               ]
            ]
         ],
         'WHERE' => [$this::getTable() . '.id' => $this->getID()],
      ];
      foreach ($DB->request($request) as $row) {
         if ($row['itemtype'] == User::class) {
            if ($_SESSION['glpiID'] == $row['items_id']) {
               return true;
            }
         } else {
            $groupUser = new Group_User();
            $groups = $groupUser->getUserGroups($_SESSION['glpiID']);
            foreach ($groups as $group) {
               if ($row['items_id'] == $group['id']) {
                  return true;
               }
            }
         }
      }

      return false;
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Form answer', 'Form answers', $nb, 'formcreator');
   }

   /**
    * Define search options for forms
    *
    * @return Array Array of fields to show in search engine and options for each fields
    */
   public function getSearchOptionsNew() {
      return $this->rawSearchOptions();
   }

   public function rawSearchOptions() {
      $tab = [];

      $display_for_form = isset($_SESSION['formcreator']['form_search_answers'])
                          && $_SESSION['formcreator']['form_search_answers'];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Characteristics')
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this::getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'searchtype'         => 'contains',
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => 'glpi_plugin_formcreator_forms',
         'field'              => 'name',
         'name'               => __('Form', 'formcreator'),
         'searchtype'         => 'contains',
         'datatype'           => 'string',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'name'               => __('Requester'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'linkfield'          => 'requester_id'
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'name'               => __('Form approver', 'formcreator'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'linkfield'          => 'users_id_validator'
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this::getTable(),
         'field'              => 'request_date',
         'name'               => __('Creation date'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'            => '7',
         'table'         => getTableForItemType('Group'),
         'field'         => 'completename',
         'name'          => __('Form approver group', 'formcreator'),
         'datatype'      => 'itemlink',
         'massiveaction' => false,
         'linkfield'     => 'groups_id_validator',
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this::getTable(),
         'field'              => 'status',
         'name'               => __('Status'),
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'datatype'           => 'specific',
         'massiveaction'      => false
      ];

      if ($display_for_form) {
         $optindex = self::SOPTION_ANSWER;
         $question = new PluginFormcreatorQuestion;
         $questions = $question->getQuestionsFromForm($_SESSION['formcreator']['form_search_answers']);

         foreach ($questions as $current_question) {
            $questions_id = $current_question->getID();
            $tab[] = [
               'id'            => $optindex,
               'table'         => PluginFormcreatorAnswer::getTable(),
               'field'         => 'answer',
               'name'          => $current_question->getField('name'),
               'datatype'      => 'string',
               'massiveaction' => false,
               'nosearch'      => false,
               'joinparams'    => [
                  'jointype'  => 'child',
                  'condition' => "AND NEWTABLE.`plugin_formcreator_questions_id` = $questions_id",
               ]
            ];

            $optindex++;
         }
      }

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
   public static function getSpecificValueToDisplay($field, $values, array $options=[]) {
      global $CFG_GLPI;

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'status' :
            $output = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/' . $values[$field] . '.png"
                         alt="' . __($values[$field], 'formcreator') . '" title="' . __($values[$field], 'formcreator') . '" /> ';
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
   public static function getSpecificValueToSelect($field, $name='', $values='', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'status' :
            $elements = [
               'waiting'   => __('waiting', 'formcreator'),
               'accepted'  => __('accepted', 'formcreator'),
               'refused'   => __('refused', 'formcreator'),
            ];
            $output = Dropdown::showFromArray($name, $elements, ['display' => false, 'value' => $values[$field]]);
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
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item instanceof PluginFormcreatorForm) {
         self::showForForm($item);
      } else {
         $item->showForm($item->fields['id']);
      }
   }

   public function defineTabs($options = []) {
      $ong = [];
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
   public function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item instanceof PluginFormcreatorForm) {
         $dbUtils = new DbUtils();
         $number = $dbUtils->countElementsInTableForMyEntities(
            static::getTable(),
            [
               self::$items_id => $item->getID()
            ]
         );
         return self::createTabEntry(self::getTypeName($number), $number);
      } else {
         return $this->getTypeName();
      }
   }

   static function showForForm(PluginFormcreatorForm $form, $params = []) {
      // set a session var to tweak search results
      $_SESSION['formcreator']['form_search_answers'] = $form->getID();

      // prepare params for search
      $item          = new PluginFormcreatorForm_Answer();
      $searchOptions      = $item->getSearchOptionsNew();
      $filteredOptions = [];
      foreach ($searchOptions as $value) {
         if (is_numeric($value['id']) && $value['id'] <= 7) {
            $filteredOptions[$value['id']] = $value;
         }
      }
      $searchOptions = $filteredOptions;
      $sopt_keys     = array_keys($searchOptions);

      $forcedisplay  = array_combine($sopt_keys, $sopt_keys);

      // do search
      $params = Search::manageParams(__CLASS__, $params);
      $data   = Search::prepareDatasForSearch(__CLASS__, $params, $forcedisplay);
      Search::constructSQL($data);
      Search::constructDatas($data);
      Search::displayDatas($data);

      // remove previous session var (restore default view)
      unset($_SESSION['formcreator']['form_search_answers']);
   }

   /**
    * Can the current user validate the form ?
    *
    * @param PluginFormcreatorForm $form
    */
   public function canValidate($form, CommonDBTM $form_answer) {
      $userId = $_SESSION['glpiID'];
      $formId = $form->getID();

      if ($form->fields['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_USER) {
         $canValidate = ($userId == $form_answer->getField('users_id_validator'));
      } else if ($form->fields['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_GROUP) {
         // Check the user is member of at least one validator group for the form answers
         if (Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
             || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST)) {
            $table_form_validator = PluginFormcreatorForm_Validator::getTable();
            $condition = "`glpi_groups`.`id` IN (
            SELECT `items_id`
            FROM `$table_form_validator`
            WHERE `itemtype` = 'Group' AND `plugin_formcreator_forms_id` = '$formId'
            )";
            $groupList = Group_User::getUserGroups($userId, $condition);
            $canValidate = (count($groupList) > 0);
         } else {
            $canValidate = false;
         }
      } else {
         $canValidate = false;
      }

      return $canValidate;
   }

   public function showForm($ID, $options = []) {
      global $DB;

      if (!isset($ID) || !$this->getFromDB($ID)) {
         Html::displayNotFoundError();
      }
      $options = ['canedit' => false];

      // Print css media
      echo Html::css(FORMCREATOR_ROOTDOC."/css/print_form_answer.css", ['media' => 'print']);

      // start form
      echo "<div class='form_answer'>";
      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      $form = new PluginFormcreatorForm();
      $formId = $this->fields['plugin_formcreator_forms_id'];
      $form->getFromDB($formId);

      $canEdit = $this->fields['status'] == 'refused'
                 && $_SESSION['glpiID'] == $this->fields['requester_id'];
      $canValidate = $this->canValidate($form, $this);

      echo '<tr><td colspan="4" class="formcreator_form form_horizontal">';

      // Form Header
      echo '<div class="form_header">';
      echo "<h1>";
      echo $form->fields['name']."&nbsp;";
      echo "<img src='".FORMCREATOR_ROOTDOC."/pics/print.png' class='pointer print_button'
                 title='".__("Print this form", 'formcreator')."' onclick='window.print();'>";
      echo "</h1>";
      if (!empty($form->fields['content'])) {
         echo html_entity_decode($form->fields['content']);
      }
      echo '</div>';

      if ($this->fields['status'] == 'refused') {
         echo '<div class="refused_header">';
         echo '<div>' . nl2br($this->fields['comment']) . '</div>';
         echo '</div>';
      } else if ($this->fields['status'] == 'accepted') {
         echo '<div class="accepted_header">';
         echo '<div>';
         if (!empty($this->fields['comment'])) {
            echo nl2br($this->fields['comment']);
         } else if ($form->fields['validation_required']) {
            echo __('Form accepted by validator.', 'formcreator');
         } else {
            echo __('Form successfully saved.', 'formcreator');
         }
         echo '</div>';
         echo '</div>';
      }

      echo '<div class="form_section">';

      // Display all fields of the section
      $query_questions = "SELECT `sections`.`name` AS `section_name`,
                                 `questions`.*,
                                 `answers`.`answer`
                          FROM `glpi_plugin_formcreator_questions` AS `questions`
                          LEFT JOIN `glpi_plugin_formcreator_answers` AS `answers`
                            ON `answers`.`plugin_formcreator_questions_id` = `questions`.`id`
                            AND `answers`.`plugin_formcreator_forms_answers_id` = '$ID'
                          INNER JOIN `glpi_plugin_formcreator_sections` AS `sections`
                            ON `questions`.`plugin_formcreator_sections_id` = `sections`.`id`
                            AND `plugin_formcreator_forms_id` = " . $form->getID() . "
                          GROUP BY `questions`.`id`
                          ORDER BY `sections`.`order` ASC,
                                   `sections`.`id` ASC,
                                   `questions`.`order` ASC";
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
      echo Html::scriptBlock('$(function() {
         formcreatorShowFields();
      })');

      // Display submit button
      if (($this->fields['status'] == 'refused') && ($_SESSION['glpiID'] == $this->fields['requester_id'])) {
         echo '<div class="form-group line'.(($questionsCount + 1) % 2).'">';
         echo '<div class="center">';
         echo '<input type="submit" name="save_formanswer" class="submit_button" value="'.__('Save').'" />';
         echo '</div>';
         echo '</div>';

         // Display validation form
      } else if (($this->fields['status'] == 'waiting') && $canValidate) {
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
      echo "</div>"; // .form_answer
      return true;
   }

   /**
    * Prepare input data for adding the question
    * Check fields values and get the order for the new question
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
   **/
   public function prepareInputForAdd($input) {
      $form = new PluginFormcreatorForm();
      $form->getFromDB($input['plugin_formcreator_forms_id']);
      $input['name'] = $form->getName();

      return $input;
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
   **/
   public function prepareInputForUpdate($input) {
      return $input;
   }

   /**
    * Actions done before deleting an item. In case of failure, prevents
    * actual deletion of the item
    *
    * @return boolean true if pre_delete actions succeeded, false if not
    */
   public function pre_deleteItem() {
      $issue = new PluginFormcreatorIssue();
      $issue->deleteByCriteria([
         'original_id'     => $this->getID(),
         'sub_itemtype'    => self::getType(),
      ]);

      return true;
   }


   public function saveAnswers($data) {
      $form   = new PluginFormcreatorForm();
      $answer = new PluginFormcreatorAnswer();

      $form->getFromDB($data['formcreator_form']);

      $formanswers_id = isset($data['id'])
                        ?intval($data['id'])
                        :-1;

      $question = new PluginFormcreatorQuestion();
      $questions = $question->getQuestionsFromForm($data['formcreator_form']);

      // Update form answers
      if (isset($data['save_formanswer'])) {
         $status = $data['status'];
         $this->update([
            'id'                          => $formanswers_id,
            'status'                      => $status,
            'comment'                     => isset($data['comment']) ? $data['comment'] : 'NULL'
         ]);

         // Update questions answers
         if ($status == 'waiting') {
            foreach ($questions as $question) {
               // unset the answer value
               $answer_value = $this->transformAnswerValue($question, $data['formcreator_field_' . $question->getID()]);

               // $answer_value may be still null if the field type is file and no file was uploaded
               if ($answer_value !== null) {
                  // Update the answer to the question
                  $questionId = $question->getID();
                  $answer = new PluginFormcreatorAnswer();
                  $answer->getFromDBByCrit([
                    'AND' => [
                      'plugin_formcreator_forms_answers_id' => $formanswers_id,
                      'plugin_formcreator_questions_id'     => $questionId
                    ]
                  ]);
                  $answer->update([
                     'id'     => $answer->getID(),
                     'answer' => $answer_value,
                  ], 0);
               }
            }
         }
         $is_newFormAnswer = false;

      } else {
         // Create new form answer object

         // Does the form need to be validated?
         if ($form->fields['validation_required']) {
            $status = 'waiting';
         } else {
            $status = 'accepted';
         }

         if ($form->getField('validation_required') == 1) {
            $usersIdValidator = isset($data['formcreator_validator'])
                                ? $data['formcreator_validator']
                                : 0;
         } else {
            $usersIdValidator = 0;
         }

         if ($form->getField('validation_required') == 2) {
            $groupIdValidator = isset($data['formcreator_validator'])
                                ? $data['formcreator_validator']
                                : 0;
         } else {
            $groupIdValidator = 0;
         }
         $id = $this->add([
            'entities_id'                 => isset($_SESSION['glpiactive_entity'])
                                                ? $_SESSION['glpiactive_entity']
                                                : $form->fields['entities_id'],
            'is_recursive'                => $form->fields['is_recursive'],
            'plugin_formcreator_forms_id' => $data['formcreator_form'],
            'requester_id'                => isset($_SESSION['glpiID'])
                                                ? $_SESSION['glpiID']
                                                : 0,
            'users_id_validator'          => $usersIdValidator,
            'groups_id_validator'         => $groupIdValidator,
            'status'                      => $status,
            'request_date'                => date('Y-m-d H:i:s'),
         ]);

         // Save questions answers
         foreach ($questions as $question) {
            // unset the answer value
            $answer_value = $this->transformAnswerValue($question, $data['formcreator_field_' . $question->getID()]);

            if ($answer_value !== null) {
               // Save the answer to the question
               $answer->add([
                  'plugin_formcreator_forms_answers_id'  => $id,
                  'plugin_formcreator_questions_id'      => $question->getID(),
                  'answer'                               => $answer_value,
               ], [], 0);
            }
         }
         $is_newFormAnswer = true;
      }

      if ($form->fields['validation_required'] || ($status == 'accepted')) {
         switch ($status) {
            case 'waiting' :
               // Notify the requester
               NotificationEvent::raiseEvent('plugin_formcreator_form_created', $this);
               // Notify the validator
               NotificationEvent::raiseEvent('plugin_formcreator_need_validation', $this);
               break;
            case 'refused' :
               // Notify the requester
               NotificationEvent::raiseEvent('plugin_formcreator_refused', $this);
               break;
            case 'accepted' :
               // Notify the requester
               if ($form->fields['validation_required']) {
                  NotificationEvent::raiseEvent('plugin_formcreator_accepted', $this);
               } else {
                  NotificationEvent::raiseEvent('plugin_formcreator_form_created', $this);
               }

               if (!$this->generateTarget()) {
                  Session::addMessageAfterRedirect(__('Cannot generate targets!', 'formcreator'), true, ERROR);

                  $this->update([
                     'id'     => $this->getID(),
                     'status' => 'waiting',
                  ]);
                  return false;
               }
               break;
         }

         // Update issues table
         $issue = new PluginFormcreatorIssue();
         $formAnswerId = $this->getID();
         if ($status != 'refused') {

            // If cannot get itemTicket from DB it happens either
            // when no item exist
            // when several rows matches
            // Both are processed the same way
            $itemTicket = new Item_Ticket();
            $rows = $itemTicket->find("`itemtype` = 'PluginFormcreatorForm_Answer' AND `items_id` = '$formAnswerId'");
            if (count($rows) != 1) {
               if ($is_newFormAnswer) {
                  // This is a new answer for the form. Create an issue
                  $issue->add([
                     'original_id'     => $id,
                     'sub_itemtype'    => 'PluginFormcreatorForm_Answer',
                     'name'            => addslashes($this->fields['name']),
                     'status'          => $status,
                     'date_creation'   => $this->fields['request_date'],
                     'date_mod'        => $this->fields['request_date'],
                     'entities_id'     => $this->fields['entities_id'],
                     'is_recursive'    => $this->fields['is_recursive'],
                     'requester_id'    => $this->fields['requester_id'],
                     'validator_id'    => $this->fields['users_id_validator'],
                     'comment'         => '',
                  ]);
               } else {
                  $issue->getFromDBByCrit([
                     'AND' => [
                       'sub_itemtype' => PluginFormcreatorForm_Answer::class,
                       'original_id'  => $formAnswerId
                     ]
                  ]);
                  $id = $this->getID();
                  $issue->update([
                     'id'              => $issue->getID(),
                     'original_id'     => $id,
                     'sub_itemtype'    => 'PluginFormcreatorForm_Answer',
                     'name'            => addslashes($this->fields['name']),
                     'status'          => $status,
                     'date_creation'   => $this->fields['request_date'],
                     'date_mod'        => $this->fields['request_date'],
                     'entities_id'     => $this->fields['entities_id'],
                     'is_recursive'    => $this->fields['is_recursive'],
                     'requester_id'    => $this->fields['requester_id'],
                     'validator_id'    => $this->fields['users_id_validator'],
                     'comment'         => '',
                  ]);
               }
            } else {
               $ticket = new Ticket();
               reset($rows);
               $itemTicket->getFromDB(key($rows));
               $ticket->getFromDB($itemTicket->getField('tickets_id'));
               $ticketId = $ticket->getID();
               if ($is_newFormAnswer) {
                  $issue->add([
                     'original_id'     => $ticketId,
                     'sub_itemtype'    => 'Ticket',
                     'name'            => addslashes($ticket->getField('name')),
                     'status'          => $ticket->getField('status'),
                     'date_creation'   => $ticket->getField('date'),
                     'date_mod'        => $ticket->getField('date_mod'),
                     'entities_id'     => $ticket->getField('entities_id'),
                     'is_recursive'    => '0',
                     'requester_id'    => $ticket->getField('users_id_recipient'),
                     'validator_id'    => '',
                     'comment'         => addslashes($ticket->getField('content')),
                  ]);
               } else {
                  $issue->getFromDBByCrit([
                    'AND' => [
                      'sub_itemtype' => PluginFormcreatorForm_Answer::class,
                      'original_id'  => $formAnswerId
                    ]
                  ]);
                  $issue->update([
                     'id'              => $issue->getID(),
                     'original_id'     => $ticketId,
                     'sub_itemtype'    => 'Ticket',
                     'name'            => addslashes($ticket->getField('name')),
                     'status'          => $ticket->getField('status'),
                     'date_creation'   => $ticket->getField('date'),
                     'date_mod'        => $ticket->getField('date_mod'),
                     'entities_id'     => $ticket->getField('entities_id'),
                     'is_recursive'    => '0',
                     'requester_id'    => $ticket->getField('users_id_recipient'),
                     'validator_id'    => '',
                     'comment'         => addslashes($ticket->getField('content')),
                  ]);
               }
            }
         } else {
            $issue->getFromDBByCrit([
              'AND' => [
                'sub_itemtype' => PluginFormcreatorForm_Answer::class,
                'original_id'  => $formAnswerId
              ]
            ]);
            $issue->update([
               'id'              => $issue->getID(),
               'sub_itemtype'    => 'PluginFormcreatorForm_Answer',
               'original_id'     => $formAnswerId,
               'status'          => $status,
            ]);
         }
      }

      Session::addMessageAfterRedirect(__('The form has been successfully saved!', 'formcreator'), true, INFO);
   }

   /**
    *
    * @param array|string $value
    * @return null|string
    */
   private function transformAnswerValue(PluginFormcreatorQuestion $question, $value = null) {
      global $CFG_GLPI;

      // unset the answer value
      $answer_value = null;
      $form = $question->getForm();

      if ($question->getField('fieldtype') != 'file') {
         // If the answer is set, check if it is an array (then implode id).
         if (isset($value)) {
            // If the answer is set, check if it is an array (then implode id).
            if ($value !== null) {
               if ($question->getField('fieldtype') != 'textarea') {
                  $answer_value = $value;
                  if (is_array(json_decode($answer_value, JSON_UNESCAPED_UNICODE))) {
                     $answer_value = json_decode($answer_value);
                     foreach ($answer_value as $key => $value) {
                        $answer_value[$key] = $value;
                     }
                     $answer_value = json_encode($answer_value, JSON_UNESCAPED_UNICODE);
                  } else {
                     $answer_value = str_replace('\\r\\n', '\n', $answer_value);
                  }
               } else {
                  if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text']) {
                     $answer_value = html_entity_decode($value);
                  } else {
                     $answer_value = $value;
                  }
               }
            } else {
               $answer_value = '';
            }
         } else {
            $answer_value = '';
         }
      } else if (is_array($_POST['_formcreator_field_' . $question->getID()])
                 && count($_POST['_formcreator_field_' . $question->getID()]) === 1) {
         $file = current($_POST['_formcreator_field_' . $question->getID()]);
         if (is_file(GLPI_TMP_DIR . '/' . $file)) {
            $answer_value = $this->saveDocument($form, $question, $file);
         }
      }

      return $answer_value;
   }

   /**
    * Save an uploaded file into a document object, link it to the answers
    * and returns the document ID
    * @param PluginFormcreatorForm $form
    * @param PluginFormcreatorQuestion $question
    * @param array $file                         an item from $_FILES array
    *
    * @return integer|NULL
    */
   private function saveDocument(PluginFormcreatorForm $form, PluginFormcreatorQuestion $question, $file) {
      global $DB;

      $doc                        = new Document();

      $file_data                 = [];
      $file_data["name"]         = Toolbox::addslashes_deep($form->getField('name'). ' - ' . $question->getField('name'));
      $file_data["entities_id"]  = isset($_SESSION['glpiactive_entity'])
                                    ? $_SESSION['glpiactive_entity']
                                    : $form->getField('entities_id');
      $file_data["is_recursive"] = $form->getField('is_recursive');
      Document::moveDocument($file_data, $file);

      if ($docID = $doc->add($file_data)) {
         $docID    = intval($docID);
         $table    = Document::getTable();
         $filename = addslashes($file);
         $query    = "UPDATE `$table` SET `filename` = '$filename'
                      WHERE `id` = '$docID'";
         $DB->query($query);

         $docItem = new Document_Item();
         $docItem->add([
            'documents_id' => $docID,
            'itemtype'     => __CLASS__,
            'items_id'     => $this->getID(),
         ]);
         return $docID;
      }

      return null;
   }

   /**
    * Mark answers of a form as refused
    *
    * @param array $data
    *
    * @return boolean
    */
   public function refuseAnswers($data) {
      $data['plugin_formcreator_forms_id'] = intval($data['formcreator_form']);
      $data['status']                      = 'refused';
      $data['save_formanswer']             = true;

      $form   = new PluginFormcreatorForm();
      $form->getFromDB($data['plugin_formcreator_forms_id']);

      if (!$this->canValidate($form, $this)) {
         Session::addMessageAfterRedirect(__('You are not the validator of these answers', 'formcreator'), true, ERROR);
         return false;
      }

      return $this->saveAnswers($data);
   }

   /**
    * Mark answers of a form as accepted
    *
    * @param array $data
    *
    * @return boolean
    */
   public function acceptAnswers($data) {
      $data['plugin_formcreator_forms_id'] = intval($data['formcreator_form']);
      $data['status']                      = 'accepted';
      $data['save_formanswer']             = true;

      $form   = new PluginFormcreatorForm();
      $form->getFromDB($data['plugin_formcreator_forms_id']);

      if (!$this->canValidate($form, $this)) {
         Session::addMessageAfterRedirect(__('You are not the validator of these answers', 'formcreator'), true, ERROR);
         return false;
      }

      return $this->saveAnswers($data);
   }

   /**
    * Generates all targets for the answers
    */
   public function generateTarget() {
      global $CFG_GLPI;

      $success = true;

      // Get all targets
      $target_class  = new PluginFormcreatorTarget();
      $found_targets = $target_class->find('plugin_formcreator_forms_id = ' . $this->fields['plugin_formcreator_forms_id']);

      $CFG_GLPI['plugin_formcreator_disable_hook_create_ticket'] = '1';
      // Generate targets
      $generatedTargets = new PluginFormcreatorComposite(new PluginFormcreatorItem_TargetTicket(), new Ticket_Ticket());
      foreach ($found_targets as $target) {
         $targetObject = new $target['itemtype'];
         $targetObject->getFromDB($target['items_id']);
         $generatedTarget = $targetObject->save($this);
         if ($generatedTarget === false) {
            $success = false;
            break;
         }
         // Map [itemtype of the target] [item ID of the target] = ID of the generated target
         $generatedTargets->addTarget($targetObject, $generatedTarget);
      }
      $generatedTargets->buildCompositeRelations();

      Session::addMessageAfterRedirect(__('The form has been successfully saved!', 'formcreator'), true, INFO);
      unset($CFG_GLPI['plugin_formcreator_disable_hook_create_ticket']);
      return $success;
   }

   /**
    * Gets answers of all fields of a form answer
    *
    * @param integer $formAnswerId
    *
    * @return array
    */
   public function getAnswers($formAnswerId) {
      $answer = new PluginFormcreatorAnswer();
      $answers = $answer->find("`plugin_formcreator_forms_answers_id` = '$formAnswerId'");
      $answers_values = [];
      foreach ($answers as $found_answer) {
         $answers_values['formcreator_field_' . $found_answer['plugin_formcreator_questions_id']] = stripslashes($found_answer['answer']);
      }
      return $answers_values;
   }

   /**
    * Gets the associated form
    * @return PluginFormcreatorForm|null the form used to create this set of answers
    */
   public function getForm() {
      $form = new PluginFormcreatorForm();
      $form->getFromDB($this->fields[$form::getForeignKeyField()]);

      if ($form->isNewItem()) {
         return null;
      }
      return $form;
   }

   /**
    * Get entire form to be inserted into a target content
    *
    * @return String Full form questions and answers to be print
    */
   public function getFullForm() {
      global $CFG_GLPI, $DB;

      $question_no = 0;
      $output      = '';
      $eol = '\r\n';

      if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text']) {
         $output .= '<h1>' . __('Form data', 'formcreator') . '</h1>';
      } else {
         $output .= __('Form data', 'formcreator') . $eol;
         $output .= '=================';
         $output .= $eol . $eol;
      }

      // retrieve answers
      $answers_values = $this->getAnswers($this->getID());

      // TODO: code very close to PluginFormcreatorTargetBase::parseTags() (factorizable ?)
      // compute all questions
      $query_questions = "SELECT sections.`name` as section_name,
                                 questions.*
                          FROM `glpi_plugin_formcreator_questions` AS questions
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
            if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text']) {
               $output .= '<h2>' . Toolbox::addslashes_deep($question_line['section_name']) . '</h2>';
            } else {
               $output .= $eol . Toolbox::addslashes_deep($question_line['section_name']) . $eol;
               $output .= '---------------------------------';
               $output .= $eol;
            }
            $last_section = $question_line['section_name'];
         }

         // Don't save tags in "full form"
         if ($question_line['fieldtype'] == 'tag') {
            continue;
         }

         if (!PluginFormcreatorFields::isVisible($question_line['id'], $answers_values)) {
            continue;
         }

         if ($question_line['fieldtype'] != 'description') {
            $question_no++;
            if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text']) {
               $output .= '<div>';
               $output .= '<b>' . $question_no . ') ##question_' . $question_line['id'] . '## : </b>';
               $output .= '##answer_' . $question_line['id'] . '##';
               $output .= '</div>';
            } else {
               $output .= $question_no . ') ##question_' . $question_line['id'] . '## : ';
               $output .= '##answer_' . $question_line['id'] . '##' . $eol . $eol;
            }
         }
      }

      return $output;
   }

   /**
    * Actions done after the PURGE of the item in the database
    * Delete answers
    *
    * @return void
    */
   public function post_purgeItem() {
      global $DB;

      $table = getTableForItemType('PluginFormcreatorAnswer');
      $query = "DELETE FROM `$table` WHERE `plugin_formcreator_forms_answers_id` = {$this->getID()};";
      $DB->query($query);

      // If the form was waiting for validation
      if ($this->fields['status'] == 'waiting') {
         // Notify the requester
         NotificationEvent::raiseEvent('plugin_formcreator_deleted', $this);
      }
   }
}
