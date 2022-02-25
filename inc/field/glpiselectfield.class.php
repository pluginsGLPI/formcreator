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

namespace GlpiPlugin\Formcreator\Field;

use Html;
use Session;
use Dropdown;
use Entity;
use Plugin;
use CommonTreeDropdown;

use GlpiPlugin\Formcreator\Exception\ComparisonException;
use Glpi\Application\View\TemplateRenderer;

class GlpiselectField extends DropdownField
{
   public function showForm(array $options): void {
      $template = '@formcreator/field/' . $this->question->fields['fieldtype'] . 'field.html.twig';

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $this->question->fields['_is_tree'] = '0';
      $this->question->fields['_tree_root'] = $decodedValues['show_tree_root'] ?? Dropdown::EMPTY_VALUE;
      $this->question->fields['_tree_root_selectable'] = $decodedValues['selectable_tree_root'] ?? '0';
      $this->question->fields['_tree_max_depth'] = $decodedValues['show_tree_depth'] ?? Dropdown::EMPTY_VALUE;
      $this->question->fields['_is_entity_restrict'] = '0';
      if (isset($this->question->fields['itemtype']) && is_subclass_of($this->question->fields['itemtype'], CommonTreeDropdown::class)) {
         $this->question->fields['_is_tree'] = '1';
         $item = new $this->question->fields['itemtype'];
         $this->question->fields['_is_entity_restrict'] = $item->isEntityAssign() ? '1' : '0';
      }
      $this->question->fields['default_values'] = Html::entities_deep($this->question->fields['default_values']);
      $this->deserializeValue($this->question->fields['default_values']);

      TemplateRenderer::getInstance()->display($template, [
         'item' => $this->question,
         'params' => $options,
      ]);
   }

   public function getDesignSpecializationField(): string {
      $rand = mt_rand();

      $label = '<label for="dropdown_glpi_objects' . $rand . '" id="label_dropdown_values">';
      $label .= _n('GLPI object', 'GLPI objects', 1, 'formcreator');
      $label .= '</label>';

      $optgroup = $this->getObjects();

      // Get additional itemtypes from plugins
      $additionalTypes = Plugin::doHookFunction('formcreator_get_glpi_object_types', []);
      // Cleanup data from plugins
      $cleanedAditionalTypes = [];
      foreach ($additionalTypes as $groupName => $itemtypes) {
         if (!is_string($groupName)) {
            continue;
         }
         $cleanedAditionalTypes[$groupName] = [];
         foreach ($itemtypes as $itemtype => $typeName) {
            if (!class_exists($itemtype)) {
               continue;
            }
            if (array_search($itemtype, $cleanedAditionalTypes[$groupName])) {
               continue;
            }
            $cleanedAditionalTypes[$groupName][$itemtype] = $typeName;
         }
      }
      // Merge new itemtypes to predefined ones
      $optgroup = array_merge_recursive($optgroup, $cleanedAditionalTypes);

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $itemtype = $this->question->fields['itemtype'];

      array_unshift($optgroup, '---');
      $field = Dropdown::showFromArray('glpi_objects', $optgroup, [
         'value'     => $itemtype,
         'rand'      => $rand,
         'on_change' => 'plugin_formcreator_changeGlpiObjectItemType();',
         'display'   => false,
      ]);

      $root = $decodedValues['show_tree_root'] ?? '0';
      $maxDepth = $decodedValues['show_tree_depth'] ?? Dropdown::EMPTY_VALUE;
      $selectableRoot = $decodedValues['selectable_tree_root'] ?? '0';

      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values' . $rand . '">';
      $additions .= __('Default values');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td id="dropdown_default_value_field">';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= "<input id='commonTreeDropdownRoot' type='hidden' value='$root'>";
      $additions .= "<input id='commonTreeDropdownMaxDepth' type='hidden' value='$maxDepth'>";
      $additions .= "<input id='commonTreeDropdownSelectableRoot' type='hidden' value='$selectableRoot'>";
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '</tr>';
      $additions .= Html::scriptBlock("plugin_formcreator_changeGlpiObjectItemType($rand);");

      $additions .= '<tr class="plugin_formcreator_question_specific plugin_formcreator_dropdown">';
      // This row will be generated by an AJAX request
      $additions .= '</tr>';

      $additions .= $this->getEntityRestrictSettiing();

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => true,
         'may_be_required' => static::canRequire(),
      ];
   }

   public static function getName(): string {
      return _n('GLPI object', 'GLPI objects', 1, 'formcreator');
   }

   public function isValidValue($value): bool {
      $itemtype = $this->getSubItemtype();
      if ($itemtype == Entity::getType() && $value == '-1') {
         return true;
      }

      return parent::isValidValue($value);
   }

   public function prepareQuestionInputForSave($input) {
      if (!isset($input['itemtype']) || empty($input['itemtype'])) {
         Session::addMessageAfterRedirect(
            __('The field value is required:', 'formcreator') . ' ' . $input['name'],
            false,
            ERROR
         );
         return [];
      }

      $itemtype = $input['itemtype'];
      $input['itemtype'] = $itemtype;
      $input['values'] = [];
      // Params for entity restrictables itemtypes
      $input['values']['entity_restrict'] = $input['entity_restrict'] ?? self::ENTITY_RESTRICT_FORM;
      unset($input['entity_restrict']);

      $input['default_values'] = $input['default_values'] ?? '';

      // Params for CommonTreeDropdown fields
      if (is_a($itemtype, CommonTreeDropdown::class, true)) {
         // Set default for depth setting
         $input['values']['show_tree_depth'] = (int) ($input['show_tree_depth'] ?? '-1');
         $input['values']['show_tree_root'] = ($input['show_tree_root'] ?? '');
         $input['values']['selectable_tree_root'] = ($input['selectable_tree_root'] ?? '0');
      }
      unset($input['show_tree_root']);
      unset($input['show_tree_depth']);
      unset($input['selectable_tree_root']);

      $input['values'] = json_encode($input['values']);

      return $input;
   }

   public static function canRequire(): bool {
      return true;
   }

   public function getAvailableValues(): array {
      return [];
   }

   public function equals($value): bool {
      $value = html_entity_decode($value);
      $itemtype = $this->getSubItemtype();
      $item = new $itemtype();
      if ($item->isNewId($this->value)) {
         return ($value === '');
      }
      if (!$item->getFromDB($this->value)) {
         throw new ComparisonException('Item not found for comparison');
      }
      return $item->getField($item->getNameField()) == $value;
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      $value = html_entity_decode($value);
      $itemtype = $this->getSubItemtype();
      $item = new $itemtype();
      if (!$item->getFromDB($this->value)) {
         throw new ComparisonException('Item not found for comparison');
      }
      return $item->getField($item->getNameField()) > $value;
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      return (preg_grep($value, $this->value)) ? true : false;
   }

   public function isPublicFormCompatible(): bool {
      return false;
   }
}
