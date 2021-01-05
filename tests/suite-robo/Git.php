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

namespace tests\units;

use GlpiPlugin\Formcreator\Tests\CommonTestCase;

// No autoload for the tested file
require_once __DIR__ . '/../../RoboFile.php';

class Git extends CommonTestCase {

   public function providerGetLastTag() {
       return [
           [
               'tags' => [
                   '2.4.0',
                   '2.4.1',
               ],
               'expected' => '2.4.1'
           ],
           [
               'tags' => [
                   '0.90-1.3.3',
                   '0.90-1.3.4',
               ],
               'expected' => '0.90-1.3.4'
           ],
           [
               'tags' => [
                   '0.90-1.3.3',
                   '0.90-1.3.4',
                   '2.4.0',
                   '2.4.1',
               ],
               'expected' => '2.4.1'
           ],
       ];
   }

    /**
     * @dataProvider providerGetLastTag
     */
   public function testgetLastTag($tags, $expected) {
       $output = \Git::getLastTag($tags);
       $this->string($output)->isEqualTo($expected);
   }

   public function providerCreateCommitList() {
       $commits = [];
       $i = 0;

       $commit = new \StdClass();
       $commit->hash = '582d37c8';
       $commit->message = 'fix(form): typo in var name';
       $commits[$i++] = $commit;

       $commit = new \StdClass();
       $commit->hash = '812c76d3';
       $commit->message = 'fix: useless escaping';
       $commits[$i++] = $commit;

       $commit = new \StdClass();
       $commit->hash = '7d296f21';
       $commit->message = 'docs: bump version in package.json';
       $commits[$i++] = $commit;

       $commit = new \StdClass();
       $commit->hash = '9247a88a';
       $commit->message = 'refactor(targetticket,formanswer): optimize getForm() methods';
       $commits[$i++] = $commit;

       return [
           [
               [
                   '582d37c8 fix(form): typo in var name',
                   '812c76d3 fix: useless escaping',
                   '7d296f21 docs: bump version in package.json',
                   '9247a88a refactor(targetticket,formanswer): optimize getForm() methods'
               ],
               'expected' => $commits,
           ]
       ];
   }

    /**
     * @dataProvider providerCreateCommitList
     */
   public function testCreateCommitList($commits, $expected) {
       $output = \Git::createCommitList($commits);
       $this->array($output)->isEqualTo($expected);
   }
}