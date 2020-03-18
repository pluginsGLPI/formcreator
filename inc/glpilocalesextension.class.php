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

class GlpiLocalesExtension extends \Twig_Extension
{
   /**
    * Sets aliases for functions
    *
    * @see Twig_Extension::getFunctions()
    * @return array
    */
   public function getFunctions() {
      return [
            new \Twig_SimpleFunction('__', '__'),
            new \Twig_SimpleFunction('__s', '__s'),
            new \Twig_SimpleFunction('_e', '_e'),
            new \Twig_SimpleFunction('_ex', '_ex'),
            new \Twig_SimpleFunction('_n', '_n'),
            new \Twig_SimpleFunction('_nx', '_nx'),
            new \Twig_SimpleFunction('_sn', '_sn'),
            new \Twig_SimpleFunction('_sx', '_sx'),
            new \Twig_SimpleFunction('_x', '_x'),
      ];
   }

   /**
    * Returns the name of the extension.
    *
    * @return string The extension name
    *
    * @see Twig_ExtensionInterface::getName()
    */
   public function getName() {
      return 'glpi_locales_extension';
   }

   public function getFilters() {
      return [
         new \Twig_SimpleFilter('fileSize', [$this, 'fileSizeFilter']),
      ];
   }

   /**
    * Format a size passing a size in octet
    * @param int $number
    * @return string
    */
   public function fileSizeFilter($number) {
      return Toolbox::getSize($number);
   }
}