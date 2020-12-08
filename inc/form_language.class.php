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

class PluginFormcreatorForm_Language extends CommonDBTM
{

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

   /**
    * Return the name of the tab for item including forms like the config page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $withtemplate
    *
    * @return String                   Name to be displayed
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginFormcreatorForm::class:
            return self::getTypeName(Session::getPluralNumber());
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
      }
   }

   public function prepareInputForAdd($input) {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      if (!isset($input[$formFk]) || !isset($input['name'])) {
         return false;
      }

      return $input;
   }

   public function prepareInputForUpdate($input) {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $translations = array_combine($input['string'], $input['translated']);
      $translations['plugin_formcreator_load_check'] = 'plugin_formcreator_load_check';
      $translations = Toolbox::stripslashes_deep($translations);
      file_put_contents(
         PluginFormcreatorForm::getTranslationFile($input[$formFk], $input['language']),
         "<?php" . PHP_EOL . "return " . var_export($translations, true) . ";"
      );

      unset($input[$formFk]);
      unset($input['name']);

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

   public function showForm($ID, $options = []) {
      if (!isset($options['parent']) || empty($options['parent'])) {
         return false;
      }
      if (!$this->isNewID($ID)) {
         if (!$this->getFromDB($ID)) {
            return false;
         }
      } else {
         $this->getEmpty();
      }

      /** @var PluginFormcreatorForm */
      $item = $options['parent'];

      $this->fields['plugin_formcreator_forms_id'] = $item->getID();

      $item->check($this->fields['plugin_formcreator_forms_id'], UPDATE);

      $options['colspan'] = 1;

      $this->showFormHeader($options);
      if ($this->isNewID($ID)) {
         echo "<tr class='tab_bg_1'>";
         echo "<td width='50%'>" . __('Language') . "</td>";
         echo "<td>";
         echo Html::hidden('plugin_formcreator_forms_id', ['value' => $item->getID()]);
            // Find existing languages for the form
         $used = [$item->fields['language'] => $item->fields['language']];
         $rows = $this->find([
            PluginFormcreatorForm::getForeignKeyField() => $item->getID(),
         ]);
         foreach ($rows as $row) {
            $used[$row['name']] = $row['name'];
         }
         Dropdown::showLanguages(
            "name",
            [
               'display_none' => false,
               'value'        => $_SESSION['glpilanguage'],
               'used'         => $used,
            ]
         );
         $this->showFormButtons($options);
         return true;
      }

      //$this->getFromDB($ID);
      echo "<tr class='tab_bg_1'>";
      echo Html::hidden('name', ['value' => $this->fields['name']]);
      //echo Dropdown::getLanguageName($this->fields['name']);
      echo "</td><td width='50%'>&nbsp;</td></tr>";

      $translationFile = PluginFormcreatorForm::getTranslationFile($item->getID(), $this->fields['name']);
      if (is_readable($translationFile)) {
         $translations = include $translationFile;
      }
      foreach ($item->getTranslatableStrings() as $type => $strings) {
         foreach ($strings as $string) {
            if (isset($translations[$string])) {
               $translatedString = $translations[$string];
            } else {
               $translatedString = '';
            }
            echo "<tr class='tab_bg_1'>";
            switch ($type) {
               case 'text':
               case 'itemlink':
                  echo "<td>" . $string . Html::hidden('string[]', ['value' => $string]) . "</td>";
                  echo "<td>" . Html::input('translated[]', ['value' => $translatedString]) . "</td>";
                  break;

               case 'string':
                  echo "<td>" . Html::entity_decode_deep($string) . Html::hidden('string[]', ['value' => $string]) . "</td>";
                  echo "<td>" . Html::textarea([
                     'name'  => 'translated[]',
                     'value' => $translatedString,
                     'enable_richtext' => true,
                     'display' => false,
                  ]) . "</td>";
            }
            echo "</tr>";
         }
      }

      // PluginFormcreatorForm_Language::dropdown([
      //    'display' => false
      // ]);
      $this->showFormButtons($options);
      return true;
   }

   public static function showForForm(CommonDBTM $item, $withtemplate = '') {
      global $DB;

      $rand    = mt_rand();
      $canedit = $item->can($item->getID(), UPDATE);

      if ($canedit) {
         $formId = $item->getID();
         echo "<div class='center'>" .
            "<a class='vsubmit' href='#' onclick='plugin_formcreator.editLanguage($formId);'>" . __('Add a new translation') .
            "</a></div><br>";
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
      echo "<table class='tab_cadre_fixehov'><tr class='tab_bg_2'>";
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
            $onhover = "style='cursor:pointer'
                        onClick=\"plugin_formcreator.editLanguage($formId, $id);\"";
         }
         echo "<tr class='tab_bg_1'>";
         if ($canedit) {
            echo "<td class='center'>";
            Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
            echo "</td>";
         }

         echo "<td $onhover>";
         echo Dropdown::getLanguageName($data['name']);
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
}
