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

class FormDuplicationTest extends SuperAdminTestCase
{

   protected $formData;
   protected $sectionData;
   protected $targetData;

   public function setUp() {
      parent::setUp();

      $this->formData = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 0
      );

      $this->sectionData = array(
         array(
            'name'                  => 'a section',
            'questions'             => array (
               array(
                  'name'                  => 'text question',
                  'fieldtype'             => 'text',
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
               ),
               array(
                  'name'                  => 'other text question',
                  'fieldtype'             => 'text',
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
               ),
            ),
         ),
         array(
            'name'                  => 'an other section',
            'questions'             => array (
               array(
                  'name'                  => 'text question',
                  'fieldtype'             => 'text',
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
               ),
               array(
                  'name'                  => 'other text question',
                  'fieldtype'             => 'text',
                  'show_rule'             => 'hidden',
                  'show_field'            => 'text question',
                  'show_condition'        => '==',
                  'show_value'            => 'azerty',
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
               ),
            ),
         ),
      );

      $this->targetData = array(
         array(
            'name'                  => 'target ticket 1',
            'itemtype'              => 'PluginFormcreatorTargetTicket',
         ),
         array(
            'name'                  => 'target ticket 2',
            'itemtype'              => 'PluginFormcreatorTargetTicket',
         )
      );
   }

   public function testInitCreateForm() {
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formData);
      $this->assertFalse($form->isNewItem());

      foreach ($this->sectionData as $sectionData) {
         // Keep questions data set apart from sections data
         $questionData = $sectionData['questions'];
         unset($sectionData['questions']);

         // Create section
         $sectionData['plugin_formcreator_forms_id'] = $form->getID();
         $section = new PluginFormcreatorSection();
         $section->add($sectionData);
         $this->assertFalse($section->isNewItem());
         $sectionId = $section->getID();
         foreach ($questionData as $oneQuestionData) {
            // Create question
            $oneQuestionData ['plugin_formcreator_sections_id'] = $section->getID();
            $question = new PluginFormcreatorQuestion();
            $question->add($oneQuestionData);
            $this->assertFalse($question->isNewItem(), json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));

            $questionData['id'] = $question->getID();
            if (isset($questionData['show_rule']) && $questionData['show_rule'] != 'always') {
               $showFieldName = $oneQuestionData['show_field'];
               $showfield = new PluginFormcreatorQuestion();
               $showfield->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_sections_id' => $sectionId,
                     'name'                           => $showFieldName
                  ]
               ]);
               $oneQuestionData['show_field'] = $showfield->getID();
               $question->updateConditions($oneQuestionData);
            }
         }
         foreach ($this->targetData as $targetData) {
            $target = new PluginFormcreatorTarget();
            $targetData['plugin_formcreator_forms_id'] = $formId;
            $target->add($targetData);
            $this->assertFalse($target->isNewItem());
         }
      }

      return $form;
   }

   /**
    * @depends testInitCreateForm
    * @param PluginFormcreatorForm $form
    */
   public function testDuplicateForm(PluginFormcreatorForm $form) {
      $sourceFormId = $form->getID();
      $this->assertTrue($form->duplicate());

      // Check the ID of the form changed
      $newFormId = $form->getID();
      $this->assertNotEquals($sourceFormId, $newFormId);

      // Check sections were copied
      $section = new PluginFormcreatorSection();
      $sourceRows = $section->find("`plugin_formcreator_forms_id` = '$sourceFormId'");
      $newRows = $section->find("`plugin_formcreator_forms_id` = '$newFormId'");
      $this->assertEquals(count($sourceRows), count ($newRows));

      // Check questions were copied
      $table_section = PluginFormcreatorSection::getTable();
      $question = new PluginFormcreatorQuestion();
      $sourceRows = $question->find("`plugin_formcreator_sections_id` IN (
            SELECT `id` FROM `$table_section` WHERE `$table_section`.`plugin_formcreator_forms_id` = '$sourceFormId'
      )");
      $newRows = $question->find("`plugin_formcreator_sections_id` IN (
            SELECT `id` FROM `$table_section` WHERE `$table_section`.`plugin_formcreator_forms_id` = '$newFormId'
      )");
      $this->assertEquals(count($sourceRows), count($newRows));

      // check target were created
      $target = new PluginFormcreatorTarget();
      $sourceRows = $target->find("`plugin_formcreator_forms_id` = '$sourceFormId'");
      $newRows = $target->find("`plugin_formcreator_forms_id` = '$sourceFormId'");
      $this->assertEquals(count($sourceRows), count($newRows));

      // check target tickets were created
      foreach ($newRows as $targetId => $newTarget) {
         if ($newTarget['itemtype'] == 'PluginFormcreatorTargetTicket') {
            $targetTicket = new PluginFormcreatorTArgetTicket();
            $targetTicket->getFromDB($newTarget['items_id']);
            $this->assertFalse($targetTicket->isNewItem());
         }
      }

   }
}
