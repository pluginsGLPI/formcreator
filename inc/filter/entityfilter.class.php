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

namespace GlpiPlugin\Formcreator\Filter;

use Dropdown;
use User;
use PluginFormcreatorForm;

class EntityFilter
{
   const ENTITY_RESTRICT_USER = 1;
   const ENTITY_RESTRICT_FORM = 2;
   const ENTITY_RESTRICT_BOTH = 3;

   public static function getEnumEntityRestriction() {
      return [
         self::ENTITY_RESTRICT_USER =>  User::getTypeName(1),
         self::ENTITY_RESTRICT_FORM =>  PluginFormcreatorForm::getTypeName(1),
         self::ENTITY_RESTRICT_BOTH =>  __('User and form', 'formcreator'),
      ];
   }

   /**
    * Show or return a dropdown of entity restriction settings
    *
    * @param string $name
    * @param array $options
    * @return string
    */
   public static function dropdown(string $name, array $options = []): ?string {
      return Dropdown::showFromArray(
         $name,
         self::getEnumEntityRestriction(),
         $options
      );
   }
}