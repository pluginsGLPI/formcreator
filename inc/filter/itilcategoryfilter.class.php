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

namespace GlpiPlugin\Formcreator\Filter;


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

use Dropdown;

class ItilCategoryFilter
{
   // Not numeric because it is serialized with other values as JSON
   const ITIL_CATEGORY_FILTER_REQUEST = 'request';
   const ITIL_CATEGORY_FILTER_INCIDENT = 'incident';
   const ITIL_CATEGORY_FILTER_BOTH = 'both';
   const ITIL_CATEGORY_FILTER_CHANGE = 'change';
   const ITIL_CATEGORY_FILTER_ALL = 'all';

   public static function getEnumItilCategoryFilterRule() {
      return [
         self::ITIL_CATEGORY_FILTER_REQUEST   => __('Request categories', 'formcreator'),
         self::ITIL_CATEGORY_FILTER_INCIDENT  => __('Incident categories', 'formcreator'),
         self::ITIL_CATEGORY_FILTER_BOTH      => __('Request categories', 'formcreator') . " + " . __('Incident categories', 'formcreator'),
         self::ITIL_CATEGORY_FILTER_CHANGE    => __('Change categories', 'formcreator'),
         self::ITIL_CATEGORY_FILTER_ALL       => __('All', 'formcreator'),
      ];
   }

   /**
    * Show or return a dropdown of itil categories filter
    *
    * @see Dropdown::showFromArray for available options
    *
    * @param string $name name of the HTML input
    * @param array $options options to display the dropdown
    *
    * @return string random ID or HTML
    */
   public static function dropdown(string $name, array $options = []): string {
      return Dropdown::showFromArray(
         $name,
         self::getEnumItilCategoryFilterRule(),
         $options
      );
   }
}