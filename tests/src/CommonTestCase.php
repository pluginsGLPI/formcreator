<?php

namespace GlpiPlugin\Formcreator\Tests;
use Session;
use Html;
use DB;
use Auth;

abstract class CommonTestCase extends CommonDBTestCase
{
   protected $str = null;

   public function beforeTestMethod($method) {
      self::resetGLPILogs();
   }

   protected function resetState() {
      self::resetGLPILogs();

      $DBvars = get_class_vars('DB');
      $result = $this->drop_database(
         $DBvars['dbuser'],
         $DBvars['dbhost'],
         $DBvars['dbdefault'],
         $DBvars['dbpassword']
      );

      $result = $this->load_mysql_file($DBvars['dbuser'],
         $DBvars['dbhost'],
         $DBvars['dbdefault'],
         $DBvars['dbpassword'],
         './save.sql'
      );
   }

   protected function resetGLPILogs() {
      // Reset error logs
      file_put_contents(GLPI_LOG_DIR."/sql-errors.log", '');
      file_put_contents(GLPI_LOG_DIR."/php-errors.log", '');
   }

   protected function setupGLPIFramework() {
      global $CFG_GLPI, $DB, $LOADED_PLUGINS, $PLUGIN_HOOKS, $AJAX_INCLUDE, $PLUGINS_INCLUDED;

      if (session_status() == PHP_SESSION_ACTIVE) {
         session_write_close();
      }
      $LOADED_PLUGINS = null;
      $PLUGINS_INCLUDED = null;
      $AJAX_INCLUDE = null;
      $_SESSION = [];
      if (is_readable(GLPI_ROOT . "/config/config.php")) {
         $configFile = "/config/config.php";
      } else {
         $configFile = "/inc/config.php";
      }
      include (GLPI_ROOT . $configFile);
      require (GLPI_ROOT . "/inc/includes.php");
      //\Toolbox::setDebugMode(Session::DEBUG_MODE);

      $DB = new DB();

      include_once (GLPI_ROOT . "/inc/timer.class.php");

      // Security of PHP_SELF
      $_SERVER['PHP_SELF'] = Html::cleanParametersURL($_SERVER['PHP_SELF']);

      if (session_status() == PHP_SESSION_ACTIVE) {
         session_write_close();
      }
      ini_set('session.use_cookies', 0); //disable session cookies
      session_start();
      $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
   }

   protected function login($name, $password, $noauto = false) {
      Session::start();
      $auth = new Auth();
      $result = $auth->login($name, $password, $noauto);
      $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
      $this->setupGLPIFramework();

      return $result;
   }

   public function afterTestMethod($method) {
      // Check logs
      $fileSqlContent = file_get_contents(GLPI_LOG_DIR."/sql-errors.log");
      $filePhpContent = file_get_contents(GLPI_LOG_DIR."/php-errors.log");

      $class = static::class;
      $class = str_replace('\\', '_', $class);
      if ($fileSqlContent != '') {
         rename(GLPI_LOG_DIR."/sql-errors.log", GLPI_LOG_DIR."/sql-errors__${class}__$method.log");
      }
      if ($fileSqlContent != '') {
         rename(GLPI_LOG_DIR."/php-errors.log", GLPI_LOG_DIR."/php-errors__${class}__$method.log");
      }

      // Reset log files
      self::resetGLPILogs();

      // Test content
      $this->variable($fileSqlContent)->isEqualTo('', 'sql-errors.log not empty');
      $this->variable($filePhpContent)->isEqualTo('', 'php-errors.log not empty');
   }

   protected function loginWithUserToken($userToken) {
      // Login as guest user
      $_REQUEST['user_token'] = $userToken;
      Session::destroy();
      self::login('', '', false);
      unset($_REQUEST['user_token']);
   }

   /**
    * Get a unique random string
    */
   protected function getUniqueString() {
      if (is_null($this->str)) {
         return $this->str = uniqid('str');
      }
      return $this->str .= 'x';
   }

   protected function getUniqueEmail() {
      return $this->getUniqueString() . "@example.com";
   }

   public function getMockForItemtype($classname, $methods = []) {
      // create mock
      $mock = $this->getMockBuilder($classname)
                   ->setMethods($methods)
                   ->getMock();

      //Override computation of table to match the original class name
      // see CommonDBTM::getTable()
      $_SESSION['glpi_table_of'][get_class($mock)] = getTableForItemType($classname);

      return $mock;
   }

   protected function terminateSession() {
      if (session_status() == PHP_SESSION_ACTIVE) {
         session_write_close();
      }
   }

   protected function restartSession() {
      if (session_status() != PHP_SESSION_ACTIVE) {
         session_start();
         session_regenerate_id();
         session_id();
         //$_SESSION["MESSAGE_AFTER_REDIRECT"] = [];
      }
   }

   protected function getForm($input = []) {
      if (!isset($input['name'])) {
         $input['name'] = 'form';
      }
      $form = new \PluginFormcreatorForm();
      $form->add($input);
      $form->getFromDB($form->getID());

      return $form;
   }

   protected function getSection($input = []) {
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      if (!isset($input[$formFk])) {
         $formId = $this->getForm()->getID();
         $input[$formFk] = $formId;
      }
      if (!isset($input['name'])) {
         $input['name'] = 'section';
      }
      $section = new \PluginFormcreatorSection();
      $section->add($input);
      return $section;
   }

   protected function getQuestion($input = []) {
      if (!isset($input['name'])) {
         $input['name'] = 'question';
      }
      $sectionFk = \PluginFormcreatorSection::getForeignKeyField();
      if (!isset($input[$sectionFk])) {
         $sectionId = $this->getSection()->getID();
         $input[$sectionFk] = $sectionId;
      }
      $defaultInput = [
         'fieldtype'                      => 'text',
         'values'                         => "",
         'required'                       => '0',
         'show_empty'                     => '0',
         'default_values'                 => '',
         'desription'                     => '',
         'order'                          => '1',
         'show_rule'                      => 'always',
         '_parameters'     => [
            'text' => [
               'range' => [
                  'range_min' => '',
                  'range_max' => '',
               ],
               'regex' => [
                  'regex' => ''
               ]
            ]
         ],
      ];
      $input = array_merge($defaultInput, $input);
      $question = new \PluginFormcreatorQuestion();
      $question->add($input);

      return $question;
   }

   protected function _checkForm($form = []) {
      $this->assertArrayNotHasKey('id', $export);
      $this->assertArrayNotHasKey('plugin_formcreator_categories_id', $export);
      $this->assertArrayNotHasKey('entities_id', $export);
      $this->assertArrayNotHasKey('usage_count', $export);
      $this->assertArrayHasKey('is_recursive', $export);
      $this->assertArrayHasKey('access_rights', $export);
      $this->assertArrayHasKey('requesttype', $export);
      $this->assertArrayHasKey('name', $export);
      $this->assertArrayHasKey('description', $export);
      $this->assertArrayHasKey('content', $export);
      $this->assertArrayHasKey('is_active', $export);
      $this->assertArrayHasKey('language', $export);
      $this->assertArrayHasKey('helpdesk_home', $export);
      $this->assertArrayHasKey('is_deleted', $export);
      $this->assertArrayHasKey('validation_required', $export);
      $this->assertArrayHasKey('is_default', $export);
      $this->assertArrayHasKey('uuid', $export);
      $this->assertArrayHasKey('_sections', $export);
      $this->assertArrayHasKey('_validators', $export);
      $this->assertArrayHasKey('_targets', $export);
      $this->assertArrayHasKey('_profiles', $export);
   }

   protected function _checkSection($section = []) {
      $keys = [
         'name',
         'order',
         'uuid',
         '_questions',
      ];
      $this->array($section)->notHasKeys([
         'id',
         'plugin_formcreator_forms_id',
      ]);
      $this->array($section)
         ->hasKeys($keys)
         ->size->isEqualTo(count($keys));

      foreach ($section["_questions"] as $question) {
         $this->_checkQuestion($question);
      }
   }

   protected function _checkQuestion($question = []) {
      $keys = [
         'fieldtype',
         'name',
         'required',
         'show_empty',
         'default_values',
         'values',
         'range_min',
         'range_max',
         'description',
         'regex',
         'order',
         'show_rule',
         'uuid',
         '_conditions',
      ];

      $this->array($question)->notHasKeys([
         'id',
         'plugin_formcreator_sections_id',
      ])->hasKeys($keys)
         ->size->isEqualTo(count($keys));;

      foreach ($question["_conditions"] as $condition) {
         $this->_checkCondition($condition);
      }
   }

   protected function _checkCondition($condition = []) {
      $keys = [
         'show_field',
         'show_condition',
         'show_value',
         'show_logic',
         'order',
         'uuid',
      ];

      $this->array($condition)->notHasKeys([
         'id',
         'plugin_formcreator_questions_id',
      ])->hasKeys($keys)
         ->size->isEqualTo(count($keys));
   }

   protected function _checkValidator($validator = []) {
      $this->array($validator)->notHasKeys([
         'id',
         'plugin_formcreator_forms_id',
         'items_id',
      ])->hasKeys([
         'itemtype',
         '_item',
         'uuid',
      ]);
   }

   protected function _checkTarget($target = []) {
      $this->array($target)->notHasKeys([
         'id',
         'plugin_formcreator_forms_id',
         'items_id',
      ])->hasKeys([
         'itemtype',
         '_data',
         'uuid',
      ]);
      $this->array($target['_data'])->hasKeys(['_actors']);

      if ($target['itemtype'] === \PluginFormcreatorTargetTicket::class) {
         $this->_checkTargetTicket($target['_data']);
      }

      foreach ($target["_data"]['_actors'] as $actor) {
         $this->_checkActor($actor);
      }
   }

   protected function _checkTargetTicket($targetticket = []) {
      $keys = [
         'title',
         'content',
         'due_date_rule',
         'due_date_question',
         'due_date_value',
         'due_date_period',
         'urgency_rule',
         'urgency_question',
         'location_rule',
         'location_question',
         'validation_followup',
         'destination_entity',
         'destination_entity_value',
         'tag_type',
         'tag_questions',
         'tag_specifics',
         'category_rule',
         'category_question',
         '_actors',
         '_ticket_relations',
      ];
      $this->array($targetticket)->notHasKeys([
         'id',
         'tickettemplates_id',
      ])->hasKeys($keys)
      ->size->isEqualTo(count($keys));
   }

   protected function _checkActor($actor = []) {
      $this->array($actor)->notHasKeys([
         'id',
         'plugin_formcreator_targettickets_id',
      ])->hasKeys([
         'use_notification',
         'uuid',
      ]);
      //we should have only one of theses keys : actor_value ,_question ,_user ,_group ,_supplier
      $actor_value_found_keys = preg_grep('/((actor_value)|(_question)|(_user)|(_group)|(_supplier))/',
                                          array_keys($actor));
      $this->array($actor_value_found_keys)->size->isEqualTo(1);

   }

   protected function _checkFormProfile($form_profile = []) {
      $this->array($form_profile)->notHasKeys([
         'id',
         'plugin_formcreator_forms_id',
         'profiles_id'
      ])->hasKeys([
         '_profile',
         'uuid',
      ]);
   }

   /**
    * Create a whole form
    * Method incomplete, some new things needs to be implemented
    */
   protected function createFullForm(
      $formData,
      $sectionsData,
      $targetsData
   ) {
      $form = new \PluginFormcreatorForm();
      $formId = $form->add($formData);
      $this->boolean($form->isNewItem())->isFalse();

      $sections = [];
      $questions = [];
      foreach ($sectionsData as $sectionData) {
         // Keep questions data set apart from sections data
         $questionsData = $sectionData['questions'];
         unset($sectionData['questions']);

         // Create section
         $sectionData['plugin_formcreator_forms_id'] = $form->getID();
         $section = new \PluginFormcreatorSection();
         $section->add($sectionData);
         $this->boolean($section->isNewItem())->isFalse();
         $sections[] = $section;
         $sectionId = $section->getID();
         foreach ($questionsData as $questionData) {
            // Create question
            $questionData ['plugin_formcreator_sections_id'] = $section->getID();
            $question = new \PluginFormcreatorQuestion();
            $question->add($questionData);
            $this->boolean($question->isNewItem())->isFalse(json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));
            $question->updateParameters($questionData);
            $questions[] = $question;
            $questionData['id'] = $question->getID();
            if (isset($questionData['show_rule']) && $questionData['show_rule'] != 'always') {
               $showFields = [];
               foreach ($questionData['show_field'] as $showFieldName) {
                  $showfield = new \PluginFormcreatorQuestion();
                  $showfield->getFromDBByCrit([
                     'AND' => [
                        'plugin_formcreator_sections_id' => $sectionId,
                        'name' => $showFieldName
                     ]
                  ]);
                  $this->boolean($showfield->isNewItem())->isFalse();
                  $showFields[] = $showfield->getID();
               }
               $questionData['show_field'] = $showFields;
               $success = $question->updateConditions($questionData);
               $this->boolean($success)->isTrue();
            }
         }
      }
      $targets = [];
      foreach ($targetsData as $targetData) {
         $target = new \PluginFormcreatorTarget();
         $targetData['plugin_formcreator_forms_id'] = $formId;
         $target->add($targetData);
         $this->boolean($target->isNewItem())->isFalse();
         $targets[] = $target;
      }

      return [
         $form,
         $sections,
         $questions,
         $targets,
      ];
   }

   /**
    * Tests the session has a specific message
    * this may be replaced by a custom asserter for atoum
    * @see http://docs.atoum.org/en/latest/asserters.html#custom-asserter
    *
    * @param string $message
    * @param integer $message_type
    */
   protected function sessionHasMessage($message, $message_type = INFO) {
      if (!is_array($message)) {
         $message = [$message];
      }
      $this->array($_SESSION['MESSAGE_AFTER_REDIRECT'][$message_type])
         ->containsValues($message);
   }
}
