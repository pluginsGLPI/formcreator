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

class ConventionalChangelog extends CommonTestCase {
   public function providerFilterCommits() {
       $commits = [];
       $i = 0;

       $commit = new \StdClass();
       $commit->hash = '12345';
       $commit->message = 'fix: valid';
       $commits[$i++] = $commit;

       $commit = new \StdClass();
       $commit->hash = '12345';
       $commit->message = 'fix(main): valid';
       $commits[$i++] = $commit;

       $commit = new \StdClass();
       $commit->hash = '12345';
       $commit->message = 'feat: valid';
       $commits[$i++] = $commit;

       $commit = new \StdClass();
       $commit->hash = '12345';
       $commit->message = 'feat(main): valid';
       $commits[$i++] = $commit;

       $commit = new \StdClass();
       $commit->hash = '12345';
       $commit->message = 'foo(main): invalid';
       $commits[$i++] = $commit;

       $commit = new \StdClass();
       $commit->hash = '12345';
       $commit->message = 'foo: invalid';
       $commits[$i++] = $commit;

       return [
           [
               'commits' => [
                   $commits[0],
                   $commits[1],
                   $commits[2],
                   $commits[3],
                   $commits[4],
                   $commits[5],
               ],
               'expected' => [
                   $commits[0],
                   $commits[1],
                   $commits[2],
                   $commits[3],
               ]
           ]
       ];
   }

    /**
     * @dataProvider providerFilterCommits
     */
   public function testFilterCommits($commits, $expected) {
       $output = \ConventionalChangelog::filterCommits($commits);
       $this->array($output)->isEqualTo($expected);
   }

   public function providerBuildLog() {
       return [
           [

           ]
       ];
   }
}