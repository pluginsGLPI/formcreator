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
         echo '<th onclick="plugin_formcreator_editSection(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ')">';
         echo "<a href='#'>";
         echo $section['name'];
         echo '</a>';
         echo '</th>';

         echo '<th align="center">';

         echo "<span class='form_control pointer'>";
         echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/delete.png"
                  title="' . __('Delete', 'formcreator') . '"
                  onclick="plugin_formcreator_deleteSection(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ')"> ';
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
            echo '<td onclick="plugin_formcreator_editQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $question['id'] . ', ' . $section['id'] . ')">';
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
                     onclick="plugin_formcreator_deleteQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $question['id'] . ')"> ';
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
         echo '<a href="javascript:plugin_formcreator_addQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ');">
                   <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+"/>
                   '.__('Add a question', 'formcreator').'
               </a>';
         echo '</td>';
         echo '</tr>';
      }

      echo '<tr class="line1 section_row">';
      echo '<th id="add_section_th">';
      echo '<a href="javascript:plugin_formcreator_addSection(' . $item->getId() . ', \'' . $token . '\');">'
            . '<img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+">'
            .__('Add a section', 'formcreator')
            . '</a>';
      echo '</th>';
      echo '<th></th>';
      echo '</tr>';

      echo '</table>';
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

      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      if (isset($input[$sectionFk])) {
         // If change section, reorder questions
         if ($input[$sectionFk] != $this->fields[$sectionFk]) {
            $oldId = $this->fields[$sectionFk];
            $newId = $input[$sectionFk];
            $order = $this->fields['order'];
            // Reorder other questions from the old section
            $DB->update(
               self::getTable(),
               new QueryExpression("`order` = `order` - 1"),
               [
                  'order' => ['>', $order],
                  $sectionFk => $oldId,
               ]
            );

            // Get the order for the new section
            $maxOrder = PluginFormcreatorCommon::getMax($this, [
               $sectionFk => $newId
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
    * Move the question up in the ordered list of questions in the section
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
                     $showCondition   = html_entity_decode(array_shift($input['show_condition']));
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
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $DB->update(
         $table,
         new QueryExpression("`order` = `order` - 1"),
        [
           'order' => ['>', $order],
           $sectionFk => $this->fields[$sectionFk]
        ]
      );

      // Always show questions with conditional display on the question being deleted
      $questionId = $this->fields['id'];
      $DB->update(
         $table,
         [
            'show_rule' => 'always'
         ],
         [
            'id' => new QuerySubquery([
               'SELECT' => self::getForeignKeyField(),
               'FROM' => $question_condition_table,
               'WHERE' => ['show_field' => $questionId]
            ])
         ]
      );

      $DB->delete(
         $question_condition_table,
         [
            'OR' => [
               self::getForeignKeyField() => $questionId,
               'show_field' => $questionId
            ]
         ]
      );
   }

   public function showForm($ID, $options = []) {
      global $CFG_GLPI;

      $rootDoc = $CFG_GLPI['root_doc'];

      // Find the form of the question
      $section = new PluginFormcreatorSection();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $section->getFromDB($this->fields[$sectionFk]);
      $form = new PluginFormcreatorForm();
      $form->getFromDBBySection($section);
      $form_id = $form->getID();

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

      // name
      echo '<td width="20%">';
      echo '<label for="name" id="label_name">';
      echo  __('Title');
      echo '<span style="color:red;">*</span>';
      echo '</label>';
      echo '</td>';

      echo '<td width="30%">';
      echo '<input type="text" name="name" id="name" style="width:90%;" autofocus value="'.$this->fields['name'].'" class="required"';
      echo '</td>';

      // Section
      echo '<td width="20%">';
      echo '<label for="dropdown_plugin_formcreator_sections_id'.$rand.'" id="label_name">';
      echo  _n('Section', 'Sections', 1, 'formcreator');
      echo '<span style="color:red;">*</span>';
      echo '</label>';
      echo '</td>';

      echo '<td width="30%">';
      $sections = [];
      foreach ((new PluginFormcreatorSection())->getSectionsFromForm($form_id) as $section) {
         $sections[$section->getID()] = $section->getField('name');
      }
      $currentSectionId = ($this->fields['plugin_formcreator_sections_id'])
                        ? $this->fields['plugin_formcreator_sections_id']
                        : intval($_REQUEST['section_id']);
      Dropdown::showFromArray('plugin_formcreator_sections_id', $sections, [
         'value' => $currentSectionId,
         'rand'  => $rand,
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';

      // Field type
      echo '<td>';
      echo '<label for="dropdown_fieldtype'.$rand.'" id="label_fieldtype">';
      echo _n('Type', 'Types', 1);
      echo '<span style="color:red;">*</span>';
      echo '</label>';
      echo '</td>';

      echo '<td>';
      $fieldtypes = PluginFormcreatorFields::getNames();
      Dropdown::showFromArray('fieldtype', $fieldtypes, [
         'value'       => $this->fields['fieldtype'],
         'on_change'   => "plugin_formcreator_changeQuestionType($rand)",
         'rand'        => $rand,
      ]);
      echo '</td>';

      echo '<td id="plugin_formcreator_subtype_label">';
      echo '</td>';

      echo '<td id="plugin_formcreator_subtype_value">';
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      // required
      echo '<td>';
      echo '<label for="dropdown_required'.$rand.'" id="label_required">';
      echo __('Required', 'formcreator');
      echo '</label>';
      echo '</td>';

      echo '<td id="plugin_formcreator_required">';
      dropdown::showYesNo('required', $this->fields['required'], -1, [
         'rand'  => $rand,
      ]);
      echo '</td>';

      // show empty
      echo '<td>';
      echo '<label for="dropdown_show_empty'.$rand.'" id="label_show_empty">';
      echo __('Show empty', 'formcreator');
      echo '</label>';
      echo '</td>';

      echo '<td id="plugin_formcreator_show_empty">';
      dropdown::showYesNo('show_empty', $this->fields['show_empty'], -1, [
         'rand'  => $rand,
      ]);
      echo '</td>';
      echo '</tr>';

      // DOM selectors of all possible parameters
      $allParameterSelectors = [];

      // Empty row for question-specific settings
      // To be replaced bydynamically
      echo '<tr class="plugin_formcreator_question_specific">';
      echo '<td></td><td></td><td></td><td></td>';
      echo '</tr>';

      echo '<tr>';
      echo '<th colspan="4">';
      echo '<label for="dropdown_show_rule'.$rand.'" id="label_show_type">';
      echo __('Show field', 'formcreator');
      echo '</label>';
      echo '</th>';
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

      echo '<tr class="line1" id="description_tr">';
      // Description of the question
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


      echo '<tr">';
      echo '<td colspan="4">';
      Dropdown::showFromArray('show_rule', [
         'always'       => __('Always displayed', 'formcreator'),
         'hidden'       => __('Hidden unless', 'formcreator'),
         'shown'        => __('Displayed unless', 'formcreator'),
      ], [
         'value'        => $this->fields['show_rule'],
         'on_change'    => 'plugin_formcreator_toggleCondition(this);',
         'rand'         => $rand,
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td colspan="4" class="center">';
      echo '<input type="hidden" name="uuid" value="'.$this->fields['uuid'].'" />';
      echo '<input type="hidden" name="id" value="'.$ID.'" />';
      // plugin_formcreator_forms_id should be removed
      // the form can be retrieved from plugin_formcreatior_sections_id
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
      echo '</table>';

      echo Html::scriptBlock("plugin_formcreator_changeQuestionType($rand)");
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
      $result = $DB->request([
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
            'AND' => [$formFk => $formId] + $crit,
         ],
         'ORDER' => [
            "$table_section.order",
            "$table_question.order",
         ]
      ]);

      $questions = [];
      foreach ($result as $row) {
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

   public static function dropdownForForm($formId, $crit, $name, $value) {
      global $DB;

      $table_question = PluginFormcreatorQuestion::getTable();
      $table_section  = PluginFormcreatorSection::getTable();
      $sectionFk      = PluginFormcreatorSection::getForeignKeyField();
      $formFk         = PluginFormcreatorForm::getForeignKeyField();
      $result = $DB->request([
         'SELECT' => [
            "$table_question.id AS qid",
            "$table_question.name AS qname",
            "$table_section.name AS sname",
         ],
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
            'AND' => [$formFk => $formId] + $crit,
         ],
         'ORDER' => [
            "$table_section.order",
            "$table_question.order",
         ]
      ]);

      $items = [];
      foreach ($result as $question) {
         if (!isset($items[$question['sname']])) {
            $items[$question['sname']] = [];
         }
         $items[$question['sname']][$question['qid']] = $question['qname'];
      }

      Dropdown::showFromArray($name, $items, []);
   }
}
