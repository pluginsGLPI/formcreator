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

use GlpiPlugin\Formcreator\Exception\ImportFailureException;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorTargetChange extends PluginFormcreatorTargetBase
{
   public static function getTypeName($nb = 1) {
      return _n('Target change', 'Target changes', $nb, 'formcreator');
   }

   static function getEnumUrgencyRule() {
      return [
         PluginFormcreatorTargetBase::URGENCY_RULE_NONE      => __('Medium', 'formcreator'),
         PluginFormcreatorTargetBase::URGENCY_RULE_SPECIFIC  => __('Specific urgency', 'formcreator'),
         PluginFormcreatorTargetBase::URGENCY_RULE_ANSWER    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   static function getEnumCategoryRule() {
      return [
         PluginFormcreatorTargetChange::CATEGORY_RULE_NONE      => __('None', 'formcreator'),
         PluginFormcreatorTargetChange::CATEGORY_RULE_SPECIFIC  => __('Specific category', 'formcreator'),
         PluginFormcreatorTargetChange::CATEGORY_RULE_ANSWER    => __('Equals to the answer to the question', 'formcreator'),
      ];
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

   protected function getItem_Item() {
      return new Change_Item();
   }

   protected function getTargetItemtypeName() {
      return Change::class;
   }

   public function getItem_Actor() {
      return new PluginFormcreatorTargetChange_Actor();
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

   /**
    * Export in an array all the data of the current instanciated target ticket
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if ($this->isNewItem()) {
         return false;
      }

      $export = $this->fields;

      // remove key and fk
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      unset($export[$formFk]);

      $subItems = [
         '_actors'     => $this->getItem_Actor()->getType(),
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
            'due_date_rule'  => ['values' => self::DUE_DATE_RULE_ANSWER, 'field' => 'due_date_question'],
            'urgency_rule'   => ['values' => self::URGENCY_RULE_ANSWER, 'field' => 'urgency_question'],
            'tag_type'       => ['values' => self::TAG_TYPE_QUESTIONS, 'field' => 'tag_questions'],
            'category_rule'  => ['values' => self::CATEGORY_RULE_ANSWER, 'field' => 'category_question'],
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

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk] = $containerId;
      $input['_skip_checks'] = true;
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
         'due_date_rule'  => ['values' => self::DUE_DATE_RULE_ANSWER, 'field' => 'due_date_question'],
         'urgency_rule'   => ['values' => self::URGENCY_RULE_ANSWER, 'field' => 'urgency_question'],
         'tag_type'       => ['values' => self::TAG_TYPE_QUESTIONS, 'field' => 'tag_questions'],
         'category_rule'  => ['values' => self::CATEGORY_RULE_ANSWER, 'field' => 'category_question'],
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
         $input[$fieldSetting['field']] = $question->getID();
      }

      // Add or update
      $originalId = $input[$idKey];
      if ($itemId !== false) {
         $input['id'] = $itemId;
         $item->update($input);
      } else {
         unset($input['id']);
         $itemId = $item->add($input);
      }
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the target to the linker
      $linker->addObject($originalId, $item);

      $subItems = [
         '_actors'     => $item->getItem_Actor()->getType(),
         '_conditions' => PluginFormcreatorCondition::class,
      ];
      $item->importChildrenObjects($item, $linker, $subItems, $input);

      return $itemId;
   }

   /**
    * Show the Form for the adminsitrator to edit in the config page
    *
    * @param  Array  $options Optional options
    *
    * @return NULL         Nothing, just display the form
    */
   public function showForm($options = []) {
      $rand = mt_rand();

      $form = $this->getForm();

      echo '<div class="center" style="width: 950px; margin: 0 auto;">';
      echo '<form name="form_target" method="post" action="' . self::getFormURL() . '" data-itemtype="' . self::class . '">';

      // General information: target_name
      echo '<table class="tab_cadre_fixe">';
      echo '<tr><th colspan="2">' . __('Edit a destination', 'formcreator') . '</th></tr>';
      echo '<tr class="line1">';
      echo '<td width="15%"><strong>' . __('Name') . ' </strong></td>';
      echo '<td width="85%"><input type="text" name="name" style="width:704px;" value="' . $this->fields['name'] . '" /></td>';
      echo '</tr>';
      echo '</table>';

      // change information: title, template...
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="4">' . _n('Target change', 'Target changes', 1, 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Change title', 'formcreator') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td colspan="3"><input type="text" name="target_name" style="width:704px;" value="' . $this->fields['target_name'] . '"></td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Description') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td colspan="3">';
      echo Html::textarea([
         'name'    => 'content',
         'value'   => $this->fields['content'],
         'display' => false,
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Impacts') . ' </strong></td>';
      echo '<td colspan="3">';
      echo Html::textarea([
         'name'    => 'impactcontent',
         'value'   => $this->fields['impactcontent'],
         'display' => false,
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Control list') . ' </strong></td>';
      echo '<td colspan="3">';
      echo Html::textarea([
         'name'    => 'controlistcontent',
         'value'   => $this->fields['controlistcontent'],
         'display' => false,
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Deployment plan') . ' </strong></td>';
      echo '<td colspan="3">';
      echo Html::textarea([
         'name'    => 'rolloutplancontent',
         'value'   => $this->fields['rolloutplancontent'],
         'display' => false,
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Backup plan') . ' </strong></td>';
      echo '<td colspan="3">';
      echo Html::textarea([
         'name'    => 'backoutplancontent',
         'value'   => $this->fields['backoutplancontent'],
         'display' => false,
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Checklist') . ' </strong></td>';
      echo '<td colspan="3">';
      echo Html::textarea([
         'name'   => 'checklistcontent',
         'value'  => $this->fields['checklistcontent'],
         'display' => false,
      ]);
      echo '</td>';
      echo '</tr>';

      $rand = mt_rand();
      $this->showDestinationEntitySetings($rand);

      echo '<tr class="line1">';
      $this->showDueDateSettings($form, $rand);
      echo '<td colspan="2"></td>';
      echo '</tr>';

      // -------------------------------------------------------------------------------------------
      //  category of the target
      // -------------------------------------------------------------------------------------------
      $this->showCategorySettings($form, $rand);

      // -------------------------------------------------------------------------------------------
      // Urgency selection
      // -------------------------------------------------------------------------------------------
      $this->showUrgencySettings($form, $rand);

      // -------------------------------------------------------------------------------------------
      //  Tags
      // -------------------------------------------------------------------------------------------
      $this->showPluginTagsSettings($form, $rand);

      // -------------------------------------------------------------------------------------------
      //  Conditions to generate the target
      // -------------------------------------------------------------------------------------------
      echo '<tr>';
      echo '<th colspan="4">';
      echo __('Condition to show the target', 'formcreator');
      echo '</label>';
      echo '</th>';
      echo '</tr>';
      $this->showConditionsSettings($rand);

      echo '</table>';

      // Buttons
      echo '<table class="tab_cadre_fixe">';

      echo '<tr class="line1">';
      echo '<td colspan="5" class="center">';
      echo '<input type="reset" name="reset" class="submit_button" value="' . __('Cancel', 'formcreator') . '"
               onclick="document.location = \'form.form.php?id=' . $this->fields['plugin_formcreator_forms_id'] . '\'" /> &nbsp; ';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      echo Html::hidden($formFk, ['value' => $this->fields[$formFk]]);
      echo '<input type="submit" name="update" class="submit_button" value="' . __('Save') . '" />';
      echo '</td>';
      echo '</tr>';

      echo '</table>';

      Html::closeForm();

      $this->showActorsSettings();

      // List of available tags

      $this->showTagsList();
      echo '</div>';
   }

   public function prepareInputForAdd($input) {
      $input = parent::prepareInputForAdd($input);

      return $input;
   }

   /**
    * Prepare input data for updating the target ticket
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
    */
   public function prepareInputForUpdate($input) {
      // Control fields values :
      if (!isset($input['_skip_checks'])
            || !$input['_skip_checks']) {

         $input['content'] = Html::entity_decode_deep($input['content']);

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
                  $input['destination_entity_value'] = 'NULL';
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

   public function post_addItem() {
      parent::post_addItem();
      if (!isset($this->input['_skip_checks']) || !$this->input['_skip_checks']) {
         $this->updateConditions($this->input);
      }
   }

   public function post_updateItem($history = 1) {
      parent::post_updateItem();
      if (!isset($this->input['_skip_checks']) || !$this->input['_skip_checks']) {
         $this->updateConditions($this->input);
      }
   }

   /**
    * Save form data to the target
    *
    * @param  PluginFormcreatorFormAnswer $formanswer    Answers previously saved
    *
    * @return Change|null generated change
    */
   public function save(PluginFormcreatorFormAnswer $formanswer) {
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
         '_users_id_assign'       => [],
         '_users_id_assign_notif' => [
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

      $data   = [];
      $change  = new Change();
      $form    = $formanswer->getForm();

      $data['requesttypes_id'] = PluginFormcreatorCommon::getFormcreatorRequestTypeId();

      // Parse data
      $data['name'] = $this->prepareTemplate(
         $this->fields['target_name'],
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
            $this->fields[$changeField],
            $formanswer,
            $changeField == 'content' // only content supports rich text
         );

         $data[$changeField] = $formanswer->parseTags($data[$changeField], $this, $changeField == 'content');
      }

      $data['_users_id_recipient']   = $_SESSION['glpiID'];

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
      }

      $data = $this->setTargetEntity($data, $formanswer, $requesters_id);
      $data = $this->setTargetDueDate($data, $formanswer);
      $data = $this->setTargetUrgency($data, $formanswer);
      $data = $this->setTargetCategory($data, $formanswer);

      $data = $this->requesters + $this->observers + $this->assigned + $this->assignedSuppliers + $data;
      $data = $this->requesterGroups + $this->observerGroups + $this->assignedGroups + $data;

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

      $this->attachDocument($formanswer->getID(), Change::class, $changeID);

      return $change;
   }

   /**
    * get all target changes for a form
    *
    * @param integer $formId
    * @return array
    */
   public function getTargetChangesForForm($formId) {
      global $DB;

      $targets = [];
      $rows = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_formcreator_forms_id' => $formId
         ],
      ]);
      foreach ($rows as $row) {
         $target = new self();
         $target->getFromDB($row['id']);
         $targets[$row['id']] = $target;
      }

      return $targets;
   }
}
