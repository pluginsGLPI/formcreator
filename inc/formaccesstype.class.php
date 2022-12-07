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

 use Glpi\Application\View\TemplateRenderer;

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
      $item->initForm($item->getID());
      TemplateRenderer::getInstance()->display('@formcreator/pages/form_accesstype.html.twig', [
         'item' => $item,
         'params' => [
            'target' => Toolbox::getItemTypeFormURL(__CLASS__),
            'candel' => false,
         ],
      ]);
   }

   /**
    * Show the access type options which varies depending on the access type
    *
    * @param CommonDBTM $form
    * @return void
    */
   public static function showAccessTypeOption(CommonDBTM $form): void {
      switch ($form->fields['access_rights']) {
         case PluginFormcreatorForm::ACCESS_PUBLIC:
             PluginFormcreatorFormAccessType::showPublicAccessTypeOptions($form);
             break;

         case PluginFormcreatorForm::ACCESS_RESTRICTED:
             PluginFormcreatorFormAccessType::showRestrictedAccessTypeOptions($form);
             break;
      }
   }

   public static function showPublicAccessTypeOptions(CommonDBTM $item) {
      TemplateRenderer::getInstance()->display('@formcreator/pages/form_accesstype.public.html.twig', [
         'item' => $item,
         'params' => [
            'target' => Toolbox::getItemTypeFormURL(__CLASS__),
            'candel' => false,
         ],
      ]);

      return true;
   }

   public static function showRestrictedAccessTypeOptions(CommonDBTM $item) {
      TemplateRenderer::getInstance()->display('@formcreator/pages/form_accesstype.restricted.html.twig', [
         'item' => $item,
         'params' => [
            'target' => Toolbox::getItemTypeFormURL(__CLASS__),
            'candel' => false,
         ],
      ]);

      return true;
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
