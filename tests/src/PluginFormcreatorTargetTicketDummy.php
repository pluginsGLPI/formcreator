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

namespace GlpiPlugin\Formcreator\Tests;

class PluginFormcreatorTargetTicketDummy extends \PluginFormcreatorTargetTicket
{
   public static function getTable($classname = null) {
      return \PluginFormcreatorTargetTicket::getTable();
   }

   public function publicSetTargetEntity($data, \PluginFormcreatorFormAnswer $formanswer, $requesters_id) {
      return $this->SetTargetEntity($data, $formanswer, $requesters_id);
   }

   public function publicPrepareTemplate($template, \PluginFormcreatorFormAnswer $formAnswer, $disableRichText = false) {
      return $this->prepareTemplate($template, $formAnswer, $disableRichText);
   }

   public function publicGetItem_User() {
      return $this->getItem_User();
   }

   public function publicGetItem_Group() {
      return $this->getItem_Group();
   }

   public function publicGetItem_Supplier() {
      return $this->getItem_Supplier();
   }

   public function publicGetItem_Item() {
      return $this->getItem_Item();
   }

   public function publicGetItem_Actor() {
      return $this->getItem_Actor();
   }

   public function publicGetCategoryFilter() {
      return $this->getCategoryFilter();
   }

   public function publicGetTaggableFields() {
      return $this->getTaggableFields();
   }

   public function publicGetTargetItemtypeName() {
      return $this->getTargetItemtypeName();
   }

   public function publicSetTargetCategory($data, $formanswer) {
      return $this->setTargetCategory($data, $formanswer);
   }

   public function publicSetTargetAssociatedItem($data, $formanswer) {
      return $this->setTargetAssociatedItem($data, $formanswer);
   }

   public function publicSetTargetType($data, $formanswer) {
      return $this->setTargetType($data, $formanswer);
   }

   public function publicGetDefaultData($formanswer) {
      return $this->getDefaultData($formanswer);
   }
}
