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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorFormanswerValidation extends CommonDBTM
{
   public static function getCurrentValidationLevel(PluginFormcreatorFormAnswer $formAnswer) {
      global $DB;

      $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
      $request = [
         'SELECT' => ['MIN' => 'level as level'],
         'FROM' => self::getTable(),
         'WHERE' => [
            $formAnswerFk => $formAnswer->fields['id'],
            ['NOT' => [
               'status' => PluginFormcreatorForm_Validator::VALIDATION_STATUS_WAITING,
            ]],
         ],
      ];
      $max = $DB->request($request)->next();
      if ($max === null || $max['level'] === null) {
         return 1;
      }

      return $max['level'] + 1;
   }

   public static function updateValidationStatusForLevel(PluginFormcreatorFormAnswer $formAnswer, int $newStatus) {
      $level = self::getCurrentValidationLevel($formAnswer);

      $self = new self();
      $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
      $rows = $self->find([
         $formAnswerFk => $formAnswer->fields['id'],
         'level' => $level
      ]);
      foreach ($rows as $row) {
         $self->update([
            'id' => $row['id'],
            'status' => $newStatus,
         ]);
      }
   }

   public static function computeValidationStatus(PluginFormcreatorFormAnswer $formAnswer) {
      global $DB;

      $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
      $result = $DB->request([
         'FROM' => self::getTable(),
         'WHERE' => [
            $formAnswerFk => $formAnswer->getID(),
         ],
         'GROUPBY' => ['level'],
      ]);

      $acceptedCount = $refusedCount = 0;
      foreach ($result as $row) {
         switch ($row['status']) {
            case PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED:
               $acceptedCount++;
               break;

            case PluginFormcreatorForm_Validator::VALIDATION_STATUS_REFUSED:
               $refusedCount++;
               break;
         }
      }

      // Get max validation level
      $request = [
         'SELECT' => ['MAX' => 'level as level'],
         'FROM' => self::getTable(),
         'WHERE' => [
            $formAnswerFk => $formAnswer->getID(),
         ],
      ];
      $max = $DB->request($request)->next();
      $maxLevel = ($max !== null) ? $max['level'] : 0;

      $validationPercent = $formAnswer->fields['validation_percent'];
      if ($validationPercent > 0) {
         // A validation percent is defined
         $acceptedRatio = $acceptedCount * 100 / $maxLevel;
         $refusedRatio = $refusedCount * 100 / $maxLevel;
         if ($acceptedRatio >= $validationPercent) {
            return PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED;
         }
         if ($refusedRatio >= $validationPercent) {
            return PluginFormcreatorForm_Validator::VALIDATION_STATUS_REFUSED;
         }
      } else {
         // No validation threshold set, one approval or denial is enough
         if ($acceptedCount > 0) {
            return PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED;
         }
         if ($refusedCount > 0) {
            return PluginFormcreatorForm_Validator::VALIDATION_STATUS_REFUSED;
         }
      }

      return PluginFormcreatorForm_Validator::VALIDATION_STATUS_WAITING;
   }
}