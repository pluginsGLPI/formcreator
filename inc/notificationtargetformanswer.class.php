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

class PluginFormcreatorNotificationTargetFormAnswer extends NotificationTarget
{
   const AUTHOR   = 101;
   const APPROVER = 102;

   public function getEvents() {
      $events =  [
         'plugin_formcreator_form_created'    => __('The form as been saved', 'formcreator'),
         'plugin_formcreator_need_validation' => __('A form need to be validate', 'formcreator'),
         'plugin_formcreator_refused'         => __('The form is refused', 'formcreator'),
         'plugin_formcreator_accepted'        => __('The form is accepted', 'formcreator'),
         'plugin_formcreator_deleted'         => __('The form is deleted', 'formcreator'),
      ];
      return $events;
   }

   public function addDataForTemplate($event, $options = []) {
      global $CFG_GLPI;

      $form = new PluginFormcreatorForm();
      $form->getFromDB($this->obj->fields['plugin_formcreator_forms_id']);
      $link = $CFG_GLPI['url_base'];
      $link .= FORMCREATOR_ROOTDOC . '/front/formanswer.form.php?id=' . $this->obj->getID();

      $requester = new User();
      $requester->getFromDB($this->obj->fields['requester_id']);
      $validator = new User();
      $validator->getFromDB($this->obj->fields['users_id_validator']);

      $this->data['##formcreator.form_id##']            = $form->getID();
      $this->data['##formcreator.form_name##']          = $form->fields['name'];
      $this->data['##formcreator.form_requester##']     = $requester->getName();
      $this->data['##formcreator.form_validator##']     = $validator->getName();
      $this->data['##formcreator.form_creation_date##'] = Html::convDateTime($this->obj->fields['request_date']);
      $this->data['##formcreator.form_full_answers##']  = $this->obj->parseTags($this->obj->getFullForm(), null, true);
      $this->data['##formcreator.validation_comment##'] = $this->obj->fields['comment'];
      $this->data['##formcreator.validation_link##']    = $link;
      $this->data['##formcreator.request_id##']         = $this->obj->fields['id'];

      $targetList = [];
      foreach ($this->obj->targetList as $target) {
         /** @var CommonDBTM $target */
         $typeName = $target->getTypeName(1);
         $typeId = $target->getID();
         $targetList[] = "$typeName $typeId";
      }
      $this->data['##formcreator.targets##']         = implode(', ', $targetList);

      $this->data['##lang.formcreator.form_id##']            = __('Form #', 'formcreator');
      $this->data['##lang.formcreator.form_name##']          = __('Form name', 'formcreator');
      $this->data['##lang.formcreator.form_requester##']     = __('Requester', 'formcreator');
      $this->data['##lang.formcreator.form_validator##']     = __('Validator', 'formcreator');
      $this->data['##lang.formcreator.form_creation_date##'] = __('Creation date');
      $this->data['##lang.formcreator.form_full_answers##']  = __('Full form answers', 'formcreator');
      $this->data['##lang.formcreator.validation_comment##'] = __('Refused comment', 'formcreator');
      $this->data['##lang.formcreator.validation_link##']    = __('Validation link', 'formcreator');
      $this->data['##lang.formcreator.request_id##']         = __('Request #', 'formcreator');
      $this->data['##lang.formcreator.targets##']            = __('List of generated targets', 'formcreator');

   }

   public function getTags() {
      $tags = [
         'formcreator.form_id'            => __('Form #', 'formcreator'),
         'formcreator.form_name'          => __('Form name', 'formcreator'),
         'formcreator.form_requester'     => __('Requester', 'formcreator'),
         'formcreator.form_validator'     => __('Validator', 'formcreator'),
         'formcreator.form_creation_date' => __('Creation date'),
         'formcreator.form_full_answers'  => __('Full form answers', 'formcreator'),
         'formcreator.validation_comment' => __('Refused comment', 'formcreator'),
         'formcreator.validation_link'    => __('Validation link', 'formcreator'),
         'formcreator.request_id'         => __('Request #', 'formcreator'),
         'formcreator.targets'            => __('List of generated targets', 'formcreator'),
      ];

      foreach ($tags as $tag => $label) {
         $this->addTagToList(['tag'    => $tag,
            'label'  => $label,
            'value'  => true,
            'events' => NotificationTarget::TAG_FOR_ALL_EVENTS]
         );
      }
   }

   public function addAdditionalTargets($event = '') {
      $this->addTarget(self::AUTHOR, __('Author'));
      $this->addTarget(self::APPROVER, __('Approver'));
   }

   public function addSpecificTargets($data, $options) {
      switch ($data['items_id']) {
         case self::AUTHOR :
            $this->addUserByField('requester_id', true);
            break;
         case self::APPROVER :
            $form = new PluginFormcreatorForm();
            $form->getFromDB($this->obj->fields['plugin_formcreator_forms_id']);
            if ($form->fields['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_USER) {
               $this->addUserByField('users_id_validator', true);
            } else if ($form->fields['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_GROUP) {
               $this->addForGroup(0, $this->obj->fields['groups_id_validator']);
            }
            break;
      }
   }
}
