<?php
class ExportImporTest extends SuperAdminTestCase {
   public static function setUpBeforeClass() {
      parent::setupBeforeClass();

      self::login('glpi', 'glpi');

      // instanciate classes
      $form           = new PluginFormcreatorForm;
      $form_section   = new PluginFormcreatorSection;
      $form_question  = new PluginFormcreatorQuestion;
      $form_condition = new PluginFormcreatorQuestion_Condition;
      $form_validator = new PluginFormcreatorForm_Validator;
      $form_target    = new PluginFormcreatorTarget;
      $form_profile   = new PluginFormcreatorForm_Profile;
      $targetTicket   = new PluginFormcreatorTargetTicket();
      $item_targetTicket = new PluginFormcreatorItem_TargetTicket();

      // create objects
      $forms_id = $form->add(['name'                => "test export form",
                              'is_active'           => true,
                              'validation_required' => PluginFormcreatorForm_Validator::VALIDATION_USER]);

      $sections_id = $form_section->add(['name'                        => "test export section",
                                          'plugin_formcreator_forms_id' => $forms_id]);

      $questions_id_1 = $form_question->add([
         'name'                           => "test export question 1",
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $sections_id,
         '_parameters'                    => [
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
      ]);
      $questions_id_2 = $form_question->add([
         'name'                           => "test export question 2",
         'fieldtype'                      => 'textarea',
         'plugin_formcreator_sections_id' => $sections_id,
         '_parameters'                    => [
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
      ]);

      $form_condition->add(['plugin_formcreator_questions_id' => $questions_id_1,
                            'show_field'                      => $questions_id_2,
                             'show_condition'                  => '==',
                             'show_value'                      => 'test']);

      $form_validator->add(['plugin_formcreator_forms_id' => $forms_id,
                            'itemtype'                    => 'User',
                            'items_id'                    => 2]);
      $form_validator->add(['plugin_formcreator_forms_id' => $forms_id,
                            'itemtype'                    => 'User',
                            'items_id'                    => 3]);

      $targets_id = $form_target->add(['plugin_formcreator_forms_id' => $forms_id,
                                       'itemtype'                    => PluginFormcreatorTargetTicket::class,
                                       'name'                        => "test export target"]);

      $targetTicket_id = $targetTicket->add(['name'         => $form_target->getField('name'),
      ]);

      $form_target->getFromDB($targets_id);
      $targettickets_id = $form_target->fields['items_id'];

      $form_profiles_id = $form_profile->add(['plugin_formcreator_forms_id' => $forms_id,
                                                   'profiles_id' => 1]);

      $item_targetTicket->add(['plugin_formcreator_targettickets_id' => $targetTicket_id,
                               'link'     => Ticket_Ticket::LINK_TO,
                               'itemtype' => $form_target->getField('itemtype'),
                               'items_id' => $targets_id
      ]);
   }

   /**
    * @cover PluginFormcreatorForm::export
    */
   public function testExportForm() {
      $form = new PluginFormcreatorForm;
      plugin_formcreator_getFromDBByField($form, 'name', "test export form");
      $export = $form->export();

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

      foreach ($export["_sections"] as $section) {
         $this->_checkSection($section);
      }

      foreach ($export["_validators"] as $validator) {
         $this->_checkValidator($validator);
      }

      foreach ($export["_targets"] as $target) {
         $this->_checkTarget($target);
      }

      foreach ($export["_profiles"] as $form_profile) {
         $this->_checkFormProfile($form_profile);
      }

      return $export;
   }

   /**
    * @cover PluginFormcreatorSection::export
    */
   public function testExportSection() {
      $section = new PluginFormcreatorSection;
      plugin_formcreator_getFromDBByField($section, 'name', "test export section");
      $export = $section->export();
      $this->_checkSection($export);

      return $export;
   }

   /**
    * @cover PluginFormcreatorQuestion::export
    */
   public function testExportQuestion() {
      $question = new PluginFormcreatorQuestion;
      plugin_formcreator_getFromDBByField($question, 'name', "test export question 1");
      $export = $question->export();
      $this->_checkQuestion($export);

      plugin_formcreator_getFromDBByField($question, 'name', "test export question 2");
      $export = $question->export();
      $this->_checkQuestion($export);

      return $export;
   }

   /**
    * @cover PluginFormcreatorTarget::export
    */
   public function testExportTarget() {
      $target = new PluginFormcreatorTarget;
      plugin_formcreator_getFromDBByField($target, 'name', "test export target");
      $export = $target->export();
      $this->_checkTarget($export);

      return $export;
   }

   /**
    * @cover PluginFormcreatorForm::import
    * @depends testExportForm
    */
   public function testImportForm($export = []) {
      $importLinker = new PluginFormcreatorImportLinker();
      $forms_id = PluginFormcreatorForm::import($importLinker, $export);

      $this->assertNotFalse($forms_id);

      return $forms_id;
   }

   /**
    * @cover PluginFormcreatorSection::import
    * @depends testImportForm
    * @depends testExportSection
    */
   public function testImportSection($forms_id, $export = []) {
      $importLinker = new PluginFormcreatorImportLinker();
      $sections_id = PluginFormcreatorSection::import($importLinker, $forms_id, $export);

      $this->assertNotFalse($sections_id);

      return $sections_id;
   }

   /**
    * @cover PluginFormcreatorQuestion::import
    * @depends testImportSection
    * @depends testExportQuestion
    */
   public function testImportQuestion($sections_id, $export = []) {
      $importLinker = new PluginFormcreatorImportLinker();
      $questions_id = PluginFormcreatorQuestion::import($importLinker, $sections_id, $export);

      $this->assertNotFalse($questions_id);

      return $questions_id;
   }

   /**
    * @cover PluginFormcreatorTarget::import
    * @depends testImportForm
    * @depends testExportTarget
    */
   public function testImportTarget($forms_id, $export = []) {
      $targets_id = PluginFormcreatorTarget::import($forms_id, $export);

      $this->assertNotFalse($targets_id);

      return $targets_id;
   }

   public function _checkSection($section = []) {
      $this->assertArrayNotHasKey('id', $section);
      $this->assertArrayNotHasKey('plugin_formcreator_forms_id', $section);
      $this->assertArrayHasKey('name', $section);
      $this->assertArrayHasKey('order', $section);
      $this->assertArrayHasKey('uuid', $section);
      $this->assertArrayHasKey('_questions', $section);

      foreach ($section["_questions"] as $question) {
         $this->_checkQuestion($question);
      }
   }

   public function _checkQuestion($question = []) {
      $this->assertArrayNotHasKey('id', $question);
      $this->assertArrayNotHasKey('plugin_formcreator_sections_id', $question);
      $this->assertArrayHasKey('fieldtype', $question);
      $this->assertArrayHasKey('name', $question);
      $this->assertArrayHasKey('required', $question);
      $this->assertArrayHasKey('required', $question);
      $this->assertArrayHasKey('show_empty', $question);
      $this->assertArrayHasKey('default_values', $question);
      $this->assertArrayHasKey('values', $question);
      $this->assertArrayHasKey('range_min', $question);
      $this->assertArrayHasKey('range_max', $question);
      $this->assertArrayHasKey('range_max', $question);
      $this->assertArrayHasKey('description', $question);
      $this->assertArrayHasKey('regex', $question);
      $this->assertArrayHasKey('regex', $question);
      $this->assertArrayHasKey('order', $question);
      $this->assertArrayHasKey('show_rule', $question);
      $this->assertArrayHasKey('uuid', $question);
      $this->assertArrayHasKey('_conditions', $question);

      foreach ($question["_conditions"] as $condition) {
         $this->_checkCondition($condition);
      }
   }

   public function _checkCondition($condition = []) {
      $this->assertArrayNotHasKey('id', $condition);
      $this->assertArrayNotHasKey('plugin_formcreator_questions_id', $condition);
      $this->assertArrayHasKey('show_field', $condition);
      $this->assertArrayHasKey('show_condition', $condition);
      $this->assertArrayHasKey('show_value', $condition);
      $this->assertArrayHasKey('show_logic', $condition);
      $this->assertArrayHasKey('uuid', $condition);
   }

   public function _checkValidator($validator = []) {
      $this->assertArrayNotHasKey('id', $validator);
      $this->assertArrayNotHasKey('plugin_formcreator_forms_id', $validator);
      $this->assertArrayNotHasKey('items_id', $validator);
      $this->assertArrayHasKey('itemtype', $validator);
      $this->assertArrayHasKey('_item', $validator);
      $this->assertArrayHasKey('uuid', $validator);
   }

   public function _checkTarget($target = []) {
      $this->assertArrayNotHasKey('id', $target);
      $this->assertArrayNotHasKey('plugin_formcreator_forms_id', $target);
      $this->assertArrayNotHasKey('items_id', $target);
      $this->assertArrayHasKey('itemtype', $target);
      $this->assertArrayHasKey('_data', $target);
      $this->assertArrayHasKey('_actors', $target['_data']);
      $this->assertArrayHasKey('uuid', $target);

      if ($target['itemtype'] == 'PluginFormcreatorTargetTicket') {
         $this->_checkTargetTicket($target['_data']);
      }

      foreach ($target["_data"]['_actors'] as $actor) {
         $this->_checkActor($actor);
      }
   }

   public function _checkTargetTicket($targetticket = []) {
      $this->assertArrayNotHasKey('id', $targetticket);
      $this->assertArrayNotHasKey('tickettemplates_id', $targetticket);
      $this->assertArrayHasKey('name', $targetticket);
      $this->assertArrayHasKey('comment', $targetticket);
      $this->assertArrayHasKey('due_date_rule', $targetticket);
      $this->assertArrayHasKey('due_date_question', $targetticket);
      $this->assertArrayHasKey('due_date_value', $targetticket);
      $this->assertArrayHasKey('due_date_period', $targetticket);
      $this->assertArrayHasKey('urgency_rule', $targetticket);
      $this->assertArrayHasKey('urgency_question', $targetticket);
      $this->assertArrayHasKey('validation_followup', $targetticket);
      $this->assertArrayHasKey('destination_entity', $targetticket);
      $this->assertArrayHasKey('destination_entity', $targetticket);
      $this->assertArrayHasKey('destination_entity_value', $targetticket);
      $this->assertArrayHasKey('tag_type', $targetticket);
      $this->assertArrayHasKey('tag_questions', $targetticket);
      $this->assertArrayHasKey('tag_specifics', $targetticket);
   }

   public function _checkActor($actor = []) {
      $this->assertArrayNotHasKey('id', $actor);
      $this->assertArrayNotHasKey('plugin_formcreator_targettickets_id', $actor);
      $this->assertArrayHasKey('actor_role', $actor);
      $this->assertArrayHasKey('actor_type', $actor);
      //we should have only one of theses keys : actor_value ,_question ,_user ,_group ,_supplier
      $actor_value_found_keys = preg_grep('/((actor_value)|(_question)|(_user)|(_group)|(_supplier))/',
                                          array_keys($actor));
      $this->assertCount(1, $actor_value_found_keys);
      $this->assertArrayHasKey('use_notification', $actor);
      $this->assertArrayHasKey('uuid', $actor);
   }

   public function _checkFormProfile($form_profile = []) {
      $this->assertArrayNotHasKey('id', $form_profile);
      $this->assertArrayNotHasKey('plugin_formcreator_forms_id', $form_profile);
      $this->assertArrayNotHasKey('profiles_id', $form_profile);
      $this->assertArrayHasKey('_profile', $form_profile);
      $this->assertArrayHasKey('uuid', $form_profile);
   }
}