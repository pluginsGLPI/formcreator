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

class PluginFormcreatorForm_Profile extends CommonDBRelation implements PluginFormcreatorExportableInterface
{
   static public $itemtype_1 = 'PluginFormcreatorForm';
   static public $items_id_1 = 'plugin_formcreator_forms_id';
   static public $itemtype_2 = 'Profile';
   static public $items_id_2 = 'profiles_id';

   static function getTypeName($nb = 0) {
      return _n('Access type', 'Access types', $nb, 'formcreator');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      return self::getTypeName(2);
   }

   /**
    * Prepare input data for adding the form
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
    */
   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid'])
         || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $DB, $CFG_GLPI;

      echo "<form name='notificationtargets_form' id='notificationtargets_form'
             method='post' action=' ";
      echo Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<table class    ='tab_cadre_fixe'>";

      echo '<tr><th colspan="2">'._n('Access type', 'Access types', 1, 'formcreator').'</th>';
      echo '</tr>';
      echo '<td>';
      Dropdown::showFromArray(
         'access_rights',
         [
            PluginFormcreatorForm::ACCESS_PUBLIC     => __('Public access', 'formcreator'),
            PluginFormcreatorForm::ACCESS_PRIVATE    => __('Private access', 'formcreator'),
            PluginFormcreatorForm::ACCESS_RESTRICTED => __('Restricted access', 'formcreator'),
         ],
         [
            'value' => (isset($item->fields["access_rights"])) ? $item->fields["access_rights"] : 1,
         ]
      );
      echo '</td>';
      echo '<td>'.__('Link to the form', 'formcreator').': ';
      if ($item->fields['is_active']) {
         $form_url = $CFG_GLPI['url_base'].'/plugins/formcreator/front/formdisplay.php?id='.$item->getID();
         echo '<a href="'.$form_url.'">'.$form_url.'</a>&nbsp;';
         echo '<a href="mailto:?subject='.$item->getName().'&body='.$form_url.'" target="_blank">';
         echo '<img src="'.$CFG_GLPI['root_doc'].'/plugins/formcreator/pics/email.png" />';
         echo '</a>';
      } else {
         echo __('Please active the form to view the link', 'formcreator');
      }
      echo '</td>';
      echo "</tr>";

      if ($item->fields["access_rights"] == PluginFormcreatorForm::ACCESS_RESTRICTED) {
         echo '<tr><th colspan="2">'.self::getTypeName(2).'</th></tr>';

         $formProfileTable = getTableForItemType(__CLASS__);
         $profileTable     = getTableForItemType(Profile::class);
         $formFk = PluginFormcreatorForm::getForeignKeyField();
         $result = $DB->request([
            'SELECT' => [
               $profileTable     => ['id', 'name'],
               $formProfileTable => ['profiles_id'],
            ],
            'LEFT JOIN' => [
               $formProfileTable => [
                  'FKEY' => [
                     $profileTable     => 'id',
                     $formProfileTable => 'profiles_id'
                  ]
               ]
            ],
            'WHERE' => [
               "$formProfileTable.$formFk" => $item->getID(),
            ],
         ]);
         foreach ($result as $row) {
            $checked = $row['profile'] !== null ? ' checked' : '';
            echo '<tr><td colspan="2"><label>';
            echo '<input type="checkbox" name="profiles_id[]" value="'.$row['id'].'" '.$checked.'> ';
            echo $row['name'];
            echo '</label></td></tr>';
         }
      }

      echo '<tr>';
         echo '<td class="center" colspan="2">';
            echo '<input type="hidden" name="profiles_id[]" value="0" />';
            echo '<input type="hidden" name="form_id" value="'.$item->fields['id'].'" />';
            echo '<input type="submit" name="update" value="'.__('Save').'" class="submit" />';
         echo "</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();
   }

   /**
    * Import a form's profile into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  integer $forms_id  id of the parent form
    * @param  array   $form_profile the validator data (match the validator table)
    * @return integer|false the form_Profile ID or false on error
    */
   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      $item = new self();
      // Find an existing form_profile to update, only if an UUID is available
      if (isset($input['uuid'])) {
         $formProfileId = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }

      // Set the profile of the form_profile
      $profile = new Profile;
      $formFk  = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk] = $containerId;
      if (!plugin_formcreator_getFromDBByField($profile, 'name', $input['_profile'])) {
         // Profile not found, lets ignore this import
         return true;
      }
      $input[Profile::getForeignKeyField()] = $profile->getID();

      // Add or update the form_profile
      if (!$item->isNewItem()) {
         $input['id'] = $formProfileId;
         $item->update($input);
      } else {
         $formProfileId = $item->add($input);
      }
      if ($formProfileId === false) {
         throw new ImportFailureException();
      }

      // add the form to the linker
      $originalId = $input['id'];
      if (isset($input['uuid'])) {
         $originalId = $input['uuid'];
      }
      $linker->addObject($originalId, $item);

      return $formProfileId;
   }

   /**
    * Export in an array all the data of the current instanciated form_profile
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if ($this->isNewItem()) {
         return false;
      }

      $form_profile = $this->fields;

      // export fk
      $profile = new Profile;
      if ($profile->getFromDB($form_profile['profiles_id'])) {
         $form_profile['_profile'] = $profile->fields['name'];
      }

      // remove fk
      unset($form_profile['profiles_id'],
            $form_profile['plugin_formcreator_forms_id']);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($form_profile[$idToRemove]);

      return $form_profile;
   }
}
