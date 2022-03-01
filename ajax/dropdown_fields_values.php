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
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use GlpiPlugin\Formcreator\Field\FieldsField;

include ('../../../inc/includes.php');

Session::checkRight('entity', UPDATE);

if (isset($_REQUEST['block_id'])
    && $_REQUEST['block_id'] != '0') {
   $block_id = $_REQUEST['block_id'];
   $fields = FieldsField::getFieldsFromBlock($block_id);

   //get value in case of update
   $selectedValue = 0;
   if (isset($_REQUEST['question_id'])){
      $question = new PluginFormcreatorQuestion();
      $question->getFromDB((int) $_REQUEST['question_id']);
      $decodedValues = json_decode($question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $selectedValue = $decodedValues['dropdown_fields_field'] ?? '0';
   }

   Dropdown::showFromArray('dropdown_fields_field',
      $fields, [
         'display_emptychoice'   => false,
         'value' => $selectedValue,
      ]
   );
}
