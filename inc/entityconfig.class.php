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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 *
 * @since 0.1.0
 */
class PluginFormcreatorEntityconfig extends CommonDBTM {

   public static $rightname = 'entity';

   const CONFIG_PARENT = -2;
   const CONFIG_GLPI_HELPDSK = 0;
   const CONFIG_SIMPLIFIED_SERVICE_CATALOG = 1;
   const CONFIG_EXTENDED_SERVICE_CATALOG = 2;

   /**
    * @var bool $dohistory maintain history
    */
   public $dohistory                   = true;

   public static function getEnumHelpdeskMode() {
      return [
         self::CONFIG_PARENT                     => __('Inheritance of the parent entity'),
         self::CONFIG_GLPI_HELPDSK               => __('GLPi\'s helpdesk', 'formcreator'),
         self::CONFIG_SIMPLIFIED_SERVICE_CATALOG => __('Service catalog simplified', 'formcreator'),
         self::CONFIG_EXTENDED_SERVICE_CATALOG   => __('Service catalog extended', 'formcreator'),
      ];
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $tabNames = [];
      if (!$withtemplate) {
         if ($item->getType() == 'Entity') {
            $tabNames[1] = _n('Form', 'Forms', 2, 'formcreator');
         }
      }
      return $tabNames;
   }

   static function isNewID($ID) {
      return Entity::isNewID($ID);
   }

   public function canUpdateItem() {
      $entity = new Entity();
      $entity->getFromDB($this->getID());
      return $entity->canUpdateItem();
   }

   public static function canDelete() {
      return false;
   }

   public static function canPurge() {
      return false;
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() != Entity::class) {
         return false;
      }
      self::showForEntity($item);
      return true;
   }

   public function prepareInputForUpdate($input) {
      $entity = new Entity();
      $entity->getFromDB($this->getID());
      if ($entity->fields['level'] < 2) {
         // This config applies to a top level entity
         $input['replace_helpdesk'] = self::CONFIG_GLPI_HELPDSK;
      }

      return $input;
   }

   public static function showForEntity(Entity $entity, $withtemplate = '') {
      $ID = $entity->fields['id'];
      if (!$entity->can($ID, READ) || !Notification::canView()) {
         return false;
      }

      $entityConfig = new self();
      $entityConfig->getFromDB($ID);
      if (!$entityConfig->getFromDB($ID)) {
         $entityConfig->add([
            'id'                 => $ID,
            'replace_helpdesk'   => self::CONFIG_PARENT
         ]);
      }

      $options = [
         'formtitle' => __('Helpdesk', 'formcreator'),
         'canedit'   => $entityConfig->canUpdateItem(),
      ];
      $entityConfig->initForm($entityConfig->getID(), $options);
      $entityConfig->showFormHeader($options);

      $elements = self::getEnumHelpdeskMode();
      $data = [
         'so'     => [
            self::getType() => $entityConfig->searchOptions(),
         ],
         'entity' => $entity,
         'item'   => $entityConfig,
         'current_replace_helpdesk' => $elements[$entityConfig->fields['replace_helpdesk']],
      ];

      plugin_formcreator_render('entityconfig/showforentity.html.twig', $data);
      $entityConfig->showFormButtons();
   }

   public function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'              => '5',
         'table'           => self::getTable(),
         'name'            => self::getTypeName(1),
         'field'           => 'replace_helpdesk',
         'datatype'        => 'specific',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      return $tab;
   }

   public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'replace_helpdesk':
            $elements = self::getEnumHelpdeskMode();
            $options['value'] = $values[$field];
            return Dropdown::showFromArray(
               $name, $elements, $options
            );
         break;
      }

      return parent::getSpecificValueToSelect($field, $name, $values, $options);
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
