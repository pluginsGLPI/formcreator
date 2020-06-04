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

abstract class PluginFormcreatorQuestionParameter
extends CommonDBChild
implements PluginFormcreatorQuestionParameterInterface, PluginFormcreatorExportableInterface
{
   use PluginFormcreatorExportable;

   // From CommonDBRelation
   static public $itemtype       = PluginFormcreatorQuestion::class;
   static public $items_id       = 'plugin_formcreator_questions_id';

   static public $disableAutoEntityForwarding   = true;

   /** @var string $fieldName the name of the field representing the parameter when editing the question */
   protected $fieldName = null;

   protected $label = null;

   protected $field;

   /** @var $domId string the first part of the DOM Id representing the parameter */
   protected $domId = '';

   /**
    * @param PluginFormcreatorFieldInterface $field Field
    * @param array $options
    *                - fieldName: name of the HTML input tag
    *                - label    : label for the parameter
    */
   public function __construct(PluginFormcreatorFieldInterface $field, array $options) {
      $fieldType = $field->getFieldTypeName();
      $fieldName = $options['fieldName'];
      $this->domId = $this->domId . "_{$fieldType}_{$fieldName}";
      $this->field = $field;
      $this->label = $options['label'];
      $this->fieldName = $options['fieldName'];
   }

   public function prepareInputforAdd($input) {
      $input = parent::prepareInputForAdd($input);
      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
   {
      $keepCriteria = [
         self::$items_id => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }
}
