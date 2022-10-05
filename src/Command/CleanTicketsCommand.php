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
use Ticket;
use Item_Ticket;
use PluginFormcreatorFormAnswer;
use Glpi\Toolbox\Sanitizer;

class CleanTicketsCommand extends Command
{
   protected function configure() {
      $this
         ->setName('glpi:plugins:formcreator:clean_tickets')
         ->setDescription("Clean Tickets having visible HTML tags in their content");
   }

   protected function execute(InputInterface $input, OutputInterface $output) {
      $output->write("<info>-> Search tickets to clean...</info>");
      $output->writeln("");

      $this->fixBadForm_1($input, $output);
      $this->fixBadForm_2($input, $output);

      $output->writeln('<info>Done.</info>');
      return 0;
   }

   /**
    * fix HTML tags double encoded
    * <p> => &lt;p&gt; => &#38;lt;p&#38;gt;
    *
    * @param InputInterface $input
    * @param OutputInterface $output
    * @return void
    */
   protected function fixBadForm_1(InputInterface $input, OutputInterface $output) {
      global $DB;

      // Search tickets having HTML tags in content in the following form
      // &#38;lt;p&#38;gt;Hello world&#38;lt;/p&#38;gt;
      // Hello world is between <p> and </p>, but with wrong escaping
      $itemTicketTable = Item_Ticket::getTable();
      $ticketTable = Ticket::getTable();
      $pattern = '&#38;lt;';
      // $pattern = str_replace(';', '\\;', $pattern);
      $tickets = $DB->request([
         'SELECT' => [$ticketTable => [Ticket::getIndexName(), 'content']],
         'FROM' => $ticketTable,
         'INNER JOIN' => [
            $itemTicketTable => [
               'FKEY' => [
                  $ticketTable => Ticket::getIndexName(),
                  $itemTicketTable => Ticket::getForeignKeyField(),
               ],
               'AND' => [
                  "$itemTicketTable.itemtype" => PluginFormcreatorFormAnswer::getType(),
               ]
            ],
         ],
         'WHERE' => [
            "$ticketTable.content" => ['LIKE', '%' . $pattern . '%'], // Matches bad encoding for '<'
         ],
      ]);

      $count = $tickets->count();
      if ($count < 1) {
         $output->writeln('<info>-> No ticket to fix.</info>');
         $output->writeln("");
         return 0;
      }

      $output->write("<info>-> Found $count tickets to clean</info>");
      $output->writeln("");
      $output->write("<info>-> Cleaning tickets...</info>");
      $output->writeln("");
      foreach ($tickets as $row) {
         $pattern = [
            '/&#38;lt;([a-z0-9]+?)&#38;gt;/',
            '/&#38;lt;(\/[a-z0-9]+?)&#38;gt;/',
         ];
         $replace = [
            '&#60;$1&#62;',
            '&#60;$1&#62;',
         ];
         $row['content'] = preg_replace($pattern, $replace, $row['content']);
         // Direct write to the table to avoid alteration of other fields
         $DB->update(
            $ticketTable,
            [
               'content' => $DB->escape($row['content'])
            ],
            [
               'id' => $row['id'],
            ]
         );
      }
   }

   /**
    * remove HTML tag <br />
    *
    * @param InputInterface $input
    * @param OutputInterface $output
    * @return void
    */
   protected function fixBadForm_2(InputInterface $input, OutputInterface $output) {
      global $DB;

      // Search tickets having HTML tags <br />
      $itemTicketTable = Item_Ticket::getTable();
      $ticketTable = Ticket::getTable();
      $pattern = 'br /';
      $tickets = $DB->request([
         'SELECT' => [$ticketTable => [Ticket::getIndexName(), 'content']],
         'FROM' => $ticketTable,
         'INNER JOIN' => [
            $itemTicketTable => [
               'FKEY' => [
                  $ticketTable => Ticket::getIndexName(),
                  $itemTicketTable => Ticket::getForeignKeyField(),
               ],
               'AND' => [
                  "$itemTicketTable.itemtype" => PluginFormcreatorFormAnswer::getType(),
               ]
            ],
         ],
         'WHERE' => [
            "$ticketTable.content" => ['LIKE', '%' . $pattern . '%'], // Matches bad encoding for '<'
         ],
      ]);

      $count = $tickets->count();
      if ($count < 1) {
         $output->writeln('<info>-> No ticket to fix.</info>');
         $output->writeln("");
         return 0;
      }

      $output->write("<info>-> Found $count tickets to clean</info>");
      $output->writeln("");
      $output->write("<info>-> Cleaning tickets...</info>");
      $output->writeln("");
      foreach ($tickets as $row) {
         $pattern = [
            '<br />',
         ];
         $replace = [
            '&lt;br /&gt;',
         ];
         $row['content'] = str_replace($pattern, $replace, $row['content']);
         // Direct write to the table to avoid alteration of other fields
         $DB->update(
            $ticketTable,
            [
               'content' => $DB->escape($row['content'])
            ],
            [
               'id' => $row['id'],
            ]
         );
      }
   }
}
