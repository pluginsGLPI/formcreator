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
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use Glpi\Application\View\TemplateRenderer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorFormanswerValidation extends CommonDBTM
{
   /**
    * Get the current validation level of a formanswer
    *
    * @param PluginFormcreatorFormAnswer $formAnswer formanswer
    * @return null|int
    */
   public static function getCurrentValidationLevel(PluginFormcreatorFormAnswer $formAnswer): ?int {
      global $DB;

      $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
      $request = [
         'SELECT' => ['MIN' => 'level as level'],
         'FROM' => self::getTable(),
         'WHERE' => [
            $formAnswerFk => $formAnswer->getID(),
            [
               'status' => PluginFormcreatorForm_Validator::VALIDATION_STATUS_WAITING,
            ],
         ],
      ];
      $result = $DB->request($request);
      $max = $result->current();
      if ($max === null || $max['level'] === null) {
         return null;
      }

      return $max['level'];
   }

   /**
    * Set the status of a validation level for a formanswer
    *
    * @param PluginFormcreatorFormAnswer $formAnswer
    * @param integer $newStatus
    * @return void
    */
   public static function updateValidationStatus(PluginFormcreatorFormAnswer $formAnswer, int $newStatus): void {
      $level = self::getCurrentValidationLevel($formAnswer);

      $self = new self();
      $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
      $rows = $self->find([
         $formAnswerFk => $formAnswer->getID(),
         'level' => $level
      ]);
      foreach ($rows as $row) {
         $self->update([
            'id' => $row['id'],
            'status' => $newStatus,
         ]);
      }
   }

   /**
    * Copy validators from form to forn answer validators
    *
    * @return bool
    */
   public static function copyValidatorsToValidation(PluginFormcreatorFormAnswer $formAnswer): bool {
      global $DB;

      if ($formAnswer->fields['groups_id_validator'] <= 0 && $formAnswer->fields['users_id_validator'] <= 0) {
         return true;
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();

      // 1st validation level is the user or group selected by the requester
      $validation = new self();
      $itemtype = User::getType();
      $itemId = $formAnswer->fields['users_id_validator'];
      if ($formAnswer->fields['groups_id_validator'] > 0) {
         $itemtype = Group::getType();
         $itemId = $formAnswer->fields['groups_id_validator'];
      }
      $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
      $newId = $validation->add([
         $formAnswerFk => $formAnswer->getID(),
         'itemtype' => $itemtype,
         'items_id' => $itemId,
         'level'    => '1',
         'status'   => PluginFormcreatorForm_Validator::VALIDATION_STATUS_WAITING
      ]);
      if ($newId === false) {
         return false;
      }

      // Next levels are copied from form configuration
      $result = $DB->request([
         'FROM' => PluginFormcreatorForm_Validator::getTable(),
         'WHERE' => [
            $formFk => $formAnswer->fields[$formFk],
            'level' => ['>', '1'],
         ]
      ]);

      foreach ($result as $row) {
         $validation = new self();
         $newId = $validation->add([
            $formAnswerFk => $formAnswer->getID(),
            'itemtype' => $row['itemtype'],
            'items_id' => $row['items_id'],
            'level'    => $row['level'],
            'status'   => PluginFormcreatorForm_Validator::VALIDATION_STATUS_WAITING
         ]);

         if ($newId === false) {
            return false;
         }
      }
      return true;
   }

   /**
    * Undocumented function
    *
    * @param PluginFormcreatorFormAnswer $formAnswer
    * @return void
    */
   public static function showValidationStatuses(PluginFormcreatorFormAnswer $formAnswer) {
      $validations = $formAnswer->getApprovers();
      if ($validations === null) {
         return;
      }

      TemplateRenderer::getInstance()->display('@formcreator/components/form//formanswer.validation.html.twig', [
         'validations'   => $validations,
         'itemtypes'     => array_keys($validations),
      ]);
   }
}
