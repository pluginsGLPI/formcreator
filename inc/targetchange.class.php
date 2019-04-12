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
         'none'      => __('None', 'formcreator'),
         'specific'  => __('Specific category', 'formcreator'),
         'answer'    => __('Equals to the answer to the question', 'formcreator'),
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
         'name',
         'content',
         'impactcontent',
         'controlistcontent',
         'rolloutplancontent',
         'backoutplancontent',
         'checklistcontent',
      ];
   }

   public function duplicate() {
      $linker              = new PluginFormcreatorLinker();

      // Add in the linker all objects the target may require
      $form = $this->getForm();
      foreach ($form->getQuestionsFromForm() as $questionId => $question) {
         $linker->addObject($questionId, $question);
         $condition = new PluginFormcreatorQuestion_Condition();

         foreach ($condition->getConditionsFromQuestion($questionId) as $conditionId => $condition) {
            $linker->addObject($conditionId, $condition);
         }
      }

   }

   /**
    * Export in an array all the data of the current instanciated target ticket
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      global $DB;

      if ($this->isNewItem()) {
         return false;
      }

      $target_data = $this->fields;

      // remove key and fk
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      unset($target_data[$formFk]);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      } else {
         // Convert IDs into UUIDs
         $target_data = $this->convertTags($this->fields);
      }
      unset($target_data[$idToRemove]);

      // get target actors
      $target_data['_actors'] = [];
      $myFk = self::getForeignKeyField();
      $all_target_actors = $DB->request([
         'SELECT' => ['id'],
         'FROM'    => PluginFormcreatorTargetChange_Actor::getTable(),
         'WHERE'   => [
            $myFk => $this->getID()
         ]
      ]);

      // Export sub items
      $form_target_actor = $this->getItem_Actor();
      foreach ($all_target_actors as $target_actor) {
         if ($form_target_actor->getFromDB($target_actor['id'])) {
            $target_data['_actors'][] = $form_target_actor->export($remove_uuid);
         }
      }

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      } else {
         // Convert IDs into UUIDs
         $target_data = $this->convertTags($this->fields);
      }
      unset($target_data[$idToRemove]);
      
      return $target_data;
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      $formFk = PluginFormcreatorForm::getForeignKeyField();

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

      $input['_skip_checks'] = true;
      $input[$formFk] = $containerId;

      // Assume that all questions are already imported
      // convert question uuid into id
      $questions = $linker->getObjectsByType(PluginFormcreatorQuestion::class);
      $questionIdentifier = 'id';
      if (isset($input['uuid'])) {
         $questionIdentifier = 'uuid';
      }
      $taggableFields = $item->getTaggableFields();
      foreach ($questions as $question) {
         $id         = $question['id'];
         $originalId = $question[$questionIdentifier];
         foreach ($taggableFields as $field) {
            $content = $input[$field];
            $content = str_replace("##question_$originalId##", "##question_$id##", $content);
            $content = str_replace("##answer_$originalId##", "##answer_$id##", $content);
            $input[$field] = $content;
         }
      }

      // escape text fields
      foreach ($taggableFields as $key) {
         $input[$key] = $DB->escape($input[$key]);
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
         throw new ImportFailureException('failed to add or update the item');
      }

      // add the target to the linker
      $linker->addObject($originalId, $item);

      if (isset($input['_actors'])) {
         foreach ($input['_actors'] as $actor) {
            PluginFormcreatorTargetChange_Actor::import($linker, $actor, $itemId);
         }
      }

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
      echo '<form name="form_target" method="post" action="' . self::getFormURL() . '">';

      // change information: title, template...
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="4">' . _n('Target change', 'Target changes', 1, 'formcreator') . '</th></tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Change title', 'formcreator') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td colspan="3"><input type="text" name="name" style="width:704px;" value="' . $this->fields['name'] . '"></td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Description') . ' <span style="color:red;">*</span></strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="content" style="width:700px;" rows="15">' . $this->fields['content'] . '</textarea>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Impacts') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="impactcontent" style="width:700px;" rows="15">' . $this->fields['impactcontent'] . '</textarea>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Control list') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="controlistcontent" style="width:700px;" rows="15">' . $this->fields['controlistcontent'] . '</textarea>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Deployment plan') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="rolloutplancontent" style="width:700px;" rows="15">' . $this->fields['rolloutplancontent'] . '</textarea>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td><strong>' . __('Backup plan') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="backoutplancontent" style="width:700px;" rows="15">' . $this->fields['backoutplancontent'] . '</textarea>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line1">';
      echo '<td><strong>' . __('Checklist') . ' </strong></td>';
      echo '<td colspan="3">';
      echo '<textarea name="checklistcontent" style="width:700px;" rows="15">' . $this->fields['checklistcontent'] . '</textarea>';
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

      echo '</table>';

      // Buttons
      echo '<table class="tab_cadre_fixe">';

      echo '<tr class="line1">';
      echo '<td colspan="5" class="center">';
      echo '<input type="reset" name="reset" class="submit_button" value="' . __('Cancel', 'formcreator') . '"
               onclick="document.location = \'form.form.php?id=' . $this->fields['plugin_formcreator_forms_id'] . '\'" /> &nbsp; ';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
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
         // - name is required
         if (empty($input['name'])) {
            Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }

         // - content is required
         if (empty($input['content'])) {
            Session::addMessageAfterRedirect(__('The description cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }

         $input['content'] = Html::entity_decode_deep($input['content']);

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
               $input['destination_entity_value'] = 'NULL';
               break;
         }

         switch ($input['urgency_rule']) {
            case PluginFormcreatorTargetBase::URGENCY_RULE_ANSWER:
               $input['urgency_question'] = $input['_urgency_question'];
               break;
            case PluginFormcreatorTargetBase::URGENCY_RULE_SPECIFIC:
               $input['urgency_question'] = $input['_urgency_specific'];
               break;
            default:
               $input['urgency_question'] = '0';
         }

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

      return true;
   }

   /**
    * Save form data to the target
    *
    * @param  PluginFormcreatorFormAnswer $formanswer    Answers previously saved
    *
    * @return Change|false generated change
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
      $changeFields = [
         'name',
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
            true
         );

         $data[$changeField] = $formanswer->parseTags($data[$changeField]);
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
         return false;
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
}
