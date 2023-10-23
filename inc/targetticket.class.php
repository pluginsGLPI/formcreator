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

use GlpiPlugin\Formcreator\Exception\ImportFailureException;
use GlpiPlugin\Formcreator\Exception\ExportFailureException;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Toolbox\Sanitizer;


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorTargetTicket extends PluginFormcreatorAbstractItilTarget
{
   const ASSOCIATE_RULE_NONE = 1;
   const ASSOCIATE_RULE_SPECIFIC = 2;
   const ASSOCIATE_RULE_ANSWER = 3;
   const ASSOCIATE_RULE_LAST_ANSWER = 4;

   const REQUESTTYPE_NONE = 0;
   const REQUESTTYPE_SPECIFIC = 1;
   const REQUESTTYPE_ANSWER = 2;

   const REQUESTSOURCE_NONE = 0;
   const REQUESTSOURCE_FORMCREATOR = 1;

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

   public static function getItem_Item(): CommonDBRelation {
      return new Item_Ticket();
   }

   public static function getTargetItemtypeName(): string {
      return Ticket::class;
   }

   protected function getTemplateItemtypeName(): string {
      return TicketTemplate::class;
   }

   protected function getTemplatePredefinedFieldItemtype(): string {
      return TicketTemplatePredefinedField::class;
   }

   protected function getCategoryFilter() {
      return [
         'OR' => [
            'is_request'  => 1,
            'is_incident' => 1
         ]
      ];
   }

   public static function getEnumAssociateRule() {
      return [
         self::ASSOCIATE_RULE_NONE        => __('None', 'formcreator'),
         self::ASSOCIATE_RULE_SPECIFIC    => __('Specific asset', 'formcreator'),
         self::ASSOCIATE_RULE_ANSWER      => __('Equals to the answer to the question', 'formcreator'),
         self::ASSOCIATE_RULE_LAST_ANSWER => __('Last valid answer', 'formcreator'),
      ];
   }

   public static function getEnumRequestSourceRule(): array {
      return [
         self::REQUESTSOURCE_NONE      => __('Source from template or user default or GLPI default', 'formcreator'),
         self::REQUESTSOURCE_FORMCREATOR  => __('Formcreator', 'formcreator'),
      ];
   }

   public static function getEnumRequestTypeRule() {
      return [
         self::REQUESTTYPE_NONE      => __('Default or from a template', 'formcreator'),
         self::REQUESTTYPE_SPECIFIC  => __('Specific type', 'formcreator'),
         self::REQUESTTYPE_ANSWER    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   public function defineTabs($options = []) {
      $tab = [];
      $this->addDefaultFormTab($tab);
      $this->addStandardTab(__CLASS__, $tab, $options);
      return $tab;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!self::canView()) {
         return '';
      }
      switch ($item->getType()) {
         case __CLASS__ :
            $tab = [
               1 => __('Properties', 'formcreator'),
               2 => __('Actors', 'formcreator'),
               3 => PluginFormcreatorCondition::getTypeName(1),
            ];
            // if ((new Plugin)->isActivated('fields')) {
            //    $tab[4] = __('Fields plugin', 'formcreator');
            // }
            return $tab;
            break;
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case self::class:
            switch ($tabnum) {
               case 1:
                  self::showProperties($item);
                  return true;
                  break;
               case 2:
                  self::showActors($item);
                  return true;
                  break;
               case 3:
                  self::showConditions($item);
                  break;
               // case 4:
               //    self::showPluginFields($item);
               //    break;
            }
            break;
      }

      return false;
   }

   public function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '2',
         'table'              => $this::getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this::getTable(),
         'field'              => 'target_name',
         'name'               => __('Ticket title', 'formcreator'),
         'datatype'           => 'string',
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this::getTable(),
         'field'              => 'content',
         'name'               => __('Content', 'formcreator'),
         'datatype'           => 'text',
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      return $tab;
   }

   /**
    * Show the Form for the adminsitrator to edit in the config page
    *
    * @param  array  $options Optional options
    * @return void
    */
   public function showForm($ID, $options = []) {
      $options = [
         'candel'      => false,
         'formoptions' => sprintf('data-itemtype="%s"', $this::getType()),
      ];
      TemplateRenderer::getInstance()->display('@formcreator/pages/targetticket.html.twig', [
         'item'   => $this,
         'params' => $options,
      ]);

      $this->getForm()->showTagsList();

      return true;
   }

   public static function showProperties(self $item) {
      echo '<form name="form"'
      . ' method="post"'
      . ' action="' . self::getFormURL() . '"'
      . ' data-itemtype="' . self::class . '"'
      . '>';

      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th class="center" colspan="4">' . __('Properties', 'formcreator') . '</th></tr>';

      $form = $item->getForm();
      $rand = mt_rand();
      $item->showDestinationEntitySetings($rand);

      echo '<tr>';
      $item->showTemplateSettings($rand);
      $item->showDueDateSettings($rand);
      echo '</tr>';

      $item->showSLASettings();
      $item->showOLASettings();

      $item->showTargetSource($rand);
      $item->showTargetType($rand);
      // -------------------------------------------------------------------------------------------
      //  associated elements of the target
      // -------------------------------------------------------------------------------------------
      $item->showAssociateSettings($rand);

      // -------------------------------------------------------------------------------------------
      //  category of the target
      // -------------------------------------------------------------------------------------------
      $item->showCategorySettings($rand);

      // -------------------------------------------------------------------------------------------
      // Urgency selection
      // -------------------------------------------------------------------------------------------
      $item->showUrgencySettings($rand);

      // -------------------------------------------------------------------------------------------
      // Location selection
      // -------------------------------------------------------------------------------------------
      $item->showLocationSettings($rand);

      // -------------------------------------------------------------------------------------------
      // Validation selection
      // -------------------------------------------------------------------------------------------
      $item->showValidationSettings($rand);

      // -------------------------------------------------------------------------------------------
      //  Tags
      // -------------------------------------------------------------------------------------------
      $item->showPluginTagsSettings($rand);

      // -------------------------------------------------------------------------------------------
      //  Composite tickets
      // -------------------------------------------------------------------------------------------
      $item->showCompositeTicketSettings($rand);

      // -------------------------------------------------------------------------------------------
      //  Validation as ticket followup
      // -------------------------------------------------------------------------------------------
      if ($form->fields['validation_required']) {
         echo '<tr>';
         echo '<td colspan="4">';
         echo '<input type="hidden" name="validation_followup" value="0" />';
         echo '<input type="checkbox" name="validation_followup" id="validation_followup" value="1" ';
         if (!isset($item->fields['validation_followup']) || ($item->fields['validation_followup'] == 1)) {
            echo ' checked="checked"';
         }
         echo '/>';
         echo ' <label for="validation_followup">';
         echo __('Add validation message as first ticket followup', 'formcreator');
         echo '</label>';
         echo '</td>';
         echo '</tr>';
      }

      echo '<tr>';
      echo '<td colspan="4" class="center">';
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      echo Html::hidden('id', ['value' => $item->getID()]);
      echo Html::hidden($formFk, ['value' => $item->fields[$formFk]]);
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td colspan="4" class="center">';
      echo Html::submit(_x('button', 'Save'), ['name' => 'update']);
      echo '</td>';
      echo '</tr>';

      echo '</table>';
      Html::closeForm();
   }

   public static function showPluginFields(self $item) {
      $formId = $item->getID();

      $canEdit = Session::haveRight('entity', UPDATE);

      if ($canEdit) {
         // Global validation settings
         echo "<form method='post' action='".self::getFormURL()."'>";
         echo "<div class='spaced'><table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th colspan='3' class='center'>";
         echo __('Add a field', 'formcreator');
         echo "</th>";

         echo "</tr>";
         echo "<tr class='tab_bg_1'><td class='center'>";
         echo __('Field', 'formcreator');
         echo "</td><td width='20%'>";
         echo Group::dropdown([
            'name'    => 'plugin_fields_fields_id',
            'display' => false,
         ]);
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td class='center'>";
         echo "<input type='hidden' name='plugin_formcreator_forms_id' value='$formId'>";
         echo "<input type='submit' name='set_validation_percent' value=\""._sx('button', 'Save')."\"
                class='submit'>";
         echo "</td>";
         echo "</tr>";
         echo "</table></div>";

         Html::closeForm();

         $rows = [];
         echo '<table class="tab_cadre_fixe">';
         echo '<tr class="tab_bg_1"><th class="center">';
         echo __('Managed fields', 'formcreator');
         echo '</th>';
         echo '</tr>';
         echo '</table>';

         if (count($rows) < 1) {
            // No valdiatorr to show
            echo "<p>".__('No managed field', 'formcreator')."</p>";
            return;
         }

      }
   }

   public static function showActors(self $item) {
      $item->showActorsSettings();
   }

   /**
    * Show settings to handle composite tickets
    * @param string $rand
    */
   protected function showCompositeTicketSettings($rand) {
      global $DB;

      echo '<tr>';
      echo '<td>';
      echo __('Link to an other ticket', 'formcreator');
      echo "<span class='fa fa-plus pointer' onClick=\"".Html::jsShow("plugin_formcreator_linked_items$rand")."\"
             title=\"".__s('Add')."\"><span class='sr-only'>" . __s('Add') . "</span></span>";

      echo '</td>';
      echo '<td colspan="3">';
      echo '<div style="display: none" id="plugin_formcreator_linked_items' . $rand . '">';
      Ticket_Ticket::dropdownLinks('_linktype');
      $elements = [
         PluginFormcreatorTargetTicket::class => __('An other destination of this form', 'formcreator'),
         Ticket::class                        => __('An existing ticket', 'formcreator'),
         PluginFormcreatorQuestion::class     => __('A ticket from an answer to a question'),
      ];
      Dropdown::showFromArray('_link_itemtype', $elements, [
         'on_change' => "plugin_formcreator_updateCompositePeerType(this)",
         'rand'      => $rand,
      ]);
      // get already linked items
      $targetTicketId = $this->getID();
      $rows = $DB->request([
         'FROM'   => PluginFormcreatorItem_TargetTicket::getTable(),
         'WHERE'  => [
            'plugin_formcreator_targettickets_id' => $targetTicketId
         ]
      ]);
      $excludedTargetTicketsIds = [$this->getID()];
      $excludedTicketIds = [];
      $excludedQuestionIds = [];
      foreach ($rows as $row) {
         switch ($row['itemtype']) {
            case PluginFormcreatorTargetTicket::getType():
               $excludedTargetTicketsIds[] = $row['items_id'];
               break;

            case Ticket::getType():
               $excludedTicketIds[] = $row['items_id'];
               break;

            case PluginFOrmcreatorQuestion::class:
               $excludedQuestionIds[] = $row['items_id'];
               break;
         }
      }

      echo '<span id="plugin_formcreator_link_ticket" style="display: none">';
      $linkparam = [
         'name'        => '_link_tickets_id',
         'rand'        => $rand,
         'used'        => $excludedTicketIds,
         'displaywith' => ['id'],
         'display'     => false
      ];
      echo Ticket::dropdown($linkparam);
      echo '</span>';

      // dropdown of target tickets
      echo '<span id="plugin_formcreator_link_target">';
      $condition = [
         'plugin_formcreator_forms_id' => $this->fields['plugin_formcreator_forms_id']
      ];
      echo PluginFormcreatorTargetTicket::dropdown([
         'name'        => '_link_targettickets_id',
         'rand'        => $rand,
         'display'     => false,
         'used'        => $excludedTargetTicketsIds,
         'displaywith' => ['id'],
         'condition'   => $condition,
      ]);
      echo '</span>';

      // dropdown of questions of type GLPI Object / Ticket
      echo '<span id="plugin_formcreator_link_question" style="display: none">';
      echo PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['glpiselect'],
            'itemtype'  => [Ticket::class],
            'used'      => $excludedQuestionIds,
         ],
         '_link_plugin_formcreator_questions_id',
         null,
         ['display'   => false]
      );
      echo '</span>';

      echo '</div>';

      // show already linked items
      foreach ($rows as $row) {
         $icons = '&nbsp;'.Html::getSimpleForm(
            PluginFormcreatorItem_TargetTicket::getFormURL(),
            'purge',
            _x('button', 'Delete permanently'),
            ['id' => $row['id']],
            'fa-times-circle'
         );
         $itemtype = $row['itemtype'];
         $item = new $itemtype();
         $item->getFromDB($row['items_id']);
         switch ($itemtype) {
            case Ticket::getType():
               echo Ticket_Ticket::getLinkName($row['link']);
               echo ' ';
               echo $itemtype::getTypeName();
               echo ' ';
               echo '<span style="font-weight:bold">' . $item->getField('name') . '</span>';
               echo ' ';
               echo $icons;
               break;

            case PluginFormcreatorTargetTicket::getType():
               echo Ticket_Ticket::getLinkName($row['link']);
               echo ' ';
               echo $itemtype::getTypeName();
               echo ' ';
               echo '<span style="font-weight:bold">' . $item->getField('name') . '</span>';
               echo ' ';
               echo $icons;
               break;

            case PluginFormcreatorQuestion::getType():
               echo Ticket_Ticket::getLinkName($row['link']);
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
      $input = parent::prepareInputForAdd($input);
      if ($input === false) {
         return false;
      }
      if (!isset($input['type_rule'])) {
         $input['type_rule'] = self::REQUESTTYPE_SPECIFIC;
      }
      if ($input['type_rule'] == self::REQUESTTYPE_SPECIFIC) {
         if (!isset($input['type_question']) || !in_array($input['type_question'], [Ticket::INCIDENT_TYPE, Ticket::DEMAND_TYPE])) {
            $input['type_question'] = Ticket::INCIDENT_TYPE;
         }
      }

      if (!isset($input['source_rule'])) {
         $input['source_rule'] = self::REQUESTSOURCE_FORMCREATOR;
      }
      $input['source_question'] = 0;
      if ($input['source_rule'] == self::REQUESTSOURCE_FORMCREATOR) {
         $input['source_question'] = PluginFormcreatorCommon::getFormcreatorRequestTypeId();
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
      // Control fields values :
      if (!$this->skipChecks) {
         if (isset($input['destination_entity'])) {
            switch ($input['destination_entity']) {
               case self::DESTINATION_ENTITY_SPECIFIC :
                  $input['destination_entity_value'] = $input['_destination_entity_value_specific'];
                  break;
               case self::DESTINATION_ENTITY_USER :
                  $input['destination_entity_value'] = $input['_destination_entity_value_user'];
                  break;
               case self::DESTINATION_ENTITY_ENTITY :
                  $input['destination_entity_value'] = $input['_destination_entity_value_entity'];
                  break;
               default :
                  $input['destination_entity_value'] = 0;
                  break;
            }
         }

         if (isset($input['urgency_rule'])) {
            switch ($input['urgency_rule']) {
               case self::URGENCY_RULE_ANSWER:
                  $input['urgency_question'] = $input['_urgency_question'];
                  break;
               case self::URGENCY_RULE_SPECIFIC:
                  $input['urgency_question'] = $input['_urgency_specific'];
                  break;
               default:
                  $input['urgency_question'] = '0';
            }
         }

         if (isset($input['sla_rule'])) {
            switch ($input['sla_rule']) {
               case self::SLA_RULE_SPECIFIC:
                  $input['sla_question_tto'] = $input['_sla_specific_tto'];
                  $input['sla_question_ttr'] = $input['_sla_specific_ttr'];
                  break;
               case self::SLA_RULE_FROM_ANWSER:
                  $input['sla_question_tto'] = $input['_sla_questions_tto'];
                  $input['sla_question_ttr'] = $input['_sla_questions_ttr'];
                  break;
            }
         }

         if (isset($input['ola_rule'])) {
            switch ($input['ola_rule']) {
               case self::OLA_RULE_SPECIFIC:
                  $input['ola_question_tto'] = $input['_ola_specific_tto'];
                  $input['ola_question_ttr'] = $input['_ola_specific_ttr'];
                  break;
               case self::OLA_RULE_FROM_ANWSER:
                  $input['ola_question_tto'] = $input['_ola_questions_tto'];
                  $input['ola_question_ttr'] = $input['_ola_questions_ttr'];
                  break;
            }
         }

         if (isset($input['type_rule'])) {
            $input['type_question'] = '0';
            switch ($input['type_rule']) {
               case self::REQUESTTYPE_ANSWER:
                  $input['type_question'] = $input['_type_question'];
                  break;
               case self::REQUESTTYPE_SPECIFIC:
                  $input['type_question'] = $input['_type_specific'];
                  break;
            }
         }

         if (isset($input['source_rule'])) {
            $input['source_question'] = '0';
            switch ($input['source_rule']) {
               case self::REQUESTSOURCE_NONE:
                  $input['source_question'] = 0;
                  break;
               case self::REQUESTSOURCE_FORMCREATOR:
                  $input['source_question'] = PluginFormcreatorCommon::getFormcreatorRequestTypeId();
                  break;
            }
         }

         if (isset($input['category_rule'])) {
            switch ($input['category_rule']) {
               case self::CATEGORY_RULE_ANSWER:
                  $input['category_question'] = $input['_category_question'];
                  break;
               case self::CATEGORY_RULE_SPECIFIC:
                  $input['category_question'] = $input['_category_specific'];
                  break;
               default:
                  $input['category_question'] = '0';
            }
         }

         if (isset($input['location_rule'])) {
            switch ($input['location_rule']) {
               case self::LOCATION_RULE_ANSWER:
                  $input['location_question'] = $input['_location_question'];
                  break;
               case self::LOCATION_RULE_SPECIFIC:
                  $input['location_question'] = $input['_location_specific'];
                  break;
               case self::LOCATION_RULE_LAST_ANSWER:
               default:
                  $input['location_question'] = '0';
            }
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
    * Hook for pre_purge of the item.
    * GLPI does not provides pre_purgeItem, this is emulated with
    * the hook pre_purge_item
    *
    * @param CommonDBTM $item
    * @return boolean
    */
   public function pre_purgeItem() {
      if (!parent::pre_purgeItem()) {
         $this->input = false;
         return false;
      }

      // delete targets linked to this instance
      $myFk = static::getForeignKeyField();
      $item_targetTicket = new PluginFormcreatorItem_TargetTicket();
      if (!$item_targetTicket->deleteByCriteria([$myFk  => $this->getID()])) {
         $this->input = false;
         return false;
      }

      // delete conditions
      if (! (new PluginFormcreatorCondition())->deleteByCriteria([
         'itemtype' => self::class,
         'items_id' => $this->getID(),
      ])) {
         return false;
      }

      return true;
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

         case PluginFormcreatorQuestion::getType():
            $itemId = (int) $input['_link_plugin_formcreator_questions_id'];
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

   protected function getTargetTemplate(array $data): int {
      global $DB;

      $targetItemtype = $this->getTemplateItemtypeName();
      $targetTemplateFk = $targetItemtype::getForeignKeyField();
      if ($targetItemtype::isNewID($this->fields[$targetTemplateFk]) && !ITILCategory::isNewID($data['itilcategories_id'])) {
         $rows = $DB->request([
            'SELECT' => ["{$targetTemplateFk}_incident", "{$targetTemplateFk}_demand"],
            'FROM'   => ITILCategory::getTable(),
            'WHERE'  => ['id' => $data['itilcategories_id']]
         ]);
         if ($row = $rows->current()) {
            // assign ticket template according to resulting ticket category and ticket type
            return ($data['type'] == Ticket::INCIDENT_TYPE
                    ? $row["{$targetTemplateFk}_incident"]
                    : $row["{$targetTemplateFk}_demand"]);
         }
      }

      return $this->fields[$targetTemplateFk] ?? 0;
   }

   /**
    * Save form data to the target
    *
    * @param  PluginFormcreatorFormAnswer $formanswer    Answers previously saved
    *
    * @return Ticket|null Generated ticket if success, null otherwise
    */
   public function save(PluginFormcreatorFormAnswer $formanswer): ?CommonDBTM {
      global $CFG_GLPI;

      $ticket  = new Ticket();
      $form = $formanswer->getForm();
      $data = $this->getDefaultData($formanswer);

      // Parse data
      // TODO: generate instances of all answers of the form and use them for the fullform computation
      //       and the computation from a admin-defined target ticket template
      $richText = true;
      $domain = PluginFormcreatorForm::getTranslationDomain($form->getID());
      $data['name'] = $this->prepareTemplate(
         Sanitizer::unsanitize(__($this->fields['target_name'], $domain)),
         $formanswer,
         false
      );
      $data['name'] = $formanswer->parseTags($data['name'], $this);
      $data['date'] = $_SESSION['glpi_currenttime'];

      $data['content'] = $this->prepareTemplate(
         Sanitizer::unsanitize(__($this->fields['content'], $domain)) ?? '',
         $formanswer,
         $richText
      );

      $data['content'] = $formanswer->parseTags($data['content'], $this, $richText);

      $data['_tickettemplates_id'] = $this->fields['tickettemplates_id'];

      $this->prepareActors($form, $formanswer);

      if (count($this->requesters['_users_id_requester']) == 0) {
         $this->addActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER, $formanswer->fields['requester_id'], true);
         $requesters_id = $formanswer->fields['requester_id'];
      } else {
         $requesterAccounts = array_filter($this->requesters['_users_id_requester'], function($v) {
            return ($v != 0);
         });
         $requesters_id = array_shift($requesterAccounts);
         if ($requesters_id === null) {
            // No account for requesters, then fallback on the account used to fill the answers
            $requesters_id = $formanswer->fields['requester_id'];
         }

         // If only one requester, revert array of requesters into a scalar
         // This is needed to process business rule affecting location of a ticket with the location of the user
         if (count($this->requesters['_users_id_requester']) == 1) {
            $this->requesters['_users_id_requester'] = array_pop($this->requesters['_users_id_requester']);
         }
      }

      $data['users_id_recipient'] = $formanswer->fields['requester_id'];
      $lastUpdater = Session::getLoginUserID();
      $data['users_id_lastupdater'] = $lastUpdater != '' ? $lastUpdater : 0;

      $data = $this->setTargetType($data, $formanswer);
      $data = $this->setTargetSource($data, $formanswer);
      $data = $this->setTargetEntity($data, $formanswer, $requesters_id);
      $data = $this->setTargetDueDate($data, $formanswer);
      $data = $this->setSLA($data, $formanswer);
      $data = $this->setOLA($data, $formanswer);
      $data = $this->setTargetUrgency($data, $formanswer);
      $data = $this->setTargetPriority($data, $formanswer);
      $data = $this->setTargetLocation($data, $formanswer);
      $data = $this->setTargetAssociatedItem($data, $formanswer);
      $data = $this->setTargetValidation($data, $formanswer);

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

      $data = $this->setDocuments($data, $formanswer);
      $data = $this->prepareUploadedFiles($data, $formanswer);

      $data = $this->appendFieldsData($data, $formanswer);

      // Cleanup actors array
      $data = $this->cleanActors($data);

      // Create the target ticket
      $data['_auto_import'] = true;
      if (!$ticketID = $ticket->add($data)) {
         return null;
      }

      // Set default document category
      $document_category = $CFG_GLPI['documentcategories_id_forticket'] ?? 0;
      if ($document_category) {
         foreach (array_keys($this->attachedDocuments) as $documents_id) {
            $document = Document::getById($documents_id);
            if (!$document) {
               continue;
            }

            $document->update([
               'id' => $document->fields['id'],
               'documentcategories_id' => $document_category,
            ]);
         }
      }

      // Add link between Ticket and FormAnswer
      $itemlink = $this->getItem_Item();
      $itemlink->add([
         'itemtype'   => PluginFormcreatorFormAnswer::class,
         'items_id'   => $formanswer->fields['id'],
         'tickets_id' => $ticketID,
      ]);

      $this->saveTags($formanswer, $ticketID);

      // Attach validation message as first ticket followup if validation is required and
      // if is set in ticket target configuration
      if ($form->validationRequired() && $this->fields['validation_followup']) {
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
           '_do_not_compute_takeintoaccount' => true,
            'itemtype'                       => Ticket::class,
            'items_id'                       => $ticketID,
         ];
         $ticketFollowup = new ITILFollowup();
         $ticketFollowup->add($followUpInput);

         // Restore mail notification setting
         PluginFormcreatorCommon::setNotification($use_mailing);
      }

      return $ticket;
   }

   protected function setTargetLocation($data, $formanswer) {
      global $DB;

      $location = null;
      switch ($this->fields['location_rule']) {
         case self::LOCATION_RULE_ANSWER:
            $location = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['location_question']
               ]
            ])->current();
            if (isset($location['answer']) && ctype_digit($location['answer'])) {
               $location = $location['answer'];
            }
            break;
         case self::LOCATION_RULE_SPECIFIC:
            $location = $this->fields['location_question'];
            break;
         case self::LOCATION_RULE_LAST_ANSWER:
            $form_answer_id = $formanswer->fields['id'];

            // Get all answers for dropdown questions of this form, ordered
            // from last to first displayed
            $answers = $DB->request([
               'SELECT' => ['answer.plugin_formcreator_questions_id', 'answer.answer', 'question.values'],
               'FROM' => PluginFormcreatorAnswer::getTable() . ' AS answer',
               'JOIN' => [
                  PluginFormcreatorQuestion::getTable() . ' AS question' => [
                     'ON' => [
                        'answer' => 'plugin_formcreator_questions_id',
                        'question' => 'id',
                     ]
                  ]
               ],
               'WHERE' => [
                  'answer.plugin_formcreator_formanswers_id' => $form_answer_id,
                  'question.fieldtype'                       => "dropdown",
               ],
               'ORDER' => [
                  'row DESC',
                  'col DESC',
               ]
            ]);

            foreach ($answers as $answer) {
               // Decode dropdown settings
               $question = PluginFormcreatorQuestion::getById($answer[PluginFormcreatorQuestion::getForeignKeyField()]);
               $itemtype = $question->fields['itemtype'];

               // Skip if not a dropdown on locations
               if ($itemtype !== Location::class) {
                  continue;
               }

               // Skip if question was not answered
               if (empty($answer['answer'])) {
                  continue;
               }

               // Skip if question is not visible
               if (!$formanswer->isFieldVisible($answer['plugin_formcreator_questions_id'])) {
                  continue;
               }

               // Found a valid answer, stop here
               $location = $answer['answer'];
               break;
            }
            break;
      }
      if (!is_null($location)) {
         $data['locations_id'] = $location;
      }

      return $data;
   }

   protected function setTargetSource(array $data, PluginFormcreatorFormAnswer $formanswer): array {
      // do nothing with self::REQUESTSOURCE_NONE
      switch ($this->fields['source_rule']) {
         case self::REQUESTSOURCE_FORMCREATOR:
            $data['requesttypes_id'] = $this->fields['source_question'];
            break;
      }

      return $data;
   }

   protected function setTargetType(array $data, PluginFormcreatorFormAnswer $formanswer) {
      global $DB;

      $type = null;
      switch ($this->fields['type_rule']) {
         case self::REQUESTTYPE_ANSWER:
            $type = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->getID(),
                  'plugin_formcreator_questions_id'   => $this->fields['type_question']
               ]
            ])->current();
            if (isset($type['answer']) && ctype_digit($type['answer'])) {
               $type = $type['answer'];
            } else {
               // Invalid value. Maybe the question is not compatible.
               trigger_error(sprintf("Attempt to set the type of a ticket from an incompatible question. Check the target ticket %s of the form ID=%s",
                  $this->fields['name'],
                  $this->getForm()->getID()
               ), E_USER_ERROR);
               $type = null;
            }
            break;
         case self::REQUESTTYPE_SPECIFIC:
            $type = $this->fields['type_question'];
            break;
      }
      if (!is_null($type)) {
         $data['type'] = $type;
      }

      return $data;
   }

   protected function showTargetSource($rand): void {
      echo '<tr>';
      echo '<td width="15%">' . __('Request source') . '</td>';
      echo '<td width="25%">';
      Dropdown::showFromArray('source_rule', static::getEnumRequestSourceRule(), [
         'value' => $this->fields['source_rule'],
         'rand' => $rand,
      ]);
      echo '<td></td><td></td>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showTargetType($rand) {
      echo '<tr>';
      echo '<td width="15%">' . __('Request type') . '</td>';
      echo '<td width="25%">';
      Dropdown::showFromArray('type_rule', static::getEnumRequestTypeRule(), [
            'value' => $this->fields['type_rule'],
            'rand' => $rand,
            'on_change' => "plugin_formcreator_changeRequestType($rand)",
         ]
      );
      echo Html::scriptBlock("plugin_formcreator_changeRequestType($rand);");
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="requesttype_question_title" style="display: none">' . PluginFormcreatorQuestion::getTypeName(1) . '</span>';
      echo '<span id="requesttype_specific_title" style="display: none">' . __('Type ', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';
      echo '<div id="requesttype_specific_value" style="display: none">';
      Ticket::dropdownType('_type_specific',
         [
            'value'   => $this->fields['type_question'],
         ]
      );
      echo '</div>';
      echo '<div id="requesttype_question_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['requesttype'],
         ],
         '_type_question',
         $this->fields['type_question']
      );
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showAssociateSettings($rand) {
      global $CFG_GLPI;

      echo '<tr>';
      echo '<td width="15%">' . __('Associated elements') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('associate_rule', static::getEnumAssociateRule(), [
         'value'                 => $this->fields['associate_rule'],
         'on_change'             => "plugin_formcreator_change_associate($rand)",
         'rand'                  => $rand
      ]);
      echo Html::scriptBlock("plugin_formcreator_change_associate($rand)");
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="plugin_formcreator_associate_question_title" style="display: none">' . PluginFormcreatorQuestion::getTypeName(1) . '</span>';
      echo '<span id="plugin_formcreator_associate_specific_title" style="display: none">' . __('Item ', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';

      echo '<div id="plugin_formcreator_associate_specific_value" style="display: none">';
      $options = json_decode($this->fields['associate_question'], true);
      if (!is_array($options)) {
         $options = [];
      }
      $options['_canupdate'] = true;
      $itemTargetTicket = new PluginFormcreatorItem_TargetTicket();
      $rows = $itemTargetTicket->find([
         self::getForeignKeyField() => $this->getID(),
         [
            'NOT' => ['itemtype' => [PluginFormcreatorTargetTicket::class, Ticket::class]],
         ],
      ]);
      foreach ($rows as $row) {
         $options['items_id'][$row['itemtype']][$row['id']] = $row['items_id'];
      }
      Item_Ticket::itemAddForm(new Ticket(), $options);
      echo '</div>';
      echo '<div id="plugin_formcreator_associate_question_value" style="display: none">';
      // select all user questions (GLPI Object)
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'glpiselect',
            'itemtype' => $CFG_GLPI['ticket_types']
         ],
         '_associate_question',
         $this->fields['associate_question']
      );
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   /**
    * @param array $data data of the target
    * @param PluginFormcreatorFormAnswer $formanswer Answers to the form used to populate the target
    * @return array
    */
   protected function setTargetAssociatedItem(array $data, PluginFormcreatorFormAnswer $formanswer) : array {
      global $DB;

      switch ($this->fields['associate_rule']) {
         case self::ASSOCIATE_RULE_ANSWER:
            // find the itemtype of the associated item
            $associateQuestion = $this->fields['associate_question'];
            $question = new PluginFormcreatorQuestion();
            if (!$question->getFromDB($associateQuestion)) {
               trigger_error(sprintf("Question ID %s not found and should be used in target ticket ID %s", $associateQuestion, $this->getID()), E_USER_ERROR);
               break;
            }
            /** @var  GlpiPlugin\Formcreator\Field\DropdownField */
            $field = $question->getSubField();
            $itemtype = $field->getSubItemtype();

            // find the id of the associated item
            $item = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $associateQuestion
               ]
            ])->current();
            $itemId = $item['answer'];

            // associate the item if it exists
            if (!class_exists($itemtype)) {
               return $data;
            }
            $item = new $itemtype();
            if ($item->getFromDB($itemId)) {
               $data['items_id'] = [$itemtype => [$itemId => $itemId]];
            }
            break;

         case self::ASSOCIATE_RULE_SPECIFIC:
            $itemTargetTicket = new PluginFormcreatorItem_TargetTicket();
            $rows = $itemTargetTicket->find([
               self::getForeignKeyField() => $this->getID(),
               [
                  'NOT' => ['itemtype' => [PluginFormcreatorTargetTicket::class, Ticket::class]],
               ],
            ]);
            $data['items_id'] = [];
            foreach ($rows as $row) {
               $data['items_id'][$row['itemtype']] [$row['items_id']] = $row['items_id'];
            }
            break;

         case self::ASSOCIATE_RULE_LAST_ANSWER:
            $form_answer_id = $formanswer->fields['id'];

            // Get all answers for glpiselect questions of this form, ordered
            // from last to first displayed
            $answers = $DB->request([
               'SELECT' => ['answer.plugin_formcreator_questions_id', 'answer.answer', 'question.values'],
               'FROM' => PluginFormcreatorAnswer::getTable() . ' AS answer',
               'JOIN' => [
                  PluginFormcreatorQuestion::getTable() . ' AS question' => [
                     'ON' => [
                        'answer' => 'plugin_formcreator_questions_id',
                        'question' => 'id',
                     ]
                  ]
               ],
               'WHERE' => [
                  'answer.plugin_formcreator_formanswers_id' => $form_answer_id,
                  'question.fieldtype'                       => "glpiselect",
               ],
               'ORDER' => [
                  'row DESC',
                  'col DESC',
               ]
            ]);

            $valid_associated_itemtypes = $_SESSION["glpiactiveprofile"]["helpdesk_item_type"];
            foreach ($answers as $answer) {
               // Skip if the object type is not valid asset type
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($answer[PluginFormcreatorQuestion::getForeignKeyField()]);
               /** @var  GlpiPlugin\Formcreator\Field\DropdownField */
               $field = $question->getSubField();
               $field->deserializeValue($answer['answer']);
               $itemtype = $field->getSubItemtype();
               if (!in_array($itemtype, $valid_associated_itemtypes)) {
                  continue;
               }

               // Skip if question was not answered
               if (empty($answer['answer'])) {
                  continue;
               }

               // Skip if question is not visible
               if (!$formanswer->isFieldVisible($answer['plugin_formcreator_questions_id'])) {
                  continue;
               }

               // Skip if item doesn't exist in the DB (shouldn't happen)
               $item = new $itemtype();
               if (!$item->getFromDB($answer['answer'])) {
                  continue;
               }

               // Found a valid answer, stop here
               $data['items_id'] = [
                  $itemtype => [$answer['answer'] => $answer['answer']]
               ];
               break;
            }

            break;
      }

      return $data;
   }

   public static function import(PluginFormcreatorLinker $linker, array $input = [], int $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk] = $containerId;
      $input['_skip_create_actors'] = true;

      $item = new self;
      // Find an existing target to update, only if an UUID is available
      $itemId = false;
      /** @var string $idKey key to use as ID (id or uuid) */
      $idKey = 'id';
      if (isset($input['uuid'])) {
         $idKey = 'uuid';
         $itemId = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }

      // Escape text fields
      foreach (['name'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // Assume that all questions are already imported
      // convert question uuid into id
      $questions = $linker->getObjectsByType(PluginFormcreatorQuestion::class);
      if ($questions !== false) {
         $taggableFields = $item->getTaggableFields();
         foreach ($questions as $originalId => $question) {
            $newId = $question->getID();
            foreach ($taggableFields as $field) {
               $content = $input[$field];
               $content = str_replace("##question_$originalId##", "##question_$newId##", $content);
               $content = str_replace("##answer_$originalId##", "##answer_$newId##", $content);
               $input[$field] = $content;
            }
         }

         // escape text fields
         foreach ($taggableFields as $key) {
            $input[$key] = $DB->escape($input[$key]);
         }
      }

      // Update links to other questions
      $questionLinks = [
         'type_rule'          => ['values' => self::REQUESTTYPE_ANSWER, 'field' => 'type_question'],
         'due_date_rule'      => ['values' => self::DUE_DATE_RULE_ANSWER, 'field' => 'due_date_question'],
         'urgency_rule'       => ['values' => self::URGENCY_RULE_ANSWER, 'field' => 'urgency_question'],
         'tag_type'           => ['values' => self::TAG_TYPE_QUESTIONS, 'field' => 'tag_questions'],
         'category_rule'      => ['values' => self::CATEGORY_RULE_ANSWER, 'field' => 'category_question'],
         'associate_rule'     => ['values' => self::ASSOCIATE_RULE_ANSWER, 'field' => 'associate_question'],
         'location_rule'      => ['values' => self::LOCATION_RULE_ANSWER, 'field' => 'location_question'],
         'destination_entity' => [
            'values' => [
               self::DESTINATION_ENTITY_ENTITY,
               self::DESTINATION_ENTITY_USER,
            ],
            'field' => 'destination_entity_value',
         ],
      ];
      foreach ($questionLinks as $field => $fieldSetting) {
         if (!is_array($fieldSetting['values'])) {
            $fieldSetting['values'] = [$fieldSetting['values']];
         }
         if (!in_array($input[$field], $fieldSetting['values'])) {
            continue;
         }
         /**@var PluginFormcreatorQuestion $question */
         $question = $linker->getObject($input[$fieldSetting['field']], PluginFormcreatorQuestion::class);
         if ($question === false) {
            $typeName = strtolower(self::getTypeName());
            throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s: a question is missing and is used in a parameter of the target', 'formceator'), $typeName, $input['name']));
         }
         $input[$fieldSetting['field']] = $question->getID();
      }

      // Add or update
      $originalId = $input[$idKey];
      $item->skipChecks = true;
      if ($itemId !== false) {
         $input['id'] = $itemId;
         $item->update($input);
      } else {
         unset($input['id']);
         $itemId = $item->add($input);
      }
      $item->skipChecks = false;
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the target to the linker
      $linker->addObject($originalId, $item);

      $subItems = [
         '_actors'            => PluginFormcreatorTarget_Actor::class,
         '_ticket_relations'  => PluginFormcreatorItem_TargetTicket::class,
         '_conditions'        => PluginFormcreatorCondition::class,
      ];
      $item->importChildrenObjects($item, $linker, $subItems, $input);

      return $itemId;
   }

   public static function countItemsToImport(array $input) : int {
      $subItems = [
         '_actors'            => PluginFormcreatorTarget_Actor::class,
         '_ticket_relations'  => PluginFormcreatorItem_TargetTicket::class,
         '_conditions'        => PluginFormcreatorCondition::class,
      ];

      return 1 + self::countChildren($subItems, $input);
   }

   protected function getTaggableFields() {
      return [
         'target_name',
         'content',
      ];
   }

   /**
    * Export in an array all the data of the current instanciated targetticket
    * @return array the array with all data (with sub tables)
    */
   public function export(bool $remove_uuid = false) : array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
      }

      $export = $this->fields;

      // remove key and fk
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      unset($export[$formFk]);

      // replace dropdown ids
      $export['_tickettemplate'] = '';
      if ($export['tickettemplates_id'] > 0) {
         $export['_tickettemplate']
            = Dropdown::getDropdownName('glpi_tickettemplates',
                                        $export['tickettemplates_id']);
      }
      unset($export['tickettemplates_id']);

      $subItems = [
         '_actors'            => PluginFormcreatorTarget_Actor::class,
         '_ticket_relations'  => PluginFormcreatorItem_TargetTicket::class,
         '_conditions'        => PluginFormcreatorCondition::class,
      ];
      $export = $this->exportChildrenObjects($subItems, $export, $remove_uuid);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      } else {
         // Convert IDs into UUIDs
         $export = $this->convertTags($export);
         $questionLinks = [
            'type_rule'          => ['values' => self::REQUESTTYPE_ANSWER, 'field' => 'type_question'],
            'due_date_rule'      => ['values' => self::DUE_DATE_RULE_ANSWER, 'field' => 'due_date_question'],
            'urgency_rule'       => ['values' => self::URGENCY_RULE_ANSWER, 'field' => 'urgency_question'],
            'tag_type'           => ['values' => self::TAG_TYPE_QUESTIONS, 'field' => 'tag_questions'],
            'category_rule'      => ['values' => self::CATEGORY_RULE_ANSWER, 'field' => 'category_question'],
            'associate_rule'     => ['values' => self::ASSOCIATE_RULE_ANSWER, 'field' => 'associate_question'],
            'location_rule'      => ['values' => self::LOCATION_RULE_ANSWER, 'field' => 'location_question'],
            'destination_entity' => [
               'values' => [
                  self::DESTINATION_ENTITY_ENTITY,
                  self::DESTINATION_ENTITY_USER,
               ],
               'field' => 'destination_entity_value'
            ],
         ];
         foreach ($questionLinks as $field => $fieldSetting) {
            if (!is_array($fieldSetting['values'])) {
               $fieldSetting['values'] = [$fieldSetting['values']];
            }
            if (!in_array($export[$field], $fieldSetting['values'])) {
               continue;
            }
            $question = new PluginFormcreatorQuestion();
            $question->getFromDB($export[$fieldSetting['field']]);
            $export[$fieldSetting['field']] = $question->fields['uuid'];
         }
      }
      unset($export[$idToRemove]);

      return $export;
   }

   private function saveAssociatedItems($input) {
      switch ($input['associate_rule']) {
         case self::ASSOCIATE_RULE_ANSWER:
         case self::ASSOCIATE_RULE_LAST_ANSWER:
            $input['associate_question'] = $input['_associate_question'];
            break;

         case self::ASSOCIATE_RULE_SPECIFIC:
            $itemTargetTicket = new PluginFormcreatorItem_TargetTicket();
            $itemTargetTicket->deleteByCriteria([
               'NOT' => ['itemtype' => [
                  PluginFormcreatorTargetTicket::class,
                  Ticket::class,
               ]],
               self::getForeignKeyField() => $this->getID(),
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

   public static function getIcon() {
      return Ticket::getIcon();
   }
}
