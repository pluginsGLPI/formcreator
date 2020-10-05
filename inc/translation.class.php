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

class PluginFormcreatorTranslation extends CommonDBTM
{

   static $rightname = 'entity';

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
    public static function getTypeName($nb = 0) {
      return _n('Translation', 'Translations', $nb, 'formcreator');
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

   public function prepareInputForAdd($input)
   {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      if (!isset($input[$formFk]) || !isset($input['language'])) {
         return false;
      }

      return $input;
   }

   public function prepareInputForUpdate($input)
   {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $translations = array_combine($input['string'], $input['translated']);
      $translations['plugin_formcreator_load_check'] = 'plugin_formcreator_load_check';
      $translations = Toolbox::stripslashes_deep($translations);
      file_put_contents(
         PluginFormcreatorForm::getTranslationFile($input['language'], $input[$formFk]),
         "<?php" . PHP_EOL . "return " . var_export($translations, true) . ";"
      );

      unset($input[$formFk]);
      unset($input['language']);

      return $input;
   }

   public function post_updateItem($history = 1) {
      global $TRANSLATE;

      // Reset cache for the edited translations
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $domain = PluginFormcreatorForm::getTranslationDomain($this->fields['language'], $this->fields[$formFk]);
      $TRANSLATE->clearCache($domain, $this->fields['language']);
   }

   public function showForm($ID, $options = []) {
      global $CFG_GLPI;

      if (isset($options['parent']) && !empty($options['parent'])) {
         /** @var PluginFormcreatorForm */
         $item = $options['parent'];
      }
      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         $options['plugin_formcreator_forms_id'] = $item->getID();

         // Create item
         $this->check(-1, CREATE, $options);
      }

      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      echo "<td width='50%'>".__('Language')."</td>";
      echo "<td>";
      echo Html::hidden('plugin_formcreator_forms_id', ['value' => $item->getID()]);
      if ($ID > 0) {
         echo Html::hidden('language', ['value' => $this->fields['language']]);
         echo Dropdown::getLanguageName($this->fields['language']);
      } else {
         $rand = Dropdown::showLanguages(
            "language", [
               'display_none' => false,
               'value'        => $_SESSION['glpilanguage']
            ]
         );
         $params = ['language' => '__VALUE__',
                    'plugin_formcreator_forms_id' => $item->getID()
                   ];
         Ajax::updateItemOnSelectEvent("dropdown_language$rand",
                                       "span_fields",
                                       $CFG_GLPI["root_doc"]."/ajax/updateTranslationFields.php",
                                       $params);
      }
      echo "</td><td colspan='2'>&nbsp;</td></tr>";

      if ($ID > 0) {
         $translationFile = PluginFormcreatorForm::getTranslationFile($this->fields['language'], $item->getID());
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

      }

      $this->showFormButtons($options);
      return true;

   }

   public static function showForForm(CommonDBTM $item, $withtemplate = '') {
      global $DB;

      $rand    = mt_rand();
      $canedit = $item->can($item->getID(), UPDATE);

      if ($canedit) {
         $formId = $item->getID();
         echo "<div id='plugin_formcreator_viewtranslation'></div>";

         echo "<div class='center'>".
              "<a class='vsubmit' href='#' onclick='plugin_formcreator.addTranslation($formId);'>". __('Add a new translation').
              "</a></div><br>";
      }

      $iterator = $DB->request([
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'plugin_formcreator_forms_id'  => $item->getID(),
         ],
         'ORDER'  => ['language ASC']
      ]);
      if (count($iterator)) {
         if ($canedit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = ['container' => 'mass'.__CLASS__.$rand];
            Html::showMassiveActions($massiveactionparams);
         }
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixehov'><tr class='tab_bg_2'>";
         echo "<th colspan='4'>".__("List of translations")."</th></tr><tr>";
         if ($canedit) {
            echo "<th width='10'>";
            echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            echo "</th>";
         }
         echo "<th>".__("Language")."</th>";
         while ($data = $iterator->next()) {
            $id = $data['id'];
            $onhover = '';
            if ($canedit) {
               $onhover = "style='cursor:pointer'
                           onClick=\"plugin_formcreator.editTranslation($formId, $id);\"";
            }
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td class='center'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
               echo "</td>";
            }

            echo "<td $onhover>";
            echo Dropdown::getLanguageName($data['language']);
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";
         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
      } else {
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'>";
         echo "<th class='b'>" . __('No translation found', 'formcreator')."</th></tr>";
         echo "</table>";
      }
      return true;
   }

   // Note : reset translation cache : Config::getCache('cache_trans')->clear();

   // $translator->clearCache('default', 'en_US');
}