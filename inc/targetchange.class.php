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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorTargetChange extends PluginFormcreatorAbstractItilTarget
{
   public static function getTypeName($nb = 1) {
      return _n('Target change', 'Target changes', $nb, 'formcreator');
   }

   protected function getItem_User() {
      return new Change_User();
   }

   protected function getItem_Group() {
      return new Change_Group();
   }

   protected function getItem_Supplier() {
      return new Change_Supplier();
   }

   public static function getItem_Item(): CommonDBRelation {
      return new Change_Item();
   }

   public static function getTargetItemtypeName(): string {
      return Change::class;
   }

   protected function getTemplateItemtypeName(): string {
      return ChangeTemplate::class;
   }

   protected function getTemplatePredefinedFieldItemtype(): string {
      return ChangeTemplatePredefinedField::class;
   }

   protected function getCategoryFilter() {
      return ['is_change' => 1];
   }

   protected function getTaggableFields() {
      return [
         'target_name',
         'content',
         'impactcontent',
         'controlistcontent',
         'rolloutplancontent',
         'backoutplancontent',
         'checklistcontent',
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

   /**
    * Export in an array all the data of the current instanciated target change
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
      $export['_changetemplate'] = '';
      if ($export['changetemplates_id'] > 0) {
         $export['_changetemplate']
            = Dropdown::getDropdownName('glpi_changetemplates',
                                        $export['changetemplates_id']);
      }
      unset($export['changetemplates_id']);

      $subItems = [
         '_actors'     => PluginFormcreatorTarget_Actor::class,
         '_conditions' => PluginFormcreatorCondition::class,
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
            'due_date_rule'      => ['values' => self::DUE_DATE_RULE_ANSWER, 'field' => 'due_date_question'],
            'urgency_rule'       => ['values' => self::URGENCY_RULE_ANSWER, 'field' => 'urgency_question'],
            'tag_type'           => ['values' => self::TAG_TYPE_QUESTIONS, 'field' => 'tag_questions'],
            'category_rule'      => ['values' => self::CATEGORY_RULE_ANSWER, 'field' => 'category_question'],
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

   public static function import(PluginFormcreatorLinker $linker, array $input = [], int $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk] = $containerId;
      $input['_skip_create_actors'] = true;

      $item = new self();
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
         'due_date_rule'      => ['values' => self::DUE_DATE_RULE_ANSWER, 'field' => 'due_date_question'],
         'urgency_rule'       => ['values' => self::URGENCY_RULE_ANSWER, 'field' => 'urgency_question'],
         'tag_type'           => ['values' => self::TAG_TYPE_QUESTIONS, 'field' => 'tag_questions'],
         'category_rule'      => ['values' => self::CATEGORY_RULE_ANSWER, 'field' => 'category_question'],
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
         '_actors'     => PluginFormcreatorTarget_Actor::class,
         '_conditions' => PluginFormcreatorCondition::class,
      ];
      $item->importChildrenObjects($item, $linker, $subItems, $input);

      return $itemId;
   }

   public static function countItemsToImport(array $input) : int {
      $subItems = [
         '_actors'            => PluginFormcreatorTarget_Actor::class,
         '_conditions'        => PluginFormcreatorCondition::class,
      ];

      return 1 + self::countChildren($subItems, $input);
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
         'name'               => __('Change title', 'formcreator'),
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

      $tab[] = [
         'id'                 => '6',
         'table'              => $this::getTable(),
         'field'              => 'impactcontent',
         'name'               => __('Impact', 'formcreator'),
         'datatype'           => 'text',
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this::getTable(),
         'field'              => 'controlistcontent',
         'name'               => __('Control list', 'formcreator'),
         'datatype'           => 'text',
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this::getTable(),
         'field'              => 'rolloutplancontent',
         'name'               => __('Deployment plan', 'formcreator'),
         'datatype'           => 'text',
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => $this::getTable(),
         'field'              => 'backoutplancontent',
         'name'               => __('Backup plan', 'formcreator'),
         'datatype'           => 'text',
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '10',
         'table'              => $this::getTable(),
         'field'              => 'checklistcontent',
         'name'               => __('Check list', 'formcreator'),
         'datatype'           => 'text',
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      return $tab;
   }

   public function showForm($ID, $options = []) {
      $options = [
         'candel'      => false,
         'formoptions' => sprintf('data-itemtype="%s"', $this::getType()),
      ];
      TemplateRenderer::getInstance()->display('@formcreator/pages/targetchange.html.twig', [
         'item'   => $this,
         'params' => $options,
      ]);

      $this->getForm()->showTagsList();

      return true;
   }

   public static function showActors(self $item) {
      $item->showActorsSettings();
   }

   public static function showProperties(self $item) {
      echo '<form name="form"'
      . ' method="post"'
      . ' action="' . self::getFormURL() . '"'
      . ' data-itemtype="' . self::class . '"'
      . '>';

      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th class="center" colspan="4">' . __('Properties', 'formcreator') . '</th></tr>';

      $rand = mt_rand();
      $item->showDestinationEntitySetings($rand);

      echo '<tr>';
      $item->showTemplateSettings();
      $item->showDueDateSettings();
      echo '</tr>';

      $item->showSLASettings();
      $item->showOLASettings();

      // -------------------------------------------------------------------------------------------
      //  category of the target
      // -------------------------------------------------------------------------------------------
      $item->showCategorySettings($rand);

      // -------------------------------------------------------------------------------------------
      // Urgency selection
      // -------------------------------------------------------------------------------------------
      $item->showUrgencySettings($rand);

      // -------------------------------------------------------------------------------------------
      //  Tags
      // -------------------------------------------------------------------------------------------
      $item->showPluginTagsSettings($rand);

      // -------------------------------------------------------------------------------------------
      // Validation selection
      // -------------------------------------------------------------------------------------------
      $item->showValidationSettings($rand);

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

   public function prepareInputForAdd($input) {
      $input = parent::prepareInputForAdd($input);

      return $input;
   }

   /**
    * Prepare input data for updating the target change
    *
    * @param array $input data used to add the item
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
                  unset($input['_destination_entity_value_specific']);
                  break;
               case self::DESTINATION_ENTITY_USER :
                  $input['destination_entity_value'] = $input['_destination_entity_value_user'];
                  unset($input['_destination_entity_value_user']);
                  break;
               case self::DESTINATION_ENTITY_ENTITY :
                  $input['destination_entity_value'] = $input['_destination_entity_value_entity'];
                  unset($input['_destination_entity_value_entity']);
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
                  unset($input['_urgency_question']);
                  break;
               case self::URGENCY_RULE_SPECIFIC:
                  $input['urgency_question'] = $input['_urgency_specific'];
                  unset($input['_urgency_specific']);
                  break;
               default:
                  $input['urgency_question'] = '0';
            }
         }

         if (isset($input['category_rule'])) {
            switch ($input['category_rule']) {
               case self::CATEGORY_RULE_ANSWER:
                  $input['category_question'] = $input['_category_question'];
                  unset($input['_category_question']);
                  break;
               case self::CATEGORY_RULE_SPECIFIC:
                  $input['category_question'] = $input['_category_specific'];
                  unset($input['_category_specific']);
                  break;
               default:
                  $input['category_question'] = '0';
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

      // delete conditions
      if (! (new PluginFormcreatorCondition())->deleteByCriteria([
         'itemtype' => self::class,
         'items_id' => $this->getID(),
      ])) {
         return false;
      }

      return true;
   }

   protected function getTargetTemplate(array $data): int {
      global $DB;

      $targetItemtype = $this->getTemplateItemtypeName();
      $targetTemplateFk = $targetItemtype::getForeignKeyField();
      if ($targetItemtype::isNewID($this->fields[$targetTemplateFk]) && !ITILCategory::isNewID($data['itilcategories_id'])) {
         $rows = $DB->request([
            'SELECT' => [$targetTemplateFk],
            'FROM'   => ITILCategory::getTable(),
            'WHERE'  => ['id' => $data['itilcategories_id']]
         ]);
         if ($row = $rows->current()) { // assign change template according to resulting change category
            return $row[$targetTemplateFk];
         }
      }

      return $this->fields[$targetTemplateFk] ?? 0;
   }

   public function getDefaultData(PluginFormcreatorFormAnswer $formanswer): array {
      $data = parent::getDefaultData($formanswer);

      return $data;
   }

   /**
    * Save form data to the target
    *
    * @param  PluginFormcreatorFormAnswer $formanswer    Answers previously saved
    *
    * @return Change|null generated change
    */
   public function save(PluginFormcreatorFormAnswer $formanswer): ?CommonDBTM {
      $data   = [];
      $change  = new Change();
      $form    = $formanswer->getForm();
      $data = $this->getDefaultData($formanswer);

      // Parse data
      $domain = PluginFormcreatorForm::getTranslationDomain($form->getID());
      $data['name'] = $this->prepareTemplate(
         __($this->fields['target_name'], $domain),
         $formanswer,
         true
      );
      $data['name'] = Toolbox::addslashes_deep($data['name']);
      $data['name'] = $formanswer->parseTags($data['name'], $this);

      $changeFields = [
         'content',
         'impactcontent',
         'controlistcontent',
         'rolloutplancontent',
         'backoutplancontent',
         'checklistcontent'
      ];
      foreach ($changeFields as $changeField) {
         $data[$changeField] = $this->prepareTemplate(
            $this->fields[$changeField] ?? '',
            $formanswer,
            $changeField == 'content' // only content supports rich text
         );
         $data[$changeField] = $data[$changeField] ?? '';

         $data[$changeField] = $formanswer->parseTags($data[$changeField], $this, $changeField == 'content');
      }

      $data['_users_id_recipient']   = $_SESSION['glpiID'];

      $this->prepareActors($form, $formanswer);

      if (count($this->requesters['_users_id_requester']) == 0) {
         $this->addActor('requester', $formanswer->fields['requester_id'], true);
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
      }

      $data = $this->setTargetEntity($data, $formanswer, $requesters_id);
      $data = $this->setTargetDueDate($data, $formanswer);
      $data = $this->setSLA($data, $formanswer);
      $data = $this->setOLA($data, $formanswer);
      $data = $this->setTargetUrgency($data, $formanswer);
      $data = $this->setTargetValidation($data, $formanswer);

      $data = $this->requesters + $this->observers + $this->assigned + $this->assignedSuppliers + $data;
      $data = $this->requesterGroups + $this->observerGroups + $this->assignedGroups + $data;

      $data = $this->prepareUploadedFiles($data, $formanswer);

      $this->appendFieldsData($formanswer, $data);

      // Cleanup actors array
      $data = $this->cleanActors($data);

      // Create the target change
      if (!$changeID = $change->add($data)) {
         return null;
      }

      $this->saveTags($formanswer, $changeID);

      // Add link between Change and FormAnswer
      $itemlink = $this->getItem_Item();
      $itemlink->add([
         'itemtype'     => PluginFormcreatorFormAnswer::class,
         'items_id'     => $formanswer->fields['id'],
         'changes_id'  => $changeID,
      ]);

      return $change;
   }

   public static function getIcon() {
      return Change::getIcon();
   }
}
