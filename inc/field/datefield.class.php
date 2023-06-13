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
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace GlpiPlugin\Formcreator\Field;

use Html;
use DateTime;

class DateField extends DatetimeField
{
   const DATE_FORMAT = 'Y-m-d';
   const DATE_ZERO   = '0000-00-00';

   public function getRenderedHtml($domain, $canEdit = true): string {
      if (!$canEdit) {
         return $this->value;
      }

      $html = '';
      $id        = $this->question->getID();
      $rand      = mt_rand();
      $fieldName = 'formcreator_field_' . $id;

      $html .= Html::showDateField($fieldName, [
         'value'   => (strtotime($this->value) != '') ? $this->value : '',
         'rand'    => $rand,
         'display' => false,
      ]);
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeDate('$fieldName', '$rand');
      });");

      return $html;
   }

   public static function getName(): string {
      return __('Date');
   }

      /**
    * Convert a string value into DateTime object
    *
    * @param string $value
    * @return false|DateTime
    */
   protected function getDateFromValue(string $value) {
      if (empty($value)) {
         $value = self::DATE_ZERO;
      }
      $datetime = DateTime::createFromFormat(self::DATE_FORMAT, $value);
      if ($datetime !== false) {
         $datetime->setTime(0, 0, 0, 0);
      }
      return $datetime;
   }

   public function getValueForApi() {
      return $this->value;
   }
}
