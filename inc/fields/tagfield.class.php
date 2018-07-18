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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorTagField extends PluginFormcreatorDropdownField
{
   const IS_MULTIPLE    = true;

   public function displayField($canEdit = true) {
      if ($canEdit) {
         $rand     = mt_rand();
         $required = $this->fields['required'] ? ' required' : '';

         $values = [];

         $obj = new PluginTagTag();
         $obj->getEmpty();

         $where = "(`type_menu` LIKE '%\"Ticket\"%' OR `type_menu` LIKE '0')";
         $where .= getEntitiesRestrictRequest('AND', getTableForItemType('PluginTagTag'), '', '', true);

         $result = $obj->find($where, "name");
         foreach ($result AS $id => $data) {
            $values[$id] = $data['name'];
         }

         Dropdown::showFromArray('formcreator_field_' . $this->fields['id'], $values, [
            'values'               => $this->getValue(),
            'comments'            => false,
            'rand'                => $rand,
            'multiple'            => true,
         ]);
         echo PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("select2-selecting", function(e) {
                        formcreatorChangeValueOf (' . $this->fields['id']. ', e.val);
                     });
                  });
               </script>';
      } else {
         $answer = $this->getAnswer();
         echo '<div class="form_field">';
         echo empty($answer) ? '' : implode('<br />', json_decode($answer));
         echo '</div>';
      }
   }

   public function getAnswer() {
      $return = [];
      $values = $this->getValue();

      if (!empty($values)) {
         foreach ($values as $value) {
            $return[] = Dropdown::getDropdownName(getTableForItemType('PluginTagTag'), $value);
         }
      }

      return json_encode($return);
   }

   public function prepareQuestionInputForSave($input) {
      return $input;
   }

   public static function getName() {
      return _n('Tag', 'Tags', 2, 'tag');
   }

   public static function getPrefs() {
      return array(
         'required'       => 0,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['tag'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
