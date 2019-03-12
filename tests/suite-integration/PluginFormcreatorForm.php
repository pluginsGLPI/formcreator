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
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorForm extends CommonTestCase {

   public function setUp() {
      parent::setUp();

      // instanciate classes
      $form              = new \PluginFormcreatorForm;
      $form_section      = new \PluginFormcreatorSection;
      $form_question     = new \PluginFormcreatorQuestion;
      $form_condition    = new \PluginFormcreatorQuestion_Condition;
      $form_validator    = new \PluginFormcreatorForm_Validator;
      $form_profile      = new \PluginFormcreatorForm_Profile;
      $targetTicket      = new \PluginFormcreatorTargetTicket();
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
                            'show_condition'                  => \PluginFormcreatorQuestion_Condition::SHOW_CONDITION_EQ,
                            'show_value'                      => 'test']);

      $form_validator->add(['plugin_formcreator_forms_id' => $forms_id,
                           'itemtype'                    => 'User',
                           'items_id'                    => 2]);
      $form_validator->add(['plugin_formcreator_forms_id' => $forms_id,
                           'itemtype'                    => 'User',
                           'items_id'                    => 3]);

      $targetTicket_id = $targetTicket->add([
         'name'         => "test export target",
         'plugin_formcreator_forms_id' => $forms_id,
      ]);

      $form_profile->add(['plugin_formcreator_forms_id' => $forms_id,
                                                   'profiles_id' => 1]);

      $item_targetTicket->add(['plugin_formcreator_targettickets_id' => $targetTicket_id,
                              'link'     => \Ticket_Ticket::LINK_TO,
                              'itemtype' => $targetTicket->getType(),
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
    *
    */
   public function testDuplicateForm() {
      global $DB;

      $formData = [
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ];

      $sectionsData = [
         [
            'name'                  => 'a section',
            'questions'             =>  [
               [
                  'name'                  => 'text question',
                  'fieldtype'             => 'text',
                  'default_values'        => '',
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
               ],
               [
                  'name'                  => 'other text question',
                  'fieldtype'             => 'text',
                  'default_values'        => '',
                  '_parameters'     => [
                     'text' => [
                        'range' => [
                           'range_min' => '',
                           'range_max' => '',
                        ],
                        'regex' => [
                           'regex' => '',
                        ]
                     ]
                  ],
               ],
            ],
         ],
         [
            'name'                  => 'an other section',
            'questions'             =>  [
               [
                  'name'                  => 'text question',
                  'fieldtype'             => 'text',
                  'default_values'        => '',
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
               ],
               [
                  'name'                  => 'other text question',
                  'fieldtype'             => 'text',
                  'default_values'        => '',
                  'show_rule'             => \PluginFormcreatorQuestion::SHOW_RULE_HIDDEN,
                  'show_field'            => ['text question'],
                  'show_condition'        => [\PluginFormcreatorQuestion_Condition::SHOW_CONDITION_EQ],
                  'show_value'            => ['azerty'],
                  'show_logic'            => [\PluginFormcreatorQuestion_Condition::SHOW_LOGIC_AND],
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
               ],
            ],
         ],
      ];

      $targetsData = [
         [
            'name'                  => 'target ticket 1',
            'itemtype'              => 'PluginFormcreatorTargetTicket',
         ],
         [
            'name'                  => 'target ticket 2',
            'itemtype'              => 'PluginFormcreatorTargetTicket',
         ]
      ];

      list($form,
         $sections,
         $questions,
         $targets
      ) = $this->createFullForm(
         $formData,
         $sectionsData,
         $targetsData
      );

      $sourceFormId = $form->getID();
      $this->integer($form->duplicate());

      // Check the ID of the form changed
      $newFormId = $form->getID();
      $this->integer((int) $newFormId)->isNotEqualTo($sourceFormId);

      // Check sections were copied
      $sourceRows = $DB->request([
         'COUNT' => 'cpt',
         'FROM'  => \PluginFormcreatorSection::getTable(),
         'WHERE' => [
            'plugin_formcreator_forms_id' => $sourceFormId,
         ]
      ])->next();
      $newRows = $DB->request([
         'COUNT' => 'cpt',
         'FROM'  => \PluginFormcreatorSection::getTable(),
         'WHERE' => [
            'plugin_formcreator_forms_id' => $newFormId,
         ]
      ])->next();
      $this->integer((int)$newRows['cpt'])->isEqualTo((int)$sourceRows['cpt']);

      // Check questions were copied
      $question_table = \PluginFormcreatorQuestion::getTable();
      $section_table  = \PluginFormcreatorSection::getTable();
      $sourceRows = $DB->request([
         'COUNT'      => 'cpt',
         'FROM'       => $question_table,
         'INNER JOIN' => [
            $section_table => [
               'FKEY' => [
                  "$section_table.id",
                  "$question_table.plugin_formcreator_sections_id"
               ]
            ]
         ],
         'WHERE'      => [
            'plugin_formcreator_forms_id'  => $sourceFormId,
         ]
      ])->next();
      $newRows = $DB->request([
         'COUNT'      => 'cpt',
         'FROM'       => $question_table,
         'INNER JOIN' => [
            $section_table => [
               'FKEY' => [
                  "$section_table.id",
                  "$question_table.plugin_formcreator_sections_id"
               ]
            ]
         ],
         'WHERE'      => [
            'plugin_formcreator_forms_id'  => $newFormId,
         ]
      ])->next();
      $this->integer((int)$newRows['cpt'])->isEqualTo((int)$sourceRows['cpt']);

      // check target were created
      $sourceRows = [
         \PluginFormcreatorTargetTicket::class => [],
         \PluginFormcreatorTargetChange::class => [],
      ];
      foreach ($form->getTargetTypes() as $targetType) {
         $request = [
            'SELECT' => ['id'],
            'FROM'  => $targetType::getTable(),
            'WHERE' => [
               'plugin_formcreator_forms_id' => $sourceFormId,
            ]
         ];
         foreach ($DB->request($request) as $row) {
            $sourceRows[$targetType][] = $row;
         }
      }

      // check target items were created
      $newRows = [
         \PluginFormcreatorTargetTicket::class => [],
         \PluginFormcreatorTargetChange::class => [],
      ];
      foreach ($form->getTargetTypes() as $targetType) {
         $request = [
            'SELECT' => ['id'],
            'FROM'  => $targetType::getTable(),
            'WHERE' => [
               'plugin_formcreator_forms_id' => $newFormId,
            ]
         ];
         foreach ($DB->request($request) as $row) {
            $newRows[$targetType][] = $row;
         }
      }
      foreach ($form->getTargetTypes() as $targetType) {
         $this->integer(count($newRows[$targetType]))->isEqualTo(count($sourceRows[$targetType]));
      }
   }
}
