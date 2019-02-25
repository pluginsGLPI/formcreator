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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorTargetTicket extends PluginFormcreatorTargetBase
{
   public static function getTypeName($nb = 1) {
      return _n('Target ticket', 'Target tickets', $nb, 'formcreator');
   }

   protected function getItem_User() {
      return new Ticket_User();
   }

   protected function getItem_Group() {
      return new Group_Ticket();
   }

   protected function getItem_Supplier() {
      return new Supplier_Ticket();
   }

   protected function getItem_Item() {
      return new Item_Ticket();
   }

   protected function getTargetItemtypeName() {
      return Ticket::class;
   }

   public function getItem_Actor() {
      return new PluginFormcreatorTargetTicket_Actor();
   }

   protected function getCategoryFilter() {
      // TODO remove if and the next raw query when 9.3/bf compat will no be needed anymore
      if (version_compare(GLPI_VERSION, "9.4", '>=')) {
         return [
            'OR' => [
               'is_request'  => 1,
               'is_incident' => 1
            ]
         ];
      }
      return "`is_request` = '1' OR `is_incident` = '1'";
   }

   static function getEnumAssociateRule() {
      return [
         'none'      => __('none', 'formcreator'),
         'specific'  => __('Specific asset', 'formcreator'),
         'answer'    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   /**
    * Show the Form for the adminsitrator to edit in the config page
    *
    * @param  Array  $options Optional options
    *
    * @return NULL         Nothing, just display the form
    */
   public function showForm($options = []) {
      global $CFG_GLPI, $DB;

      $rand = mt_rand();

      $target = $DB->request([
         'FROM'    => PluginFormcreatorTarget::getTable(),
         'WHERE'   => [
            'itemtype' => __CLASS__,
            'items_id' => (int) $this->getID()
         ]
      ])->next();

      $form = new PluginFormcreatorForm();
      $form->getFromDB($target['plugin_formcreator_forms_id']);

      echo '<div class="center" style="width: 950px; margin: 0 auto;">';
      echo '<form name="form_target" method="post" action="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php">';

      // General information: name
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="2">' . __('Edit a destination', 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td width="15%"><strong>' . __('Name') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td width="85%"><input type="text" name="name" style="width:704px;" value="' . $target['name'] . '" /></td>';
      echo '</tr>';

      echo '</table>';

      // Ticket information: title, template...
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="4">' . _n('Target ticket', 'Target tickets', 1, 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Ticket title', 'formcreator') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td colspan="3"><input type="text" name="title" style="width:704px;" value="' . $this->fields['name'] . '"/></td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Description') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="content" style="width:700px;" rows="15">' . $this->fields['content'] . '</textarea>';
      if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI["use_rich_text"]) {
         Html::initEditorSystem('content');
      }
      echo '</td>';
      echo '</tr>';

      $rand = mt_rand();
      $this->showDestinationEntitySetings($rand);

      echo '<tr class="line1">';
      $this->showTemplateSettings($rand);
      $this->showDueDateSettings($form, $rand);
      echo '</tr>';

      // -------------------------------------------------------------------------------------------
      //  associated elements of the target
      // -------------------------------------------------------------------------------------------
      $this->showAssociateSettings($rand);

      // -------------------------------------------------------------------------------------------
      //  category of the target
      // -------------------------------------------------------------------------------------------
      $this->showCategorySettings($form, $rand);

      // -------------------------------------------------------------------------------------------
      // Urgency selection
      // -------------------------------------------------------------------------------------------
      $this->showUrgencySettings($form, $rand);

      // -------------------------------------------------------------------------------------------
      // Location selection
      // -------------------------------------------------------------------------------------------
      $this->showLocationSettings($form, $rand);
      // -------------------------------------------------------------------------------------------
      //  Tags
      // -------------------------------------------------------------------------------------------
      $this->showPluginTagsSettings($form, $rand);

      // -------------------------------------------------------------------------------------------
      //  Composite tickets
      // -------------------------------------------------------------------------------------------
      $this->showCompositeTicketSettings($rand);

      // -------------------------------------------------------------------------------------------
      //  Validation as ticket followup
      // -------------------------------------------------------------------------------------------
      if ($form->fields['validation_required']) {
         echo '<tr class="line1">';
         echo '<td colspan="4">';
         echo '<input type="hidden" name="validation_followup" value="0" />';
         echo '<input type="checkbox" name="validation_followup" id="validation_followup" value="1" ';
         if (!isset($this->fields['validation_followup']) || ($this->fields['validation_followup'] == 1)) {
            echo ' checked="checked"';
         }
         echo '/>';
         echo ' <label for="validation_followup">';
         echo __('Add validation message as first ticket followup', 'formcreator');
         echo '</label>';
         echo '</td>';
         echo '</tr>';
      }

      echo '</table>';

      // Buttons
      echo '<table class="tab_cadre_fixe">';

      echo '<tr class="line1">';
      echo '<td colspan="5" class="center">';
      echo '<input type="reset" name="reset" class="submit_button" value="' . __('Cancel', 'formcreator') . '"
               onclick="document.location = \'form.form.php?id=' . $target['plugin_formcreator_forms_id'] . '\'" /> &nbsp; ';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="submit" name="update" class="submit_button" value="' . __('Save') . '" />';
      echo '</td>';
      echo '</tr>';

      echo '</table>';
      Html::closeForm();

      $this->showActorsSettings();

      $this->showTagsList();
      echo '</div>';
   }

   /**
    * Show settings to handle composite tickets
    * @param string $rand
    */
   protected function showCompositeTicketSettings($rand) {
      global $DB;

      echo '<tr class="line1">';
      echo '<td>';
      echo __('Link to an other ticket', 'formcreator');
      echo "<span class='fa fa-plus pointer' onClick=\"".Html::jsShow("plugin_formcreator_linked_items$rand")."\"
             title=\"".__s('Add')."\"><span class='sr-only'>" . __s('Add') . "</span></span>";

      echo '</td>';
      echo '<td colspan="3">';
      echo '<div style="display: none" id="plugin_formcreator_linked_items' . $rand . '">';
      Ticket_Ticket::dropdownLinks('_linktype');
      $elements = [
         'PluginFormcreatorTargetTicket'  => __('An other destination of this form', 'formcreator'),
         'Ticket'                         => __('An existing ticket', 'formcreator'),
      ];
      Dropdown::showFromArray('_link_itemtype', $elements, [
         'on_change' => 'updateCompositePeerType()',
         'rand'      => $rand,
      ]);
      $script = <<<EOS
      function updateCompositePeerType() {
         if ($('#dropdown__link_itemtype$rand').val() == 'Ticket') {
            $('#plugin_formcreator_link_ticket').show();
            $('#plugin_formcreator_link_target').hide();
         } else {
            $('#plugin_formcreator_link_ticket').hide();
            $('#plugin_formcreator_link_target').show();
         }
      }
      updateCompositePeerType();
EOS;
      echo Html::scriptBlock($script);
      // get already linked items
      $targetTicketId = $this->getID();
      $rows = $DB->request([
         'FROM'   => PluginFormcreatorItem_TargetTicket::getTable(),
         'WHERE'  => [
            'plugin_formcreator_targettickets_id' => $targetTicketId
         ]
      ]);
      $excludedTargetTicketsIds = [];
      $excludedTicketIds = [];
      foreach ($rows as $row) {
         switch ($row['itemtype']) {
            case PluginFormcreatorTargetTicket::getType():
               $excludedTargetTicketsIds[] = $row['items_id'];
               break;

            case Ticket::getType():
               $excludedTicketIds[] = $row['items_id'];
               break;
         }
      }

      echo '<span id="plugin_formcreator_link_ticket">';
      $linkparam = [
         'name'        => '_link_tickets_id',
         'used'        => $excludedTicketIds,
         'displaywith' => ['id'],
         'display'     => false
      ];
      echo Ticket::dropdown($linkparam);
      echo '</span>';

      // dropdown of target tickets
      echo '<span id="plugin_formcreator_link_target">';
      $excludedTargetTicketsIds[] = $this->getID();
      $condition = "`id` IN (
         SELECT `items_id` FROM `glpi_plugin_formcreator_targets` AS `t1`
         WHERE `plugin_formcreator_forms_id` = (
            SELECT `plugin_formcreator_forms_id` FROM `glpi_plugin_formcreator_targets` AS `t2`
            WHERE `t2`.`itemtype` = 'PluginFormcreatorTargetTicket' AND `t2`.`items_id` = '$targetTicketId'
         )
         AND `t1`.`itemtype` = 'PluginFormcreatorTargetTicket'
      )";
      // TODO remove if and the above raw query when 9.3/bf compat will no be needed anymore
      if (version_compare(GLPI_VERSION, "9.4", '>=')) {
         $condition = [
            'id' => new QuerySubQuery([
               'SELECT' => ['items_id'],
               'FROM'   => 'glpi_plugin_formcreator_targets AS t1',
               'WHERE'  => [
                  't1.itemtype'                 => 'PluginFormcreatorTargetTicket',
                  'plugin_formcreator_forms_id' => new QuerySubQuery([
                     'SELECT' => ['plugin_formcreator_forms_id'],
                     'FROM'   => 'glpi_plugin_formcreator_targets AS t2',
                     'WHERE'  => [
                        't2.itemtype' => 'PluginFormcreatorTargetTicket',
                        't2.items_id' => $targetTicketId
                     ]
                  ]),
               ]
            ]),
         ];
      }
      echo PluginFormcreatorTargetTicket::dropdown([
         'name'      => '_link_targettickets_id',
         'rand'      => $rand,
         'display'   => false,
         'used'      => $excludedTargetTicketsIds,
         'condition' => $condition,
      ]);
      echo '</span>';
      echo '</div>';

      // show already linked items
      foreach ($rows as $row) {
         $icons = '&nbsp;'.Html::getSimpleForm(PluginFormcreatorItem_TargetTicket::getFormURL(), 'purge',
               _x('button', 'Delete permanently'),
               ['id'                => $row['id']],
               'fa-times-circle');
               $itemtype = $row['itemtype'];
               $item = new $itemtype();
               $item->getFromDB($row['items_id']);
         switch ($itemtype) {
            case Ticket::getType():
               //TODO: when merge of https://github.com/glpi-project/glpi/pull/2840 (this is a BC)
               //echo Ticket_Ticket::getLinkName($row['link']);
               echo PluginFormcreatorCommon::getLinkName($row['link']);
               echo ' ';
               echo $itemtype::getTypeName();
               echo ' ';
               echo '<span style="font-weight:bold">' . $item->getField('name') . '</span>';
               echo ' ';
               echo $icons;
               break;

            case PluginFormcreatorTargetTicket::getType():
               // TODO: when merge of https://github.com/glpi-project/glpi/pull/2840 (this is a BC)
               //echo Ticket_Ticket::getLinkName($row['link']);
               echo PluginFormcreatorCommon::getLinkName($row['link']);
               echo ' ';
               echo $itemtype::getTypeName();
               echo ' ';
               echo '<span style="font-weight:bold">' . $item->getField('name') . '</span>';
               echo ' ';
               echo $icons;
               break;
         }
         echo '<br>';
      }

      echo '</td>';
      echo '</tr>';
   }

   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   /**
    * Prepare input datas for updating the target ticket
    *
    * @param array $input datas used to add the item
    *
    * @return array the modified $input array
   */
   public function prepareInputForUpdate($input) {
      global $CFG_GLPI;

      // Control fields values :
      if (!isset($input['_skip_checks'])
          || !$input['_skip_checks']) {
         // - name is required
         if (empty($input['title'])) {
            Session::addMessageAfterRedirect(__('The title cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }

         // - comment is required
         if (strlen($input['content']) < 1) {
            Session::addMessageAfterRedirect(__('The description cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }

         if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text']) {
            $input['content'] = Html::entity_decode_deep($input['content']);
         }

         switch ($input['destination_entity']) {
            case 'specific' :
               $input['destination_entity_value'] = $input['_destination_entity_value_specific'];
               break;
            case 'user' :
               $input['destination_entity_value'] = $input['_destination_entity_value_user'];
               break;
            case 'entity' :
               $input['destination_entity_value'] = $input['_destination_entity_value_entity'];
               break;
            default :
               $input['destination_entity_value'] = 'NULL';
               break;
         }

         switch ($input['urgency_rule']) {
            case 'answer':
               $input['urgency_question'] = $input['_urgency_question'];
               break;
            case 'specific':
               $input['urgency_question'] = $input['_urgency_specific'];
               break;
            default:
               $input['urgency_question'] = '0';
         }

         switch ($input['category_rule']) {
            case 'answer':
               $input['category_question'] = $input['_category_question'];
               break;
            case 'specific':
               $input['category_question'] = $input['_category_specific'];
               break;
            default:
               $input['category_question'] = '0';
         }

         switch ($input['location_rule']) {
            case 'answer':
               $input['location_question'] = $input['_location_question'];
               break;
            case 'specific':
               $input['location_question'] = $input['_location_specific'];
               break;
            default:
               $input['location_question'] = '0';
         }

         $plugin = new Plugin();
         if ($plugin->isInstalled('tag') && $plugin->isActivated('tag')) {
            $input['tag_questions'] = (!empty($input['_tag_questions']))
                                       ? implode(',', $input['_tag_questions'])
                                       : '';
            $input['tag_specifics'] = (!empty($input['_tag_specifics']))
                                       ? implode(',', $input['_tag_specifics'])
                                       : '';
         }
      }

      if (isset($input['_linktype']) && isset($input['_link_itemtype'])) {
         $input = $this->saveLinkedItem($input);
      }

      if (isset($input['items_id'])) {
         $input = $this->saveAssociatedItems($input);
      }

      return parent::prepareInputForUpdate($input);
   }

   /**
    * Save links to other items for composite tickets
    * @param array $input form data
    *
    * @return array
    */
   private function saveLinkedItem($input) {
      // Check link type is valid
      $linktype = (int) $input['_linktype'];
      if ($linktype < Ticket_Ticket::LINK_TO || $linktype > Ticket_Ticket::PARENT_OF) {
         Session::addMessageAfterRedirect(__('Invalid link type', 'formcreator'), false, ERROR);
         return [];
      }

      // Check itemtype
      $itemtype = $input['_link_itemtype'];
      switch ($itemtype) {
         case Ticket::getType():
            $itemId = (int) $input['_link_tickets_id'];
            break;

         case PluginFormcreatorTargetTicket::getType():
            $itemId = (int) $input['_link_targettickets_id'];
            break;

         default:
            Session::addMessageAfterRedirect(__('Invalid linked item type', 'formcreator'), false, ERROR);
            return [];
      }
      $item = new $itemtype();

      // Check an id was provided (if not, then the fields were not populated)
      if ($item::isNewID($itemId)) {
         // nothing to do
         return $input;
      }

      // Check item exists
      if (!$item->getFromDB($itemId)) {
         Session::addMessageAfterRedirect(__('Linked item does not exists', 'formcreator'), false, ERROR);
         return [];
      }

      $item_targetTicket = new PluginFormcreatorItem_TargetTicket();
      $item_targetTicket->add([
         'plugin_formcreator_targettickets_id'  => $this->getID(),
         'link'                                 => $linktype,
         'itemtype'                             => $itemtype,
         'items_id'                             => $itemId,
      ]);

      if ($item_targetTicket->isNewItem()) {
         Session::addMessageAfterRedirect(__('Failed to link the item', 'formcreator'), false, ERROR);
      }

      return $input;
   }

   public function pre_deleteItem() {
      // delete actors related to this instance
      $targetTicketActor = new PluginFormcreatorTargetTicket_Actor();
      $success = $targetTicketActor->deleteByCriteria([
         'plugin_formcreator_targettickets_id' => $this->getID(),
      ]);

      // delete targets linked to this instance
      $item_targetTicket = new PluginFormcreatorItem_TargetTicket();
      $success |= $item_targetTicket->deleteByCriteria([
         'plugin_formcreator_targettickets_id'  => $this->getID(),
      ]);
      $success |= $item_targetTicket->deleteByCriteria([
         'itemtype'  => $this->getID(),
         'items_id'  => $this->getID(),
      ]);

      return $success;
   }

   /**
    * Save form data to the target
    *
    * @param  PluginFormcreatorFormAnswer $formanswer    Answers previously saved
    *
    * @return Ticket|false Generated ticket if success, null otherwise
    */
   public function save(PluginFormcreatorFormAnswer $formanswer) {
      global $DB, $CFG_GLPI;

      // Prepare actors structures for creation of the ticket
      $this->requesters = [
         '_users_id_requester'         => [],
         '_users_id_requester_notif'   => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ],
      ];
      $this->observers = [
         '_users_id_observer'          => [],
         '_users_id_observer_notif'    => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ],
      ];
      $this->assigned = [
         '_users_id_assign'            => [],
         '_users_id_assign_notif'      => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ],
      ];

      $this->assignedSuppliers = [
         '_suppliers_id_assign'        => [],
         '_suppliers_id_assign_notif'  => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ]
      ];

      $this->requesterGroups = [
         '_groups_id_requester'        => [],
      ];

      $this->observerGroups = [
         '_groups_id_observer'         => [],
      ];

      $this->assignedGroups = [
         '_groups_id_assign'           => [],
      ];

      $data = Ticket::getDefaultValues();
      $ticket  = new Ticket();
      $form    = $formanswer->getForm();
      $answer  = new PluginFormcreatorAnswer();

      $data['requesttypes_id'] = PluginFormcreatorCommon::getFormcreatorRequestTypeId();

      // Get predefined Fields
      $ttp                  = new TicketTemplatePredefinedField();
      $predefined_fields    = $ttp->getPredefinedFields($this->fields['tickettemplates_id'], true);

      if (isset($predefined_fields['_users_id_requester'])) {
         $this->addActor('requester', $predefined_fields['_users_id_requester'], true);
         unset($predefined_fields['_users_id_requester']);
      }
      if (isset($predefined_fields['_users_id_observer'])) {
         $this->addActor('observer', $predefined_fields['_users_id_observer'], true);
         unset($predefined_fields['_users_id_observer']);
      }
      if (isset($predefined_fields['_users_id_assign'])) {
         $this->addActor('assigned', $predefined_fields['_users_id_assign'], true);
         unset($predefined_fields['_users_id_assign']);
      }

      if (isset($predefined_fields['_groups_id_requester'])) {
         $this->addGroupActor('assigned', $predefined_fields['_groups_id_requester']);
         unset($predefined_fields['_groups_id_requester']);
      }
      if (isset($predefined_fields['_groups_id_observer'])) {
         $this->addGroupActor('observer', $predefined_fields['_groups_id_observer']);
         unset($predefined_fields['_groups_id_observer']);
      }
      if (isset($predefined_fields['_groups_id_assign'])) {
         $this->addGroupActor('assigned', $predefined_fields['_groups_id_assign']);
         unset($predefined_fields['_groups_id_assign']);
      }

      $data = array_merge($data, $predefined_fields);

      // Parse data
      // TODO: generate instances of all answers of the form and use them for the fullform computation
      //       and the computation from a admin-defined target ticket template
      $richText = version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text'];
      $data['name'] = $this->prepareTemplate(
         $this->fields['name'],
         $formanswer,
         false
      );
      $data['name'] = Toolbox::addslashes_deep($data['name']);
      $data['name'] = $formanswer->parseTags($data['name']);

      $data['content'] = $this->prepareTemplate(
         $this->fields['content'],
         $formanswer,
         $richText
      );

      $data['content'] = Toolbox::addslashes_deep($data['content']);
      $data['content'] = $formanswer->parseTags($data['content'], $richText);

      $data['_users_id_recipient'] = $_SESSION['glpiID'];
      $data['_tickettemplates_id'] = $this->fields['tickettemplates_id'];

      $this->prepareActors($form, $formanswer);

      if (count($this->requesters['_users_id_requester']) == 0) {
         $this->addActor('requester', $formanswer->fields['requester_id'], true);
         $requesters_id = $formanswer->fields['requester_id'];
      } else if (count($this->requesters['_users_id_requester']) >= 1) {
         if ($this->requesters['_users_id_requester'][0] == 0) {
            $this->addActor('requester', $formanswer->fields['requester_id'], true);
            $requesters_id = $formanswer->fields['requester_id'];
         } else {
            $requesters_id = $this->requesters['_users_id_requester'][0];
         }

         // If only one requester, revert array of requesters into a scalar
         // This is needed to process business rule affecting location of a ticket with the location of the user
         if (count($this->requesters['_users_id_requester']) == 1) {
            $this->requesters['_users_id_requester'] = array_pop($this->requesters['_users_id_requester']);
         }
      }

      $data = $this->setTargetEntity($data, $formanswer, $requesters_id);
      $data = $this->setTargetDueDate($data, $formanswer);
      $data = $this->setTargetUrgency($data, $formanswer);
      $data = $this->setTargetCategory($data, $formanswer);
      $data = $this->setTargetLocation($data, $formanswer);
      $data = $this->setTargetAssociatedItem($data, $formanswer);

      // There is always at least one requester
      $data = $this->requesters + $data;

      // Overwrite default actors only if populated
      if (count($this->observers['_users_id_observer']) > 0) {
         $data = $this->observers + $data;
      }
      if (count($this->assigned['_users_id_assign']) > 0) {
         $data = $this->assigned + $data;
      }
      if (count($this->assignedSuppliers['_suppliers_id_assign']) > 0) {
         $data = $this->assignedSuppliers + $data;
      }
      if (count($this->requesterGroups['_groups_id_requester']) > 0) {
         $data = $this->requesterGroups + $data;
      }
      if (count($this->observerGroups['_groups_id_observer']) > 0) {
         $data = $this->observerGroups + $data;
      }
      if (count($this->assignedGroups['_groups_id_assign']) > 0) {
         $data = $this->assignedGroups + $data;
      }

      // Create the target ticket
      if (!$ticketID = $ticket->add($data)) {
         return null;
      }

      $this->saveTags($formanswer, $ticketID);

      // Add link between Ticket and FormAnswer
      $itemlink = $this->getItem_Item();
      $itemlink->add([
         'itemtype'   => PluginFormcreatorFormAnswer::class,
         'items_id'   => $formanswer->fields['id'],
         'tickets_id' => $ticketID,
      ]);

      $this->attachDocument($formanswer->getID(), Ticket::class, $ticketID);

      // Attach validation message as first ticket followup if validation is required and
      // if is set in ticket target configuration
      if ($form->fields['validation_required'] && $this->fields['validation_followup']) {
         $message = addslashes(__('Your form has been accepted by the validator', 'formcreator'));
         if (!empty($formanswer->fields['comment'])) {
            $message.= "\n".addslashes($formanswer->fields['comment']);
         }

         // Disable email notification when adding a followup
         $use_mailing = PluginFormcreatorCommon::isNotificationEnabled();
         PluginFormcreatorCommon::setNotification(false);

         $followUpInput = [
           'date'                            => $_SESSION['glpi_currenttime'],
           'users_id'                        => Session::getLoginUserID(),
           'content'                         => $message,
           '_do_not_compute_takeintoaccount' => true
         ];
         if (class_exists(ITILFollowup::class)) {
            // GLPI 9.4+
            $followUpInput += [
               'items_id' => $ticketID,
               'itemtype' => Ticket::class,
            ];
            $ticketFollowup = new ITILFollowup();
            $ticketFollowup->add($followUpInput);
         } else {
            // GLPI < 9.4
            $followUpInput += [
               'tickets_id' => $ticketID,
            ];
            $ticketFollowup = new TicketFollowup();
            $ticketFollowup->add($followUpInput);
         }

         // Restore mail notification setting
         PluginFormcreatorCommon::setNotification($use_mailing);
      }

      return $ticket;
   }

   protected function setTargetUrgency($data, $formanswer) {
      global $DB;

      switch ($this->fields['urgency_rule']) {
         case 'answer':
            $urgency = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['urgency_question']
               ]
            ])->next();
            $urgency = $urgency['answer'];
            break;
         case 'specific':
            $urgency = $this->fields['urgency_question'];
            break;
         default:
            $urgency = null;
      }
      if (!is_null($urgency)) {
         $data['urgency'] = $urgency;
      }

      return $data;
   }

   protected function setTargetLocation($data, $formanswer) {
      global $DB;

      switch ($this->fields['location_rule']) {
         case 'answer':
            $location = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['location_question']
               ]
            ])->next();
            $location = $location['answer'];
            break;
         case 'specific':
            $location = $this->fields['location_question'];
            break;
         default:
            $location = null;
      }
      if (!is_null($location)) {
         $data['locations_id'] = $location;
      }

      return $data;
   }

   protected function showAssociateSettings($rand) {
      global $DB, $CFG_GLPI;

      echo '<tr class="line0">';
      echo '<td width="15%">' . __('Associated elements') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('associate_rule', static::getEnumAssociateRule(), [
         'value'                 => $this->fields['associate_rule'],
         'on_change'             => 'change_associate()',
         'rand'                  => $rand
      ]);
      $script = <<<JAVASCRIPT
         function change_associate() {
            $('#associate_specific_title').hide();
            $('#associate_specific_value').hide();
            $('#associate_question_title').hide();
            $('#associate_question_value').hide();

            switch($('#dropdown_associate_rule$rand').val()) {
               case 'answer' :
                  $('#associate_question_title').show();
                  $('#associate_question_value').show();
                  break;
               case 'specific':
                  $('#associate_specific_title').show();
                  $('#associate_specific_value').show();
                  break;
            }
         }
         change_associate();
JAVASCRIPT;
      echo Html::scriptBlock($script);
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="associate_question_title" style="display: none">' . __('Question', 'formcreator') . '</span>';
      echo '<span id="associate_specific_title" style="display: none">' . __('Item ', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';

      echo '<div id="associate_specific_value" style="display: none">';
      $options = json_decode($this->fields['associate_question'], true);
      if (!is_array($options)) {
         $options = [];
      }
      $options['_canupdate'] = true;
      $targetTicketFk = self::getForeignKeyField();
      $itemTargetTicket = new PluginFormcreatorItem_TargetTicket();
      $targetTicketId = $this->getID();
      $exclude = implode("', '", [PluginFormcreatorTargetTicket::class, Ticket::class]);
      $rows = $itemTargetTicket->find("`$targetTicketFk` = '$targetTicketId'
         AND `itemtype` NOT IN ('$exclude')");
      foreach ($rows as $row) {
         $options['items_id'][$row['itemtype']][$row['id']] = $row['id'];
      }
      Item_Ticket::itemAddForm(new Ticket(), $options);
      echo '</div>';
      echo '<div id="associate_question_value" style="display: none">';
      // select all user questions (GLPI Object)
      $ticketTypes = implode("' ,'", $CFG_GLPI["ticket_types"]);
      $query2 = "SELECT q.id, q.name, q.values
                FROM glpi_plugin_formcreator_questions q
                INNER JOIN glpi_plugin_formcreator_sections s
                  ON s.id = q.plugin_formcreator_sections_id
                INNER JOIN glpi_plugin_formcreator_targets t
                  ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = ".$this->getID()."
                  AND q.fieldtype = 'glpiselect'
                  AND q.values IN ('$ticketTypes')";
      $result2 = $DB->query($query2);
      $users_questions = [];
      while ($question = $DB->fetch_array($result2)) {
         $users_questions[$question['id']] = $question['name'];
      }
      Dropdown::showFromArray('_associate_question', $users_questions, [
         'value' => $this->fields['associate_question'],
      ]);
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function setTargetAssociatedItem($data, $formanswer) {
      switch ($this->fields['associate_rule']) {
         case 'answer':
            // find the itemtype of the associated item
            $associateQuestion = $this->fields['associate_question'];
            $question = new PluginFormcreatorQuestion();
            $question->getFromDB($associateQuestion);
            $itemtype = $question->fields['values'];

            // find the id of the associated item
            $answer  = new PluginFormcreatorAnswer();
            $formAnswerId = $formanswer->fields['id'];
            $found  = $answer->find("`plugin_formcreator_forms_answers_id` = '$formAnswerId'
                  AND `plugin_formcreator_questions_id` = '$associateQuestion'");
            $associate = array_shift($found);
            $itemId = $associate['answer'];

            // associate the item if it exists
            if (!class_exists($itemtype)) {
               return $data;
            }
            $item = new $itemtype();
            if ($item->getFromDB($itemId)) {
               $data['items_id'] = [$itemtype => [$itemId => $itemId]];
            }
            break;

         case 'specific':
            $targetTicketFk = self::getForeignKeyField();
            $itemTargetTicket = new PluginFormcreatorItem_TargetTicket();
            $targetTicketId = $this->getID();
            $exclude = implode("', '", [PluginFormcreatorTargetTicket::class, Ticket::class]);
            $rows = $itemTargetTicket->find("`$targetTicketFk` = '$targetTicketId'
               AND `itemtype` NOT IN ('$exclude')");
            foreach ($rows as $row) {
               $data['items_id'] = [$row['itemtype'] => [$row['items_id'] => $row['items_id']]];
            }
            break;
      }

      return $data;
   }

   /**
    * Import a form's targetticket into the db
    * @see PluginFormcreatorTarget::import
    *
    * @param  integer $targetitems_id  current id
    * @param  array   $target_data the targetticket data (match the targetticket table)
    * @return integer the targetticket's id
    */
   public static function import($targetitems_id = 0, $target_data = []) {
      global $DB;

      $item = new self;

      $target_data['_skip_checks'] = true;
      $target_data['id'] = $targetitems_id;

      // convert question uuid into id
      $targetTicket = new PluginFormcreatorTargetTicket();
      $targetTicket->getFromDB($targetitems_id);

      $section = new PluginFormcreatorSection();
      $foundSections = $section->getSectionsFromForm($targetTicket->getForm()->getID());
      $tab_section = [];
      foreach ($foundSections as $section) {
         $tab_section[] = $section->getID();
      }

      if (!empty($tab_section)) {
         $sectionFk = PluginFormcreatorSection::getForeignKeyField();
         $rows = $DB->request([
            'SELECT' => ['id', 'uuid'],
            'FROM'   => PluginFormcreatorQuestion::getTable(),
            'WHERE'  => [
               $sectionFk => $tab_section
            ],
            'ORDER'  => 'order ASC'
         ]);
         foreach ($rows as $question_line) {
            $id    = $question_line['id'];
            $uuid  = $question_line['uuid'];

            $content = $target_data['title'];
            $content = str_replace("##question_$uuid##", "##question_$id##", $content);
            $content = str_replace("##answer_$uuid##", "##answer_$id##", $content);
            $target_data['title'] = $content;

            $content = $target_data['content'];
            $content = str_replace("##question_$uuid##", "##question_$id##", $content);
            $content = str_replace("##answer_$uuid##", "##answer_$id##", $content);
            $target_data['content'] = $content;
         }
      }

      // escape text fields
      foreach (['title', 'content'] as $key) {
         $target_data[$key] = $DB->escape($target_data[$key]);
      }

      // update target ticket
      $item->update($target_data);

      if ($targetitems_id) {
         if (isset($target_data['_actors'])) {
            foreach ($target_data['_actors'] as $actor) {
               PluginFormcreatorTargetTicket_Actor::import($targetitems_id, $actor);
            }
         }
         if (isset($target_data['_ticket_relations'])) {
            foreach ($target_data['_ticket_relations'] as $ticketLink) {
               PluginFormcreatorItem_TargetTicket::import($targetitems_id, $ticketLink);
            }
         }
      }

      return $targetitems_id;
   }

   /**
    * Export in an array all the data of the current instanciated targetticket
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      global $DB;

      if (!$this->getID()) {
         return false;
      }

      $target_data = $this->fields;

      // replace dropdown ids
      if ($target_data['tickettemplates_id'] > 0) {
         $target_data['_tickettemplate']
            = Dropdown::getDropdownName('glpi_tickettemplates',
                                        $target_data['tickettemplates_id']);
      }

      // convert questions ID into uuid for ticket description
      $found_section = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => PluginFormcreatorSection::getTable(),
         'WHERE'  => [
            'plugin_formcreator_forms_id' => $this->getForm()->getID()
         ],
         'ORDER'  => 'order ASC'
      ]);
      $tab_section = [];
      foreach ($found_section as $section_item) {
         $tab_section[] = $section_item['id'];
      }

      if (!empty($tab_section)) {
         $rows = $DB->request([
            'SELECT' => ['id', 'uuid'],
            'FROM'   => PluginFormcreatorQuestion::getTable(),
            'WHERE'  => [
               'plugin_formcreator_sections_id' => $tab_section
            ],
            'ORDER' => 'order ASC'
         ]);
         foreach ($rows as $question_line) {
            $id    = $question_line['id'];
            $uuid  = $question_line['uuid'];

            $content = $target_data['name'];
            $content = str_replace("##question_$id##", "##question_$uuid##", $content);
            $content = str_replace("##answer_$id##", "##answer_$uuid##", $content);
            $target_data['name'] = $content;

            $content = $target_data['content'];
            $content = str_replace("##question_$id##", "##question_$uuid##", $content);
            $content = str_replace("##answer_$id##", "##answer_$uuid##", $content);
            $target_data['content'] = $content;
         }
      }

      // get data from ticket relations
      $target_data['_ticket_relations'] = [];
      $target_ticketLink = new PluginFormcreatorItem_TargetTicket();
      $all_ticketLinks = $DB->request([
         'SELECT'  => ['id'],
         'FROM'    => $target_ticketLink::getTable(),
         'WHERE'   => [
            'plugin_formcreator_targettickets_id' => $target_data['id']
         ],
      ]);
      foreach ($all_ticketLinks as $ticketLink) {
         $target_ticketLink->getFromDB($ticketLink['id']);
         $target_data['_ticket_relations'][] = $target_ticketLink->export();
      }

      // remove key and fk
      unset($target_data['id'],
            $target_data['tickettemplates_id']);

      $target_data['title'] = $target_data['name'];
      unset($target_data['name']);
      return $target_data;
   }

   private function saveAssociatedItems($input) {
      switch ($input['associate_rule']) {
         case 'answer':
            $input['associate_question'] = $input['_associate_question'];
            break;

         case 'specific':
            $itemTargetTicket = new PluginFormcreatorItem_TargetTicket();
            $itemTargetTicket->deleteByCriteria([
               'NOT' => ['itemtype' => [
                  PluginFormcreatorTargetTicket::class,
                  Ticket::class,
               ]]
            ]);
            $targetTicketFk = self::getForeignKeyField();
            foreach ($input['items_id'] as $itemtype => $items) {
               foreach ($items as $id) {
                  $itemTargetTicket = new PluginFormcreatorItem_TargetTicket();
                  $itemTargetTicket->add([
                     'itemtype' => $itemtype,
                     'items_id' => $id,
                     $targetTicketFk => $this->getID(),
                  ]);
               }
            }
            break;
      }
      unset($input['items_id']);
      return $input;
   }
}
