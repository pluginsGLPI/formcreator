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

namespace GlpiPlugin\Formcreator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Html;
use RuntimeException;
use Plugin;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class CompileScssCommand extends Command
{
   /**
    * Error code returned if unable to write compiled CSS.
    *
    * @var integer
    */
   const ERROR_UNABLE_TO_WRITE_COMPILED_FILE = 1;

   protected function configure() {

      $this
         ->setName('glpi:plugins:formcreator:scss')
         ->setDescription("Compile SCSS");
   }

   protected function initialize(InputInterface $input, OutputInterface $output) {
      $compile_directory = Plugin::getPhpDir('formcreator') . '/css_compiled';

      if (!@is_dir($compile_directory) && !@mkdir($compile_directory)) {
         throw new RuntimeException(
            sprintf(
               'Destination directory "%s" cannot be accessed.',
               $compile_directory
            )
         );
      }

      $this->addOption(
         'file',
         'f',
         InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
         'File to compile (compile main style by default)'
      );
   }

   protected function execute(InputInterface $input, OutputInterface $output) {

      $files = $input->getOption('file');

      if (empty($files)) {
         $root_path = realpath(Plugin::getPhpDir('formcreator'));

         $css_dir_iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root_path . '/css'),
            RecursiveIteratorIterator::SELF_FIRST
         );
         /** @var SplFileInfo $file */
         foreach ($css_dir_iterator as $file) {
            if (!$file->isReadable() || !$file->isFile() || $file->getExtension() !== 'scss') {
               continue;
            }

            $files[] = str_replace($root_path . '/', '', dirname($file->getRealPath()))
               . '/'
               . preg_replace('/^_?(.*)\.scss$/', '$1', $file->getBasename());
         }
      }

      foreach ($files as $file) {
         $output->writeln(
            '<comment>' . sprintf('Processing "%s".', $file) . '</comment>',
            OutputInterface::VERBOSITY_VERBOSE
         );

         $compiled_path =  Plugin::getPhpDir('formcreator') . "/css_compiled/" . basename($file) . ".min.css";
         $css = Html::compileScss(
            [
               'file'    => Plugin::getPhpDir('formcreator', false) . '/' . $file,
               'nocache' => true,
               'debug'   => true,
            ]
         );

         if (strlen($css) === @file_put_contents($compiled_path, $css)) {
            $message = sprintf('"%s" compiled successfully in "%s".', $file, $compiled_path);
            $output->writeln(
               '<info>' . $message . '</info>',
               OutputInterface::VERBOSITY_NORMAL
            );
         } else {
            $message = sprintf('Unable to write compiled CSS in "%s".', $compiled_path);
            $output->writeln(
               '<error>' . $message . '</error>',
               OutputInterface::VERBOSITY_QUIET
            );
            return self::ERROR_UNABLE_TO_WRITE_COMPILED_FILE;
         }
      }

      return 0; // Success
   }
}
