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
class PluginFormcreatorQuestion_Condition extends CommonTestCase {
   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      self::login('glpi', 'glpi');
   }

   public function testGetConditionsFromQuestion() {
      // crete a question with some conditions
      $question = $this->getQuestion();

      $questionFk = \PluginFormcreatorQuestion::getForeignKeyField();
      $questionCondition = $this->newTestedInstance();
      $questionCondition->add([
         $questionFk => $question->getID(),
      ]);
      $this->boolean($questionCondition->isNewItem())->isFalse();

      $questionCondition = $this->newTestedInstance();
      $questionCondition->add([
         $questionFk => $question->getID(),
      ]);
      $this->boolean($questionCondition->isNewItem())->isFalse();

      // Check that all conditions are retrieved
      $output = $questionCondition->getConditionsFromQuestion($question->getID());
      $this->array($output)->hasSize(2);
   }
}
