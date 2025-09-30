<?php

/**
 *
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
 * @copyright Copyright © 2011 - 2018-2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class RoboFilePlugin extends \Robo\Tasks
{

   protected $csignore = [
      '/vendor/',
      '/node_modules/',
      '/lib/',
      '/development/',
      '/output/',
   ];
   protected $csfiles  = ['./'];

   /**
    * Minify all
    *
    * @return void
    */
   public function minify() {
      $this->minifyCSS()
         ->minifyJS();
   }

   /**
    * Minify CSS stylesheets
    *
    * @return RoboFilePlugin
    */
   public function minifyCSS(): RoboFilePlugin {
      $css_dir = __DIR__ . '/css';
      if (is_dir($css_dir)) {
         foreach (glob("$css_dir/*.scss") as $css_file) {
            $outfile = dirname(dirname($css_file)) . '/css_compiled/' . basename($css_file, '.scss').'.css';
            if (!$this->endsWith($css_file, 'min.css')) {
               $this->taskScss([
                  $css_file => $outfile,
               ])->run();
               $this->taskMinify($outfile)->run();
            }
         }
      }
      return $this;
   }

   /**
    * Minify JavaScript files stylesheets
    *
    * @return void
    */
   public function minifyJS() {
      $js_dir = __DIR__ . '/js';
      if (is_dir($js_dir)) {
         foreach (glob("$js_dir/*.js") as $js_file) {
            if (!$this->endsWith($js_file, 'min.js')) {
               $this->taskMinify($js_file)
                  ->to(str_replace('.js', '.min.js', $js_file))
                  ->type('js')
                  ->run();
            }
         }
      }
      return $this;
   }

   /**
    * Extract translatable strings
    *
    * @return void
    */
   public function localesExtract() {
      $this->_exec('tools/extract_template.sh');
      return $this;
   }

   /**
    * Push locales to transifex
    *
    * @return void
    */
   public function localesPush() {
      $success = $this->taskExec('tx')
         ->arg('push')
         ->arg('-s')
         ->run();
      if ($success->getExitCode() != 0) {
         throw new RuntimeException("failed to send source locales to transifex");
      }
      return $this;
   }

   /**
    * Pull locales from transifex.
    *
    * @param integer $percent Completeness percentage
    *
    * @return void
    */
   public function localesPull($percent = 70) {
      $this->_exec('tx pull -f -a --minimum-perc=' .$percent);
      return $this;
   }

   /**
    * Build MO files
    *
    * @return void
    */
   public function localesMo() {
      $po_files = preg_grep('/\.po$/', scandir('./locales'));
      foreach ($po_files as $po_file) {
         $mo_file = preg_replace('/\.po$/', '.mo', $po_file);
         echo("Processing {$po_file}\n");
         passthru("cd ./locales && msgfmt -f -o {$mo_file} {$po_file}", $exit_code);
         if ($exit_code > 0) {
            exit($exit_code);
         }
      }

      return $this;
   }

   /**
    * Extract and send locales
    *
    * @return void
    */
   public function localesSend() {
      $this->localesExtract()
           ->localesPush();
      return $this;
   }

   /**
    * Retrieve locales and generate mo files
    *
    * @param integer $percent Completeness percentage
    *
    * @return void
    */
   public function localesGenerate($percent = 70) {
      $this->localesPull($percent)
           ->localesMo();
      return $this;
   }

   /**
    * Checks if a string ends with another string
    *
    * @param string $haystack Full string
    * @param string $needle   Ends string
    *
    * @return boolean
    * @see http://stackoverflow.com/a/834355
    */
   private function endsWith($haystack, $needle) {
      $length = strlen($needle);
      if ($length == 0) {
         return true;
      }

      return (substr($haystack, -$length) === $needle);
   }

   /**
    * Code sniffer.
    *
    * Run the PHP Codesniffer on a file or directory.
    *
    * @param string $file    A file or directory to analyze.
    * @param array  $options Options:
    * @option $autofix Whether to run the automatic fixer or not.
    * @option $strict  Show warnings as well as errors.
    *    Default is to show only errors.
    *
    *    @return void
    */
   public function codeCs(
      $file = null,
      $options = [
         'autofix'   => false,
         'strict'    => false,
      ]
   ) {
      if ($file === null) {
         $file = implode(' ', $this->csfiles);
      }

      $csignore = '';
      if (count($this->csignore)) {
         $csignore .= '--ignore=';
         $csignore .= implode(',', $this->csignore);
      }

      $strict = $options['strict'] ? '' : '-n';

      $result = $this->taskExec("./vendor/bin/phpcs $csignore --standard=vendor/glpi-project/coding-standard/GlpiStandard/ --standard=tests/rulest.xml {$strict} {$file}")->run();

      if (!$result->wasSuccessful()) {
         if (!$options['autofix'] && !$options['no-interaction']) {
            $options['autofix'] = $this->confirm('Would you like to run phpcbf to fix the reported errors?');
         }
         if ($options['autofix']) {
            $result = $this->taskExec("./vendor/bin/phpcbf $csignore --standard=vendor/glpi-project/coding-standard/GlpiStandard/ {$file}")->run();
         }
      }

      return $result;
   }

}
