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
 * @copyright Copyright © 2011 - 2022 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link     https://github.com/pluginsGLPI/formcreator/
 * @link     https://pluginsglpi.github.io/formcreator/
 * @link     http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorForm_User extends PluginFormcreatorRestrictedFormCriteria
{
   public static $itemtype_2 = User::class;
   public static $items_id_2 = 'users_id';

   /**
    * Check if the current logged in user is in the "whitelisted"
    * users list for this form
    *
    * @param PluginFormcreatorForm $form The given form
    *
    * @return bool True if there is a match, the user is whitelisted
    */
   public static function getListCriteriaSubQuery(): QuerySubQuery {
      // Allow only the current user.
      return new QuerySubQuery([
         'SELECT' => static::$items_id_1,
         'FROM'   => self::getTable(),
         'WHERE'  => [
            static::$items_id_2 => Session::getLoginUserID(),
         ]
      ]);
   }
}
