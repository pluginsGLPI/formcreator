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
 * @link      http://plugins.glpi-project.org/#/plugin/formcreatorp@
 * ---------------------------------------------------------------------
 */

use Glpi\Toolbox\Sanitizer;
use GlpiPlugin\Formcreator\Field\DropdownField;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorFormAnswer extends CommonDBTM
{
   public $dohistory  = true;
   public $usenotepad = true;
   public $usenotepadrights = true;
   protected static $showTitleInNavigationHeader = true;

   /**
    * Generated targets after creation of a form answer
    * Populated only after creation of a FormAnswer in DB with add() method
    *
    * @var array
    */
   public $targetList = [];

   const SOPTION_ANSWER = 900000;

   // Values choosen to not conflict with status of ticket constants
   // @see PluginFormcreatorIssue::getNewStatusArray
   const STATUS_WAITING = 101;
   const STATUS_REFUSED = 102;
   const STATUS_ACCEPTED = 103;

   /** @var null|PluginFormcreatorAbstractField[] fields of the form answers */
   private $questionFields = null;

   /** @var boolean True if the answers are loaded and are valid */
   private $isAnswersValid = false;

   /** @var PluginFormcreatorForm $form The form attached to the object */
   private $form = null;

   /** @var array $answers set of answers */
   private array $answers = [];

   public static function getStatuses() {
      return [
         self::STATUS_WAITING  => __('Waiting', 'formcreator'),
         self::STATUS_REFUSED  => __('Refused', 'formcreator'),
         self::STATUS_ACCEPTED => __('Accepted', 'formcreator'),
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

   public function canViewItem() {
      global $DB;

      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         $advFormAnswer = new PluginAdvformFormanswer();
         $advFormAnswer->getFromDB($this->getID());
         return $advFormAnswer->canViewItem();
      }

      $currentUser = Session::getLoginUserID();

      if ($currentUser === false) {
         return false;
      }

      if (Session::haveRight('entity', UPDATE)) {
         return true;
      }

      if ($currentUser == $this->fields['requester_id']) {
         return true;
      }

      if ($currentUser == $this->fields['users_id_validator']) {
         return true;
      }

      $groupUser = new Group_User();
      $groups = $groupUser->getUserGroups($currentUser);
      if (in_array($this->fields['users_id_validator'], $groups)) {
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
            if ($currentUser == $row['items_id']) {
               return true;
            }
         } else {
            foreach ($groups as $group) {
               if ($group['id'] == $row['items_id']) {
                  return true;
               }
            }
         }
      }

      return false;
   }

   public static function canPurge() {
      return true;
   }

   public function canPurgeItem() {
      return Session::haveRight('entity', UPDATE);
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

   public function rawSearchOptions() {
      global $DB;

      $tab = parent::rawSearchOptions();

      $display_for_form = isset($_SESSION['formcreator']['form_search_answers'])
                          && $_SESSION['formcreator']['form_search_answers'];

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
         'name'               => PluginFormcreatorForm::getTypeName(1),
         'searchtype'         => 'contains',
         'datatype'           => 'string',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'name'               => _n('Requester', 'Requesters', 1),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'linkfield'          => 'requester_id'
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => User::getTable(),
         'field'              => 'name',
         'name'               => __('Form approver', 'formcreator'),
         'datatype'           => 'itemlink',
         'forcegroupby'       => true,
         'massiveaction'      => false,
         'linkfield'          => 'users_id_validator',
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
         'table'         => Group::getTable(),
         'field'         => 'completename',
         'name'          => __('Form approver group', 'formcreator'),
         'datatype'      => 'itemlink',
         'forcegroupby'  => true,
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

      $tab[] = [
         'id' => '9',
         'table' => PluginFormcreatorForm::getTable(),
         'field' => 'id',
         'name' => __('ID'),
         'searchtype' => 'contains',
         'datatype' => 'integer',
         'massiveaction' => false,
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
               'name'          => $current_question->fields['name'],
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
   public static function getSpecificValueToDisplay($field, $values, array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'status' :
            if (!isAPI()) {
               switch ($values[$field]) {
                  case PluginFormcreatorFormAnswer::STATUS_WAITING:
                     $status = CommonITILObject::WAITING;
                     break;
                  case PluginFormcreatorFormAnswer::STATUS_REFUSED:
                     $status = Change::REFUSED;
                     break;
                  case PluginFormcreatorFormAnswer::STATUS_ACCEPTED:
                     $status = CommonITILObject::ACCEPTED;
                     break;
                  default:
                     $status = $values[$field];
               }
               $status = CommonITILObject::getStatusClass($status);
               return '<i class="'.$status.'"></i>';
            }
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
    */
   public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'status' :
            $elements = self::getStatuses();
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
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
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
         $this->addStandardTab(Ticket::class, $ong, $options);
         $this->addStandardTab(Document_Item::class, $ong, $options);
         $this->addStandardTab(Notepad::class, $ong, $options);
         $this->addStandardTab(Log::class, $ong, $options);
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
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item instanceof PluginFormcreatorForm) {
         $dbUtils = new DbUtils();
         $formFk = PluginFormcreatorForm::getForeignKeyField();
         $number = $dbUtils->countElementsInTableForMyEntities(
            static::getTable(),
            [
               $formFk => $item->getID(),
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
      $item            = PluginFormcreatorCommon::getFormAnswer();
      $searchOptions   = $item->rawSearchOptions();
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
      $params = Search::manageParams(__CLASS__, $params, false);
      $data   = Search::prepareDatasForSearch(__CLASS__, $params, $forcedisplay);
      Search::constructSQL($data);
      Search::constructData($data);
      Search::displayData($data);

      // remove previous session var (restore default view)
      unset($_SESSION['formcreator']['form_search_answers']);
   }

    /**
    * Can the current user validate the form ?
    */
   public function canValidate(): bool {
      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         $formAnswer = new PluginAdvformFormAnswer();
         $formAnswer->getFromDB($this->getID());
         return $formAnswer->canValidate($this);
      }

      if (!PluginFormcreatorCommon::canValidate()) {
         return false;
      }

      $form = $this->getForm();
      switch ($form->fields['validation_required']) {
         case PluginFormcreatorForm_Validator::VALIDATION_USER:
            return (Session::getLoginUserID() == $this->fields['users_id_validator']);
            break;

         case PluginFormcreatorForm_Validator::VALIDATION_GROUP:
            $groupList = Group_User::getUserGroups(
               Session::getLoginUserID(),
               ['glpi_groups.id' => $this->fields['groups_id_validator']]
            );
            return (count($groupList) > 0);
            break;
      }

      return false;
   }

   public function showForm($ID, $options = []) {
      if (!isset($ID) || !$this->getFromDB($ID)) {
         Html::displayNotFoundError();
      }
      $options['canedit'] = false;

      // Print css media
      echo Html::css(FORMCREATOR_ROOTDOC . "/css/print_form.css", ['media' => 'print']);

      $style = "<style>";
      // force colums width
      $width_percent = 100 / PluginFormcreatorSection::COLUMNS;
      for ($i = 0; $i < PluginFormcreatorSection::COLUMNS; $i++) {
         $width = ($i+1) * $width_percent;
         $style.= '
         #plugin_formcreator_form.plugin_formcreator_form [data-itemtype = "PluginFormcreatorQuestion"][gs-w="' . ($i+1) . '"],
         #plugin_formcreator_form.plugin_formcreator_form .plugin_formcreator_gap[gs-w="' . ($i+1) . '"]
         {
            min-width: ' . $width_percent . '%;
            width: ' . $width . '%;
         }
         ';
      }
      $style.= "</style>";
      echo $style;

      $formUrl = static::getFormURL(); // May be called from iinherited classes
      $formName = 'plugin_formcreator_form';
      echo '<form name="' . $formName . '" method="post" role="form" enctype="multipart/form-data"'
      . ' class="plugin_formcreator_form"'
      . ' action="' . $formUrl . '"'
      . ' id="plugin_formcreator_form"'
      . '>';

      $form = $this->getForm();

      // Edit mode for validator
      $editMode = !isset($options['edit']) ? false : ($options['edit'] != '0');

      // form title
      if (version_compare(GLPI_VERSION, '10.0.3') < 0) {
         echo "<h1 class='form-title'>";
         echo $this->fields['name'] . "&nbsp;";
         echo '<i class="pointer print_button fas fa-print" title="' . __("Print this form", 'formcreator') . '" onclick="window.print();"></i>';
         echo '</h1>';
      }

      // Form Header
      if (!empty($form->fields['content']) || !empty($form->getExtraHeader())) {
         echo '<div class="form_header">';
         echo html_entity_decode($form->fields['content']);
         echo html_entity_decode($form->getExtraHeader());
         echo '</div>';
      }

      // Validation status and comment
      if ($this->fields['status'] == self::STATUS_REFUSED) {
         echo '<div class="refused_header">';
         echo '<strong>' . __('Comment', 'formcreator') . '</strong>';
         echo '<div>' . nl2br($this->fields['comment']) .'</div>';
         echo '</div>';
      } else if ($this->fields['status'] == self::STATUS_ACCEPTED) {
         echo '<div class="accepted_header">';
         echo '<div>';
         if (!empty($this->fields['comment'])) {
            echo '<strong>' . __('Comment', 'formcreator') . '</strong>';
            echo nl2br($this->fields['comment']);
         } else if ($form->validationRequired()) {
            echo __('Form accepted by validator.', 'formcreator');
         } else {
            echo __('Form successfully saved.', 'formcreator');
         }
         echo '</div>';
         echo '</div>';
      }

      echo '<ol>';
      $domain = PluginFormcreatorForm::getTranslationDomain($_SESSION['glpilanguage'], $form->getID());

      // Get fields populated with answers
      $this->loadAnswers();
      $this->answers['plugin_formcreator_forms_id'] = $form->getID();
      $visibility = PluginFormcreatorFields::updateVisibility($this->answers);

      $sections = (new PluginFormcreatorSection)->getSectionsFromForm($form->getID());
      foreach ($sections as $section) {
         $sectionId = $section->getID();

         // Section header
         $hiddenAttribute = $visibility[$section->getType()][$sectionId] ? '' : 'hidden=""';
         echo '<li'
         . ' class="plugin_formcreator_section"'
         . ' data-itemtype="' . PluginFormcreatorSection::class . '"'
         . ' data-id="' . $sectionId . '"'
         . " $hiddenAttribute"
         . '">';

         // section name
         echo '<h2>';
         echo empty($section->fields['name']) ? '(' . $sectionId . ')' : $section->fields['name'];
         echo '</h2>';

         // Section content
         echo '<div>';

         // Display all fields of the section
         $lastQuestion = null;
         $questions = (new PluginFormcreatorQuestion)->getQuestionsFromSection($sectionId);
         foreach ($questions as $question) {
            if ($lastQuestion !== null) {
               if ($lastQuestion->fields['row'] < $question->fields['row']) {
                  // the question begins a new line
                  echo '<div class="plugin_formcreator_newRow"></div>';
               } else {
                  $x = $lastQuestion->fields['col'] + $lastQuestion->fields['width'];
                  $width = $question->fields['col'] - $x;
                  if ($x < $question->fields['col']) {
                     // there is an horizontal gap between previous question and current one
                     echo '<div class="plugin_formcreator_gap" gs-x="' . $x . '" gs-w="' . $width . '"></div>';
                  }
               }
            }
            echo $question->getRenderedHtml($domain, $editMode, $this, $visibility[$question->getType()][$question->getID()]);
            $lastQuestion = $question;
         }
         echo '</div>';

         echo '</li>';
      }

      //add requester info
      echo '<div class="form-group">';
      echo '<label for="requester">' . _n('Requester', 'Requesters', 1) . '</label>';
      echo Dropdown::getDropdownName('glpi_users', $this->fields['requester_id']);
      echo '</div>';

      // Display submit button
      if (($this->fields['status'] == self::STATUS_REFUSED) && (Session::getLoginUserID() == $this->fields['requester_id'])) {
         echo '<div class="form-group">';
         echo '<div class="center">';
         echo Html::submit(__('Save'), ['name' => 'save_formanswer']);
         echo '</div>';
         echo '</div>';

      } else if (($this->fields['status'] == self::STATUS_WAITING) && $this->canValidate()) {
         // Display validation form
         echo '<div class="form-group required line1">';
         echo '<label for="comment">' . __('Comment', 'formcreator') . ' <span class="red">*</span></label>';
         Html::textarea([
            'name' => 'comment',
            'value' => $this->fields['comment']
         ]);
         echo '<div class="help-block">' . __('Required if refused', 'formcreator') . '</div>';
         echo '</div>';

         echo '<div class="form-group line1">';
         echo '<div class="center" style="float: left; width: 30%;">';
         echo Html::submit(
            __('Refuse', 'formcreator'), [
               'name'      => 'refuse_formanswer',
               'onclick'   => 'return plugin_formcreator_checkComment(this)',
            ]);
         echo '</div>';
         echo '<div class="center" style="float: left; width: 40%;">';
         if (!$editMode) {
            echo Html::submit(
               __('Edit answers', 'formcreator'), [
                  'name'      => 'edit_answers',
                  'onclick'   => 'reloadTab("edit=1"); return false;',
               ]);
         } else {
            echo Html::submit(
               __('Cancel edition', 'formcreator'), [
                  'name'      => 'edit_answers',
                  'onclick'   => 'reloadTab("edit=0"); return false;',
               ]);
         }
         echo '</div>';         echo '<div class="center">';
         echo Html::submit(
            __('Accept', 'formcreator'), [
               'name'      => 'accept_formanswer',
            ]);
         echo '</div>';
         echo '</div>';
      }

      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         $formAnswer = PluginFormcreatorCommon::getFormAnswer();
         $formAnswer->getFromDB($this->getID());
         PluginAdvformFormanswerValidation::showValidationStatuses($formAnswer);
      }
      $options['canedit'] = true;
      $options['candel'] = false;

      echo Html::hidden('plugin_formcreator_forms_id', ['value' => $form->getID()]);
      echo Html::hidden('id', ['value' => $this->getID()]);
      echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);

      echo '</div>';
      echo '<script type="text/javascript">
               function plugin_formcreator_checkComment(field) {
                  if ($("textarea[name=comment]").val() == "") {
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
    */
   public function prepareInputForAdd($input) {
      global $DB;

      // A requester submits his answers to a form
      if (!isset($input['plugin_formcreator_forms_id'])) {
         return false;
      }

      $form = $this->getForm($input['plugin_formcreator_forms_id']);

      if ($form->validationRequired()) {
         if (($validator = $this->getUniqueValidator($form)) !== null) {
            $input['formcreator_validator'] = $validator->getType() . '_' . $validator->getID();
         }
      }

      if (!$this->validateFormAnswer($input)) {
         // Validation of answers failed
         return false;
      }

      $input['name'] = $DB->escape($this->parseTags($form->fields['formanswer_name']));

      $input = $this->setValidator($input, $form);

      $input['entities_id'] = isset($_SESSION['glpiactive_entity'])
                            ? $_SESSION['glpiactive_entity']
                            : $form->fields['entities_id'];

      $input['is_recursive']                = $form->fields['is_recursive'];
      $input['plugin_formcreator_forms_id'] = $form->getID();
      // requester_id is actually the author
      // TODO : rename this column
      $input['requester_id']                = $_SESSION['glpiID'] ?? 0;
      $input['request_date']                = $_SESSION['glpi_currenttime'];
      $input['comment']                     = '';

      return $input;
   }

   public function prepareInputForUpdate($input) {
      // A requester submits his answers to a form
      if (!isset($input['plugin_formcreator_forms_id'])) {
         return false;
      }

      $skipValidation = false;
      $input['status'] = self::STATUS_WAITING;
      if (isset($input['refuse_formanswer']) || isset($input['accept_formanswer'])) {
         // The formanswer is being acepted or refused

         // Check the user haas right to validate the form answer
         if (!$this->canValidate()) {
            Session::addMessageAfterRedirect(__('You are not the validator of these answers', 'formcreator'), true, ERROR);
            return false;
         }

         $newStatus = isset($input['refuse_formanswer'])
            ? PluginFormcreatorForm_Validator::VALIDATION_STATUS_REFUSED
            : PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED;
         if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
            PluginAdvformFormanswerValidation::updateValidationStatus($this, $newStatus);
         }
         $computedStatus = self::getValidationStatus($this);

         switch ($computedStatus) {
            case PluginFormcreatorForm_Validator::VALIDATION_STATUS_REFUSED:
               $input = $this->refuse($input);
               $skipValidation = true;
               break;

            case PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED:
               $input = $this->accept($input);
               break;
         }

         if ($input['status'] != self::STATUS_REFUSED) {
            // The requester mai edit his answers
            // or the validator accepts the answers and may edit the requester's answers

            // check if the input contains answers to validate
            $fieldPresence = [];
            $this->getQuestionFields($input['plugin_formcreator_forms_id']);
            foreach ($this->questionFields as $id => $field) {
               $fieldPresence[$id] = $field->hasInput($input);
            }
            $skipValidation = !in_array(true, $fieldPresence, true);
            if ($skipValidation) {
               $this->questionFields = null;
            }
         }
      }

      if (!$skipValidation && !$this->validateFormAnswer($input, false)) {
         // Validation of answers failed
         return false;
      }

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
         'items_id'    => $this->getID(),
         'itemtype'    => self::getType(),
      ]);

      return true;
   }

   /**
    * return the validator user or group if it is the ony choice for the requester
    *
    * @param PluginFormcreatorForm $form Form to search the unique validator
    * @return null|User|Group
    */
   protected function getUniqueValidator(PluginFormcreatorForm $form): ?CommonDBTM {
      $validValidators = PluginFormcreatorForm_Validator::getValidatorsForForm(
         $form
      );

      if (count($validValidators) != 1) {
         return null;
      }

      return array_pop($validValidators);
   }

   /**
    * Generates all targets for the answers
    * @return bool true if targets were successfully generated
    */
   public function generateTarget(): bool {
      global $CFG_GLPI;

      $success = true;

      // Get all targets
      $form = $this->getForm();
      $all_targets = $form->getTargetsFromForm();

      $CFG_GLPI['plugin_formcreator_disable_hook_create_ticket'] = '1';

      // get all fields to compute visibility of targets
      $this->questionFields = $form->getFields();
      $this->deserializeAnswers();

      // Generate targets
      $generatedTargets = new PluginFormcreatorComposite(new PluginFormcreatorItem_TargetTicket(), new Ticket_Ticket(), $this);
      foreach ($all_targets as $targets) {
         foreach ($targets as $targetObject) {
            // Check the condition of the target
            if (!PluginFormcreatorFields::isVisible($targetObject, $this->questionFields)) {
               // The target shall not be generated
               continue;
            }

            // Generate the target
            $generatedTarget = $targetObject->save($this);
            if ($targetObject->getTargetType() == PluginFormcreatorAbstractItilTarget::TARGET_TYPE_OBJECT) {
               if ($generatedTarget === null) {
                  // If the target generates an object but none generated
                  $success = false;
               } else {
                  $this->targetList[] = $generatedTarget;
                  // Map [itemtype of the target] [item ID of the target] = ID of the generated target
                  $generatedTargets->addTarget($targetObject, $generatedTarget);
               }
            }
         }
      }
      $generatedTargets->buildCompositeRelations();

      Session::addMessageAfterRedirect(__('The form has been successfully saved!', 'formcreator'), true, INFO);

      /** @var CommonDBTM $target */
      foreach ($this->targetList as $target) {
         // TRANS: %1$s is the name of the target, %2$s is the type of the target, %3$s is the ID of the target in a HTML hyperlink
         $targetUrl = '<a href="' . $target->getFormURLWithID($target->getID()) . '">' . $target->getID() . '</a>';
         Session::addMessageAfterRedirect(sprintf(__('Item sucessfully added: %1$s (%2$s: %3$s)', 'formcreator'), $target->getName(), $target->getTypeName(1), $targetUrl), false, INFO);
      }

      unset($CFG_GLPI['plugin_formcreator_disable_hook_create_ticket']);
      return $success;
   }

   /**
    * Gets answers of all fields of a form answer
    *
    * @return void
    */
   public function loadAnswers(): void {
      global $DB;

      $this->answers = [];
      if ($this->isNewItem()) {
         return;
      }

      $answers = $DB->request([
         'SELECT' => ['plugin_formcreator_questions_id', 'answer'],
         'FROM'   => PluginFormcreatorAnswer::getTable(),
         'WHERE'  => [
            'plugin_formcreator_formanswers_id' => $this->getID()
         ]
      ]);
      $answers_values = [];
      foreach ($answers as $found_answer) {
         $answers_values['formcreator_field_' . $found_answer['plugin_formcreator_questions_id']] = $found_answer['answer'];
      }
      $this->answers = $answers_values;
   }

   /**
    * Load answers to questoins from session
    *
    * @return void
    */
   public function loadAnswersFromSession(): void {
      $this->answers = $_SESSION['formcreator']['data'] ?? [];
   }

   public function getAnswers() {
      return $this->answers;
   }

   /**
    * Gets the associated form
    * @param int $formId : specify the form to return. If null, find the form from the FK
    *
    * @return PluginFormcreatorForm|null the form used to create this set of answers
    */
   public function getForm(int $formId = null): ?PluginFormcreatorForm {
      if ($this->form !== null) {
         return $this->form;
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $form = PluginFormcreatorCommon::getForm();
      $form->getFromDB($formId ?? $this->fields[$formFk]);
      if ($form === false) {
         return null;
      }
      $this->form = $form;
      return $form;
   }

   /**
    * Get entire form to be inserted into a target content
    *
    * @param bool $richText If true, enable rich text output
    * @return string Full form questions and answers to be print
    */
   public function getFullForm($richText = false): string {
      global $DB;

      $question_no = 0;
      $output      = '';
      $eol = "\r\n";

      if ($richText) {
         $output .= '<h1>' . __('Form data', 'formcreator') . '</h1>';
      } else {
         $output .= __('Form data', 'formcreator') . $eol;
         $output .= '=================';
         $output .= $eol . $eol;
      }

      // retrieve answers
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $fields = $this->getQuestionFields($this->fields[$formFk]);

      $this->deserializeAnswers();

      // TODO: code very close to PluginFormcreatorAbstractTarget::parseTags() (factorizable ?)
      // compute all questions
      $questionTable = PluginFormcreatorQuestion::getTable();
      $sectionTable = PluginFormcreatorSection::getTable();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $questions = $DB->request([
         'SELECT' => [
            $sectionTable => ['name as section_name'],
            $questionTable => ['id', 'fieldtype', $sectionFk],
         ],
         'FROM' => [
            $questionTable,
         ],
         'INNER JOIN' => [
            $sectionTable => [
               'FKEY' => [
                  $questionTable => $sectionFk,
                  $sectionTable => 'id',
               ],
            ],
         ],
         'WHERE' => [
            'AND' => [
               "$sectionTable.$formFk" => $this->fields[$formFk],
            ],
         ],
         'GROUPBY' => [
            "$questionTable.id",
         ],
         'ORDER' => [
            "$sectionTable.order ASC",
            "$sectionTable.id ASC",
            "$questionTable.row *ASC",
            "$questionTable.col *ASC",
         ],
      ]);
      $last_section = -1;
      foreach ($questions as $question_line) {
         // Get and display current section if needed
         if ($last_section != $question_line[$sectionFk]) {
            $currentSection = new PluginFormcreatorSection();
            $currentSection->getFromDB($question_line[$sectionFk]);
            if (!PluginFormcreatorFields::isVisible($currentSection, $this->questionFields)) {
               // The section is not visible, skip it as well all its questions
               continue;
            }
            if ($richText) {
               $output .= '<h2>' . $question_line['section_name'] . '</h2>';
            } else {
               $output .= $eol . $question_line['section_name'] . $eol;
               $output .= '---------------------------------' . $eol;
            }
            $last_section = $question_line[$sectionFk];
         }

         // Don't save tags in "full form"
         if ($question_line['fieldtype'] == 'tag') {
            continue;
         }

         if ($question_line['fieldtype'] == 'fields') {
            continue;
         }

         if (!PluginFormcreatorFields::isVisible($fields[$question_line['id']]->getQuestion(), $this->questionFields)) {
            continue;
         }

         if ($question_line['fieldtype'] != 'description') {
            $question_no++;
            if ($richText) {
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

   public function post_addItem() {
      // Save questions answers
      $formAnswerId = $this->getID();
      $formId = $this->input[PluginFormcreatorForm::getForeignKeyField()];
      /** @var PluginFormcreatorAbstractField $field */
      foreach ($this->getQuestionFields($formId) as $questionId => $field) {
         $field->moveUploads();
         $answer = new PluginFormcreatorAnswer();
         $answer_value = $field->serializeValue($this);
         $answer->add([
            'plugin_formcreator_formanswers_id'  => $formAnswerId,
            'plugin_formcreator_questions_id'    => $questionId,
            'answer'                             => Toolbox::addslashes_deep($answer_value),
         ], [], 0);
         foreach ($field->getDocumentsForTarget() as $documentId) {
            $docItem = new Document_Item();
            $docItem->add([
               'documents_id' => $documentId,
               'itemtype'     => __CLASS__,
               'items_id'     => $formAnswerId,
            ]);
         }
      }

      $this->sendNotification();
      $formAnswer = clone $this;
      if ($this->input['status'] == self::STATUS_ACCEPTED) {
         Plugin::doHookFunction('formcreator_before_generate_target', $this);

         if (!$this->generateTarget()) {
            Session::addMessageAfterRedirect(__('Cannot generate targets!', 'formcreator'), true, ERROR);

            // TODO: find a way to validate the answers
            // It the form is not being validated, nothing gives the power to anyone to validate the answers
            $formAnswer->updateStatus(self::STATUS_WAITING);
            return;
         }
      }
      if ($this->input['status'] != self::STATUS_REFUSED) {
         $this->createIssue();
      }
      $minimalStatus = $formAnswer->getAggregatedStatus();
      if ($minimalStatus !== null) {
         $this->updateStatus($minimalStatus);
      }
      Session::addMessageAfterRedirect(__('The form has been successfully saved!', 'formcreator'), true, INFO);
   }

   public function post_updateItem($history = 1) {
      // Save questions answers
      $formAnswerId = $this->getID();
      $formId = $this->input[PluginFormcreatorForm::getForeignKeyField()];
      if ($this->questionFields !== null) {
         /** @var PluginFormcreatorAbstractField $field */
         foreach ($this->getQuestionFields($formId) as $questionId => $field) {
            if (!$field->hasInput($this->input)) {
               continue;
            }
            $field->moveUploads();
            $answer = new PluginFormcreatorAnswer();
            $answer->getFromDBByCrit([
               'plugin_formcreator_formanswers_id' => $formAnswerId,
               'plugin_formcreator_questions_id' => $questionId,
            ]);
            $answer->update([
               'id'     => $answer->getID(),
               'answer' => $field->serializeValue($this),
            ], 0);
            foreach ($field->getDocumentsForTarget() as $documentId) {
               $docItem = new Document_Item();
               $docItem->add([
                  'documents_id' => $documentId,
                  'itemtype'     => __CLASS__,
                  'items_id'     => $formAnswerId,
               ]);
            }
         }
      }
      $this->sendNotification();
      $formAnswer = clone $this;
      if ($this->input['status'] == self::STATUS_ACCEPTED) {
         if (!$this->generateTarget()) {
            Session::addMessageAfterRedirect(__('Cannot generate targets!', 'formcreator'), true, ERROR);

            // TODO: find a way to validate the answers
            // If the form is not being validated, nothing gives the power to anyone to validate the answers
            $this->updateStatus(self::STATUS_WAITING);
            return;
         }
      }
      $this->updateIssue();
      $minimalStatus = $formAnswer->getAggregatedStatus();
      if ($minimalStatus !== null) {
         $this->updateStatus($minimalStatus);
      }
      Session::addMessageAfterRedirect(__('The form has been successfully saved!', 'formcreator'), true, INFO);
   }

   /**
    * Actions done after the PURGE of the item in the database
    * Delete answers
    *
    * @return void
    */
   public function post_purgeItem() {
      global $DB;

      $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
      $DB->delete(
         PluginFormcreatorAnswer::getTable(), [
            $formAnswerFk => $this->getID(),
         ]
      );

      // If the form was waiting for validation
      if ($this->fields['status'] == self::STATUS_WAITING) {
         // Notify the requester
         NotificationEvent::raiseEvent('plugin_formcreator_deleted', $this);
      }
   }

   /**
    * Parse target content to replace TAGS like ##FULLFORM## by the values
    *
    * @param  string $content                            String to be parsed
    * @param  PluginFormcreatorTargetInterface $target   Target for which output is being generated
    * @param  boolean $richText                          Disable rich text mode for field rendering
    * @return string                                     Parsed string with tags replaced by form values
    */
   public function parseTags(string $content, PluginFormcreatorTargetInterface $target = null, $richText = false): string {
      // Prepare all fields of the form
      $form = $this->getForm();
      $this->getQuestionFields($form->getID());
      $this->deserializeAnswers();
      $domain = PluginFormcreatorForm::getTranslationDomain($form->getID());

      foreach ($this->questionFields as $questionId => $field) {
         if (!$field->isPrerequisites()) {
            continue;
         }
         $question = $field->getQuestion();
         $name = '';
         $value = '';
         if (PluginFormcreatorFields::isVisible($question, $this->questionFields)) {
            $name  = $question->fields['name'];
            $value = $this->questionFields[$questionId]->getValueForTargetText($domain, $richText);
         }

         $content = str_replace('##question_' . $questionId . '##', Sanitizer::sanitize($name), $content);
         if ($question->fields['fieldtype'] === 'file') {
            if (strpos($content, '##answer_' . $questionId . '##') !== false) {
               if ($target !== null && $target instanceof PluginFormcreatorAbstractItilTarget) {
                  foreach ($this->questionFields[$questionId]->getDocumentsForTarget() as $documentId) {
                     $target->addAttachedDocument($documentId);
                  }
               }
            }
         }
         $content = str_replace('##answer_' . $questionId . '##', Sanitizer::sanitize($value ?? ''), $content);

         if ($this->questionFields[$questionId] instanceof DropdownField) {
            $content = $this->questionFields[$questionId]->parseObjectProperties($field->getValueForDesign(), $content);
         }
      }

      if ($richText) {
         // convert sanitization from old style GLPI ( up to 9.5 to modern style)
         $content = Sanitizer::unsanitize($content);
         $content = Sanitizer::sanitize($content);
      }

      $hook_data = Plugin::doHookFunction('formcreator_parse_extra_tags', [
         'formanswer' => $this,
         'content'    => $content,
         'target'     => $target,
         'richtext'   => $richText,
      ]);

      return $hook_data['content'];
   }

   /**
    * Validates answers of a form
    *
    * @param array $input fields from the HTML form
    * @param bolean $checkValidator True if validator input must be checked
    * @return boolean true if answers are valid, false otherwise
    */
   protected function validateFormAnswer($input, $checkValidator = true) {
      // Find the form the requester is answering to
      $form = PluginFormcreatorCommon::getForm();
      $form->getFromDB($input['plugin_formcreator_forms_id']);
      $this->getQuestionFields($input['plugin_formcreator_forms_id']);

      // Parse form answers
      $fieldValidities = [];
      foreach (array_keys($this->questionFields) as $id) {
         // Test integrity of the value
         $fieldValidities[$id] = $this->questionFields[$id]->parseAnswerValues($input);
      }
      // any invalid field will invalidate the answers
      $this->isAnswersValid = !in_array(false, $fieldValidities, true);

      // check captcha if any
      if ($this->isAnswersValid && $form->fields['access_rights'] == PluginFormcreatorForm::ACCESS_PUBLIC && $form->fields['is_captcha_enabled'] != '0') {
         if (!isset($_SESSION['plugin_formcreator']['captcha'])) {
            Session::addMessageAfterRedirect(__('No turing test set', 'formcreator'));
            $this->isAnswersValid = false;
         }
         $this->isAnswersValid = PluginFormcreatorCommon::checkCaptcha($input['plugin_formcreator_captcha_id'], $input['plugin_formcreator_captcha']);
         if (!$this->isAnswersValid) {
            Session::addMessageAfterRedirect(__('You failed the Turing test', 'formcreator'));
         }
      }

      if ($this->isAnswersValid) {
         foreach ($this->questionFields as $id => $field) {
            if (!$this->questionFields[$id]->isPrerequisites()) {
               continue;
            }
            if (PluginFormcreatorFields::isVisible($field->getQuestion(), $this->questionFields) && !$this->questionFields[$id]->isValid()) {
               $this->isAnswersValid = false;
            }
         }
      }

      if ($form->validationRequired() && $checkValidator) {
         $this->validateValidator($input);
      }

      if (!$this->isAnswersValid) {
         return false;
      }

      return true;
   }

   /**
    * Validate validator
    *
    * @param array $input fields from the HTML form
    * @return boolean true if answers are valid, false otherwise
    */
   protected function validateValidator(array $input): bool {
      // Find the form the requester is answering to
      $form = PluginFormcreatorCommon::getForm();
      $form->getFromDB($input['plugin_formcreator_forms_id']);

      // Check required_validator
      if ($form->validationRequired() && empty($input['formcreator_validator'])) {
         // Check if only one validator of level 1 is available
         Session::addMessageAfterRedirect(__('You must select validator!', 'formcreator'), false, ERROR);
         $this->isAnswersValid = false;
         return false;
      }

      return true;
   }

   private function sendNotification() {
      switch ($this->input['status']) {
         case self::STATUS_WAITING :
            // Notify the requester
            NotificationEvent::raiseEvent('plugin_formcreator_form_created', $this);
            // Notify the validator
            NotificationEvent::raiseEvent('plugin_formcreator_need_validation', $this);
            break;
         case self::STATUS_REFUSED :
            // Notify the requester
            NotificationEvent::raiseEvent('plugin_formcreator_refused', $this);
            break;
         case self::STATUS_ACCEPTED :
            // Notify the requester
            $form = $this->getForm();
            if ($form->validationRequired()) {
               NotificationEvent::raiseEvent('plugin_formcreator_accepted', $this);
            } else {
               NotificationEvent::raiseEvent('plugin_formcreator_form_created', $this);
            }

            break;
      }
   }

   public function createIssue() {
      global $DB;

      // If cannot get itemTicket from DB it happens either
      // when no item exist
      // or when several rows matches
      // Both are processed the same way
      $ticketTable = Ticket::getTable();
      $itemTicketTable = Item_Ticket::getTable();
      $ticketFk = Ticket::getForeignKeyField();
      $rows = $DB->request([
         'SELECT' => ["$itemTicketTable.id", $ticketFk, 'status'],
         'FROM'   => $itemTicketTable,
         'INNER JOIN' => [
            $ticketTable => [
               'FKEY' => [
                  $ticketTable => 'id',
                  $itemTicketTable => $ticketFk,
               ],
            ],
         ],
         'WHERE'  => [
            'itemtype' => PluginFormcreatorFormAnswer::class,
            'items_id' => $this->getID(),
         ]
      ]);

      $issue = new PluginFormcreatorIssue();
      if ($rows->count() != 1) {
         // There is no or several tickets for this form answer
         // The issue must be created from this form answer
         $issueName = $this->fields['name'] != '' ? addslashes($this->fields['name']) : '(' . $this->getID() . ')';
         $issue->add([
            'items_id'           => $this->getID(),
            'itemtype'           => PluginFormcreatorFormAnswer::class,
            'name'               => $issueName,
            'status'             => $this->fields['status'],
            'date_creation'      => $this->fields['request_date'],
            'date_mod'           => $this->fields['request_date'],
            'entities_id'        => $this->fields['entities_id'],
            'is_recursive'       => $this->fields['is_recursive'],
            'requester_id'       => $this->fields['requester_id'],
            'users_id_validator' => $this->fields['users_id_validator'],
            'groups_id_validator'=> $this->fields['groups_id_validator'],
            'comment'            => '',
         ]);

         return;
      }

      // There is one ticket for this form answer
      // The issue must be created from this ticket
      $result = $rows->current();
      $itemTicket = new Item_Ticket();
      $itemTicket->getFromDB($result['id']);
      $ticket = new Ticket();
      if (!$ticket->getFromDB($itemTicket->fields['tickets_id'])) {
         throw new RuntimeException('Formcreator: Missing ticket ' . $itemTicket->fields['tickets_id'] . ' for formanswer ' . $this->getID());
      }
      $ticketId = $ticket->getID();
      $ticketUser = new Ticket_User();
      $ticketUserRow = $ticketUser->find([
         'tickets_id' => $ticketId,
         'type' => CommonITILActor::REQUESTER,
         ], [
            'id ASC'
         ],
         1
      );
      $ticketUserRow = array_pop($ticketUserRow);
      $requester = $ticketUserRow !== null ? $ticketUserRow['users_id'] : 0;
      $issueName = $ticket->fields['name'] != '' ? addslashes($ticket->fields['name']) : '(' . $ticket->getID() . ')';
      $issue->add([
         'items_id'           => $ticketId,
         'itemtype'           => Ticket::class,
         'name'               => $issueName,
         'status'             => $ticket->fields['status'],
         'date_creation'      => $ticket->fields['date'],
         'date_mod'           => $ticket->fields['date_mod'],
         'entities_id'        => $ticket->fields['entities_id'],
         'is_recursive'       => '0',
         'requester_id'       => $requester,
         'comment'            => addslashes($ticket->fields['content']),
      ]);
   }

   private function updateIssue() {
      global $DB;

      $issue = new PluginFormcreatorIssue();
      if ($this->input['status'] == self::STATUS_REFUSED) {
         $issue->getFromDBByCrit([
            'AND' => [
              'itemtype'     => PluginFormcreatorFormAnswer::class,
              'items_id'     => $this->getID()
            ]
         ]);
         $issue->update([
            'id'              => $issue->getID(),
            'itemtype'        => PluginFormcreatorFormAnswer::class,
            'items_id'        => $this->getID(),
            'status'          => $this->fields['status'],
         ]);
         return;
      }

      $ticketTable = Ticket::getTable();
      $itemTicketTable = Item_Ticket::getTable();
      $ticketFk = Ticket::getForeignKeyField();
      $rows = $DB->request([
         'SELECT' => ["$itemTicketTable.id", $ticketFk, 'status'],
         'FROM'   => $itemTicketTable,
         'INNER JOIN' => [
            $ticketTable => [
               'FKEY' => [
                  $ticketTable => 'id',
                  $itemTicketTable => $ticketFk,
               ],
            ],
         ],
         'WHERE'  => [
            'itemtype' => PluginFormcreatorFormAnswer::class,
            'items_id' => $this->getID(),
         ]
      ]);
      if ($rows->count() != 1) {
         // There are several tickets for this form answer
         // The issue must be updated from this form answer
         $issue->getFromDBByCrit([
            'AND' => [
            'itemtype'     => PluginFormcreatorFormAnswer::class,
            'items_id'     => $this->getID()
            ]
         ]);
         $issueName = $this->fields['name'] != '' ? addslashes($this->fields['name']) : '(' . $this->getID() . ')';
         $issue->update([
            'id'                 => $issue->getID(),
            'items_id'           => $this->getID(),
            'itemtype'           => PluginFormcreatorFormAnswer::class,
            'name'               => $issueName,
            'status'             => $this->fields['status'],
            'date_creation'      => $this->fields['request_date'],
            'date_mod'           => $this->fields['request_date'],
            'entities_id'        => $this->fields['entities_id'],
            'is_recursive'       => $this->fields['is_recursive'],
            'requester_id'       => $this->fields['requester_id'],
            'users_id_validator' => $this->fields['users_id_validator'],
            'groups_id_validator'=> $this->fields['groups_id_validator'],
            'comment'            => '',
         ]);

         return;
      }
      // There is one ticket for this form answer
      // The issue must be updated from this ticket
      $result = $rows->current();
      $itemTicket = new Item_Ticket();
      $itemTicket->getFromDB($result['id']);
      $ticket = new Ticket();
      if (!$ticket->getFromDB($itemTicket->fields['tickets_id'])) {
         throw new RuntimeException('Formcreator: Missing ticket ' . $itemTicket->fields['tickets_id'] . ' for formanswer ' . $this->getID());
      }
      $ticketId = $ticket->getID();
      $ticketUser = new Ticket_User();
      $ticketUserRow = $ticketUser->find([
            'tickets_id' => $ticketId,
            'type' => CommonITILActor::REQUESTER,
         ], [
            'id ASC'
         ],
         1
      );
      $ticketUserRow = array_pop($ticketUserRow);
      $issue->getFromDBByCrit([
         'AND' => [
            'itemtype'     => PluginFormcreatorFormAnswer::class,
            'items_id'     => $this->getID()
         ]
      ]);
      $issueName = $ticket->fields['name'] != '' ? addslashes($ticket->fields['name']) : '(' . $ticket->getID() . ')';
      $issue->update([
         'id'                 => $issue->getID(),
         'items_id'           => $ticketId,
         'itemtype'           => Ticket::class,
         'name'               => $issueName,
         'status'             => $ticket->fields['status'],
         'date_creation'      => $ticket->fields['date'],
         'date_mod'           => $ticket->fields['date_mod'],
         'entities_id'        => $ticket->fields['entities_id'],
         'is_recursive'       => '0',
         'requester_id'       => $ticketUserRow['users_id'],
         'comment'            => addslashes($ticket->fields['content']),
      ]);
   }

   /**
    * get all fields from a form
    *
    * @param int $formId ID of the form where come the fileds to load
    * @return PluginFormcreatorAbstractField[]
    */
   public function getQuestionFields($formId) : array {
      if ($this->questionFields !== null) {
         return $this->questionFields;
      }

      $form = PluginFormcreatorCommon::getForm();
      if ($form->isNewID($formId)) {
         return [];
      }
      if (!$form->getFromDB($formId)) {
         return [];
      }

      $this->questionFields = $form->getFields();
      $this->isAnswersValid = false;

      return $this->questionFields;
   }

   public function getIsAnswersValid() : bool {
      return $this->isAnswersValid;
   }

   /**
    * Deserialize answers from the DB
    *
    * @return boolean True on success
    */
   public function deserializeAnswers() : bool {
      if ($this->isNewItem()) {
         return false;
      }

      $this->loadAnswers();
      $answers_values = $this->getAnswers();
      foreach (array_keys($this->questionFields) as $id) {
         if (!$this->questionFields[$id]->hasInput($answers_values)) {
            continue;
         }
         $this->questionFields[$id]->deserializeValue($answers_values['formcreator_field_' . $id]);
      }

      return true;
   }

   /**
    * get visibility of a field from all field values of the form answer
    *
    * @param int $id
    * @return bool
    */
   public function isFieldVisible(int $id) : bool {
      if ($this->isNewItem()) {
         throw new RuntimeException("Instance is empty");
      }

      if (!isset($this->questionFields[$id])) {
         throw new RuntimeException("Question not found");
      }

      return PluginFormcreatorFields::isVisible($this->questionFields[$id]->getQuestion(), $this->questionFields);
   }

   protected function setValidator(array $input): array {
      $groupIdValidator = 0;
      $usersIdValidator = 0;

      $input['status'] = self::STATUS_ACCEPTED;
      if (isset($input['formcreator_validator'])) {
         switch ($this->getForm()->fields['validation_required']) {
            case PluginFormcreatorForm::VALIDATION_USER:
               $validatorItem = explode('_', $input['formcreator_validator']);
               if ($validatorItem[0] != User::class) {
                  break;
               }
               $usersIdValidator = (int) $validatorItem[1];
               if (Session::getLoginUserID(true) == $usersIdValidator) {
                  // The requester is the validator. No need to validate
                  break;
               }
               $input['status'] = self::STATUS_WAITING;
               break;

            case PluginFormcreatorForm::VALIDATION_GROUP:
               $validatorItem = explode('_', $input['formcreator_validator']);
               if ($validatorItem[0] != Group::class) {
                  break;
               }
               $groupIdValidator = (int) $validatorItem[1];
               if (Session::getLoginUserID(true) !== false && in_array($groupIdValidator, $_SESSION['glpigroups'])) {
                  // The requester is a member of the validator group
                  break;
               }
               $input['status'] = self::STATUS_WAITING;
               break;
         }
      }

      $input['users_id_validator']  = $usersIdValidator;
      $input['groups_id_validator'] = $groupIdValidator;

      return $input;
   }

   /**
    * Undocumented function
    *
    * @param array $input
    * @return array
    */
   private function refuse(array $input): array {
      $input['status'] = self::STATUS_REFUSED;
      // Update is restricted to a subset of fields
      $input = [
         'id'      => $input['id'],
         'status'  => $input['status'],
         'comment' => $input['comment'] ?? 'NULL',
      ];

      return $input;
   }

   /**
    * Undocumented function
    *
    * @param array $input
    * @return array
    */
   private function accept(array $input): array {
      $input['status'] = self::STATUS_ACCEPTED;

      return $input;
   }

   /**
    * Undocumented function
    *
    * @param array $input
    * @return integer
    */
   protected static function getValidationStatus(PluginFormcreatorFormAnswer $formAnswer): int {
      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         return PluginAdvformFormAnswer::getValidationStatus($formAnswer);
      }

      return isset($formAnswer->input['refuse_formanswer'])
             ? PluginFormcreatorForm_Validator::VALIDATION_STATUS_REFUSED
             : PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED;
   }

   /**
    * Get approver users or approver groups
    *
    * @param array $crit
    * @return array|null
    */
   public function getApprovers(array $crit = []): ?array {
      if ($this->isNewItem()) {
         return null;
      }

      if ($this->fields['users_id_validator'] > 0) {
         $id = $this->fields['users_id_validator'];
         return [
            User::class => [
               $id => [
                  'pluginformcreator_formanswers_id' => $this->getID(),
                  'itemtype' => User::class,
                  'items_id' => $id,
               ],
            ]
         ];
      }

      if ($this->fields['groups_id_validator'] > 0) {
         $id = $this->fields['groups_id_validator'];
         return [
            Group::class => [
               $id => [
                  'pluginformcreator_formanswers_id' => $this->getID(),
                  'itemtype' => Group::class,
                  'items_id' => $id,
               ],
            ]
         ];
      }

      return null;
   }

   /**
    * Get users or groupe in charge of valdiation
    *
    * @return array|null
    */
   public function getCurrentApprovers(): ?array {
      if ($this->fields['status'] != self::STATUS_WAITING) {
         return null;
      }

      // the form answers are waiting for validation
      return $this->getApprovers();
   }

   /**
    * get all generated targets by the form answer
    * populates the generated targets associated to the instance
    *
    * @param array $itemtypes Get only the targets of the given itemtypes
    *
    * @return array An array of target itemtypes to track
    */
   public function getGeneratedTargets($itemtypes = []): array {
      $targets = [];
      if ($this->isNewItem()) {
         return [];
      }

      if (count($itemtypes) < 1) {
         $itemtypes = PluginFormcreatorForm::getTargetTypes();
      } else {
         $itemtypes = array_intersect(PluginFormcreatorForm::getTargetTypes(), $itemtypes);
      }
      /** @var PluginFormcreatorTargetInterface $targetType */
      foreach ($itemtypes as $targetType) {
         $targets = array_merge($targets, $targetType::findForFormAnswer($this));
      }

      return $targets;
   }

   /**
    * Get the lowest status among the associated tickets
    *
    * @return null|int
    */
   public function getAggregatedStatus(): ?int {
      $generatedTargets = $this->getGeneratedTargets([PluginFormcreatorTargetTicket::getType()]);

      $isWaiting = false;
      $isAssigned = false;
      $isProcessing = false;

      // Find the minimal status of the first generated tickets in the array (deleted items excluded)
      $generatedTarget = array_shift($generatedTargets);
      while ($generatedTarget!== null && $generatedTarget->fields['is_deleted']) {
         $generatedTarget = array_shift($generatedTargets);
      }
      if ($generatedTarget === null) {
         // No target found, nothing to do
         return null;
      }

      // Find status of the first ticket in the array
      $aggregatedStatus = PluginFormcreatorCommon::getTicketStatusForIssue($generatedTarget);
      if ($aggregatedStatus == CommonITILObject::ASSIGNED) {
         $isAssigned = true;
      }
      if ($aggregatedStatus == CommonITILObject::PLANNED) {
         $isProcessing = true;
      }
      if ($aggregatedStatus == CommonITILObject::WAITING) {
         $isWaiting = true;
      }

      // Traverse all other tickets and set the minimal status
      foreach ($generatedTargets as $generatedTarget) {
         /** @var Ticket $generatedTarget  */
         if ($generatedTarget::getType() != Ticket::getType()) {
            continue;
         }
         if ($generatedTarget->isDeleted()) {
            continue;
         }
         $ticketStatus = PluginFormcreatorCommon::getTicketStatusForIssue($generatedTarget);
         if ($ticketStatus >= PluginFormcreatorFormAnswer::STATUS_WAITING) {
            continue;
         }

         if ($ticketStatus == CommonITILObject::ASSIGNED) {
            $isAssigned = true;
         }
         if ($ticketStatus == CommonITILObject::PLANNED) {
            $isProcessing = true;
         }
         if ($ticketStatus == CommonITILObject::WAITING) {
            $isWaiting = true;
         }
         $aggregatedStatus = min($aggregatedStatus, $ticketStatus);
      }

      // Assigned status takes precedence
      if ($isAssigned) {
         $aggregatedStatus = CommonITILObject::ASSIGNED;
      }
      // Planned status takes precedence
      if ($isProcessing) {
         $aggregatedStatus = CommonITILObject::PLANNED;
      }
      // Waiting status takes precedence to inform the requester his feedback is required
      if ($isWaiting) {
         $aggregatedStatus = CommonITILObject::WAITING;
      }

      return $aggregatedStatus;
   }

   /**
    * update the status
    *
    * @param integer $status
    * @return bool
    */
   public function updateStatus(int $status): bool {
      global $DB;

      $success = $DB->update(static::getTable(), [
         'status' => $status,
      ], [
         'id' => $this->getID()
      ]);
      if (!$success) {
         return false;
      }
      $this->fields['status'] = $status;

      return $DB->update(PluginFormcreatorIssue::getTable(), [
         'status' => $status,
      ], [
         'itemtype' => $this->getType(),
         'items_id' => $this->getID(),
      ]);
   }

   /**
    * Get the filename of a document for a question of this formanswer
    *
    * @param int $questionId
    * @param int $index
    *
    * @return string
    */
   public function getFileName($questionId, $index): string {
      $document = Document::getById($this->questionFields[$questionId]->getDocumentsForTarget()[$index]);
      if (!is_object($document)) {
         return '';
      }

      return $document->fields['filepath'];
   }

   /**
    * get properties of file uploads
    *
    * @return array
    *
    */
   public function getFileProperties(): array {
      $file_names = [];
      $file_tags = [];

      foreach ($this->getQuestionFields($this->getForm()->getID()) as $question_id => $field) {
         if (!in_array($field->getQuestion()->fields['fieldtype'], ['file'])) {
            continue;
         }

         foreach ($field->getDocumentsForTarget() as $documentId) {
            $document = Document::getById($documentId);
            if (!is_object($document)) {
               // If the document no longer exists
               continue;
            }
            $file_names[$question_id][] = $document->fields['filename'];
            $file_tags[$question_id][] = $document->fields['tag'];
         }
      }

      return [
         "_filename" => $file_names,
         "_tag_filename" => $file_tags
      ];
   }

   public static function getDefaultSearchRequest(): array {
      return [
         'sort' => 6, // See self::rawSearchOptions()
         'order' => 'DESC'
      ];
   }

   /**
    * Get a formanswer from a generated ticket
    *
    * @param Ticket|int $item
    * @return bool
    */
   public function getFromDbByTicket($item) {
      if (($item instanceof Ticket)) {
         $id = $item->getID();
      } else if (is_integer($item)) {
         $id = $item;
      } else {
         throw new InvalidArgumentException("$item must be an integer or a " . Ticket::class);
      }

      return $this->getFromDBByCrit([
         'id' => new QuerySubQuery([
            'SELECT' => 'items_id',
            'FROM'   => Item_Ticket::getTable(),
            'WHERE'  => [
               'itemtype' => PluginFormcreatorFormAnswer::getType(),
               'tickets_id' => $id,
            ]
         ])
      ]);
   }
}
