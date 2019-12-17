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

/**
 *
 * @since 0.1.0
 */
class PluginFormcreatorEntityconfig extends CommonDBTM {

   const CONFIG_PARENT = -2;
   const CONFIG_SIMPLIFIED_SERVICE_CATALOG = 1;
   const CONFIG_EXTENDED_SERVICE_CATALOG = 2;

   /**
    * @var bool $dohistory maintain history
    */
   public $dohistory                   = true;

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $tabNames = [];
      if (!$withtemplate) {
         if ($item->getType() == 'Entity') {
            $tabNames[1] = _n('Form', 'Forms', 2, 'formcreator');
         }
      }
      return $tabNames;
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'Entity') {
         $config = new self();
         $config->showFormForEntity($item);
      }
   }

   public function showFormForEntity(Entity $entity) {
      $ID = $entity->getField('id');
      if (!$entity->can($ID, READ)
            || !Notification::canView()) {
         return false;
      }

      if (!$this->getFromDB($ID)) {
         $this->add([
               'id'                 => $ID,
               'replace_helpdesk'   => self::CONFIG_PARENT
         ]);
      }

      $canedit = $entity->canUpdateItem();
      echo "<div class='spaced'>";
      if ($canedit) {
         echo "<form method='post' name=form action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      }

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Helpdesk', 'formcreator')."</th></tr>";

      if ($ID != 0) {
         $elements = [
            self::CONFIG_PARENT => __('Inheritance of the parent entity')
         ];
      } else {
         $elements = [];
      }
      $elements[0] = __('GLPi\'s helpdesk', 'formcreator');
      $elements[1] = __('Service catalog simplified', 'formcreator');
      $elements[2] = __('Service catalog extended', 'formcreator');

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Helpdesk mode', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('replace_helpdesk', $elements, ['value' => $this->fields['replace_helpdesk']]);
      if ($this->fields['replace_helpdesk'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('replace_helpdesk', $ID);
         echo '<div class="green">';
         echo $elements[$tid];
         echo '</div>';
      }
      echo '</td></tr>';

      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='4'>";
         echo "<input type='hidden' name='id' value='".$entity->fields["id"]."'>";
         echo "<input type='submit' name='update' value=\""._sx('button', 'Save')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();

      } else {
         echo "</table>";
      }

      echo "</div>";
   }

   /**
    * Retrieve data of current entity or parent entity
    *
    * @since version 0.84 (before in entitydata.class)
    *
    * @param $fieldref        string   name of the referent field to know if we look at parent entity
    * @param $entities_id
    * @param $fieldval        string   name of the field that we want value (default '')
    * @param $default_value   integer  value to return (default -2)
    */
   static function getUsedConfig($fieldref, $entities_id, $fieldval = '', $default_value = -2) {

      // for calendar
      if (empty($fieldval)) {
         $fieldval = $fieldref;
      }

      $entity = new Entity();
      $entityConfig = new self();
      // Search in entity data of the current entity
      if ($entity->getFromDB($entities_id)) {
         // Value is defined : use it
         if ($entityConfig->getFromDB($entities_id)) {
            if (is_numeric($default_value)
                  && ($entityConfig->fields[$fieldref] != self::CONFIG_PARENT)) {
                     return $entityConfig->fields[$fieldval];
            }
            if (!is_numeric($default_value)) {
               return $entityConfig->fields[$fieldval];
            }

         }
      }

      // Entity data not found or not defined : search in parent one
      if ($entities_id > 0) {

         if ($entity->getFromDB($entities_id)) {
            $ret = self::getUsedConfig($fieldref, $entity->fields['entities_id'], $fieldval,
                  $default_value);
            return $ret;

         }
      }
      /*
       switch ($fieldval) {
       case "tickettype" :
       // Default is Incident if not set
       return Ticket::INCIDENT_TYPE;
       }
       */
      return $default_value;
   }
}
