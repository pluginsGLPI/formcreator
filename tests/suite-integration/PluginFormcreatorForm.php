<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorForm extends CommonTestCase {

   public function setUp() {
      parent::setUp();

      // instanciate classes
      $form           = new \PluginFormcreatorForm;
      $form_section   = new \PluginFormcreatorSection;
      $form_question  = new \PluginFormcreatorQuestion;
      $form_condition = new \PluginFormcreatorQuestion_Condition;
      $form_validator = new \PluginFormcreatorForm_Validator;
      $form_target    = new \PluginFormcreatorTarget;
      $form_profile   = new \PluginFormcreatorForm_Profile;
      $targetTicket   = new \PluginFormcreatorTargetTicket();
      $item_targetTicket = new \PluginFormcreatorItem_TargetTicket();

      // create objects
      $forms_id = $form->add(['name'                => "test export form",
                              'is_active'           => true,
                              'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER]);

      $sections_id = $form_section->add(['name'                        => "test export section",
                                          'plugin_formcreator_forms_id' => $forms_id]);

      $questions_id_1 = $form_question->add(['name'                           => "test export question 1",
                                             'fieldtype'                      => 'text',
                                             'plugin_formcreator_sections_id' => $sections_id]);
      $questions_id_2 = $form_question->add(['name'                           => "test export question 2",
                                             'fieldtype'                      => 'textarea',
                                             'plugin_formcreator_sections_id' => $sections_id]);

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
                                       'itemtype'                    => \PluginFormcreatorTargetTicket::class,
                                       'name'                        => "test export target"]);

      $targetTicket_id = $targetTicket->add(['name'         => $form_target->getField('name'),
      ]);

      $form_target->getFromDB($targets_id);
      $targettickets_id = $form_target->fields['items_id'];

      $form_profiles_id = $form_profile->add(['plugin_formcreator_forms_id' => $forms_id,
                                                   'profiles_id' => 1]);

      $item_targetTicket->add(['plugin_formcreator_targettickets_id' => $targetTicket_id,
                              'link'     => \Ticket_Ticket::LINK_TO,
                              'itemtype' => $form_target->getField('itemtype'),
                              'items_id' => $targets_id
      ]);
   }

   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testExportImportForm':
         case 'testDuplicateForm':
            $_SESSION['glpiactive_entity'] = 0;
            break;
      }
   }

   /**
    * @cover PluginFormcreatorForm::export
    */
   public function testExportImportForm() {
      // Export the form
      $form = new \PluginFormcreatorForm;
      plugin_formcreator_getFromDBByField($form, 'name', "test export form");
      $this->boolean($form->isNewItem())->isFalse();
      $export = $form->export();

      $this->array($export)->notHasKeys([
         'id',
         'plugin_formcreator_categories_id',
         'entities_id',
         'usage_count',
      ])->hasKeys([
         'is_recursive',
         'access_rights',
         'is_recursive',
         'access_rights',
         'requesttype',
         'name',
         'description',
         'content',
         'is_active',
         'language',
         'helpdesk_home',
         'is_deleted',
         'validation_required',
         'is_default',
         'uuid',
         '_sections',
         '_validators',
         '_targets',
         '_profiles',
      ]);

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
         $this->checkFormProfile($form_profile);
      }

      // Test import form
      $forms_id = \PluginFormcreatorForm::import($export);

      $this->variable($forms_id)->isNotFalse();
   }

   public function testExportImportSection() {
      // Export a section
      $section = new \PluginFormcreatorSection;
      plugin_formcreator_getFromDBByField($section, 'name', "test export section");
      $export = $section->export();
      $this->_checkSection($export);

      // Import a section
      $form = new \PluginFormcreatorForm;
      plugin_formcreator_getFromDBByField($form, 'name', "test export form");
      $sections_id = \PluginFormcreatorSection::import($form->getID(), $export);
      $this->variable($sections_id)->isNotFalse();
   }

   public function testExportImportQuestion() {
      // Export a question
      $question = new \PluginFormcreatorQuestion;
      plugin_formcreator_getFromDBByField($question, 'name', "test export question 1");
      $export = $question->export();
      $this->_checkQuestion($export);

      plugin_formcreator_getFromDBByField($question, 'name', "test export question 2");
      $export = $question->export();
      $this->_checkQuestion($export);

      // Import a question
      $section = new \PluginFormcreatorSection;
      plugin_formcreator_getFromDBByField($section, 'name', "test export section");
      $questions_id = \PluginFormcreatorQuestion::import($section->getID(), $export);
      $this->variable($questions_id)->isNotFalse();
   }

   public function testExportImportTarget() {
      // Export a target
      $target = new \PluginFormcreatorTarget;
      plugin_formcreator_getFromDBByField($target, 'name', "test export target");
      $export = $target->export();
      $this->_checkTarget($export);

      // Import a target
      $form = new \PluginFormcreatorForm;
      plugin_formcreator_getFromDBByField($form, 'name', "test export form");
      $targets_id = \PluginFormcreatorTarget::import($form->getID(), $export);

      $this->variable($targets_id)->isNotFalse();

      return $targets_id;
   }

   public function _checkSection($section = []) {
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

   public function _checkQuestion($question = []) {
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

   public function _checkCondition($condition = []) {
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

   public function _checkValidator($validator = []) {
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

   public function _checkTarget($target = []) {
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

   public function _checkTargetTicket($targetticket = []) {
      $keys = [
         'title',
         'comment',
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

   public function _checkActor($actor = []) {
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

   public function checkFormProfile($form_profile = []) {
      $this->array($form_profile)->notHasKeys([
         'id',
         'plugin_formcreator_forms_id',
         'profiles_id'
      ])->hasKeys([
         '_profile',
         'uuid',
      ]);
   }

   public function testDuplicateForm() {
      $formData = [
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ];

      $sectionData = [
         [
            'name'                  => 'a section',
            'questions'             =>  [
               [
                  'name'                  => 'text question',
                  'fieldtype'             => 'text'
               ],
               [
                  'name'                  => 'other text question',
                  'fieldtype'             => 'text'
               ],
            ],
         ],
         [
            'name'                  => 'an other section',
            'questions'             =>  [
               [
                  'name'                  => 'text question',
                  'fieldtype'             => 'text'
               ],
               [
                  'name'                  => 'other text question',
                  'fieldtype'             => 'text',
                  'show_rule'             => 'hidden',
                  'show_field'            => 'text question',
                  'show_condition'        => '==',
                  'show_value'            => 'azerty',
               ],
            ],
         ],
      ];

      $targetData = [
         [
            'name'                  => 'target ticket 1',
            'itemtype'              => 'PluginFormcreatorTargetTicket',
         ],
         [
            'name'                  => 'target ticket 2',
            'itemtype'              => 'PluginFormcreatorTargetTicket',
         ]
      ];

      $form = new \PluginFormcreatorForm();
      $formId = $form->add($formData);
      $this->boolean($form->isNewItem())->isFalse();

      foreach ($sectionData as $sectionData) {
         // Keep questions data set apart from sections data
         $questionsData = $sectionData['questions'];
         unset($sectionData['questions']);

         // Create section
         $sectionData['plugin_formcreator_forms_id'] = $form->getID();
         $section = new \PluginFormcreatorSection();
         $section->add($sectionData);
         $this->boolean($section->isNewItem())->isFalse();
         $sectionId = $section->getID();
         foreach ($questionsData as $questionData) {
            // Create question
            $questionData ['plugin_formcreator_sections_id'] = $section->getID();
            $question = new \PluginFormcreatorQuestion();
            $question->add($questionData);
            $this->boolean($question->isNewItem(), $_SESSION['MESSAGE_AFTER_REDIRECT'])->isFalse();

            $questionData['id'] = $question->getID();
            if (isset($questionData['show_rule']) && $questionData['show_rule'] != 'always') {
               $showFieldName = $questionData['show_field'];
               $showfield = new \PluginFormcreatorQuestion();
               $showfield->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_sections_id' => $sectionId,
                     'name' => $showFieldName
                  ]
               ]);
               $questionData['show_field'] = $showfield->getID();
               $question->updateConditions($questionData);
            }
         }
         foreach ($targetData as $targetDataItem) {
            $target = new \PluginFormcreatorTarget();
            $targetDataItem['plugin_formcreator_forms_id'] = $formId;
            $target->add($targetDataItem);
            $this->boolean($target->isNewItem())->isFalse();
         }
      }

      $sourceFormId = $form->getID();
      $this->boolean($form->duplicate())->isTrue();

      // Check the ID of the form changed
      $newFormId = $form->getID();
      $this->integer((int) $newFormId)->isNotEqualTo($sourceFormId);

      // Check sections were copied
      $section = new \PluginFormcreatorSection();
      $sourceRows = $section->find("`plugin_formcreator_forms_id` = '$sourceFormId'");
      $newRows = $section->find("`plugin_formcreator_forms_id` = '$newFormId'");
      $this->integer(count($newRows))->isEqualTo(count($sourceRows));

      // Check questions were copied
      $table_section = \PluginFormcreatorSection::getTable();
      $question = new \PluginFormcreatorQuestion();
      $sourceRows = $question->find("`plugin_formcreator_sections_id` IN (
            SELECT `id` FROM `$table_section` WHERE `$table_section`.`plugin_formcreator_forms_id` = '$sourceFormId'
      )");
      $newRows = $question->find("`plugin_formcreator_sections_id` IN (
            SELECT `id` FROM `$table_section` WHERE `$table_section`.`plugin_formcreator_forms_id` = '$newFormId'
      )");
      $this->integer(count($newRows))->isEqualTo(count($sourceRows));

      // check target were created
      $target = new \PluginFormcreatorTarget();
      $sourceRows = $target->find("`plugin_formcreator_forms_id` = '$sourceFormId'");
      $newRows = $target->find("`plugin_formcreator_forms_id` = '$sourceFormId'");
      $this->integer(count($newRows))->isEqualTo(count($sourceRows));

      // check target tickets were created
      foreach ($newRows as $targetId => $newTarget) {
         if ($newTarget['itemtype'] == 'PluginFormcreatorTargetTicket') {
            $targetTicket = new \PluginFormcreatorTArgetTicket();
            $targetTicket->getFromDB($newTarget['items_id']);
            $this->boolean($targetTicket->isNewItem())->isFalse();
         }
      }
   }
}