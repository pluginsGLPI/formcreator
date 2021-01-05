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

use PluginFormcreatorIssue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncIssuesCommand extends Command
{
   protected function configure() {
      $table = PluginFormcreatorIssue::getTable();

      $this
         ->setName('glpi:plugins:formcreator:syncissues')
         ->setDescription("Rebuild `$table` table.");
   }

   protected function execute(InputInterface $input, OutputInterface $output) {
      global $DB;

      $output->write("<info>-> Step 1/2...</info>");
      $DB->delete(PluginFormcreatorIssue::getTable(), [1]);
      $output->write('<info> OK.</info>');
      $output->writeln("");

      $output->write("<info>-> Step 2/2...</info>");
      PluginFormcreatorIssue::syncIssues();
      $output->write('<info> OK.</info>');
      $output->writeln("");

      $output->writeln('<info>Done.</info>');
      return 0;
   }
}
