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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorFormList extends CommonGLPI
{

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Form', 'Forms', $nb, 'formcreator');
   }

   static function getMenuContent() {
      $menu = parent::getMenuContent();
      $menu['title'] = static::getTypeName(2);
      if (method_exists(Plugin::class, 'getWebDir')) {
         $menu['page'] = '/' . Plugin::getWebDir('formcreator', false) . '/front/formlist.php';
      } else {
         $menu['page'] =  '/plugins/formcreator/front/formlist.php';
      }
      $menu['icon'] = 'fas fa-edit';
      $image = '<img src="' . FORMCREATOR_ROOTDOC . '/pics/check.png"
                  title="' . __('Forms waiting for validation', 'formcreator') . '"
                  alt="' . __('Forms waiting for validation', 'formcreator') . '">';

      $menu['links']['search'] = PluginFormcreatorFormList::getSearchURL(false);
      if (PluginFormcreatorForm::canCreate()) {
         $menu['links']['add'] = PluginFormcreatorForm::getFormURL(false);
      }
      $menu['links']['config'] = PluginFormcreatorForm::getSearchURL(false);
      $menu['links'][$image]   = PluginFormcreatorFormAnswer::getSearchURL(false);

      return $menu;
   }

}
