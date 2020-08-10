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

class PluginFormcreatorForm_Profile extends CommonDBRelation implements PluginFormcreatorExportableInterface
{
   use PluginFormcreatorExportable;

   static public $itemtype_1 = PluginFormcreatorForm::class;
   static public $items_id_1 = 'plugin_formcreator_forms_id';
   static public $itemtype_2 = Profile::class;
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
      switch (get_class($item)) {
         case PluginFormcreatorForm::class:
            static::showForForm($item, $withtemplate);
            break;
      }
   }

   public static function showForForm(CommonDBTM $item, $withtemplate = '') {
      global $DB, $CFG_GLPI;

      echo "<form name='form_profiles_form' id='form_profiles_form'
             method='post' action=' ";
      echo Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<table class    ='tab_cadre_fixe'>";

      echo '<tr><th colspan="2">'._n('Access type', 'Access types', 1, 'formcreator').'</th>';
      echo '</tr>';
      echo '<td>';
      Dropdown::showFromArray(
         'access_rights',
         PluginFormcreatorForm::getEnumAccessType(),
         [
            'value' => (isset($item->fields["access_rights"])) ? $item->fields["access_rights"] : 1,
         ]
      );
      echo '</td>';
      echo '<td>'.__('Link to the form', 'formcreator').': ';
      if ($item->fields['is_active']) {
         $baseUrl = parse_url($CFG_GLPI['url_base']);
         $baseUrl = $baseUrl['scheme'] . '://' . $baseUrl['host'] . ':' . $baseUrl['port'];
         $form_url = $baseUrl . FORMCREATOR_ROOTDOC . '/front/formdisplay.php?id='.$item->getID();
         echo '<a href="'.$form_url.'">'.$form_url.'</a>&nbsp;';
         echo '<a href="mailto:?subject='.$item->getName().'&body='.$form_url.'" target="_blank">';
         echo '<img src="'.FORMCREATOR_ROOTDOC.'/pics/email.png" />';
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
               new QueryExpression("IF(`$formProfileTable`.`profiles_id` IS NOT NULL, 1, 0) AS `is_enabled`")
            ],
            'FROM' => $profileTable,
            'LEFT JOIN' => [
               $formProfileTable => [
                  'FKEY' => [
                     $profileTable     => 'id',
                     $formProfileTable => 'profiles_id',
                     [
                        'AND' => [
                           "$formProfileTable.$formFk" => $item->getID()
                        ]
                     ]
                  ]
               ]
            ],
         ]);
         foreach ($result as $row) {
            $checked = $row['is_enabled'] != '0' ? ' checked' : '';
            echo '<tr><td colspan="2">';
            echo '<input type="checkbox" name="profiles_id[]" value="'.$row['id'].'" '.$checked.'> ';
            echo '<label>' . $row['name']. '</label>';
            echo '</td></tr>';
         }
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      echo '<tr>';
         echo '<td class="center" colspan="2">';
            echo Html::hidden('profiles_id[]', ['value' => '0']);
            echo Html::hidden($formFk, ['value' => $item->fields['id']]);
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
      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk] = $containerId;
      $item = new self();
      // Find an existing form to update, only if an UUID is available
      $itemId = false;
      /** @var string $idKey key to use as ID (id or uuid) */
      $idKey = 'id';
      if (isset($input['uuid'])) {
         // Try to find an existing item to update
         $idKey = 'uuid';
         $itemId = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }

      // Set the profile of the form_profile
      $profile = new Profile;
      $formFk  = PluginFormcreatorForm::getForeignKeyField();
      if (!plugin_formcreator_getFromDBByField($profile, 'name', $input['_profile'])) {
         // Profile not found, lets ignore this import
         return true;
      }
      $input[Profile::getForeignKeyField()] = $profile->getID();

      // Add or update the form_profile
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

      // add the form_profile to the linker
      $linker->addObject($originalId, $item);

      return $itemId;
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

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
   {
      $keepCriteria = [
         self::$items_id_1 => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }
}
