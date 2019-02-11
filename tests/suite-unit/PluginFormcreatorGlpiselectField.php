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
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */


namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorGlpiselectField extends CommonTestCase {

   public function testGetName() {
      $output = \PluginFormcreatorGlpiselectField::getName();
      $this->string($output)->isEqualTo('GLPI object');
   }

   public function providerGetAnswer() {
      $user = new \User();
      $user->add([
         'name' => $this->getUniqueString(),
         'realname' => 'John',
         'firstname' => 'Doe',
      ]);
      $this->boolean($user->isNewItem())->isFalse();

      $computer = new \Computer();
      $computer->add([
         'name' => $this->getUniqueString(),
         \Entity::getForeignKeyField() => 0,
      ]);
      $this->boolean($computer->isNewItem())->isFalse();

      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $user->getID(),
               'values'          => \User::class,
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => true,
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => $user->getID(),
            'expectedValue'   => (new \DbUtils())->getUserName($user->getID()),
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '1',
               'default_values'  => '',
               'values'          => \User::class,
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => true,
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => '0',
            'expectedValue'   => '',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '1',
               'default_values'  => $user->getID(),
               'values'          => \User::class,
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => false,
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => $user->getID(),
            'expectedValue'   => (new \DbUtils())->getUserName($user->getID()),
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => \User::class,
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => false,
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '',
            'expectedIsValid' => true
         ],

         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $computer->getID(),
               'values'          => \Computer::class,
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => true,
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => $computer->getName(),
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => \Computer::class,
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => true,
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '&nbsp;',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $computer->getID(),
               'values'          => \Computer::class,
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => false,
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => $computer->getName(),
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => \Computer::class,
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => false,
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '&nbsp;',
            'expectedIsValid' => true
         ],
      ];

      return $dataset;
   }

   public function ptroviderIsValid() {
      return $this->providerGetAnswer();
   }

   /**
    * @dataProvider ptroviderIsValid
    */
   public function testIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $instance = $this->newTestedInstance($fields, $data);
      $instance->deserializeValue($fields['default_values']);

      $output = $instance->isValid();
      $this->boolean($output)->isEqualTo($expectedValidity);
   }

   public function testIsAnonymousFormCompatible() {
      $instance = new \PluginFormcreatorGlpiselectField([]);
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isFalse();
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }
}
