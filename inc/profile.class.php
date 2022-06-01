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

class PluginFormcreatorProfile extends Profile {

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      return self::createTabEntry(PluginFormcreatorForm::getTypeName(Session::getPluralNumber()));
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $formcreatorprofile = new self();
      $formcreatorprofile->showForm($item->getID());
      return true;
   }

   function showForm($ID, $options = []) {
      if (!self::canView()) {
         return false;
      }

      echo "<div class='spaced'>";
      $profile = new Profile();
      $profile->getFromDB($ID);
      echo "<form method='post' action='".$profile->getFormURL()."'>";

      $rights = [['itemtype'  => PluginFormcreatorForm::getType(),
                  'label'     => PluginFormcreatorForm::getTypeName(Session::getPluralNumber()),
                  'field'     => PluginFormcreatorForm::$rightname]];
      $matrix_options['title'] = PluginFormcreatorForm::getTypeName(Session::getPluralNumber());
      $profile->displayRightsChoiceMatrix($rights, $matrix_options);

      echo "<div class='center'>";
      echo Html::hidden('id', ['value' => $ID]);
      echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
      echo "</div>\n";
      Html::closeForm();
      echo "</div>";
   }
}
