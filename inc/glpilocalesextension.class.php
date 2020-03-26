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
use Twig\TwigFunction;

class GlpiLocalesExtension extends AbstractExtension
{
   /**
    * Sets aliases for functions
    *
    * @see Twig_Extension::getFunctions()
    * @return array
    */
   public function getFunctions() {
      return [
            new TwigFunction('__', '__'),
            new TwigFunction('__s', '__s'),
            new TwigFunction('_e', '_e'),
            new TwigFunction('_ex', '_ex'),
            new TwigFunction('_n', '_n'),
            new TwigFunction('_nx', '_nx'),
            new TwigFunction('_sn', '_sn'),
            new TwigFunction('_sx', '_sx'),
            new TwigFunction('_x', '_x'),
            new TwigFunction('sprintf', 'sprintf'),
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