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

namespace GlpiPlugin\Formcreator;

use CommonGLPI;
use Glpi\Application\View\TemplateRenderer;
use Profile as GlpiProfile;
use Session;

class Profile extends GlpiProfile {

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      return self::createTabEntry(Form::getTypeName(Session::getPluralNumber()));
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      /** @var CommonDBTM $item */
      $formcreatorprofile = new self();
      $formcreatorprofile->showForProfile($item->getID());
      return true;
   }

   public static function getAllRights($all = false) {
      $rights = [
         [
            'itemtype' => Form::getType(),
            'label'    => Form::getTypeName(Session::getPluralNumber()),
            'field'    => Form::$rightname
         ]
      ];

      return $rights;
   }

   public function showForProfile($ID, $options = []) {
      $profile = new GlpiProfile();
      $profile->getFromDB($ID);

      TemplateRenderer::getInstance()->display('@formcreator/pages/profile.html.twig', [
         'can_edit' => self::canUpdate(),
         'profile'  => $profile,
         'rights'   => self::getAllRights(),
      ]);
   }
}
