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
use PluginFormcreatorIssue;
use RSSFeed;
use Ticket;
use User;

class PluginFormcreatorCommon extends CommonTestCase {
   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testGetTicketStatusForIssue':
            $this->login('glpi', 'glpi');
            $_SESSION['glpiset_default_tech'] = false;
            $this->beforeGetTicketStatusForIssue();
            break;
      }
   }

   public function testGetFormcreatorRequestTypeId() {
      $requestTypeId = \PluginFormcreatorCommon::getFormcreatorRequestTypeId();

      // The ID must be > 0 (aka found)
      $this->integer((integer) $requestTypeId)->isGreaterThan(0);
   }

   public function testIsNotificationEnabled() {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = '0';
      $output = \PluginFormcreatorCommon::isNotificationEnabled();
      $this->boolean($output)->isFalse();

      $CFG_GLPI['use_notifications'] = '1';
      $output = \PluginFormcreatorCommon::isNotificationEnabled();
      $this->boolean($output)->isTrue();
   }

   public function testSetNotification() {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = '1';
      \PluginFormcreatorCommon::setNotification(false);
      $this->integer((int) $CFG_GLPI['use_notifications'])->isEqualTo('0');

      \PluginFormcreatorCommon::setNotification(true);
      $this->integer((int) $CFG_GLPI['use_notifications'])->isEqualTo('1');
   }

   public function providerGetPictoFilename() {
      return [
         [
            'version' => '9.5.0',
            'expected' => 'font-awesome.php',
         ],
         [
            'version' => '9.9.0',
            'expected' => 'font-awesome.php',
         ]
      ];
   }

   /**
    * Undocumented function
    *
    * @dataProvider providerGetPictoFilename
    */
   public function testGetPictoFilename($version, $expected) {
      $output = \PluginFormcreatorCommon::getPictoFilename($version);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerPrepareBooleanKeywords() {
      return [
         [
            'input' => '',
            'expected' => '',
         ],
         [
            'input' => 'foo bar',
            'expected' => 'foo* bar*',
         ],
         [
            'input' => 'foo bar ',
            'expected' => 'foo* bar*',
         ],
         [
            'input' => 'foo***** bar* ',
            'expected' => 'foo* bar*',
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareBooleanKeywords
    */
   public function testPrepareBooleanKeywords($input, $expected) {
      $output = \PluginFormcreatorCommon::prepareBooleanKeywords($input);
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetCaptcha() {
      unset($_SESSION['plugin_formcreator']['captcha']);

      $captchaId = 'someRandomId';
      $output = \PluginFormcreatorCommon::getCaptcha($captchaId);
      $this->array($output)->hasKeys([
         'img',
         'phrase'
      ])->size->isEqualTo(2);
      $this->array($_SESSION['plugin_formcreator']['captcha'])
         ->hasKeys([$captchaId])
         ->size->isEqualTo(1);
      $this->array($_SESSION['plugin_formcreator']['captcha'][$captchaId]);
   }

   public function testCheckCaptcha() {
      unset($_SESSION['plugin_formcreator']['captcha']);

      $captchaId = 'someRandomId';
      $output = \PluginFormcreatorCommon::getCaptcha($captchaId);
      $challenge = $_SESSION['plugin_formcreator']['captcha'][$captchaId]['phrase'];
      $output = \PluginFormcreatorCommon::checkCaptcha($captchaId, $challenge);
      $this->boolean($output)->isTrue();
      $output = \PluginFormcreatorCommon::checkCaptcha($captchaId, $challenge . 'foo');
      $this->boolean($output)->isFalse();
   }

   public function testCleanOldCaptchas() {
      $output = \PluginFormcreatorCommon::getCaptcha('captcha1');
      $challenge = $_SESSION['plugin_formcreator']['captcha']['captcha1']['phrase'];
      sleep(2); // Wait 5 seconds
      $output = \PluginFormcreatorCommon::getCaptcha('captcha2');
      $output = \PluginFormcreatorCommon::checkCaptcha('captcha1', $challenge, 1);
      $this->boolean($output)->isFalse();
      $this->array($challenge = $_SESSION['plugin_formcreator']['captcha'])
         ->notHasKey('captcha1');
   }

   public function beforeGetTicketStatusForIssue() {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = '0';
   }

   public function providerGetTicketStatusForIssue() {
      $data = [];

      // Build test cases for 1st and last columns of tabhe in docblock of
      // PluginFormcreatorCommon::getTicketStatusForIssue (total 18 test cases)
      $expectedStatus = [
            \Ticket::INCOMING,
            \Ticket::ASSIGNED,
            \Ticket::PLANNED,
            \Ticket::WAITING,
            \Ticket::SOLVED,
            \Ticket::CLOSED,
      ];
      foreach ($expectedStatus as $ticketStatus) {
         // generate tickets with a validation
         $ticket = $this->getGlpiCoreItem(Ticket::class, [
            'name' => 'a ticket',
            'content' => "should be " . \Ticket::getStatus($ticketStatus),
            'status'  =>  \CommonITILObject::INCOMING,
            '_add_validation' => '0',
            'validatortype' => User::class,
            'users_id_validate' => [4], // Tech
         ]);
         // Creating a ticket directly with status solved or closed
         // will prevent credation of ticketvalidation item
         if ($ticketStatus > \CommonITILObject::INCOMING) {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
               '_users_id_assign' => 4,
            ]);
         } else {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
            ]);
         }
         $this->integer((int) $ticket->fields['status'])->isEqualTo($ticketStatus);
         $ticket->fields['global_validation'] = \CommonITILValidation::NONE;
         $dataSet = [
            'ticket' => $ticket,
            'expected' => $ticketStatus
         ];
         yield $dataSet;

         $ticket = $this->getGlpiCoreItem(Ticket::class, [
            'name' => 'a ticket',
            'content' => "should be " . \Ticket::getStatus($ticketStatus),
            'status'  =>  $ticketStatus,
         ]);
         $dataSet = [
            'ticket' => $ticket,
            'expected' => $ticketStatus
         ];
         $data["no validation, " . \Ticket::getStatus($ticketStatus)] = $dataSet;

         $ticket = $this->getGlpiCoreItem(Ticket::class, [
            'name' => 'a ticket',
            'content' => "should be " . \Ticket::getStatus($ticketStatus),
            'status'  =>  \CommonITILObject::INCOMING,
            '_add_validation' => '0',
            'validatortype' => User::class,
            'users_id_validate' => [4], // Tech
         ]);
         // Creating a ticket directly with status solved or closed
         // will prevent credation of ticketvalidation item
         if ($ticketStatus > \CommonITILObject::INCOMING) {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
               '_users_id_assign' => 4 // Tech,
            ]);
         } else {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
            ]);
         }
         $this->integer((int) $ticket->fields['status'])->isEqualTo($ticketStatus);
         $ticket->fields['global_validation'] = \CommonITILValidation::ACCEPTED;
         $dataSet = [
            'ticket' => $ticket,
            'expected' => $ticketStatus
         ];
         yield $dataSet;
      }

      // Build test cases for 2nd column of tabhe in docblock of
      // PluginFormcreatorCommon::getTicketStatusForIssue (total 4 test cases)
      $expectedStatus = [
         \Ticket::INCOMING,
         \Ticket::ASSIGNED,
         \Ticket::PLANNED,
         \Ticket::WAITING,
      ];
      foreach ($expectedStatus as $ticketStatus) {
         // generate tickets with a validation
         $ticket = $this->getGlpiCoreItem(Ticket::class, [
            'name' => 'a ticket',
            'content' => "should be " . \CommonITILValidation::getStatus(\CommonITILValidation::WAITING),
            'status'  =>  \CommonITILObject::INCOMING,
            '_add_validation' => '0',
            'validatortype' => User::class,
            'users_id_validate' => [4], // Tech
         ]);
         // Creating a ticket directly with status solved or closed
         // will prevent credation of ticketvalidation item
         if ($ticketStatus > \CommonITILObject::INCOMING) {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
               '_users_id_assign' =>  4, /* Tech */
            ]);
         } else {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
            ]);
         }
         $this->integer((int) $ticket->fields['status'])->isEqualTo($ticketStatus);
         $ticket->fields['global_validation'] = \CommonITILValidation::WAITING;
         $dataSet = [
            'ticket' => $ticket,
            'expected' => \PluginFormcreatorFormAnswer::STATUS_APPROVAL
         ];
         yield $dataSet;
      }

      $expectedStatus = [
         \Ticket::SOLVED,
         \Ticket::CLOSED,
      ];
      foreach ($expectedStatus as $ticketStatus) {
         $ticket = $this->getGlpiCoreItem(Ticket::class, [
            'name' => 'a ticket',
            'content' => "should be " . \Ticket::getStatus($ticketStatus),
            'status'  =>  \CommonITILObject::INCOMING,
            '_add_validation' => '0',
            'validatortype' => User::class,
            'users_id_validate' => [4], // Tech
         ]);
         // Creating a ticket directly with status solved or closed
         // will prevent credation of ticketvalidation item
         $ticket->update([
            'id' => $ticket->getID(),
            'status' => $ticketStatus,
            '_users_id_assign' => ($ticketStatus > \CommonITILObject::INCOMING) ? 4 /* Tech */ : 0,
         ]);
         $this->integer((int) $ticket->fields['status'])->isEqualTo($ticketStatus);
         $ticket->fields['global_validation'] = \CommonITILValidation::WAITING;
         $dataSet = [
            'ticket' => $ticket,
            'expected' => $ticketStatus
         ];
         yield $dataSet;
      }

      // Build test cases for 3rd column of tabhe in docblock of
      // PluginFormcreatorCommon::getTicketStatusForIssue (total 4 test cases)
      $expectedStatus = [
         \Ticket::INCOMING,
         \Ticket::ASSIGNED,
         \Ticket::PLANNED,
         \Ticket::WAITING,
      ];
      foreach ($expectedStatus as $ticketStatus) {
         // generate tickets with a validation
         $ticket = $this->getGlpiCoreItem(Ticket::class, [
            'name' => 'a ticket',
            'content' => "should be " . \CommonITILValidation::getStatus(\CommonITILValidation::REFUSED),
            'status'  =>  \CommonITILObject::INCOMING,
            '_add_validation' => '0',
            'validatortype' => User::class,
            'users_id_validate' => [4], // Tech
         ]);
         // Creating a ticket directly with status solved or closed
         // will prevent credation of ticketvalidation item
         if ($ticketStatus > \CommonITILObject::INCOMING) {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
               '_users_id_assign' => 4, /* Tech */
            ]);
         } else {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
            ]);
         }
         $this->integer((int) $ticket->fields['status'])->isEqualTo($ticketStatus);
         $ticket->fields['global_validation'] = \CommonITILValidation::REFUSED;
         $dataSet = [
            'ticket' => $ticket,
            'expected' => \PluginFormcreatorFormAnswer::STATUS_REFUSED
         ];
         yield $dataSet;
      }

      $expectedStatus = [
         \Ticket::SOLVED,
         \Ticket::CLOSED,
      ];
      foreach ($expectedStatus as $ticketStatus) {
         $ticket = $this->getGlpiCoreItem(Ticket::class, [
            'name' => 'a ticket',
            'content' => "should be " . \Ticket::getStatus($ticketStatus),
            'status'  =>  \CommonITILObject::INCOMING,
            '_add_validation' => '0',
            'validatortype' => User::class,
            'users_id_validate' => [4], // Tech
         ]);
         // Creating a ticket directly with status solved or closed
         // will prevent credation of ticketvalidation item
         if ($ticketStatus > \CommonITILObject::INCOMING) {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
               '_users_id_assign' => 4, /* Tech */
            ]);
         } else {
            $ticket->update([
               'id' => $ticket->getID(),
               'status' => $ticketStatus,
            ]);
         }
         $this->integer((int) $ticket->fields['status'])->isEqualTo($ticketStatus);
         $ticket->fields['global_validation'] = \CommonITILValidation::REFUSED;
         $dataSet = [
            'ticket' => $ticket,
            'expected' => $ticketStatus
         ];
         yield $dataSet;
      }
   }

   /**
    * @dataProvider providerGetTicketStatusForIssue
    *
    * @param \Ticket $ticket
    * @param array $expected
    * @return void
    */
   public function testGetTicketStatusForIssue($ticket, $expected) {
      $output = \PluginFormcreatorCommon::getTicketStatusForIssue($ticket);
      $this->integer((int) $output)->isEqualTo($expected);
   }

   public function testGetFormAnswer() {
      $output = \PluginFormcreatorCommon::getFormAnswer();
      $this->string($output->getType())->isEqualTo(\PluginFormcreatorFormAnswer::class);
   }

   public function testGetFormanswerItemtype() {
      $output = \PluginFormcreatorCommon::getFormanswerItemtype();
      $this->string($output)->isEqualTo(\PluginFormcreatorFormAnswer::class);
   }

   public function testGetInterface() {
      // test Public access
      \Session::destroy();
      $output = \PluginFormcreatorCommon::getInterface();
      $this->string($output)->isEqualTo('public');

      // test normal interface
      $this->login('glpi', 'glpi');
      $output = \PluginFormcreatorCommon::getInterface();
      $this->string($output)->isEqualTo('central');

      // test simplified interface
      $entityConfig = new \PluginFormcreatorEntityConfig();
      $entityConfig->getFromDbByCrit(['entities_id' => 0]);
      $entityConfig->update([
         'id' => $entityConfig->getID(),
         'replace_helpdesk' => '0',
      ]);
      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorCommon::getInterface();
      $this->string($output)->isEqualTo('self-service');

      // test service catalog
      $entityConfig = new \PluginFormcreatorEntityConfig();
      $entityConfig->getFromDbByCrit(['entities_id' => 0]);
      $entityConfig->update([
         'id' => $entityConfig->getId(),
         'replace_helpdesk' => \PluginFormcreatorEntityConfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG,
      ]);
      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorCommon::getInterface();
      $this->string($output)->isEqualTo('servicecatalog');

      $entityConfig = new \PluginFormcreatorEntityConfig();
      $entityConfig->update([
         'id' => $entityConfig->getId(),
         'replace_helpdesk' => \PluginFormcreatorEntityConfig::CONFIG_EXTENDED_SERVICE_CATALOG,
      ]);
      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorCommon::getInterface();
      $this->string($output)->isEqualTo('servicecatalog');
   }

   public function providerHookRedefineMenu() {
      global $DB;

      // Create an entity
      $this->login('glpi', 'glpi');
      $entity = new \Entity();
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => __FUNCTION__ . $this->getUniqueString(),
      ]);
      // Force creation of the entity config
      \PluginFormcreatorEntityConfig::getUsedConfig('replace_helpdesk', $entityId);

      // Use an not-self-service account
      $this->login('glpi', 'glpi');

      // Check the menu is left as is
      yield [
         'input'    => \Html::generateMenuSession(true),
         'expected' => \Html::generateMenuSession(true),
      ];

      // Check that simplified interface without service catalog) shows "Forms" menu item
      $entityConfig = new \PluginFormcreatorEntityConfig();
      $entityConfig->getFromDbByCrit(['entities_id' => $entityId]);
      $this->boolean($entityConfig->isNewItem())->isFalse();
      $entityConfig->update([
         'id' => $entityConfig->getID(),
         'replace_helpdesk' => '0',
      ]);
      $this->login('post-only', 'postonly');
      \Session::changeActiveEntities($entityId);

      yield [
         'input'    => \Html::generateHelpMenu(),
         'expected' => [
            'home' => [
               'default' => '/front/helpdesk.public.php',
               'title' => 'Home',
               'icon' => 'fas fa-home',
            ],
            'create_ticket' => [
               'default' => '/front/helpdesk.public.php?create_ticket=1',
               'title' => 'Create a ticket',
               'icon' => 'ti ti-plus',
            ],
            'seek_assistance' => [
               'default' => "plugins/formcreator/front/wizard.php",
               'title' => 'Forms',
               'icon' => 'fa-fw ti ti-headset',
            ],
            'tickets' => [
               'default' => '/front/ticket.php',
               'title' => 'Tickets',
               'icon' => 'ti ti-alert-circle',
               'content' => [
                  'ticket' => [
                     'links' => [
                        'search' => '/front/ticket.php',
                        'lists' => '',
                        'add' => '/front/helpdesk.public.php?create_ticket=1',
                     ],
                  ],
               ],
            ],
            'reservation' => [
               'default' => '/front/reservationitem.php',
               'title' => 'Reservations',
               'icon' => 'ti ti-calendar-event',
            ],
            'faq' => [
               'default' => '/front/helpdesk.faq.php',
               'title' => 'FAQ',
               'icon' => 'ti ti-lifebuoy',
            ],
         ],
      ];

      // Check that service catalog enabled does not impacts the menu for Central users
      $entityConfig = new \PluginFormcreatorEntityConfig();
      $entityConfig->getFromDbByCrit(['entities_id' => $entityId]);
      $this->boolean($entityConfig->isNewItem())->isFalse();
      $entityConfig->update([
         'id' => $entityConfig->getID(),
         'replace_helpdesk' => '1',
      ]);
      $this->login('glpi', 'glpi');
      \Session::changeActiveEntities($entityId);

      yield [
         'input'    => \Html::generateHelpMenu(),
         'expected' => \Html::generateHelpMenu(),
      ];

      $this->login('post-only', 'postonly');
      \Session::changeActiveEntities($entityId);
      $DB->truncate(\RSSFeed::getTable());
      $rssFeeds = (new \RssFeed())->find([1]);
      $this->integer(count($rssFeeds))->isEqualTo(0);
      yield [
         'input' => \Html::generateHelpMenu(),
         'expected' => [
            'seek_assistance' => [
               'default' => 'plugins/formcreator/front/wizard.php',
               'title' => 'Seek assistance',
               'icon' => 'fa-fw ti ti-headset',
            ],
            'my_assistance_requests' => [
               'default' => '/plugins/formcreator/front/issue.php',
               'title' => 'My requests for assistance',
               'icon' => 'fa-fw ti ti-list',
               'content' => [
                  PluginFormcreatorIssue::class => [
                     'title' => __('My requests for assistance', 'formcreator'),
                     'icon'  => 'fa-fw ti ti-list',
                     'links'   => [
                        'lists' => '',
                     ],
                  ],
               ],
            ],
            'reservation' => [
               'default' => '/front/reservationitem.php',
               'title' => 'Reservations',
               'icon' => 'ti ti-calendar-event',
            ],
         ]
      ];

      // Workaround HTTP request to the RSS url when using RSSFeed->add()
      $DB->insert(RSSFeed::getTable(), [
         'name' => 'RSS feed',
         'url' => 'https://localhost/feed/',
         'is_active' => 1,
      ]);
      $rssFeed = new RSSFeed();
      $rssFeed->getFromDB($DB->insertId());

      $this->boolean($rssFeed->isNewItem())->isFalse();
      $entityRssFeed = new \Entity_RSSFeed();
      $entityRssFeed->add([
         'entities_id' => $entityId,
         'rssfeeds_id' => $rssFeed->getID()
      ]);
      $this->boolean($entityRssFeed->isNewItem())->isFalse();
      yield [
         'input' => \Html::generateHelpMenu(),
         'expected' => [
            'seek_assistance' =>
            [
              'default' => 'plugins/formcreator/front/wizard.php',
              'title' => 'Seek assistance',
              'icon' => 'fa-fw ti ti-headset',
            ],
            'my_assistance_requests' =>
            [
              'default' => '/plugins/formcreator/front/issue.php',
              'title' => 'My requests for assistance',
              'icon' => 'fa-fw ti ti-list',
              'content' => [
                  PluginFormcreatorIssue::class => [
                     'title' => __('My requests for assistance', 'formcreator'),
                     'icon'  => 'fa-fw ti ti-list',
                     'links'   => [
                        'lists' => '',
                     ],
                  ],
               ],
            ],
            'reservation' =>
            [
              'default' => '/front/reservationitem.php',
              'title' => 'Reservations',
              'icon' => 'ti ti-calendar-event',
            ],
            'feeds' =>
            [
              'default' => 'plugins/formcreator/front/wizardfeeds.php',
              'title' => 'Consult feeds',
              'icon' => 'fa-fw ti ti-rss',
            ],
         ]
      ];
   }

   /**
    * @dataProvider providerHookRedefineMenu
    */
   public function testHookRedefineMenu($input, $expected) {
      $output = \PluginFormcreatorCommon::hookRedefineMenu($input);
      $this->array($output)->isIdenticalTo($expected);
   }
}
