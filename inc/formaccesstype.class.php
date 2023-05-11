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
 * @copyright Copyright © 2011 - 2022 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorFormAccessType extends CommonGLPI
{
   public static function getTypeName($nb = 0) {
      return _n('Access type', 'Access types', $nb, 'formcreator');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      return self::getTypeName(2);
   }

   public static function displayTabContentForItem(
      CommonGLPI $item,
      $tabnum = 1,
      $withtemplate = 0
   ) {
      if ($item instanceof PluginFormcreatorForm) {
         static::showForForm($item, $withtemplate);
      }
   }

   public static function showForForm(CommonDBTM $item, $withtemplate = '') {
      global $CFG_GLPI;

      echo "<form name='form_profiles_form' id='form_profiles_form'
             method='post' action='";
      echo Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="2">'._n('Access type', 'Access types', 1, 'formcreator').'</th>';
      echo '</tr>';

      // Access type
      echo '<tr>';
      echo '<td>';
      Dropdown::showFromArray(
         'access_rights',
         PluginFormcreatorForm::getEnumAccessType(),
         [
            'value' => $item->fields['access_rights'] ?? PluginFormcreatorForm::ACCESS_PRIVATE,
            'on_change' => 'plugin_formcreator.showMassiveRestrictions(this)',
         ]
      );
      echo '</td>';
      echo '<td>'.__('Link to the form', 'formcreator').': ';
      if ($item->fields['is_active']) {
         $parsedBaseUrl = parse_url($CFG_GLPI['url_base']);
         $baseUrl = $parsedBaseUrl['scheme'] . '://' . $parsedBaseUrl['host'];
         if (isset($parsedBaseUrl['port'])) {
            $baseUrl .= ':' . $parsedBaseUrl['port'];
         }
         $form_url = $baseUrl . FORMCREATOR_ROOTDOC . '/front/formdisplay.php?id='.$item->getID();
         echo '<a href="'.$form_url.'">'.$form_url.'</a>&nbsp;';
         echo '<a href="mailto:?subject='.$item->getName().'&body='.$form_url.'" target="_blank">';
         echo '<i class="fas fa-envelope"><i/>';
         echo '</a>';
      } else {
         echo __('Please activate the form to view the link', 'formcreator');
      }
      echo '</td>';
      echo '</tr>';

      // Captcha
      $is_visible = $item->fields["access_rights"] == PluginFormcreatorForm::ACCESS_PUBLIC;
      echo '<tr id="plugin_formcreator_captcha" style="display: ' . ($is_visible ? 'block' : 'none') . '">';
      echo '<td>' . __('Enable captcha', 'formcreator') . '</td>';
      echo '<td>';
      Dropdown::showYesNo('is_captcha_enabled', $item->fields['is_captcha_enabled']);
      echo '</td>';
      echo '</tr>';

      // Access restrictions
      $is_visible = $item->fields["access_rights"] == PluginFormcreatorForm::ACCESS_RESTRICTED;
      echo '<tr id="plugin_formcreator_restrictions_head" style="display: ' . ($is_visible ? 'block' : 'none') . '">';
      echo '<th colspan="2">' . self::getTypeName(2) . '</th>';
      echo '</tr>';
      echo '<tr id="plugin_formcreator_restrictions" style="display: ' . ($is_visible ? 'block' : 'none') . '">';
      echo '<td><label>' . __('Restricted to') . '</label></td>';
      echo '<td class="restricted-form">';
      echo PluginFormcreatorRestrictedFormDropdown::show('restrictions', [
         'users_id'    => $item->fields['users'] ?? [],
         'groups_id'   => $item->fields['groups'] ?? [],
         'profiles_id' => $item->fields['profiles'] ?? [],
      ]);
      echo '</td>';
      echo '</tr>';

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      echo '<tr>';
      echo '<td class="center" colspan="2">';
      echo Html::hidden($formFk, ['value' => $item->fields['id']]);
      echo '<input type="submit" class="btn btn-primary me-2" name="update" value="'.__('Save').'" class="submit" />';
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();
   }

   /**
    * Get the SQL criteria to filter restricted forms
    *
    * @return array
    */
   public static function getRestrictedFormListCriteria(): array {
      $form_table = PluginFormcreatorForm::getTable();

      return [
         'OR' => [
            // OK if at least one user match
            ["$form_table.id" => PluginFormcreatorForm_User::getListCriteriaSubQuery()],

            // OK if at least one group match
            ["$form_table.id" => PluginFormcreatorForm_Group::getListCriteriaSubQuery()],

            // OK if at least one profile match
            ["$form_table.id" => PluginFormcreatorForm_Profile::getListCriteriaSubQuery()],

            [
               // OK if all criteria are empty for this form
               'AND' => [
                  ['NOT' => ["$form_table.id" => PluginFormcreatorForm_User::getFormWithDefinedRestrictionSubQuery()]],
                  ['NOT' => ["$form_table.id" => PluginFormcreatorForm_Group::getFormWithDefinedRestrictionSubQuery()]],
                  ['NOT' => ["$form_table.id" => PluginFormcreatorForm_Profile::getFormWithDefinedRestrictionSubQuery()]],
               ]
            ]
         ]
      ];
   }

   /**
    * Check if the current user can see the given restricted form
    * The user should have access if he verify at least one restriction
    *
    * @param PluginFormcreatorForm $form The given form
    *
    * @return bool True if allowed
    */
   public static function canSeeRestrictedForm(
      PluginFormcreatorForm $form
   ): bool {
      // Check access type
      $access_type = $form->fields['access_rights'];
      if ($access_type !== PluginFormcreatorForm::ACCESS_RESTRICTED) {
         $message = "This form is not restricted: $form->fields[id]";
         throw new \InvalidArgumentException($message);
      }

      // Check if the user match the "users" restrictions
      if (PluginFormcreatorForm_User::userMatchRestrictionCriteria($form)) {
         return true;
      }

      // Check if the user match the "group" restrictions
      if (PluginFormcreatorForm_Group::userMatchRestrictionCriteria($form)) {
         return true;
      }

      // Check if the user match the "profiles" restrictions
      if (PluginFormcreatorForm_Profile::userMatchRestrictionCriteria($form)) {
         return true;
      }

      // No match for users, groups or profiles
      return false;
   }
}
