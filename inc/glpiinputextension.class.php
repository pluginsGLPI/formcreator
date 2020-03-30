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

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Toolbox;

class GlpiInputExtension extends AbstractExtension
{
   /**
    * Sets aliases for functions
    *
    * @see Twig_Extension::getFunctions()
    * @return array
    */
    public function getFilters() {
      return [
            new TwigFilter('field', [$this, 'formatField'], ['is_safe' => ['html']]),
      ];
   }

      /**
    * Sets aliases for functions
    *
    * @see Twig_Extension::getFunctions()
    * @return array
    */
    public function getFunctions() {
      return [
         new TwigFunction('rand', 'mt_rand'),
         new TwigFunction('canUpdateItem', [__CLASS__, 'canUpdateItem']),
         new TwigFunction('canPurgeItem', [__CLASS__, 'canPurgeItem']),
         new TwigFunction('canEdit', [__CLASS__, 'canEdit']),
      ];
   }

   public function formatField($item, $searchOptions, $searchOptionId, $options = []) {
      $options['display'] = false;

      $searchOption = $searchOptions[$item::getType()][$searchOptionId];
      if (!isset($searchOption['datatype'])) {
         $searchOption['datatype'] = '';
      } else if ($searchOption['datatype'] == 'itemlink') {
         $searchOption['datatype'] = 'string';
      }
      $field = $searchOption['field'];
      if (isset($options['field'])) {
         $field = $options['field'];
      }
      if (method_exists($item, 'getDropdownCondition')) {
         $options['condition'] = $item->getDropdownCondition($searchOption['field']);
      }

      if (isset($options['hidden']) && $options['hidden']) {
         $output = Html::hidden($field, ['value' => $item->fields[$field]]);
         return $output;
      }

      if ($searchOption['datatype'] == 'color') {
         $options['value'] = $item->fields[$field];
      }

      $output = $item->getValueToSelect($searchOption, $field, $item->fields[$searchOption['field']], $options);
      return $output;
   }

   public static function canUpdateItem(CommonDBTM $item) {
      return $item->canUpdateItem();
   }

   public static function canPurgeItem(CommonDBTM $item) {
      return $item->canPurgeItem();
   }

   public static function canEdit(CommonDBTM $item) {
      return $item->canEdit($item->getID());
   }
}