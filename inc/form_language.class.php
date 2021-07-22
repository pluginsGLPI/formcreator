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
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use GlpiPlugin\Formcreator\Exception\ImportFailureException;
use GlpiPlugin\Formcreator\Exception\ExportFailureException;

class PluginFormcreatorForm_Language extends CommonDBChild
implements PluginFormcreatorExportableInterface
{
   static public $itemtype = PluginFormcreatorForm::class;
   static public $items_id = 'plugin_formcreator_forms_id';

   static $rightname = 'entity';

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Form language', 'Form languages', $nb, 'formcreator');
   }

   public function defineTabs($options = []) {
      $tabs = [];
      $this->addDefaultFormTab($tabs);
      $this->addStandardTab(__CLASS__, $tabs, $options);
      return $tabs;
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginFormcreatorForm::class:
            return self::getTypeName(Session::getPluralNumber());
            break;
         case __CLASS__:
            $nb = 0;
            return self::createTabEntry(
               _n('Translation', 'Translations', $nb, 'formcreator'),
               $nb
            );
            break;
      }
      return '';
   }

   /**
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Form Item)
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch (get_class($item)) {
         case PluginFormcreatorForm::class:
            self::showForForm($item, $withtemplate);
            break;
         case __CLASS__:
            /** @var self $item */
            switch ($tabnum) {
               case 0:
                  break;
               case 1:
                  $item->showTranslations();
                  break;
            }
      }
   }

   public function prepareInputForAdd($input) {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      if (!isset($input['name'])) {
         Session::addMessageAfterRedirect(
            __('The name cannot be empty!', 'formcreator'),
            false,
            ERROR
         );
         return [];
      }
      if (!isset($input[$formFk])) {
         Session::addMessageAfterRedirect(
            __('The language must be associated to a form!', 'formcreator'),
            false,
            ERROR
         );
         return [];
      }

      // generate a unique id
      if (!isset($input['uuid'])
            || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public function prepareInputForUpdate($input) {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      unset($input[$formFk]);
      unset($input['name']);

      // generate a uniq id
      if (!isset($input['uuid'])
            || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public function post_updateItem($history = 1) {
      global $TRANSLATE;

      // Reset cache for the edited translations
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $domain = PluginFormcreatorForm::getTranslationDomain($this->fields['name'], $this->fields[$formFk]);
      $TRANSLATE->clearCache($domain, $this->fields['name']);
   }

   public function pre_deleteItem() {
      // Delete translation file
      $file = PluginFormcreatorForm::getTranslationFile(
         $this->fields[PluginFormcreatorForm::getForeignKeyField()],
         $this->fields['name']
      );
      if (file_exists($file)) {
         return unlink($file);
      }
      return true;
   }

   public function massDeleteTranslations($post) {
      global $TRANSLATE;

      $form = new PluginFormcreatorForm();
      if (!$form->getFromDB($this->fields['plugin_formcreator_forms_id'])) {
         return;
      }
      $translations = $form->getTranslations($this->fields['name']);
      foreach ($post['plugin_formcreator_translation'] as $translationId => $checked) {
         if ($checked != '1') {
            continue;
         }
         $translated = $form->getTranslatableStrings([
            'id'       => $translationId,
            'language' => $this->fields['name'],
         ]);
         $original = $translated[$translated['id'][$translationId]][$translationId];
         unset($translations[$original]);
      }
      $form->setTranslations($this->fields['name'], $translations);
      $TRANSLATE->clearCache('formcreator', $this->fields['name']);
   }

   public function showForm($ID, $options = []) {
      if (!$this->isNewID($ID)) {
         if (!$this->getFromDB($ID)) {
            return false;
         }
         $item = new PluginFormcreatorForm();
         $item->getFromDB($this->fields[PluginFormcreatorForm::getForeignKeyField()]);
      } else {
         $this->getEmpty();
         if (!isset($options['parent']) || empty($options['parent'])) {
            return false;
         }
         $this->fields[PluginFormcreatorForm::getForeignKeyField()] = $options['parent']->getID();
         $item = $options['parent'];
      }

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo '<tr class="tab_bg_1">';
      echo '<td><strong>' . __('Name') . ' <span class="red">*</span></strong></td>';
      $used = [];
      $rows = $this->find([PluginFormcreatorForm::getForeignKeyField() => $item->getID()]);
      foreach ($rows as $row) {
         $used[$row['name']] = $row['name'];
      }
      echo '<td>' . Dropdown::showLanguages('name', [
         'required' => true,
         'display'  => false,
         'value'    => $this->isNewID($ID) ? $_SESSION['glpilanguage'] : $this->fields['name'],
         'used'     => $this->isNewID($ID) ? $used : [],
         'disabled' => !$this->isNewID($ID)
      ]) . '</td>';
      echo "</tr></tr>";
      echo '<td><strong>' . __('Comment') . '</strong></td>';
      echo '<td>';
      Html::textarea([
         'name' => 'comment',
         'value' => $this->fields['comment'],
      ]);
      echo '</td>';
      echo "</tr></tr>";
      echo '<td>';
      echo Html::hidden(PluginFormcreatorForm::getForeignKeyField(), ['value' => $item->getID()]);
      echo '</td>';
      echo '</tr>';

      $this->showFormButtons($options);
      return true;
   }

   public function showNewTranslation($options = []) {
      $form = new PluginFormcreatorForm();
      $form->getFromDB($this->fields[PluginFormcreatorForm::getForeignKeyField()]);

      echo '<div data-itemtype="PluginFormcreatorForm_Language" data-id="' . $this->getID() . '">';
      $options['formtitle'] = __('Add a translation', 'formcreator');
      $options['target'] = 'javascript:plugin_formcreator.saveNewTranslation(this);';

      $this->initForm($this->getID(), $options);
      //$this->showFormHeader($options);
      echo '<form name="plugin_formcreator_translation" onsubmit="plugin_formcreator.saveNewTranslation(this); return false;" >';
      echo "<div class='spaced' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe' id='mainformtable'>";

      echo "<tr class='tab_bg_1'><td>";
      echo Html::hidden('name', ['value' => $this->fields['name']]);
      echo "</td><td width='50%'>&nbsp;</td></tr>";

      echo '<tr id="plugin_formcreator_editTranslation">';

      echo PluginFormcreatorTranslation::getEditorFieldsHtml($this);
      echo '</tr>';
      echo "<tr class='tab_bg_1'><td>";
      echo "</td><td width='50%'>&nbsp;</td></tr>";

      echo '<tr class="tab_bg_2">'
      . '<td class="center" colspan="4">'
      . Html::hidden(self::getForeignKeyField(), ['value' => $this->getID()])
      . '<button type="submit" value="<i class=&quot;fas fa-save&quot;></i>&nbsp;Save" name="save_translation" class="vsubmit">'
      . '<i class="fas fa-save"></i>&nbsp;Save'
      . '</button>'
      . '</td>'
      . '</tr>'
      . '</table>';
      Html::closeForm();
      echo '</div>';
   }

   public function showTranslationEntry($input) : void {
      $options['formtitle'] = __('Add a translation', 'formcreator');
      $this->initForm($this->getID(), $options);
      echo '<form name="plugin_formcreator_translation" onsubmit="plugin_formcreator.saveNewTranslation(this); return false;" >';
      echo "<div class='spaced' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe' id='mainformtable'>";

      echo PluginFormcreatorTranslation::getEditorFieldsHtml($this, $input['plugin_formcreator_translations_id']);

      echo '<tr class="tab_bg_2">'
      . '<td class="center" colspan="4">'
      . Html::hidden(self::getForeignKeyField(), ['value' => $this->getID()])
      . '<button type="submit" value="<i class=&quot;fas fa-save&quot;></i>&nbsp;Save" name="save_translation" class="vsubmit">'
      . '<i class="fas fa-save"></i>&nbsp;Save'
      . '</button>'
      . '</td>'
      . '</tr>'
      . '</table>';
      Html::closeForm();
   }

   public function showTranslations($options = []) {
      $form = new PluginFormcreatorForm();
      $form->getFromDB($this->fields[PluginFormcreatorForm::getForeignKeyField()]);
      echo '<div data-itemtype="PluginFormcreatorForm_Language" data-id="' . $this->getID() . '">';
      echo '<div>';
      echo '<button'
      . ' name="new_override"'
      . ' class="vsubmit"'
      . ' value="<i class=\'fas fa-plus\'></i>&nbsp;' . __('New translation', 'formcreator') . '"'
      .' onclick="' . 'plugin_formcreator.newTranslation(' . $this->getID() . ')'
      . '"><i class=\'fas fa-plus\'></i>&nbsp;' . __('New translation', 'formcreator') . '</button>';
      echo '</div>';
      echo '<div class="plugin_formcreator_filter_translations">';
      echo '<input type="text" placeholder="'.__("Filter list", 'formcreator').'">';
      echo '</div>';

      $translations = $form->getTranslations($this->fields['name']);
      if (count($translations) < 1) {
         echo '<p>' . __('No translation found', 'formcreator') . '</p>';
         return;
      }

      $options['formtitle'] = false;
      $options['formoptions'] = 'onsubmit="' . Html::getConfirmationOnActionScript(__('Do you want to delete the selected items?', 'formcreator')) . '"';
      $this->showFormHeader($options);
      $this->initForm($this->getID());
      $rand = mt_rand();

      if (count($translations) > 15) {
         // Massive actions
         echo '<table class="tab_cadre_fixe tab_glpi">'
         . '<tr>'
         . '<td width="30px"><img src="../../../pics/arrow-left-top.png" alt=""></td>'
         . '<td width="100%" class="left">'
         . Html::input('delete', ['class' => 'vsubmit', 'type' => 'submit', 'value' => __('Delete', 'formcreator')])
         . '</td>'
         . '</tr>'
         . '</table>';
      }

      echo '<table class="tab_cadrehov tab_cadre_fixe translation_list" id="translation_list' . $rand . '">';
      echo '<thead>';
      $header = '<tr>';
      $header.= '<th>' . Html::getCheckAllAsCheckbox("translation_list$rand", $rand) . '</th>';
      $header.= '<th>' . __('Original string', 'formcreator') . '</th>';
      $header.= '<th>' . __('Translation', 'formcreator') . '</th>';
      $header.= '</tr>';
      echo $header;
      echo '</thead>';

      echo '<tbody>';
      foreach ($translations as $original => $translated) {
         $id = PluginFormcreatorTranslation::getTranslatableStringId($original);
         echo '<tr data-itemtype="PluginFormcreatorTranslation" data-id="' . $id . '">';
         echo '<td>'
         . Html::getCheckbox([
            'name'  => 'plugin_formcreator_translation[' . $id . ']',
            'value' => 1,
         ])
         . '</td>';
         echo "<td><a href='#' onclick='plugin_formcreator.showUpdateTranslationForm(this)'>" . $original . "</a></td>";
         echo "<td><a href='#' onclick='plugin_formcreator.showUpdateTranslationForm(this)'>" . $translated . "</a></td>";
         echo '</tr>';
      }
      echo '</tbody>';
      echo '</table>';

      if (count($translations) > 0) {
         // Massive actions
         echo '<table class="tab_cadre_fixe tab_glpi">'
         . '<tr>'
         . '<td width="30px"><img src="../../../pics/arrow-left.png" alt=""></td>'
         . '<td width="100%" class="left">'
         . Html::input('delete', ['class' => 'vsubmit', 'type' => 'submit', 'value' => __('Delete', 'formcreator')])
         . '</td>'
         . '</tr>'
         . '</table>';
      }
      echo Html::hidden('id', ['value' => $this->getID()]);
      $this->showFormButtons([
         'canedit' => false,
         'candel'  => false,
      ]);
      echo '</div>';
   }

   public static function showForForm(CommonDBTM $item, $withtemplate = '') {
      global $DB;

      $rand    = mt_rand();
      $canedit = $item->can($item->getID(), UPDATE);

      if ($canedit) {
         $formId = $item->getID();
         $url = self::getFormURL();
         echo "<div class='center'>" .
            "<a class='vsubmit' href='#' onclick='plugin_formcreator.createLanguage($formId);'>" . __('Add a new language') .
            "</a></div><br>";
         echo '<div id="plugin_formcreator_formLanguage"></div>';
      }

      $iterator = $DB->request([
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_formcreator_forms_id'  => $item->getID(),
         ],
         'ORDER'  => ['name ASC']
      ]);
      if (count($iterator) < 1) {
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'>";
         echo "<th class='b'>" . __('No translation found', 'formcreator') . "</th></tr>";
         echo "</table>";
         return true;
      }

      if ($canedit) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = ['container' => 'mass' . __CLASS__ . $rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixehov translation_list'><tr class='tab_bg_2'>";
      // table header
      echo "<tr>";
      if ($canedit) {
         echo "<th width='10'>";
         echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
         echo "</th>";
      }
      echo "<th>" . __("Language") . "</th>";
      echo "</tr>";

      while ($data = $iterator->next()) {
         $id = $data['id'];
         $onhover = '';
         if ($canedit) {
            $onhover = "style='cursor:pointer'";
         }
         echo "<tr class='tab_bg_1'>";
         if ($canedit) {
            echo "<td class='center'>";
            Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
            echo "</td>";
         }

         $url = PluginFormcreatorForm_Language::getFormURLWithID($id);
         echo "<td $onhover>";
         echo '<a href="' . $url . '">';
         echo Dropdown::getLanguageName($data['name']);
         echo '</a>';
         echo "</td>";
         echo "</tr>";
      }
      echo "<tr>";

      // table footer
      if ($canedit) {
         echo "<th width='10'>";
         echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
         echo "</th>";
      }
      echo "<th>" . __("Language") . "</th>";
      echo "</tr>";
      echo "</table>";
      if ($canedit) {
         echo '<div id="plugin_formcreator_viewtranslation" style="display: none"><img class="plugin_formcreator_spinner" src="../../../pics/spinner.48.gif"></div>';

         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      return true;
   }

   public function getForbiddenStandardMassiveAction() {
      return [
         'update', 'clone', 'add_note',
      ];
   }

   public static function countItemsToImport(array $input) : int {
      return 1;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) : bool {
      $keepCriteria = [
         self::$items_id => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      // restore key and FK
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk]        = $containerId;

      $item = new self();
      // Find an existing section to update, only if an UUID is available
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

      // Escape text fields
      foreach (['name'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // Add or update form language
      $originalId = $input[$idKey];
      if ($itemId !== false) {
         $input['id'] = $itemId;
         $item->update($input);
      } else {
         unset($input['id']);
         $item->useAutomaticOrdering = false;
         $itemId = $item->add($input);
      }
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the form language to the linker
      $linker->addObject($originalId, $item);

      $form = new PluginFormcreatorForm();
      $form->getFromDB($input[$formFk]);
      $translations = $input['_strings'] ?? [];
      $form->setTranslations($input['name'], $translations);

      return $itemId;
   }

   public function export(bool $remove_uuid = false) : array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
      }

      $export = $this->fields;

      // remove key and fk
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      unset($export[$formFk]);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($export[$idToRemove]);

      $form = new PluginFormcreatorForm();
      $form->getFromDB($this->fields[$formFk]);
      $export['_strings'] = $form->getTranslations($this->fields['name']);

      return $export;
   }
}
