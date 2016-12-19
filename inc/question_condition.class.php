<?php
class PluginFormcreatorQuestion_Condition extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorQuestion";
   static public $items_id = "plugin_formcreator_questions_id";

   public function prepareInputForAdd($input) {
      // generate a uniq id
      if (!isset($input['uuid'])
            || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public static function getEnumShowLogic() {
      return array(
            'AND'    => 'AND',
            'OR'     => 'OR',
            'XOR'    => 'XOR',
      );
   }

   /**
    * Import a question's condition into the db
    * @see PluginFormcreatorQuestion::import
    *
    * @param  integer $questions_id  id of the parent question
    * @param  array   $condition the condition data (match the condition table)
    * @return integer the condition's id
    */
   public static function import($questions_id = 0, $condition = array()) {
      $item = new self;

      $condition['plugin_formcreator_questions_id'] = $questions_id;

      if ($conditions_id = plugin_formcreator_getFromDBByField($item, 'uuid', $condition['uuid'])) {
         // add id key
         $condition['id'] = $conditions_id;

         // update condition
         $item->update($condition);
      } else {
         //create condition
         $conditions_id = $item->add($condition);
      }

      return $conditions_id;
   }

   /**
    * Export in an array all the data of the current instanciated condition
    * @return array the array with all data (with sub tables)
    */
   public function export() {
      if (!$this->getID()) {
         return false;
      }

      $condition = $this->fields;

      unset($condition['id'],
            $condition['plugin_formcreator_questions_id']);

      return $condition;
   }

   public static function install(Migration $migration)
   {
      global $DB;

      $table = self::getTable();
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                  `id`                                int(11)                NOT NULL AUTO_INCREMENT,
                  `plugin_formcreator_questions_id`   int(11)                NOT NULL DEFAULT '0',
                  `show_field`                        int(11)                NULL     DEFAULT NULL,
                  `show_condition`                    enum('==','!=','<','>','<=','>=') NULL DEFAULT NULL,
                  `show_value`                        varchar(255)           NULL     DEFAULT NULL ,
                  `show_logic`                        enum('AND','OR','XOR') NULL     DEFAULT NULL,
                  `order`                             int(11)                NOT NULL DEFAULT '1',
                  `uuid`                              varchar(255)           NULL     DEFAULT NULL
                  PRIMARY KEY (`id`)
                  )
                  ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }

      // Migration 0.85-1.0 => 0.85-1.1
      $question_table = 'glpi_plugin_formcreator_questions';
      if (FieldExists($question_table, 'show_type', false)) {
         // Migrate date from "questions" table to "questions_conditions" table
         $query  = "SELECT `id`, `show_type`, `show_field`, `show_condition`, `show_value`
         FROM $question_table";
         $result = $DB->query($query);
         while ($line = $DB->fetch_array($result)) {
            switch ($line['show_type']) {
               case 'hide' :
                  $show_rule = 'hidden';
                  break;
               default:
                  $show_rule = 'always';
            }
            switch ($line['show_condition']) {
               case 'notequal' :
                  $show_condition = '!=';
                  break;
               case 'lower' :
                  $show_condition = '<';
                  break;
               case 'greater' :
                  $show_condition = '>';
                  break;
               default:
                  $show_condition = '==';
            }

            $show_field = empty($line['show_field']) ? 'NULL' : $line['show_field'];

            $query_udate = "UPDATE `$question_table` SET
            `show_rule` = '$show_rule'
            WHERE `id` = " . $line['id'];
            $DB->query($query_udate) or die ($DB->error());

            $query_udate = "INSERT INTO `$table` SET
            `plugin_formcreator_questions_id` = {$line['id']},
            `show_field`     = $show_field,
            `show_condition` = '$show_condition',
            `show_value`     = '" . Toolbox::addslashes_deep($line['show_value']) . "'";
            $DB->query($query_udate) or die ($DB->error());
         }

         // Delete old fields
         $query = "ALTER TABLE `$table`
         DROP `show_type`,
         DROP `show_field`,
         DROP `show_condition`,
         DROP `show_value`;";
         $DB->query($query) or die ($DB->error());
      }

      // Migrate "question_conditions" table
      $query  = "SELECT `id`, `show_value`
      FROM `$table`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `$table` SET
         `show_value` = '" . plugin_formcreator_encode($line['show_value']) . "'
                           WHERE `id` = " . $line['id'];
         $DB->query($query_update) or die ($DB->error());
      }

      // add uuid to questions conditions
      if (!FieldExists($table, 'uuid', false)) {
         $migration->addField($table, 'uuid', 'string');
         $migration->migrationOneTable($table);
      }

      if (!FieldExists($table, 'order', false)) {
         $migration->addField($table, 'order', 'integer', array('after' => 'show_logic', 'value' => '1'));
         $migration->migrationOneTable($table);
      }

      // fill missing uuid (force update of questions, see self::prepareInputForUpdate)
      $condition_obj = new self();
      $all_conditions = $condition_obj->find("uuid IS NULL");
      foreach($all_conditions as $conditions_id => $condition) {
         $condition_obj->update(array('id'   => $conditions_id,
               'uuid' => plugin_formcreator_getUuid()));
      }

      return true;
   }

   public function getConditionsFromQuestion($questionId) {
      $questionConditions = array();
      $rows = $this->find("`plugin_formcreator_questions_id` = '$questionId'", "`order` ASC");
      foreach ($rows as $questionConditionId => $row) {
         $questionCondition = new static();
         $questionCondition->getFromDB($questionConditionId);
         $questionConditions[] = $questionCondition;
      }

      return $questionConditions;
   }

   public function getConditionHtml($questionId = 0) {
      global $CFG_GLPI;

      if ($this->isNewItem()) {
         $show_field       = '';
         $show_condition   = '==';
         $show_value       = '';
         $show_logic       = '';
      } else {
         $show_field       = $this->fields['show_field'];
         $show_condition   = $this->fields['show_condition'];
         $show_value       = $this->fields['show_value'];
         $show_logic       = $this->fields['show_logic'];
         $questionId       = $this->fields['plugin_formcreator_questions_id'];
      }
      $rootDoc = $CFG_GLPI['root_doc'];
      $rand = mt_rand();

      $form = new PluginFormcreatorForm();
      $form->getByQuestionId($questionId);
      $form_id = $form->getId();

      $question = new PluginFormcreatorQuestion();
      $questionsInForm = $question->getQuestionsFromForm($form_id);
      $questions_tab = array();
      foreach($questionsInForm as $question) {
         if (strlen($question->getField('name')) > 30) {
            $questions_tab[$question->getID()] = substr($question->getField('name'),
                  0,
                  strrpos(substr($question->getField('name'), 0, 30), ' ')) . '...';
         } else {
            $questions_tab[$question->getID()] = $question->getField('name');
         }
      }

      $html = '';
      $html.= '<tr class="plugin_formcreator_logicRow">';
      $html.= '<td colspan="4">';
      $html.= '<div class="div_show_condition">';

      $html.= '<div class="div_show_condition_field">';
      $html.= Dropdown::showFromArray('show_field[]', $questions_tab, array(
            'display'      => false,
            'used'         => array($questionId => ''),
            'value'        => $show_field,
            'rand'         => $rand,
      ));
      $html.= '</div>';

      $html.= '<div class="div_show_condition_operator">';
      $html.= Dropdown::showFromArray('show_condition[]', array(
            '=='           => '=',
            '!='           => '&ne;',
            '<'            => '&lt;',
            '>'            => '&gt;',
            '<='           => '&le;',
            '>='           => '&ge;',
      ), array(
            'display'      => false,
            'value'        => $show_condition,
            'rand'         => $rand,
      ));
      $html.= '</div>';
      $html.= '<div class="div_show_condition_value">';
      $html.= '<input type="text" name="show_value[]" id="show_value" class="small_text"'
              .'value="'. $show_value . '" size="8">';
      $html.= '</div>';
      $html.= '<div class="div_show_condition_logic">';
      $html.= Dropdown::showFromArray('show_logic[]',
            PluginFormcreatorQuestion_Condition::getEnumShowLogic(),
            array(
                  'display'               => false,
                  'value'                 => $show_logic,
                  'display_emptychoice'   => false,
                  'rand'                  => $rand,
            ));
      $html.= '</div>';
      $html.= '<div class="div_show_condition_add">';
      $html.= '<img src="../../../pics/plus.png" onclick="addEmptyCondition(this)"/></div>';
      $html.= '<div class="div_show_condition_remove">';
      $html.= '<img src="../../../pics/moins.png" onclick="removeNextCondition(this)"/></div>';
      $html.= '</div>';
      $html.= '</td>';
      $html.= '</tr>';

      return $html;
   }

   public static function uninstall()
   {
      global $DB;

      $table = self::getTable();
      $query = "DROP TABLE IF EXISTS `$table`";
      $DB->query($query) or die($DB->error());

      return true;
   }

}