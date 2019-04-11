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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorQuestion extends CommonDBChild implements PluginFormcreatorExportableInterface {
   static public $itemtype = PluginFormcreatorSection::class;
   static public $items_id = 'plugin_formcreator_sections_id';

   /** @var PluginFormcreatorFieldInterface|null $field a field describing the question denpending on its field type  */
   private $field = null;

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

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Question', 'Questions', $nb, 'formcreator');
   }


   function addMessageOnAddAction() {}
   function addMessageOnUpdateAction() {}
   function addMessageOnDeleteAction() {}
   function addMessageOnPurgeAction() {}

   /**
    * Return the name of the tab for item including forms like the config page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $withtemplate
    *
    * @return String                   Name to be displayed
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      global $DB;

      switch ($item->getType()) {
         case PluginFormcreatorForm::class:
            $number      = 0;
            $found       = $DB->request([
               'SELECT' => ['id'],
               'FROM'   => PluginFormcreatorSection::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_forms_id' => $item->getID()
               ]
            ]);
            $tab_section = [];
            foreach ($found as $section_item) {
               $tab_section[] = $section_item['id'];
            }

            if (!empty($tab_section)) {
               $count = $DB->request([
                  'COUNT' => 'cpt',
                  'FROM'  => self::getTable(),
                  'WHERE' => [
                     'plugin_formcreator_sections_id' => $tab_section
                  ]
               ])->next();
               $number = $count['cpt'];
            }
            return self::createTabEntry(self::getTypeName($number), $number);
      }
      return '';
   }

   /**
    * Display a list of all form sections and questions
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Form Item)
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch (get_class($item)) {
         case PluginFormcreatorForm::class:
            static::showForForm($item, $withtemplate);
            break;
      }
   }

   public static function showForForm(CommonDBTM $item, $withtemplate = '') {
      global $CFG_GLPI, $DB;
      // TODO: move the content of this method into a new showForForm() method
      echo '<table class="tab_cadre_fixe">';

      // Get sections
      $found_sections = $DB->request([
         'FROM'    => PluginFormcreatorSection::getTable(),
         'WHERE'   => [
            'plugin_formcreator_forms_id' => (int) $item->getId()
         ],
         'ORDER' => 'order'
      ]);
      $section_number   = count($found_sections);
      $token            = Session::getNewCSRFToken();
      foreach ($found_sections as $section) {
         echo '<tr class="section_row" id="section_row_' . $section['id'] . '">';
         echo '<th onclick="editSection(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ')">';
         echo "<a href='#'>";
         echo $section['name'];
         echo '</a>';
         echo '</th>';

         echo '<th align="center">';

         echo "<span class='form_control pointer'>";
         echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/delete.png"
                  title="' . __('Delete', 'formcreator') . '"
                  onclick="deleteSection(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ')"> ';
         echo "</span>";

         echo "<span class='form_control pointer'>";
         echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/clone.png"
                  title="' . _sx('button', "Duplicate") . '"
                  onclick="duplicateSection(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ')"> ';
         echo "</span>";

         echo "<span class='form_control pointer'>";
         if ($section['order'] != $section_number) {
            echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/down.png"
                     title="' . __('Bring down') . '"
                     onclick="moveSection(\'' . $token . '\', ' . $section['id'] . ', \'down\');" >';
         }
         echo "</span>";

         echo "<span class='form_control pointer'>";
         if ($section['order'] != 1) {
            echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/up.png"
                     title="' . __('Bring up') . '"
                     onclick="moveSection(\'' . $token . '\', ' . $section['id'] . ', \'up\');"> ';
         }
         echo "</span>";

         echo '</th>';
         echo '</tr>';

         // Get questions
         $found_questions = $DB->request([
            'FROM'  => PluginFormcreatorQuestion::getTable(),
            'WHERE' => [
               'plugin_formcreator_sections_id' => (int) $section['id']
            ],
            'ORDER' => 'order'
         ]);
         $question_number   = count($found_questions);
         $i = 0;
         foreach ($found_questions as $question) {
            $i++;
            echo '<tr class="line' . ($i % 2) . '" id="question_row_' . $question['id'] . '">';
            echo '<td onclick="editQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $question['id'] . ', ' . $section['id'] . ')">';
            echo "<a href='#'>";
            echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/ui-' . $question['fieldtype'] . '-field.png" title="" /> ';
            echo $question['name'];
            echo "<a>";
            echo '</td>';

            echo '<td align="center">';

            $classname = PluginFormcreatorFields::getFieldClassname($question['fieldtype']);
            $fields = $classname::getPrefs();

            // avoid quote js error
            $question['name'] = htmlspecialchars_decode($question['name'], ENT_QUOTES);

            echo "<span class='form_control pointer'>";
            echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/delete.png"
                     title="' . __('Delete', 'formcreator') . '"
                     onclick="deleteQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $question['id'] . ')"> ';
            echo "</span>";

            echo "<span class='form_control pointer'>";
            echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/clone.png"
                     title="' . _sx('button', "Duplicate") . '"
                     onclick="duplicateQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $question['id'] . ')"> ';
            echo "</span>";

            if ($fields['required'] != 0) {
               $required_pic = ($question['required'] ? "required": "not-required");
               echo "<span class='form_control pointer'>";
               echo "<img src='" . $CFG_GLPI['root_doc'] . "/plugins/formcreator/pics/$required_pic.png'
                        title='" . __('Required', 'formcreator') . "'
                        onclick='setRequired(\"".$token."\", ".$question['id'].", ".($question['required']?0:1).")' > ";
               echo "</span>";
            }

            echo "<span class='form_control pointer'>";
            if ($question['order'] != 1) {
               echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/up.png"
                        title="' . __('Bring up') . '"
                        onclick="moveQuestion(\'' . $token . '\', ' . $question['id'] . ', \'up\');" align="absmiddle"> ';
            }
            echo "</span>";

            echo "<span class='form_control pointer'>";
            if ($question['order'] != $question_number) {
               echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/down.png"
                        title="' . __('Bring down') . '"
                        onclick="moveQuestion(\'' . $token . '\', ' . $question['id'] . ', \'down\');"> ';
            }
            echo "</span>";

            echo '</td>';
            echo '</tr>';
         }

         echo '<tr class="line' . (($i + 1) % 2) . '">';
         echo '<td colspan="6" id="add_question_td_' . $section['id'] . '" class="add_question_tds">';
         echo '<a href="javascript:addQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ');">
                   <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+"/>
                   '.__('Add a question', 'formcreator').'
               </a>';
         echo '</td>';
         echo '</tr>';
      }

      echo '<tr class="line1 section_row">';
      echo '<th id="add_section_th">';
      echo '<a href="javascript:addSection(' . $item->getId() . ', \'' . $token . '\');">
                <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+">
                '.__('Add a section', 'formcreator').'
            </a>';
      echo '</th>';
      echo '<th></th>';
      echo '</tr>';

      echo "</table>";
   }

   /**
    * Validate form fields before add or update a question
    * @param  array $input Datas used to add the item
    * @return array        The modified $input array
    */
   private function checkBeforeSave($input) {
      // Control fields values :
      // - name is required
      if (isset($input['name'])) {
         if (empty($input['name'])) {
            Session::addMessageAfterRedirect(__('The title is required', 'formcreator'), false, ERROR);
            return [];
         }
      }

      // - field type is required
      if (isset($input['fieldtype'])
          && empty($input['fieldtype'])) {
         Session::addMessageAfterRedirect(__('The field type is required', 'formcreator'), false, ERROR);
         return [];
      }

      // - section is required
      if (isset($input['plugin_formcreator_sections_id'])
          && empty($input['plugin_formcreator_sections_id'])) {
         Session::addMessageAfterRedirect(__('The section is required', 'formcreator'), false, ERROR);
         return [];
      }

      if (!isset($input['fieldtype'])) {
         $input['fieldtype'] = $this->fields['fieldtype'];
      }
      $this->field = PluginFormcreatorFields::getFieldInstance(
         $input['fieldtype'],
         $this
      );
      if ($this->field === null) {
         Session::addMessageAfterRedirect(
            // TRANS: $%1$s is a type of field, %2$s is the label of a question
            sprintf(
               __('Field type %1$s is not available for question %2$s.', 'formcreator'),
               $input['fieldtype'],
               $input['name']
               ),
            false,
            ERROR
         );
         return [];
      }
      // - field type is compatible with accessibility of the form
      $section = new PluginFormcreatorSection();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      if (isset($input[$sectionFk])) {
         $section->getFromDB($input[$sectionFk]);
      } else {
         $section->getFromDB($this->fields[$sectionFk]);
      }
      $form = new PluginFormcreatorForm();
      $form->getFromDBBySection($section);
      if ($form->isPublicAccess() && !$this->field->isAnonymousFormCompatible()) {
         Session::addMessageAfterRedirect(__('This type of question is not compatible with public forms.', 'formcreator'), false, ERROR);
         return [];
      }

      // Check the parameters are provided
      $parameters = $this->field->getEmptyParameters();
      if (count($parameters) > 0) {
         if (!isset($input['_parameters'][$input['fieldtype']])) {
            // This should not happen
            Session::addMessageAfterRedirect(__('This type of question requires parameters', 'formcreator'), false, ERROR);
            return [];
         }
         foreach ($parameters as $parameter) {
            if (!isset($input['_parameters'][$input['fieldtype']][$parameter->getFieldName()])) {
               // This should not happen
               Session::addMessageAfterRedirect(__('A parameter is missing for this question type', 'formcreator'), false, ERROR);
               return [];
            }
         }
      }

      $input = $this->field->prepareQuestionInputForSave($input);
      if ($input === false || !is_array($input)) {
         // Invalid data
         return [];
      }

      // Might need to merge $this->fields and $input, $input having precedence
      // over $this->fields
      $input['default_values'] = $this->field->serializeValue();

      return $input;
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
      if (!isset($input['_skip_checks'])
          || !$input['_skip_checks']) {
         $input = $this->checkBeforeSave($input);
      }
      if (count($input) === 0) {
         return [];
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      // Get next order
      $maxOrder = PluginFormcreatorCommon::getMax($this, [
         "plugin_formcreator_sections_id" => $input['plugin_formcreator_sections_id']
      ], 'order');
      if ($maxOrder === null) {
         $input['order'] = 1;
      } else {
         $input['order'] = $maxOrder + 1;
      }

      return $input;
   }

   /**
    * Prepare input data for adding the question
    * Check fields values and get the order for the new question
    *
    * @param array $input data used to add the item
    *
    * @array return the modified $input array
    */
   public function prepareInputForUpdate($input) {
      global $DB;

      if (!isset($input['_skip_checks'])
          || !$input['_skip_checks']) {
         $input = $this->checkBeforeSave($input);
      }

      if (!is_array($input) || count($input) == 0) {
         return false;
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      if (isset($input['plugin_formcreator_sections_id'])) {
         // If change section, reorder questions
         if ($input['plugin_formcreator_sections_id'] != $this->fields['plugin_formcreator_sections_id']) {
            $oldId = $this->fields['plugin_formcreator_sections_id'];
            $newId = $input['plugin_formcreator_sections_id'];
            $order = $this->fields['order'];
            // Reorder other questions from the old section
            $table = self::getTable();
            $query = "UPDATE `$table` SET
                `order` = `order` - 1
                WHERE `order` > '$order'
                AND plugin_formcreator_sections_id = '$oldId'";
            $DB->query($query);

            // Get the order for the new section
            $maxOrder = PluginFormcreatorCommon::getMax($this, [
               "plugin_formcreator_sections_id" => $newId
            ], 'order');
            if ($maxOrder === null) {
               $input['order'] = 1;
            } else {
               $input['order'] = $maxOrder + 1;
            }
         }
      }

      return $input;
   }

   protected function serializeDefaultValue($input) {
      // Might need to merge $this->fields and $input, $input having precedence
      // over $this->fields
      $question = new self();
      $question->fields = $input;
      $field = PluginFormcreatorFields::getFieldInstance(
         $input['fieldtype'],
         $question
      );
      $field->parseDefaultValue($input['default_values']);
      $input['default_values'] = $field->serializeValue();
      return $input;
   }

   /**
    * Moves the question up in the ordered list of questions in the section
    */
   public function moveUp() {
      $order      = $this->fields['order'];
      $sectionId  = $this->fields['plugin_formcreator_sections_id'];
      $otherItem  = new static();

      $otherItem->getFromDBByRequest([
         'WHERE' => [
            'AND' => [
               'plugin_formcreator_sections_id' => $sectionId,
               'order'                          => ['<', $order]
            ]
         ],
         'ORDER' => ['order DESC'],
         'LIMIT' => 1
      ]);

      if (!$otherItem->isNewItem()) {
         $this->update([
            'id'     => $this->getID(),
            'order'  => $otherItem->getField('order'),
            '_skip_checks' => true,
         ]);
         $otherItem->update([
            'id'           => $otherItem->getID(),
            'order'        => $order,
            '_skip_checks' => true,
         ]);
      }
   }

   /**
    * Moves the question down in the ordered list of questions in the section
    */
   public function moveDown() {
      $order      = $this->fields['order'];
      $sectionId  = $this->fields['plugin_formcreator_sections_id'];
      $otherItem  = new static();
      $otherItem->getFromDBByRequest([
         'WHERE' => [
            'AND' => [
               'plugin_formcreator_sections_id' => $sectionId,
               'order'                          => ['>', $order]
            ]
         ],
         'ORDER' => ['order ASC'],
         'LIMIT' => 1
      ]);
      if (!$otherItem->isNewItem()) {
         $this->update([
            'id'     => $this->getID(),
            'order'  => $otherItem->getField('order'),
            '_skip_checks' => true,
         ]);
         $otherItem->update([
            'id'           => $otherItem->getID(),
            'order'        => $order,
            '_skip_checks' => true,
         ]);
      }
   }

   /**
    * set or reset the required flag
    *
    * @param boolean $isRequired
    */
   public function setRequired($isRequired) {
      $this->update([
         'id'           => $this->getID(),
         'required'     => $isRequired,
         '_skip_checks' => true,
      ]);
   }

   /**
    * Updates the conditions of the question
    * @param array $input
    * @return boolean true if success, false otherwise
    */
   public function updateConditions($input) {
      // Delete all existing conditions for the question
      $question_condition = new PluginFormcreatorQuestion_Condition();
      $question_condition->deleteByCriteria(['plugin_formcreator_questions_id' => $input['id']]);

      if (isset($input['show_field']) && isset($input['show_condition'])
            && isset($input['show_value']) && isset($input['show_logic'])) {
         if (is_array($input['show_field']) && is_array($input['show_condition'])
               && is_array($input['show_value']) && is_array($input['show_logic'])) {
            // All arrays of condition exists
            if ($input['show_rule'] != 'always') {
               if ((count($input['show_field']) == count($input['show_condition'])
                     && count($input['show_value']) == count($input['show_logic'])
                     && count($input['show_field']) == count($input['show_value']))) {
                  // Arrays all have the same count and have at least one item
                  $order = 0;
                  while (count($input['show_field']) > 0) {
                     $order++;
                     $value            = array_shift($input['show_value']);
                     $showField       = (int) array_shift($input['show_field']);
                     $showCondition   = array_shift($input['show_condition']);
                     $showLogic        = array_shift($input['show_logic']);
                     $question_condition = new PluginFormcreatorQuestion_Condition();
                     $question_condition->add([
                           'plugin_formcreator_questions_id'   => $input['id'],
                           'show_field'                        => $showField,
                           'show_condition'                    => $showCondition,
                           'show_value'                        => $value,
                           'show_logic'                        => $showLogic,
                           'order'                             => $order,
                     ]);
                     if ($question_condition->isNewItem()) {
                        return false;
                     }
                  }
                  return true;
               }
            }
         }
      }

      return false;
   }

   /**
    * Adds or updates parameters of the question
    * @param array $input parameters
    */
   public function updateParameters($input) {
      if (!isset($this->fields['fieldtype'])) {
         return;
      }

      $this->field = PluginFormcreatorFields::getFieldInstance(
         $input['fieldtype'],
         $this
      );
      $parameters = $this->field->getParameters();
      if (isset($input['_parameters'][$this->fields['fieldtype']])) {
         foreach ($input['_parameters'][$this->fields['fieldtype']] as $fieldName => $parameterInput) {
            $parameterInput['plugin_formcreator_questions_id'] = $this->getID();
            if ($parameters[$fieldName]->isNewItem()) {
               $parameters[$fieldName]->add($parameterInput);
            } else {
               $parameterInput['id'] = $parameters[$fieldName]->getID();
               $parameters[$fieldName]->update($parameterInput);
            }
         }
      }
   }

   public function pre_deleteItem() {
      $this->field = PluginFormcreatorFields::getFieldInstance(
         $this->getField('fieldtype'),
         $this
      );
      return $this->field->deleteParameters($this);
   }

   public function post_updateItem($history = 1) {
      if (!in_array('fieldtype', $this->updates)) {
         // update question parameters into the database
         if ($this->field instanceof PluginFormcreatorFieldInterface) {
            // Set by self::checkBeforeSave()
            $this->field->updateParameters($this, $this->input);
         }
      } else {
         // Field type changed
         // Drop old parameters
         $oldField = PluginFormcreatorFields::getFieldInstance(
            $this->oldvalues['fieldtype'],
            $this
         );
         $oldField->deleteParameters($this);

         // add new ones
         $this->field->addParameters($this, $this->input);
      }
   }

   /**
    * Actions done after the PURGE of the item in the database
    * Reorder other questions
    *
    * @return void
    */
   public function post_purgeItem() {
      global $DB;

      $table = self::getTable();
      $question_condition_table = PluginFormcreatorQuestion_Condition::getTable();

      // Update order of questions
      $order = $this->fields['order'];
      $query = "UPDATE `$table` SET
                `order` = `order` - 1
                WHERE `order` > '$order'
                AND plugin_formcreator_sections_id = {$this->fields['plugin_formcreator_sections_id']}";
      $DB->query($query);

      // Always show questions with conditional display on the question being deleted
      $questionId = $this->fields['id'];
      $query = "UPDATE `$table` SET `show_rule`='always'
            WHERE `id` IN (
                  SELECT `plugin_formcreator_questions_id` FROM `$question_condition_table`
                  WHERE `show_field` = '$questionId'
            )";
      $DB->query($query);

      $query = "DELETE FROM `$question_condition_table`
            WHERE `plugin_formcreator_questions_id` = '$questionId'
            OR `show_field` = '$questionId'";
      $DB->query($query);
   }

   public function showForm($ID, $options = []) {
      global $CFG_GLPI;

      $rootDoc = $CFG_GLPI['root_doc'];

      $form_id = (int) $_REQUEST['form_id'];
      $form = new PluginFormcreatorForm();
      $form->getFromDB($form_id);

      $rand = mt_rand();
      $action = Toolbox::getItemTypeFormURL('PluginFormcreatorQuestion');
      echo '<form name="form_question" method="post" action="'.$action.'">';

      echo '<table class="tab_cadre_fixe">';
      echo '<tr>';
      echo '<th colspan="4">';
      echo (0 == $ID) ? __('Add a question', 'formcreator') : __('Edit a question', 'formcreator');
      echo '</th>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td width="20%">';
      echo '<label for="name" id="label_name">';
      echo  __('Title');
      echo '<span style="color:red;">*</span>';
      echo '</label>';
      echo '</td>';

      echo '<td width="30%">';
      echo '<input type="text" name="name" id="name" style="width:90%;" autofocus value="'.$this->fields['name'].'" class="required"';
      echo '</td>';

      echo '<td width="20%">';
      echo '<label for="dropdown_fieldtype'.$rand.'" id="label_fieldtype">';
      echo _n('Type', 'Types', 1);
      echo '<span style="color:red;">*</span>';
      echo '</label>';
      echo '</td>';

      echo '<td width="30%">';
      $fieldtypes = PluginFormcreatorFields::getNames();
      Dropdown::showFromArray('fieldtype', $fieldtypes, [
         'value'       => $this->fields['fieldtype'],
         'on_change'   => 'plugin_formcreator_changeQuestionType();',
         'rand'        => $rand,
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td>';
      echo '<label for="dropdown_plugin_formcreator_sections_id'.$rand.'" id="label_name">';
      echo  _n('Section', 'Sections', 1, 'formcreator');
      echo '<span style="color:red;">*</span>';
      echo '</label>';
      echo '</td>';

      echo '<td>';
      $sections = [];
      foreach ((new PluginFormcreatorSection())->getSectionsFromForm($form_id) as $section) {
         $sections[$section->getID()] = $section->getField('name');
      }
      Dropdown::showFromArray('plugin_formcreator_sections_id', $sections, [
         'value' => ($this->fields['plugin_formcreator_sections_id']) ?:intval($_REQUEST['section_id']),
         'rand'  => $rand,
      ]);
      echo '</td>';

      echo '<td>';
      echo '<label for="dropdown_dropdown_values'.$rand.'" id="label_dropdown_values">';
      echo _n('Dropdown', 'Dropdowns', 1);
      echo '</label>';
      echo '<label for="dropdown_glpi_objects<?php'.$rand.'" id="label_glpi_objects">';
      echo _n('GLPI object', 'GLPI objects', 1, 'formcreator');
      echo '</label>';
      echo '<label for="dropdown_ldap_auth<?php'.$rand.'" id="label_glpi_ldap">';
      echo _n('LDAP directory', 'LDAP directories', 1);
      echo '</label>';
      echo '</td>';

      echo '<td>';
      echo '<div id="dropdown_values_field">';
      $optgroup = Dropdown::getStandardDropdownItemTypes();
      $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
      array_unshift($optgroup, '---');
      Dropdown::showFromArray('dropdown_values', $optgroup, [
         'value'     => $decodedValues['itemtype'],
         'rand'      => $rand,
         'on_change' => 'plugin_formcreator_change_dropdown("' . $rand . '"); changeQuestionType();',
      ]);
      echo '</div>';
      echo '<div id="glpi_objects_field">';
      $optgroup = [
         __("Assets") => [
            Computer::class         => Computer::getTypeName(2),
            Monitor::class          => Monitor::getTypeName(2),
            Software::class         => Software::getTypeName(2),
            Networkequipment::class => Networkequipment::getTypeName(2),
            Peripheral::class       => Peripheral::getTypeName(2),
            Printer::class          => Printer::getTypeName(2),
            Cartridgeitem::class    => Cartridgeitem::getTypeName(2),
            Consumableitem::class   => Consumableitem::getTypeName(2),
            Phone::class            => Phone::getTypeName(2),
            Line::class             => Line::getTypeName(2)],
         __("Assistance") => [
            Ticket::class           => Ticket::getTypeName(2),
            Problem::class          => Problem::getTypeName(2),
            TicketRecurrent::class  => TicketRecurrent::getTypeName(2)],
         __("Management") => [
            Budget::class           => Budget::getTypeName(2),
            Supplier::class         => Supplier::getTypeName(2),
            Contact::class          => Contact::getTypeName(2),
            Contract::class         => Contract::getTypeName(2),
            Document::class         => Document::getTypeName(2),
            Project::class          => Project::getTypeName(2)],
         __("Tools") => [
            Reminder::class         => __("Notes"),
            RSSFeed::class          => __("RSS feed")],
         __("Administration") => [
            User::class             => User::getTypeName(2),
            Group::class            => Group::getTypeName(2),
            Entity::class           => Entity::getTypeName(2),
            Profile::class          => Profile::getTypeName(2)],
      ];
      array_unshift($optgroup, '---');
      Dropdown::showFromArray('glpi_objects', $optgroup, [
         'value'     => $this->fields['values'],
         'rand'      => $rand,
         'on_change' => 'plugin_formcreator_change_glpi_objects("' . $rand . '");',
      ]);
      echo '</div>';
      echo '<div id="glpi_ldap_field">';
      $ldap_values = json_decode(plugin_formcreator_decode($this->fields['values']), JSON_OBJECT_AS_ARRAY);
      if ($ldap_values === null) {
         $ldap_values = [];
      }
      Dropdown::show('AuthLDAP', [
         'name'      => 'ldap_auth',
         'rand'      => $rand,
         'value'     => (isset($ldap_values['ldap_auth'])) ? $ldap_values['ldap_auth'] : '',
         'on_change' => 'change_LDAP(this)',
      ]);
      echo '</div>';
      echo '</td>';
      echo '</tr>';

      // DOM selectors of all possible parameters
      $allParameterSelectors = [];

      // generate JS to show / hide parameters depending on field type
      $showHideForFieldTypeJs = "
      function plugin_formcreator_changeQuestionType() {
         var value = document.getElementById('dropdown_fieldtype$rand').value
         plugin_formcreator_hideAllParameters()" . "\n";

      // build JS code to show parameters for the current question type
      // also colelcts all selectors of the parameters to hide all parameters
      $showHideForFieldTypeJs.= "switch(value) {" . "\n";
      $evenRow = 0;
      foreach (PluginFormcreatorFields::getClasses() as $fieldType => $classname) {
         $evenRow++;
         $showHideForFieldTypeJs.= "case '$fieldType':" . "\n";
         $field = new $classname([]);
         $evenColumnGroup = 0;
         foreach ($field->getEmptyParameters() as $parameter) {
            $evenColumnGroup++;
            if ($parameter->getParameterFormSize() > 0) {
               // The parameter needs a 4 columns to show its form
               // Force a hew table row prior showing the parameter form
               if (($evenColumnGroup % 2) === 0) {
                  $evenColumnGroup++;
               }
            }
            if (($evenColumnGroup % 2) === 1) {
               echo '<tr class="line' . $evenRow % 2 . '">';
            }
            $jsSelector = $parameter->getJsShowHideSelector();
            // Output the table row for the parameter
            echo $parameter->getParameterForm($form, $this);

            // If the parameter form needs 4 columns, count it twice
            if ($parameter->getParameterFormSize() > 0) {
               $evenColumnGroup++;
            }

            // generate JS code to show the parameter depending on the selected field type
            $showHideForFieldTypeJs.= "$('$jsSelector').show()" . "\n";
            $showHideForFieldTypeJs.= "$('$jsSelector + td').show()" . "\n";

            // save the selector to build JS code to hide all parameters
            $allParameterSelectors[] = $jsSelector;
            if (($evenColumnGroup % 2) === 0) {
               echo '</tr>';
            }
         }
         if ($evenColumnGroup > 0 && ($evenColumnGroup % 2) === 1) {
            // If the question has an odd quantity of parameters
            // the last row is incomplete
            // Fill it with an empty dumy cell
            // show or hide it with the last parameter
            echo '<td colspan="2"></td>';
            $showHideForFieldTypeJs.= "$('$jsSelector + td + td').show()" . "\n";
            $allParameterSelectors[] = "$jsSelector + td + td";
            echo '</tr>';
         }
         $showHideForFieldTypeJs.= "break" . "\n";
      }
      $showHideForFieldTypeJs.= "}
         changeQuestionType()
      }

      function plugin_formcreator_hideAllParameters() {
         showFields(0, 0, 0, 0, 0, 0, 0, 0, 0, 0)" . "\n";

      foreach ($allParameterSelectors as $jsSelector) {
         $showHideForFieldTypeJs.= "$('$jsSelector').hide()" . "\n";
         $showHideForFieldTypeJs.= "$('$jsSelector + td').hide()" . "\n";
      }
      $showHideForFieldTypeJs.= "}" . "\n";

      echo '<tr class="line0" id="required_tr">';
      echo '<td>';
      echo '<label for="dropdown_required'.$rand.'" id="label_required">';
      echo __('Required', 'formcreator');
      echo '</label>';
      echo '</td>';

      echo '<td>';
      dropdown::showYesNo('required', $this->fields['required'], -1, [
         'rand'  => $rand,
      ]);
      echo '</td>';

      echo '<td>';
      echo '<label for="dropdown_show_empty'.$rand.'" id="label_show_empty">';
      echo __('Show empty', 'formcreator');
      echo '</label>';
      echo '</td>';

      echo '<td>';
      echo '<div id="show_empty">';
      dropdown::showYesNo('show_empty', $this->fields['show_empty'], -1, [
         'rand'  => $rand,
      ]);
      echo '</div>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1" id="cat_restrict_tr">';
      echo '<td>';
      echo '<label for="dropdown_show_ticket_categories'.$rand.'" id="label_show_ticket_categories">';
      echo __('Show ticket categories', 'formcreator');
      echo '</label>';
      echo '</td>';
      echo '<td>';
      $ticketCategoriesOptions = [
         'request'  => __('Request categories', 'formcreator'),
         'incident' => __('Incident categories', 'formcreator'),
         'both'     => __('Request categories', 'formcreator'). " + ".__('Incident categories', 'formcreator'),
         'change'   => __('Change'),
         'all'      => __('All'),
      ];
      dropdown::showFromArray('show_ticket_categories', $ticketCategoriesOptions, [
         'rand'  => $rand,
         'value' => isset($decodedValues['show_ticket_categories'])
                    ? $decodedValues['show_ticket_categories']
                    : 'both'
      ]);
      echo '</td>';
      echo '<td>';
      echo '<label for="dropdown_show_ticket_categories_depth'.$rand.'" id="label_show_ticket_categories_depth">';
      echo __('Limit ticket categories depth', 'formcreator');
      echo '</label>';
      echo '</td>';
      echo '<td>';
      dropdown::showNumber('show_ticket_categories_depth', [
                           'rand'  => $rand,
                           'value' => isset($decodedValues['show_ticket_categories_depth'])
                                      ? $decodedValues['show_ticket_categories_depth']
                                      : 0,
                           'min' => 1,
                           'max' => 16,
                           'toadd' => [0 => __('No limit', 'formcreator')],
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1" id="cat_root_tr">';
      echo '<td>';
      echo '<label for="dropdown_root_ticket_categories'.$rand.'" id="label_root_ticket_categories">';
      echo __('ticket categories root', 'formcreator');
      echo '</label>';
      echo '</td>';
      echo '<td>';
      $decodedValue = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
      $rootValue = isset($decodedValue['show_ticket_categories_root'])
                     ? $decodedValue['show_ticket_categories_root']
                     : Dropdown::EMPTY_VALUE;
      Dropdown::show(ITILCategory::class, [
         'name'  => 'show_ticket_categories_root',
         'value' => $rootValue,
         'rand'  => $rand,
      ]);
      echo '</td>';
      echo '<td>';
      echo '</td>';
      echo '<td>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1" id="values_tr">';
      echo '<td>';
      echo '<label for="dropdown_default_values'.$rand.'" id="label_default_values">';
      echo __('Default values');
      echo '<small>('.__('One per line for lists', 'formcreator').')</small>';
      echo '</label>';
      echo '<label for="dropdown_dropdown_default_value'.$rand.'" id="label_dropdown_default_value">';
      echo __('Default value');
      echo '</label>';
      echo '</td>';
      echo '<td>';
      $defaultValues = '';
      if (!$this->isNewItem()) {
         $fieldObject = PluginFormcreatorFields::getFieldInstance(
            $this->fields['fieldtype'],
            $this
         );
         $fieldObject->deserializeValue($this->fields['default_values']);
         $defaultValues = $fieldObject->getValueForDesign();
      }
      echo '<textarea name="default_values" id="default_values" rows="4" cols="40"'
         .'style="width: 90%">'
         .$defaultValues
         .'</textarea>';
      echo '<div id="dropdown_default_value_field">';
      if (!empty($this->fields['values'])) {
         if ($this->fields['fieldtype'] == 'glpiselect' && class_exists($this->fields['values'])) {
            Dropdown::show($this->fields['values'], [
               'name'  => 'dropdown_default_value',
               'value' => $this->fields['default_values'],
               'rand'  => $rand,
            ]);
         }
         if ($this->fields['fieldtype'] == 'dropdown') {
            $decodedValue = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
            if (class_exists($decodedValue['itemtype'])) {
               Dropdown::show($decodedValue['itemtype'], [
                  'name'  => 'dropdown_default_value',
                  'value' => $this->fields['default_values'],
                  'rand'  => $rand,
               ]);
            }
         }
      }
      echo '</div>';
      echo '</td>';

      echo '<td>';
      echo '<label for="values" id="label_values">';
      echo __('Values', 'formcreator');
      echo '<small>('.__('One per line', 'formcreator').')</small>';
      echo '</label>';
      echo '</td>';
      echo '<td>';
      echo '<textarea name="values" id="values" rows="4" cols="40"'
           .'style="width: 90%">'.$this->fields['values'].'</textarea>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1" id="ldap_tr">';
      echo '<td>';
      echo '<label for="ldap_filter">';
      echo __('Filter', 'formcreator');
      echo '</label>';
      echo '</td>';

      echo '<td>';
      echo '<input type="text" name="ldap_filter" id="ldap_filter" style="width:98%;"'
           .'value="'.(isset($ldap_values['ldap_filter']) ? $ldap_values['ldap_filter'] : '').'" />';
      echo '</td>';

      echo '<td>';
      echo '<label for="ldap_attribute">';
      echo __('Attribute', 'formcreator');
      echo '</label>';
      echo '</td>';

      echo '<td>';
      $rand2 = mt_rand();
      Dropdown::show('RuleRightParameter', [
         'name'  => 'ldap_attribute',
         'rand'  => $rand2,
         'value' => (isset($ldap_values['ldap_attribute'])) ? $ldap_values['ldap_attribute'] : '',
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0" id="ldap_tr2">';
      echo '<td>';
      echo '</td>';
      echo '<td>';
      echo '</td>';
      echo '<td colspan="2">&nbsp;</td>';
      echo '</tr>';

      echo '<tr class="line1" id="description_tr">';
      echo '<td>';
      echo '<label for="description" id="label_description">';
      echo __('Description');
      echo '</label>';
      echo '</td>';

      echo '<td width="80%" colspan="3">';
      echo '<textarea name="description" id="description" rows="6" cols="108"'
           .'style="width: 97%">'.$this->fields['description'].'</textarea>';
      Html::initEditorSystem('description');
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<th colspan="4">';
      echo '<label for="dropdown_show_rule'.$rand.'" id="label_show_type">';
      echo __('Show field', 'formcreator');
      echo '</label>';
      echo '</th>';
      echo '</tr>';

      echo '<tr">';
      echo '<td colspan="4">';
      Dropdown::showFromArray('show_rule', [
         'always'       => __('Always displayed', 'formcreator'),
         'hidden'       => __('Hidden unless', 'formcreator'),
         'shown'        => __('Displayed unless', 'formcreator'),
      ], [
         'value'        => $this->fields['show_rule'],
         'on_change'    => 'toggleCondition(this);',
         'rand'         => $rand,
      ]);

      echo '</td>';
      echo '</tr>';
      $questionCondition = new PluginFormcreatorQuestion_Condition();
      $questionConditions = $questionCondition->getConditionsFromQuestion($ID);
      reset($questionConditions);
      $questionCondition = array_shift($questionConditions);
      if ($questionCondition !== null) {
            echo $questionCondition->getConditionHtml($form_id, 0, true);
      }
      foreach ($questionConditions as $questionCondition) {
         echo $questionCondition->getConditionHtml($form_id);
      }
      echo '<tr class="line1">';
      echo '<td colspan="4" class="center">';
      echo '<input type="hidden" name="uuid" value="'.$this->fields['uuid'].'" />';
      echo '<input type="hidden" name="id" value="'.$ID.'" />';
      echo '<input type="hidden" name="plugin_formcreator_forms_id" value="'.intval($form_id).'" />';
      if (0 == $ID) {
         echo '<input type="submit" name="add" class="submit_button" value="'.__('Add').'" />';
      } else {
         echo '<input type="submit" name="update" class="submit_button" value="'.__('Save').'" />';
      }
      echo '</td>';
      echo '</tr>';
      $rootDoc = $CFG_GLPI['root_doc'];
      $allTabFields = PluginFormcreatorFields::printAllTabFieldsForJS();

      echo Html::scriptBlock("$showHideForFieldTypeJs

      function changeQuestionType() {
         var value = document.getElementById('dropdown_fieldtype$rand').value;

         if(value != '') {
            var tab_fields_fields = [];
            $allTabFields

            eval(tab_fields_fields[value]);
         } else {
            showFields(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
         }
      }
      plugin_formcreator_changeQuestionType();
      function showFields(required, default_values, values, range, show_empty, regex, show_type, dropdown_value, glpi_object, ldap_values) {
         if(required) {
            document.getElementById('dropdown_required$rand').style.display   = 'inline';
            document.getElementById('label_required').style.display                          = 'inline';
         } else {
            document.getElementById('dropdown_required$rand').style.display   = 'none';
            document.getElementById('label_required').style.display                          = 'none';
         }
         if(default_values) {
            document.getElementById('default_values').style.display                          = 'inline';
            document.getElementById('label_default_values').style.display                    = 'inline';
         } else {
            document.getElementById('default_values').style.display                          = 'none';
            document.getElementById('label_default_values').style.display                    = 'none';
         }
         if(show_type) {
            document.getElementById('dropdown_show_rule$rand').style.display  = 'inline';
            document.getElementById('label_show_type').style.display                         = 'inline';
         } else {
            document.getElementById('dropdown_show_rule$rand').style.display  = 'none';
            document.getElementById('label_show_type').style.display                         = 'none';
         }
         if(values) {
            document.getElementById('values').style.display                                  = 'inline';
            document.getElementById('label_values').style.display                            = 'inline';
         } else {
            document.getElementById('values').style.display                                  = 'none';
            document.getElementById('label_values').style.display                            = 'none';
         }
         if(dropdown_value) {
            document.getElementById('dropdown_values_field').style.display = 'inline';
            document.getElementById('label_dropdown_values').style.display                   = 'inline';
            dd = document.getElementById('dropdown_dropdown_values$rand');
            ddvalue = dd.options[dd.selectedIndex].value;
            if(ddvalue == 'ITILCategory') {
               document.getElementById('cat_restrict_tr').style.display                      = 'table-row';
               document.getElementById('cat_root_tr').style.display                          = 'table-row';
            } else {
               document.getElementById('cat_restrict_tr').style.display                      = 'none';
               document.getElementById('cat_root_tr').style.display                          = 'none';
            }
         } else {
            document.getElementById('dropdown_values_field').style.display = 'none';
            document.getElementById('label_dropdown_values').style.display                   = 'none';
            document.getElementById('cat_restrict_tr').style.display                         = 'none';
            document.getElementById('cat_root_tr').style.display                             = 'none';
         }
         if(glpi_object) {
            document.getElementById('glpi_objects_field').style.display = 'inline';
            document.getElementById('label_glpi_objects').style.display                      = 'inline';
         } else {
            document.getElementById('glpi_objects_field').style.display = 'none';
            document.getElementById('label_glpi_objects').style.display                      = 'none';
         }
         if (dropdown_value || glpi_object) {
            document.getElementById('dropdown_default_value_field').style.display            = 'inline';
            document.getElementById('label_dropdown_default_value').style.display            = 'inline';
         } else {
            document.getElementById('dropdown_default_value_field').style.display            = 'none';
            document.getElementById('label_dropdown_default_value').style.display            = 'none';
         }
         if(show_empty) {
            document.getElementById('show_empty').style.display = 'inline';
            document.getElementById('label_show_empty').style.display                        = 'inline';
         } else {
            document.getElementById('show_empty').style.display = 'none';
            document.getElementById('label_show_empty').style.display                        = 'none';
         }
         if(values || default_values || dropdown_value || glpi_object) {
            document.getElementById('values_tr').style.display                               = 'table-row';
         } else {
            document.getElementById('values_tr').style.display                               = 'none';
         }
         if(required || show_empty) {
            document.getElementById('required_tr').style.display                             = 'table-row';
         } else {
            document.getElementById('required_tr').style.display                             = 'none';
         }
         if(ldap_values) {
            document.getElementById('glpi_ldap_field').style.display                         = 'inline';
            document.getElementById('label_glpi_ldap').style.display                         = 'inline';
            document.getElementById('ldap_tr').style.display                                 = 'table-row';
         } else {
            document.getElementById('glpi_ldap_field').style.display                         = 'none';
            document.getElementById('label_glpi_ldap').style.display                         = 'none';
            document.getElementById('ldap_tr').style.display                                 = 'none';
         }
      }

      function addEmptyCondition(target) {
         $.ajax({
            url: '$rootDoc/plugins/formcreator/ajax/question_condition.php',
            data: {
               plugin_formcreator_questions_id: $ID,
               plugin_formcreator_forms_id: $form_id,
               _empty: ''
            }
         }).done(function (data) {
            $(target).parents('tr').after(data);
            $('.plugin_formcreator_logicRow .div_show_condition_logic').first().hide();
         });
      }

      function change_LDAP(ldap) {
         var ldap_directory = ldap.value;

         jQuery.ajax({
           url: '$rootDoc/plugins/formcreator/ajax/ldap_filter.php',
           type: 'POST',
           data: {
               value: ldap_directory,
               _glpi_csrf_token: '" . Session::getNewCSRFToken() . "'
            },
         }).done(function(response){
            document.getElementById('ldap_filter').value = response;
         });
      }
      ");
      echo '</table>';
      Html::closeForm();
   }

   /**
    * Duplicate a question
    *
    * @return integer|boolean ID of  the new question, false otherwise
    */
   public function duplicate() {
      global $DB;

      $oldQuestionId       = $this->getID();
      $newQuestion         = new static();
      $question_condition  = new PluginFormcreatorQuestion_Condition();

      $row = $this->fields;
      unset($row['id'],
            $row['uuid']);

      $row['_skip_checks'] = true;

      // escape text fields
      foreach (['name', 'description'] as $key) {
         $row[$key] = $DB->escape($row[$key]);
      }

      $newQuestion_id = $newQuestion->add($row);
      if ($newQuestion_id === false) {
         return false;
      }

      // Form questions parameters
      $this->field = PluginFormcreatorFields::getFieldInstance(
         $this->getField('fieldtype'),
         $this
      );
      $parameters = $this->field->getParameters();
      foreach ($parameters as $parameter) {
         $row = $parameter->fields;
         $row[PluginFormcreatorQuestion::getForeignKeyField()] = $newQuestion->getID();
         unset($row['id']);
         $parameter->add($row);
      }

      // Form questions conditions
      $rows = $DB->request([
         'FROM'    => $question_condition::getTable(),
         'WHERE'   => [
            'plugin_formcreator_questions_id' => $oldQuestionId
         ]
      ]);
      foreach ($rows as $row) {
         unset($row['id'],
               $row['uuid']);
         $row['plugin_formcreator_questions_id'] = $newQuestion_id;
         if (!$question_condition->add($row)) {
            return false;
         }
      }

      return $newQuestion_id;
   }

   /**
    * Import a section's question into the db
    * @see PluginFormcreatorSection::import
    *
    * @param  integer $sections_id  id of the parent section
    * @param  array   $question the question data (match the question table)
    * @return integer the question's id
    */
   /*
   public static function import($sections_id = 0, $question = []) {
      $item = new self;

      $question['plugin_formcreator_sections_id'] = $sections_id;
      $question['_skip_checks']                   = true;

      if ($questions_id = plugin_formcreator_getFromDBByField($item, 'uuid', $question['uuid'])) {
         // add id key
         $question['id'] = $questions_id;

         // update question
         $item->update($question);
      } else {
         //create question
         $questions_id = $item->add($question);
      }

      if ($questions_id
          && isset($question['_conditions'])) {
         foreach ($question['_conditions'] as $condition) {
            PluginFormcreatorQuestion_Condition::import($questions_id, $condition);
         }
      }

      return $questions_id;
   }
   */

   public static function import(PluginFormcreatorImportLinker $importLinker, $sections_id = 0, $question = []) {
      global $DB;

      $item = new self;

      $question['plugin_formcreator_sections_id'] = $sections_id;
      $question['_skip_checks']                   = true;

      // escape text fields
      foreach (['name', 'description', 'values'] as $key) {
         $question[$key] = $DB->escape($question[$key]);
      }

      if ($questions_id = plugin_formcreator_getFromDBByField($item, 'uuid', $question['uuid'])) {
         // add id key
         $question['id'] = $questions_id;
         $item->field = PluginFormcreatorFields::getFieldInstance(
            $question['fieldtype'],
            $item
         );
         // update question
         $item->update($question);
      } else {
         //create question

         $questions_id = $item->add($question);
      }

      if ($questions_id
          && isset($question['_conditions'])) {
         $importLinker->addImportedObject($question['uuid'], $item);

         foreach ($question['_conditions'] as $condition) {
            PluginFormcreatorQuestion_Condition::import($importLinker, $questions_id, $condition);
         }
         $questionInstance = new self();
         $questionInstance->fields = $question;
         $questionInstance->fields['id'] = $questions_id;
         $field = PluginFormcreatorFields::getFieldInstance(
            $question['fieldtype'],
            $questionInstance
         );
         $parameters = $field->getParameters();
         foreach ($parameters as $fieldName => $parameter) {
            $parameter::import($importLinker, $questions_id, $fieldName, $question['_parameters'][$question['fieldtype']][$fieldName]);
         }
      }

      return $questions_id;
   }

   /**
    * Export in an array all the data of the current instanciated question
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      global $DB;

      if (!$this->getID()) {
         return false;
      }

      $form_question_condition = new PluginFormcreatorQuestion_Condition;
      $question                = $this->fields;

      // remove key and fk
      unset($question['id'],
            $question['plugin_formcreator_sections_id']);

      // get question conditions
      $question['_conditions'] = [];
      $all_conditions = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => $form_question_condition::getTable(),
         'WHERE'  => [
            'plugin_formcreator_questions_id' => $this->getID()
         ]
      ]);
      foreach ($all_conditions as $condition) {
         if ($form_question_condition->getFromDB($condition['id'])) {
            $question['_conditions'][] = $form_question_condition->export($remove_uuid);
         }
      }

      // get question parameters
      $question['_parameters'] = [];
      $this->field = PluginFormcreatorFields::getFieldInstance($this->getField('fieldtype'), $this);
      $parameters = $this->field->getParameters();
      foreach ($parameters as $fieldname => $parameter) {
         $question['_parameters'][$this->fields['fieldtype']][$fieldname] = $parameter->export();
      }

      if ($remove_uuid) {
         $question['uuid'] = '';
      }

      return $question;
   }

   /**
    * get the form belonging to the question
    *
    * @return boolean|PluginFormcreatorForm the form or false if not found
    */
   public function getForm() {
      global $DB;

      $form = new PluginFormcreatorForm();
      $iterator = $DB->request([
         'SELECT' => $form::getForeignKeyField(),
         'FROM' => PluginFormcreatorSection::getTable(),
         'INNER JOIN' => [
            $this::getTable() => [
               'FKEY' => [
                  PluginFormcreatorSection::getTable() => PluginFormcreatorSection::getIndexName(),
                  $this::getTable() => PluginFormcreatorSection::getForeignKeyField()
               ]
            ]
         ],
         'WHERE' => [
            $this::getTable() . '.' . $this::getIndexName() => $this->getID()
         ]
      ]);
      if ($iterator->count() !== 1) {
         return false;
      }
      $form->getFromDB($iterator->next()[$form::getForeignKeyField()]);
      if ($form->isNewItem()) {
         return false;
      }

      return $form;
   }

   /**
    * return array of question objects belonging to a form
    * @param integer $formId
    * @param array $crit array for the WHERE clause
    * @return PluginFormcreatorQuestion[]
    */
   public function getQuestionsFromForm($formId, $crit = []) {
      global $DB;

      $table_question = PluginFormcreatorQuestion::getTable();
      $table_section  = PluginFormcreatorSection::getTable();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $request = [
         'SELECT' => "$table_question.*",
         'FROM' => $table_question,
         'LEFT JOIN' => [
            $table_section => [
               'FKEY' => [
                  $table_question => $sectionFk,
                  $table_section => 'id',
               ],
            ],
         ],
         'WHERE' => [
            $formFk => $formId,
         ],
         'ORDER' => [
            "$table_section.order",
            "$table_question.order",
         ]
      ];
      if (count($crit) > 0) {
         $request['WHERE']['AND'] = $crit;
      }

      $result = $DB->request($request);
      $questions = [];
      while ($row = $result->next()) {
         $question = new self();
         $question->getFromDB($row['id']);
         $questions[$row['id']] = $question;
      }

      return $questions;
   }

   /**
    * Gets questions belonging to a section
    *
    * @param integer $sectionId
    *
    * @return PluginFormcreatorQuestion[]
    */
   public function getQuestionsFromSection($sectionId) {
      global $DB;

      $questions = [];
      $rows = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_formcreator_sections_id' => $sectionId
         ],
         'ORDER'  => 'order ASC'
      ]);
      foreach ($rows as $row) {
            $question = new self();
            $question->getFromDB($row['id']);
            $questions[$row['id']] = $question;
      }

      return $questions;
   }
}
