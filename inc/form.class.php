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
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use GlpiPlugin\Formcreator\Exception\ImportFailureException;
use GlpiPlugin\Formcreator\Exception\ExportFailureException;
use Glpi\Application\View\TemplateRenderer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorForm extends CommonDBTM implements
PluginFormcreatorExportableInterface,
PluginFormcreatorDuplicatableInterface,
PluginFormcreatorConditionnableInterface,
PluginFormcreatorTranslatableInterface
{
   use PluginFormcreatorConditionnableTrait;
   use PluginFormcreatorExportableTrait;
   use PluginFormcreatorTranslatable;

   static $rightname = 'entity';

   public $dohistory = true;

   public $skipChecks = false;

   const ACCESS_PUBLIC       = 0;
   const ACCESS_PRIVATE      = 1;
   const ACCESS_RESTRICTED   = 2;

   const VALIDATION_NONE     = 0;
   const VALIDATION_USER     = 1;
   const VALIDATION_GROUP    = 2;

   public static function getEnumAccessType() {
      return [
         self::ACCESS_PUBLIC     => __('Public access', 'formcreator'),
         self::ACCESS_PRIVATE    => __('Private access', 'formcreator'),
         self::ACCESS_RESTRICTED => __('Restricted access', 'formcreator'),
      ];
   }

   public static function getEnumShowRule() : array {
      return PluginFormcreatorCondition::getEnumShowRule();
   }

   public static function canCreate() {
      return Session::haveRight('entity', UPDATE);
   }

   public static function canView() {
      return true;
   }

   public static function canDelete() {
      return Session::haveRight('entity', UPDATE);
   }

   public static function canPurge() {
      return Session::haveRight('entity', UPDATE);
   }

   public function canPurgeItem() {
      $DbUtil = new DbUtils();

      $criteria = [
         PluginFormcreatorForm::getForeignKeyField() => $this->getID(),
      ];
      if ($DbUtil->countElementsInTable(PluginFormcreatorFormAnswer::getTable(), $criteria) > 0) {
         return false;
      }
      return Session::haveRight('entity', UPDATE);
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Form', 'Forms', $nb, 'formcreator');
   }

   public static function getIcon() {
      return 'fas fa-edit';
   }

   public static function getMenuContent() {
      $menu  = parent::getMenuContent();
      $menu['icon'] = 'fas fa-edit';
      $validation_image = '<i class="fa fa-check-square"
                                title="' . __('Answers waiting for validation', 'formcreator') . '"></i>';
      $import_image     = '<i class="fas fa-download"
                                title="' . __('Import forms', 'formcreator') . '"></i>';
      $requests_image   = '<i class="fa fa-paper-plane"
                                 title="' . PluginFormcreatorIssue::getTypeName(Session::getPluralNumber()) . '"></i>';

      $menu['links']['search']          = PluginFormcreatorFormList::getSearchURL(false);
      $menu['links'][$validation_image] = PluginFormcreatorFormAnswer::getSearchURL(false).'?criteria[0][link]=AND&criteria[0][field]=8&criteria[0][searchtype]=equals&criteria[0][value]=' . PluginFormcreatorFormAnswer::STATUS_WAITING;
      $menu['links'][$import_image]     = PluginFormcreatorForm::getFormURL(false)."?import_form=1";
      $menu['links'][$requests_image]   = PluginFormcreatorIssue::getSearchURL(false);
      return $menu;
   }

   public function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Characteristics')
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this::getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this::getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this::getTable(),
         'field'              => 'description',
         'name'               => __('Description'),
         'datatype'           => 'string',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => Entity::getTypeName(1),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this::getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Recursive'),
         'datatype'           => 'bool',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this::getTable(),
         'field'              => 'language',
         'name'               => __('Language'),
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals'
         ],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this::getTable(),
         'field'              => 'helpdesk_home',
         'name'               => __('Homepage', 'formcreator'),
         'datatype'           => 'bool',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => $this::getTable(),
         'field'              => 'access_rights',
         'name'               => __('Access', 'formcreator'),
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '10',
         'table'              => PluginFormcreatorCategory::getTable(),
         'field'              => 'name',
         'name'               => PluginFormcreatorCategory::getTypeName(1),
         'datatype'           => 'dropdown',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => $this::getTable(),
         'field'              => 'content',
         'name'               => _n('Header', 'Headers', 1, 'formcreator'),
         'datatype'           => 'text',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '30',
         'table'              => $this::getTable(),
         'field'              => 'is_active',
         'name'               => __('Active'),
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '31',
         'table'              => $this::getTable(),
         'field'              => 'icon',
         'name'               => __('Icon', 'formcreator'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '32',
         'table'              => $this::getTable(),
         'field'              => 'icon_color',
         'name'               => __('Icon color', 'formcreator'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '33',
         'table'              => $this::getTable(),
         'field'              => 'background_color',
         'name'               => __('Background color', 'formcreator'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '34',
         'table'              => $this::getTable(),
         'field'              => 'is_visible',
         'name'               => __('Visible', 'formcreator'),
         'datatype'           => 'bool',
         'searchtype'         => ['equals'],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '35',
         'table'              => $this::getTable(),
         'field'              => 'is_default',
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'name'               => __('Default form', 'formcreator'),
         'searchtype'         => ['equals'],
         'massiveaction'      => true
      ];

      return $tab;
   }

   /**
    * Define how to display search field for a specific type
    *
    * @since version 0.84
    *
    * @param String $field           Name of the field as define in $this->getSearchOptions()
    * @param String $name            Name attribute for the field to be posted (default '')
    * @param Array  $values          Array of all values to display in search engine (default '')
    * @param Array  $options         Options (optional)
    *
    * @return String                 Html string to be displayed for the form field
    */
   public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'is_active' :
            return Dropdown::showFromArray($name, [
               '0' => __('Inactive'),
               '1' => __('Active'),
            ], [
               'value'               => $values[$field],
               'display_emptychoice' => false,
               'display'             => false
            ]);
            break;

         case 'is_default' :
            return Dropdown::showFromArray($name, [
               '0' => __('Not default form'),
               '1' => __('Default form'),
            ], [
               'value'               => $values[$field],
               'display_emptychoice' => false,
               'display'             => false
            ]);
            break;

         case 'access_rights' :
            return Dropdown::showFromArray(
               $name,
               self::getEnumAccessType(), [
                  'value'               => $values[$field],
                  'display_emptychoice' => false,
                  'display'             => false
               ]
            );
            break;

         case 'language' :
            return Dropdown::showLanguages($name, [
               'value'               => $values[$field],
               'display_emptychoice' => true,
               'emptylabel'          => '--- ' . __('All languages', 'formcreator') . ' ---',
               'display'             => false
            ]);
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   /**
    * Define how to display a specific value in search result table
    *
    * @param  string $field   Name of the field as define in $this->getSearchOptions()
    * @param  mixed  $values  The value as it is stored in DB
    * @param  array  $options Options (optional)
    * @return mixed           Value to be displayed
    */
   public static function getSpecificValueToDisplay($field, $values, array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }

      if (isAPI()) {
         return parent::getSpecificValueToDisplay($field, $values, $options);
      }

      switch ($field) {
         case 'is_active':
            if ($values[$field] == 0) {
               $class = "plugin-formcreator-inactive";
               $title =  __('Inactive');
            } else {
               $class = "plugin-formcreator-active";
               $title =  __('Active');
            }
            if (isset($options['raw_data']['id'])) {
               $output = '<i class="fa fa-circle '
               . $class
               . '" aria-hidden="true" title="' . $title . '"></i>';
               $output = '<div style="text-align: center" onclick="plugin_formcreator.toggleForm(' . $options['raw_data']['id']. ')">' . $output . '</div>';
            } else {
               $output = $title;
            }
            return $output;
            break;

         case 'is_default':
            if ($values[$field] == 0) {
               $class = "plugin-formcreator-inactive";
               $title =  __('Not default form', 'formcreator');
            } else {
               $class = "plugin-formcreator-active";
               $title =  __('Default form', 'formcreator');
            }
            if (isset($options['raw_data']['id'])) {
               $output = '<i class="fa fa-circle '
               . $class
               . '" aria-hidden="true" title="' . $title . '"></i>';
               $output = '<div style="text-align: center" onclick="plugin_formcreator.toggleDefaultForm(' . $options['raw_data']['id']. ')">' . $output . '</div>';
            } else {
               $output = $title;
            }
            return $output;
            break;

         case 'access_rights':
            switch ($values[$field]) {
               case self::ACCESS_PUBLIC :
                  return __('Public access', 'formcreator');
                  break;

               case self::ACCESS_PRIVATE :
                  return __('Private access', 'formcreator');
                  break;

               case self::ACCESS_RESTRICTED :
                  return __('Restricted access', 'formcreator');
                  break;
            }
            return '';
            break;

         case 'language' :
            if (empty($values[$field])) {
               return __('All languages', 'formcreator');
            } else {
               return Dropdown::getLanguageName($values[$field]);
            }
            break;

         case 'icon':
            if ($values[$field] == '') {
               return '';
            }
            return '<i class="' . $values[$field] . '"></i>';
            break;

         case 'icon_color':
         case 'background_color':
            return '<span style="background: ' . $values[$field] . '">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
      }

      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * Show the Form for the adminsitrator to edit in the config page
    *
    * @param  Array  $options Optional options
    *
    * @return NULL   Nothing, just display the form
    */
   public function showForm($ID, $options = []) {
      $this->initForm($ID, $options);
      TemplateRenderer::getInstance()->display('@formcreator/pages/form.html.twig', [
       'item'   => $this,
       'params' => $options,
      ]);

      return true;
   }

   public function showFormAnswerProperties($ID, $options = []) {
      $options['candel'] = false;
      $this->initForm($ID, $options);
      TemplateRenderer::getInstance()->display('@formcreator/pages/form_formanswerproperties.html.twig', [
       'item'   => $this,
       'params' => $options,
      ]);

      $this->showTagsList();

      return true;
   }

   public function showTargets($ID, $options = []) {
      echo '<table class="tab_cadrehov">';
      echo '<tr>';
      echo '<th>'._n('Target', 'Targets', 2, 'formcreator').'</th>';
      echo '<th>'._n('Type', 'Types', 1).'</th>';
      echo '<th class="right">'.__('Actions', 'formcreator').'</th>';
      echo '</tr>';

      $allTargets = $this->getTargetsFromForm();
      $i = 0;
      foreach ($allTargets as $targetType => $targets) {
         foreach ($targets as $targetId => $target) {
            /** @var PluginFormcreatorAbstractTarget $target  */
            $i++;
            echo '<tr class="tab_bg_'.($i % 2).'">';
            $targetItemUrl = $targetType::getFormURLWithID($targetId);
            echo '<td><a href="' . $targetItemUrl . '">';
            echo $target->fields['name'];
            echo '</a></td>';

            echo '<td>';
            echo $target->getTypeName();
            echo '</td>';

            echo '<td align="center" width="32">';
            echo '<i
               class="far fa-clone plugin_formcreator_duplicate_target"
               alt="*"
               title="' . __('Duplicate', 'formcreator') . '"
               data-itemtype="' . get_class($target) . '"
               data-items-id="' . $targetId . '"
               align="absmiddle"
               style="cursor: pointer"
            ></i>';
            echo '&nbsp;';
            echo '<i
               class="far fa-trash-alt plugin_formcreator_delete_target"
               alt="*"
               title="' . __('Delete', 'formcreator') . '"
               data-itemtype="' . get_class($target) . '"
               data-items-id="' . $targetId . '"
               align="absmiddle"
               style="cursor: pointer"
            ></i>';
            echo '</td>';

            echo '</tr>';
         }
      }

      // Display add target link...
      echo '<tr class="tab_bg_'.(($i + 1) % 2).' id="add_target_row">';
      echo '<td colspan="3">';
      echo '<a href="javascript:plugin_formcreator_addTarget('.$ID.');">
                <i class="fa fa-plus"></i>
                '.__('Add a target', 'formcreator').'
            </a>';
      echo '</td>';
      echo '</tr>';

      // OR display add target form
      echo '<tr id="add_target_form" style="display: none;">';
      echo '<td colspan="3" id="add_target_form_td"></td>';
      echo '</tr>';

      echo "</table>";
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item instanceof PluginFormcreatorForm) {
         $nb = $_SESSION['glpishow_count_on_tabs'] ? $item->countTargets() : 0;
         return [
            1 => self::createTabEntry(
               _n('Target', 'Targets', $nb, 'formcreator'),
               $item->countTargets()
            ),
            2 => __('Preview'),
            3 => __('Form answer properties', 'formcreator'),
         ];
      }
      if ($item->getType() == Central::class) {
         return _n('Form', 'Forms', Session::getPluralNumber(), 'formcreator');
      }
      return '';
   }

   /**
    * Display a list of all forms on the configuration page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item instanceof PluginFormcreatorForm) {
         /** @var PluginFormcreatorForm $item */
         switch ($tabnum) {
            case 1:
               $item->showTargets($item->getID());
               break;

            case 2:
               echo '<div style="text-align: left">';
               $item->displayUserForm();
               echo '</div>';
               break;

            case 3:
               $item->showFormAnswerProperties($item->getID());
               break;
         }
         return;
      }
      if ($item->getType() == Central::getType()) {
         $form = PluginFormcreatorCommon::getForm();
         $form->showForCentral();
      }
   }


   public function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(PluginFormcreatorForm_Validator::class, $ong, $options);
      $this->addStandardTab(PluginFormcreatorQuestion::class, $ong, $options);
      $this->addStandardTab(PluginFormcreatorFormAccessType::class, $ong, $options);
      $this->addStandardTab(self::class, $ong, $options);
      $this->addStandardTab(PluginFormcreatorFormAnswer::class, $ong, $options);
      $this->addStandardTab(PluginFormcreatorForm_Language::class, $ong, $options);
      $this->addStandardTab(Document_Item::class, $ong, $options);
      $this->addStandardTab(Log::class, $ong, $options);
      return $ong;
   }

   /**
    * Show the list of forms to be displayed to the end-user
    */
   public function showList() : void {
      echo '<div id="plugin_formcreator_wizard" class="card-group">';
      $this->showWizard();
      echo '<div id="plugin_formcreator_lastForms" class="d-flex flex-column ms-sm-2">';
      $this->showMyLastForms();
      echo '</div>';
      echo '</div>';
   }

   public function showServiceCatalog() : void {
      echo '<div id="plugin_formcreator_wizard" class="card-group">';
      $this->showWizard();
      echo '</div>';
   }

   public function showWizard() : void {
      echo '<div id="plugin_formcreator_wizard_categories" class="card">';

      echo '<div><h2 class="card-title">'._n("Category", "Categories", 2, 'formcreator').'</h2></div>';
      echo '<div class="category-divider"></div>';
      echo '<div class="slinky-menu"></div>';
      echo '<div><a href="#" id="wizard_seeall">' . __('See all', 'formcreator') . '</a></div>';
      echo '</div>';

      echo '<div id="plugin_formcreator_wizard_right" class="card">';
      echo '<div class="card-body">';

      if (PluginFormcreatorEntityconfig::getUsedConfig('is_header_visible', Session::getActiveEntity()) == PluginFormcreatorEntityconfig::CONFIG_HEADER_VISIBLE) {
         echo '<div id="plugin_formcreator_header">';
         $this->showHeader();
         echo '</div>';
      }
      if (PluginFormcreatorEntityconfig::getUsedConfig('is_search_visible', Session::getActiveEntity()) == PluginFormcreatorEntityconfig::CONFIG_SEARCH_VISIBLE) {
         echo '<div id="plugin_formcreator_searchBar">';
         $this->showSearchBar();
         echo '</div>';
      }
      $sort_settings = PluginFormcreatorEntityConfig::getEnumSort();
      echo '<div class="plugin_formcreator_sort">';
      echo '<span class="radios">';
      $sort_order = PluginFormcreatorEntityconfig::getUsedConfig('sort_order', Session::getActiveEntity());
      $selected = $sort_order == PluginFormcreatorEntityconfig::CONFIG_SORT_POPULARITY ? 'checked="checked"' : '';
      echo '<input type="radio" class="-check-input" id="plugin_formcreator_mostPopular" name="sort" value="mostPopularSort" '.$selected.' onclick="showTiles(tiles)"/>';
      echo '<label for="plugin_formcreator_mostPopular">';
      echo '<a title="' . $sort_settings[PluginFormcreatorEntityConfig::CONFIG_SORT_POPULARITY] . '">&nbsp;<i class="fa fa-star" aria-hidden="true"></i></a>';
      echo '</label>';
      echo '</span>';
      echo '&nbsp;';
      echo '<span class="radios">';
      $selected = $sort_order == PluginFormcreatorEntityconfig::CONFIG_SORT_ALPHABETICAL ? 'checked="checked"' : '';
      echo '<input type="radio" class="-check-input" id="plugin_formcreator_alphabetic" name="sort" value="alphabeticSort" '.$selected.' onclick="showTiles(tiles)"/>';
      echo '<label for="plugin_formcreator_alphabetic">';
      echo '<a title="' . $sort_settings[PluginFormcreatorEntityConfig::CONFIG_SORT_ALPHABETICAL] . '">&nbsp;<i class="fa fa-arrow-down-a-z"></i></a>';
      echo '</label>';
      echo '</span>';
      echo '</div>';
      echo '<div id="plugin_formcreator_wizard_forms">';
      echo '</div>';
      echo '</div>';
      echo '</div>';
      echo Html::scriptblock("$(function() {
         plugin_formcreator.updateWizardFormsView();
      });");
   }

   /**
    * Show form and FAQ items
    * @param number $rootCategory Items of this subtree only. 0 = no filtering
    * @param string $keywords Filter items with keywords
    * @param bool $helpdeskHome show items for helpdesk only
    * @return array
    */
   public static function getFormList(int $rootCategory = 0, string $keywords = '', bool $helpdeskHome = false): array {
      global $DB, $TRANSLATE;

      $display_format = PluginFormcreatorEntityconfig::getUsedConfig('tile_design', Session::getActiveEntity());

      $table_cat      = getTableForItemType(PluginFormcreatorCategory::class);
      $table_form     = getTableForItemType(PluginFormcreatorForm::class);
      $table_section  = getTableForItemType(PluginFormcreatorSections::class);
      $table_question = getTableForItemType(PluginFormcreatorQuestions::class);

      $categoryFk = PluginFormcreatorCategory::getForeignKeyField();

      $order         = "$table_form.name ASC";

      $formList = self::getFormListQuery();
      $formList['SELECT'] = [
         $table_form => ['id', 'name', 'icon', 'icon_color', 'background_color', 'description', 'usage_count', 'is_default'],
      ];
      $formList['LEFT JOIN'][$table_cat] = [
         'FKEY' => [
            $table_cat => 'id',
            $table_form => $categoryFk,
         ]
      ];
      $formList['LEFT JOIN'][$table_section] = [
         'FKEY' => [
            $table_section => PluginFormcreatorForm::getForeignKeyField(),
            $table_form => 'id',
         ]
      ];
      $formList['LEFT JOIN'][$table_question] = [
         'FKEY' => [
            $table_question => PluginFormcreatorSection::getForeignKeyField(),
            $table_section => 'id'
         ]
      ];
      if ($helpdeskHome) {
         $formList['WHERE']['AND']["$table_form.helpdesk_home"] = '1';
      }

      $selectedCategories = [];
      if ($rootCategory != 0) {
         // The user choosed a category
         $selectedCategories = getSonsOf($table_cat, $rootCategory);
         $formList['WHERE']['AND']["$table_form.$categoryFk"] = $selectedCategories;
      } else {
         // Searching for all forms without category restriction
         $onlyDefault = PluginFormcreatorEntityconfig::getUsedConfig('default_form_list_mode', Session::getActiveEntity());
         if ($onlyDefault == PluginFormcreatorEntityconfig::CONFIG_DEFAULT_FORM_LIST_DEFAULT) {
            $formList['WHERE']['AND']['is_default'] = 1;
         }
      }

      $formList['GROUPBY'] = [
         "$table_form.id",
         "$table_form.name",
         "$table_form.description",
         "$table_form.usage_count",
         "$table_form.is_default"
      ];

      $formList['ORDER'] = [
         $order
      ];

      // Find forms accessible by the current user
      $keywords = trim($keywords);
      if (!empty($keywords)) {
         $keywordsWithWilcards = $DB->escape(PluginFormcreatorCommon::prepareBooleanKeywords($keywords));
         $formList['WHERE']['AND'][] = [
            'OR' => [
               new QueryExpression("MATCH($table_form.`name`, $table_form.`description`)
                  AGAINST('$keywordsWithWilcards' IN BOOLEAN MODE)"),
               new QueryExpression("MATCH($table_question.`name`, $table_question.`description`)
                  AGAINST('$keywordsWithWilcards' IN BOOLEAN MODE)"),
            ]
         ];
      }

      $result_forms = $DB->request($formList);

      $formList = [];
      foreach ($result_forms as $form) {
         // load thanguage for the form, if any
         $domain = self::getTranslationDomain($form['id']);
         $phpfile = self::getTranslationFile($form['id'], $_SESSION['glpilanguage']);
         if (file_exists($phpfile)) {
            $TRANSLATE->addTranslationFile('phparray', $phpfile, $domain, $_SESSION['glpilanguage']);
         }
         $formList[] = [
            'id'               => $form['id'],
            'name'             => __($form['name'], $domain),
            'icon'             => $form['icon'],
            'icon_color'       => $form['icon_color'],
            'background_color' => $form['background_color'],
            'description'      => __($form['description'], $domain),
            'type'             => 'form',
            'usage_count'      => $form['usage_count'],
            'is_default'       => $form['is_default'] ? "true" : "false",
            'tile_template'    => $display_format,
         ];
      }

      if (PluginFormcreatorEntityConfig::getUsedConfig('is_kb_separated', Session::getActiveEntity()) != PluginFormcreatorEntityconfig::CONFIG_KB_DISTINCT
         && Session::haveRight('knowbase', KnowbaseItem::READFAQ)
      ) {
         // Find FAQ entries
         $params = [
            'faq'      => '1',
            'contains' => $keywords
         ];
         if (version_compare(GLPI_VERSION, '10.0.6') > 0) {
            $params['knowbaseitemcategories_id'] = KnowbaseItemCategory::SEEALL;
         } else {
            $params['knowbaseitemcategories_id'] = 0;
         }
         if (count($selectedCategories) > 0) {
            $iterator = $DB->request($table_cat, [
               'WHERE' => [
                  'id' => $selectedCategories
               ]
            ]);
            $kbcategories = [];
            foreach ($iterator as $knowbase_category) {
               $kbcategories[] = $knowbase_category['knowbaseitemcategories_id'];
            }
            $params['knowbaseitemcategories_id'] = $kbcategories;
         }
         $query_faqs = KnowbaseItem::getListRequest($params);

         $result_faqs = $DB->request($query_faqs);
         foreach ($result_faqs as $faq) {
            $formList[] = [
               'id'               => $faq['id'],
               'name'             => $faq['name'],
               'icon'             => '',
               'icon_color'       => '',
               'background_color' => '',
               'description'      => '',
               'type'             => 'faq',
               'usage_count'      => $faq['view'],
               'is_default'       => false,
               'tile_template'    => PluginFormcreatorEntityConfig::CONFIG_UI_FORM_MASONRY
            ];
         }
      }

      $defaultForms = (count($formList) == 0);
      if ($defaultForms) {
         // No form nor FAQ have been selected
         // Fallback to default forms
         $query_forms = self::getFormListQuery();
         $query_forms['SELECT'] = [
            $table_form => ['id', 'name', 'icon', 'icon_color', 'background_color', 'description', 'usage_count'],
         ];
         $query_forms['WHERE']['AND']["$table_form.is_default"] = ['<>', '0'];
         $query_forms['ORDER'] = [
            $order
         ];
         $result_forms = $DB->request($query_forms);

         foreach ($result_forms as $form) {
            $formList[] = [
               'id'               => $form['id'],
               'name'             => $form['name'],
               'icon'             => $form['icon'],
               'icon_color'       => $form['icon_color'],
               'background_color' => $form['background_color'],
               'description'      => $form['description'],
               'type'             => 'form',
               'usage_count'      => $form['usage_count'],
               'is_default'       => true,
               'tile_template'    => $display_format,
            ];
         }
      }
      return ['default' => $defaultForms, 'forms' => $formList];
   }

   protected function showHeader(): void {
      $header = PluginFormcreatorEntityconfig::getUsedConfig('is_header_visible', Session::getActiveEntity(), 'header');
      echo Html::entity_decode_deep($header);
   }

   protected function showSearchBar() : void {
      echo '<form name="plugin_formcreator_search" onsubmit="javascript: return false;" >';
      echo '<input type="text" name="words" id="plugin_formcreator_search_input" required class="form-control" />';
      echo '<span id="plugin_formcreator_search_input_bar"></span>';
      echo '<label for="plugin_formcreator_search_input">'.__('What are you looking for?', 'formcreator').'</label>';
      echo '</form>';
   }

   protected function showMyLastForms() : void {
      $limit = 5;
      $formanswerUrl = PluginFormcreatorFormAnswer::getSearchURL();
      $rawKeyBase = 'ITEM_' . PluginFormcreatorFormAnswer::class;
      echo '<div id="plugin_formcreator_last_req_forms" class="card">';
      echo '<div class="card-title">'.sprintf(__('My %1$d last forms (requester)', 'formcreator'), $limit).'</div>';
      $criteria = [
         'criteria' => [
            0 => [
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => 'myself',
            ],
         ],
         'sort' => [
            0 => 6
         ],
         'order' => [
            0 => 'DESC'
         ],
      ];
      $showColumns = [
         2, // id
         1, // name
         6, // request date
         8, // status
      ];
      $backupListLimit = $_SESSION['glpilist_limit'];
      $_SESSION['glpilist_limit'] = 5;
      $search = Search::getDatas(PluginFormcreatorFormAnswer::class, $criteria, $showColumns);
      $_SESSION['glpilist_limit'] = $backupListLimit;
      if ($search['data']['count'] == 0) {
         echo '<div class="card-body text-center text-muted">'.__('No form posted yet', 'formcreator').'</div>';
      } else {
         echo '<div class="card-body">';
         echo '<ul class="list-group">';
         foreach ($search['data']['rows'] as $formAnswer) {
            switch ($formAnswer['raw']["{$rawKeyBase}_8"]) {
               case PluginFormcreatorFormAnswer::STATUS_WAITING:
                  $status = CommonITILObject::WAITING;
                  break;
               case PluginFormcreatorFormAnswer::STATUS_REFUSED:
                  $status = Change::REFUSED;
                  break;
               case PluginFormcreatorFormAnswer::STATUS_ACCEPTED:
                  $status = CommonITILObject::ACCEPTED;
                  break;
               default:
                  $status = $formAnswer['raw']["{$rawKeyBase}_8"];
            }
            $status = CommonITILObject::getStatusClass($status);
            echo '<li data-itemtype="PluginFormcreatorFormanswer" data-id="'  . $formAnswer['raw']["{$rawKeyBase}_2"] . '">';
            echo '<i class="'.$status.'"></i><a href="formanswer.form.php?id='. $formAnswer['raw']["{$rawKeyBase}_2"] .'">'. $formAnswer['raw']["{$rawKeyBase}_1"] .'</a>';
            echo '<span class="plugin_formcreator_date">'.Html::convDateTime($formAnswer['raw']["{$rawKeyBase}_6"]).'</span>';
            echo '</li>';
         }
         echo '</ul>';
         echo '<div class="text-center  card-footer">';
         $criteria = Toolbox::append_params($criteria, '&amp;');
         echo '<a href="' . $formanswerUrl . '?' . $criteria . '">';
         echo __('All my forms (requester)', 'formcreator');
         echo '</a>';
         echo '</div>';
         echo '</div>';
      }
      echo '</div>';

      if (!PluginFormcreatorCommon::canValidate()) {
         // The user cannot validate, then do not show the next card
         return;
      }

      echo '<div id="plugin_formcreator_val_forms" class="card mt-0 mt-sm-2">';
      echo '<div class="card-title">'.sprintf(__('My %1$d last forms (validator)', 'formcreator'), $limit).'</div>';
      $criteria = [
         'criteria' => [
            0 => [
               'field'      => 5,
               'searchtype' => 'equals',
               'value'      => 'myself',
            ],
         ],
         'sort' => [
            0 => 6
         ],
         'order' => [
            0 => 'DESC'
         ],
      ];
      if (count($_SESSION['glpigroups'] ?? []) > 0) {
         // The user is member of some groups, then add criteria for those groups
         $criteria['criteria'][] = [
            'link'       => 'OR',
            'field'      => 7,
            'searchtype' => 'equals',
            'value'      => 'mygroups',
         ];
      }
      $backupListLimit = $_SESSION['glpilist_limit'];
      $_SESSION['glpilist_limit'] = 5;
      $search = Search::getDatas(PluginFormcreatorFormAnswer::class, $criteria, $showColumns);
      $_SESSION['glpilist_limit'] = $backupListLimit;
      if ($search['data']['count'] == 0) {
         echo '<div class="card-body text-center text-muted" >'.__('No form waiting for validation', 'formcreator').'</div>';
      } else {
         echo '<div class="card-body">';
         echo '<ul class="list-group">';
         foreach ($search['data']['rows'] as $formAnswer) {
            switch ($formAnswer['raw']["{$rawKeyBase}_8"]) {
               case PluginFormcreatorFormAnswer::STATUS_WAITING:
                  $status = CommonITILObject::WAITING;
                  break;
               case PluginFormcreatorFormAnswer::STATUS_REFUSED:
                  $status = Change::REFUSED;
                  break;
               case PluginFormcreatorFormAnswer::STATUS_ACCEPTED:
                  $status = CommonITILObject::ACCEPTED;
                  break;
               default:
                  $status = $formAnswer['raw']["{$rawKeyBase}_8"];
            }
            $status = CommonITILObject::getStatusClass($status);
            echo '<li data-itemtype="PluginFormcreatorFormanswer" data-id="'  . $formAnswer['raw']["{$rawKeyBase}_2"] . '">';
            echo '<i class="'.$status.'"></i><a href="formanswer.form.php?id='. $formAnswer['raw']["{$rawKeyBase}_2"] .'">'. $formAnswer['raw']["{$rawKeyBase}_1"] .'</a>';
            echo '<span class="plugin_formcreator_date">'.Html::convDateTime($formAnswer['raw']["{$rawKeyBase}_6"]).'</span>';
            echo '</li>';
         }
         echo '</ul>';
         echo '<div class="text-center card-footer">';
         $criteria = Toolbox::append_params($criteria, '&amp;');
         echo '<a href="' . $formanswerUrl . '?' . $criteria . '">';
         echo __('All my forms (validator)', 'formcreator');
         echo '</a>';
         echo '</div>';
         echo '</div>';
      }
      echo '</div>';
   }

   /**
    * Display the Form end-user form to be filled
    *
    * @return void
    */
   public function displayUserForm() : void {
      global $TRANSLATE;

      // Print css media
      $css = '/' . Plugin::getWebDir('formcreator', false) . '/css/print_form.css';
      echo Html::css($css, ['media' => 'print']);

      $formId = $this->getID();
      $domain = self::getTranslationDomain($formId);
      $phpfile = self::getTranslationFile($formId, $_SESSION['glpilanguage']);
      if (file_exists($phpfile)) {
         $TRANSLATE->addTranslationFile('phparray', $phpfile, $domain, $_SESSION['glpilanguage']);
      }

      $formanswer = new PluginFormcreatorFormAnswer();
      TemplateRenderer::getInstance()->display('@formcreator/pages/userform.html.twig', [
         'item'    => $this,
         'options' => [
            'columns' => PluginFormcreatorSection::COLUMNS,
            'domain'  => $domain, // For translation
            'public'  => isset($_SESSION['formcreator_public']),
            'formanswer' => $formanswer,
            'use_captcha' => ($this->fields['access_rights'] == PluginFormcreatorForm::ACCESS_PUBLIC
                              && $this->fields['is_captcha_enabled'] != '0'),
         ]
      ]);
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

      // Control fields values :
      // - name is required
      if (isset($input['name'])) {
         if (empty($input['name'])) {
            Session::addMessageAfterRedirect(
               __('The name cannot be empty!', 'formcreator'),
               false,
               ERROR
            );
            return [];
         }
      }

      if (!isset($input['formanswer_name']) || strlen($input['formanswer_name']) < 1) {
         $input['formanswer_name'] = $input['name'] ?? $this->fields['formanswer_name'];
      }

      if (!$this->skipChecks) {
         if (!$this->checkConditionSettings($input)) {
            $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
         }

         if (!$this->checkValidators($input)) {
            $input['validation_required'] = self::VALIDATION_NONE;
         }
      }

      return $input;
   }

   /**
    * Actions done after the ADD of the item in the database
    *
    * @return void
    */
   public function post_addItem() {
      if (isset($this->input['_create_empty_section'])) {
         $section = new PluginFormcreatorSection();
         $section->add([
            self::getForeignKeyField() => $this->getID(),
            'name' => PluginFormcreatorSection::getTypeName(1),
         ]);
      }
      $this->updateValidators();
      $this->updateConditions($this->input);
      return true;
   }

   /**
    * Actions done after the UPDATE of the item in the database
    *
    * @return void
    */
   public function post_updateItem($history = 1) {
      $this->input = $this->addFiles(
         $this->input,
         [
            'force_update'  => true,
            'content_field' => 'content',
            'name'          => 'content',
         ]
      );

      $this->updateValidators();
      $this->updateConditions($this->input);

      if (count($this->updates) < 1) {
         // When updating conditions in the questions tab, need to clear saved inputs
         // because no data changed in the form itself, and GLPI does not clears the input
         // saved in the session
         $this->clearSavedInput();
      }

      // Update access types restrictions
      $this->update1NTableData(PluginFormcreatorForm_User::class, 'users');
      $this->update1NTableData(PluginFormcreatorForm_Group::class, 'groups');
      $this->update1NTableData(PluginFormcreatorForm_Profile::class, 'profiles');
   }

   /**
    * Prepare input data for updating the form
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
    */
   public function prepareInputForUpdate($input) {
      if (isset($input['toggle'])) {
         // Enable / disable form
         if ($input['toggle'] == 'active') {
            return [
               'id' => $input['id'],
               'is_active' => $this->fields['is_active'] == '0' ? '1' : '0',
            ];
         }
         if ($input['toggle'] == 'default') {
            return [
               'id' => $input['id'],
               'is_default' => $this->fields['is_default'] == '0' ? '1' : '0',
            ];
         }
      }

      if (isset($input['usage_count'])) {
         // update usage count after form submission
         return [
            'id' => $input['id'],
            'usage_count' => $input['usage_count'],
         ];
      }

      // prevent change of UUID
      unset($input['uuid']);

      if (isset($input['access_rights'])
            || isset($_POST['massiveaction'])) {

         if (!$this->skipChecks) {
            if (!$this->checkAccessRight($input)) {
               return false;
            }

            // if (!$this->checkConditionSettings($input)) {
            //    $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
            // }

            // if (!$this->checkValidators($input)) {
            //    $input['validation_required'] = self::VALIDATION_NONE;
            // }
         }

         // return $input;
      }

      // Control fields values :
      // - name is required
      if (isset($input['name'])) {
         if (empty($input['name'])) {
            Session::addMessageAfterRedirect(
               __('The name cannot be empty!', 'formcreator'),
               false,
               ERROR
            );
            return [];
         }
      }

      if (isset($input['formanswer_name']) && strlen($input['formanswer_name']) < 1) {
         unset($input['formanswer_name']);
         Session::addMessageAfterRedirect(
            __('Cannot use empty name for form answers. Keeping the previous value.', 'formcreator'),
            false,
            WARNING
         );
      }

      if (!$this->skipChecks) {
         if (!$this->checkConditionSettings($input)) {
            $input['show_rule'] = PluginFormcreatorCondition::SHOW_RULE_ALWAYS;
         }

         if (!$this->checkValidators($input)) {
            $input['validation_required'] = self::VALIDATION_NONE;
         }
      }

      if (isset($input['restrictions'])) {
         $input['users']    = AbstractRightsDropdown::getPostedIds($input['restrictions'], User::class);
         $input['groups']   = AbstractRightsDropdown::getPostedIds($input['restrictions'], Group::class);
         $input['profiles'] = AbstractRightsDropdown::getPostedIds($input['restrictions'], Profile::class);
         unset($input['restrictions']);
      }

      return $input;
   }

   /**
    * Actions done after the PURGE of the item in the database
    *
    * @return void
    */
   public function post_purgeItem() {
      $associated = [
         PluginFormcreatorTargetTicket::class,
         PluginFormcreatorTargetChange::class,
         PluginFormcreatorSection::class,
         PluginFormcreatorForm_Validator::class,
         PluginFormcreatorForm_Profile::class,
         PluginFormcreatorForm_User::class,
         PluginFormcreatorForm_Group::class,
         PluginFormcreatorForm_Language::class,
      ];
      foreach ($associated as $itemtype) {
         $item = new $itemtype();
         $item->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);
      }
   }

   /**
    * Validate validators settings
    *
    * @param array $input
    * @return bool
    */
   protected function checkValidators(array $input): bool {
      if (!isset($input['validation_required'])) {
         return true;
      }

      switch ($input['validation_required']) {
         case self::VALIDATION_NONE:
            return true;
            break;

         case self::VALIDATION_USER:
            $fieldName = '_validator_users';
            break;

         case self::VALIDATION_GROUP:
            $fieldName = '_validator_groups';
            break;
      }

      if (!isset($input[$fieldName])) {
         return false;
      }

      if (is_array($input[$fieldName]) && count($input[$fieldName]) < 1) {
         return false;
      }

      return true;
   }

   /**
    * Validate acess rights settings
    *
    * @param array $input
    * @return boolean
    */
   protected function checkAccessRight(array $input): bool {
      if (!isset($input['access_rights'])) {
         return true;
      }

      switch ($input['access_rights']) {
         case self::ACCESS_PUBLIC:
            $fields = $this->getFields();
            $incompatibleQuestion = false;
            foreach ($fields as $field) {
               if (!$field->isPublicFormCompatible()) {
                  $incompatibleQuestion = true;
                  $message = __('The question %s is not compatible with public forms', 'formcreator');
                  Session::addMessageAfterRedirect(sprintf($message, $field->getLabel()), false, ERROR);
               }
            }
            if ($incompatibleQuestion) {
               return false;
            }

         break;
      }

      return true;
   }

   /**
    * Save form validators
    *
    * @return void
    */
   private function updateValidators() : void {
      if (!isset($this->input['validation_required'])) {
         return;
      }
      if ($this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_NONE) {
         return;
      }
      if ($this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_USER
         && empty($this->input['_validator_users'])) {
         return;
      }
      if ($this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_GROUP
         && empty($this->input['_validator_groups'])) {
         return;
      }

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);

      switch ($this->input['validation_required']) {
         case PluginFormcreatorForm_Validator::VALIDATION_USER:
            $validators = $this->input['_validator_users'];
            $validatorItemtype = User::class;
            break;
         case PluginFormcreatorForm_Validator::VALIDATION_GROUP:
            $validators = $this->input['_validator_groups'];
            $validatorItemtype = Group::class;
            break;
      }
      if (!is_array($validators)) {
         $validators = [$validators];
      }
      foreach ($validators as $itemId) {
         $form_validator = new PluginFormcreatorForm_Validator();
         $form_validator->add([
            'plugin_formcreator_forms_id' => $this->getID(),
            'itemtype'                    => $validatorItemtype,
            'items_id'                    => $itemId
         ]);
      }
   }

   public function increaseUsageCount(): bool {
      // Increase usage count of the form
      return $this->update([
            'id' => $this->getID(),
            'usage_count' => $this->fields['usage_count'] + 1,
      ]);
   }

   public function duplicate(array $options = []) {
      $linker = new PluginFormcreatorLinker($options);

      try {
         $export = $this->export(true);
         $new_form_id =  self::import($linker, $export);
      } catch (ImportFailureException $e) {
         $forms = $linker->getObjectsByType(PluginFormcreatorForm::class);
         $form = reset($forms);
         $form->update([
            'id' => $form->getID(),
            'name' => $form->fields['name'] . ' [' . __('Errored duplicate', 'formcreator') . ']',
         ]);
         Session::addMessageAfterRedirect($e->getMessage(), false, WARNING);
         return false;
      }

      if ($new_form_id === false) {
         return false;
      }
      $newForm = new self();
      $newForm->getFromDB($new_form_id);
      $newName = $newForm->fields['name'] . ' [' . __('Duplicate', 'formcreator') . ']';
      $newForm->update([
         'id' => $new_form_id,
         'name' => Toolbox::addslashes_deep($newName),
      ]);
      $linker->linkPostponed();

      return $new_form_id;
   }

   /**
    * Transfer a form to another entity. Execute transfert action for massive action.
    *
    * @return Boolean true if success, false otherwize.
    */
   public function transfer($entity) {
      global $DB;

      $result = $DB->update(
         self::getTable(),
         [
            Entity::getForeignKeyField() => $entity
         ],
         [
            'id' => $this->getID()
         ]
      );
      return $result;
   }

   public function getForbiddenStandardMassiveAction() {
      return [
         'clone',
      ];
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    */
   public static function showMassiveActionsSubForm(MassiveAction $ma) {
      switch ($ma->getAction()) {
         case 'Transfert':
            Entity::dropdown([
               'name' => 'entities_id',
            ]);
            echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;

         case 'AccessRights':
            echo '<p></p>';
            Dropdown::showFromArray(
               'access_rights',
               PluginFormcreatorForm::getEnumAccessType(),
               [
                  'value'     => PluginFormcreatorForm::ACCESS_PRIVATE,
                  'on_change' => 'plugin_formcreator.showMassiveRestrictions(this)',
               ]
            );
            echo '<p></p>';
            echo '<div id="plugin_formcreator_restrictions_head" style="display: none">';
            echo PluginFormcreatorFormAccessType::getTypeName(2);
            echo '</div>';
            echo '<div id="plugin_formcreator_restrictions" style="display: none">';
            echo PluginFormcreatorRestrictedFormDropdown::show('restrictions', [
               'users_id'    => [],
               'groups_id'   => [],
               'profiles_id' => [],
            ]);
            echo '</div>';
            echo '<div id="plugin_formcreator_captcha" style="display: none">';
            echo __('Enable captcha', 'formcreator') . '&nbsp;';
            Dropdown::showYesNo('is_captcha_enabled');
            echo '</div>';
            echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;

      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * Execute massive action for PluginFormcreatorFrom
    *
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    */
   public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      switch ($ma->getAction()) {
         case 'Duplicate' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->duplicate() !== false) {
                  Session::addMessageAfterRedirect(sprintf(__('Form duplicated: %s', 'formcreator'), $item->getName()));
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            return;
         case 'Transfert' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->transfer($ma->POST['entities_id'])) {
                  Session::addMessageAfterRedirect(sprintf(__('Form Transfered: %s', 'formcreator'), $item->getName()));
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            return;
         case 'Export' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id)) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            echo "<br>";
            echo "<div class='center'>";
            echo "<a href='#' onclick='window.history.back()'>".__("Back")."</a>";
            echo "</div>";

            $listOfId = ['plugin_formcreator_forms_id' => array_values($ids)];
            Html::redirect(FORMCREATOR_ROOTDOC."/front/export.php?".Toolbox::append_params($listOfId));
            header("Content-disposition:attachment filename=\"test\"");
            return;

         case 'AccessRights':
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->update($ma->POST + ['id' => $id])) {
                  Session::addMessageAfterRedirect(sprintf(__('Form updated: %s', 'formcreator'), $item->getName()));
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   /**
    * Get the nnumber of forms the user can see
    * @return int count of forms
    */
   public static function countAvailableForm(): int {
      global $DB;

      $formTable        = self::getTable();
      $formProfileTable = PluginFormcreatorForm_Profile::getTable();
      $formUserTable = PluginFormcreatorForm_user::getTable();
      $formGroupTable = PluginFormcreatorForm_Group::getTable();

      if (!$DB->tableExists($formTable)
          || !$DB->tableExists($formProfileTable)
          || !$DB->tableExists($formUserTable)
          || !$DB->tableExists($formGroupTable)
          || !isset($_SESSION['glpiactiveprofile']['id'])) {
             return 0;
      }

      $listQuery = self::getFormListQuery();
      $listQuery['COUNT'] = 'c';
      $result = $DB->request($listQuery);
      $result->rewind();
      $nb = $result->current()['c'];

      return $nb;
   }

   public function export(bool $remove_uuid = false): array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
      }

      $export = $this->fields;

      // replace entity id
      /** @var Entity */
      $entity = Entity::getById($export['entities_id']);
      $export['_entity'] = $entity->fields['completename'];

      // replace form category id
      $export['_plugin_formcreator_category'] = '';
      /** @var PluginFormcreatorCategory */
      $formCategory = PluginFormcreatorCategory::getById($export['plugin_formcreator_categories_id']);
      if ($formCategory instanceof CommonDBTM) {
         $export['_plugin_formcreator_category'] = $formCategory->fields['completename'];
      }

      // remove non needed keys
      unset($export['plugin_formcreator_categories_id'],
            $export['entities_id'],
            $export['usage_count']);

      $subItems = [
         '_profiles'     => PluginFormcreatorForm_Profile::class,
         '_users'        => PluginFormcreatorForm_User::class,
         '_groups'       => PluginFormcreatorForm_Group::class,
         '_sections'     => PluginFormcreatorSection::class,
         '_conditions'   => PluginFormcreatorCondition::class,
         '_targets'      => self::getTargetTypes(),
         '_validators'   => PluginFormcreatorForm_Validator::class,
         '_translations' => PluginFormcreatorForm_Language::class,
      ];
      $export = $this->exportChildrenObjects($subItems, $export, $remove_uuid);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($export[$idToRemove]);

      return $export;
   }

   /**
    * Display an html form to upload a json with forms data
    */
   public function showImportForm() {
      $documentType = new DocumentType();
      $jsonTypeExists = $documentType->getFromDBByCrit(['ext' => 'json']);
      $jsonTypeEnabled = $jsonTypeExists && $documentType->getField('is_uploadable');
      $canAddType = $documentType->canCreate();
      $canUpdateType = $documentType->canUpdate();

      if (! ($jsonTypeExists && $jsonTypeEnabled)) {
         if (!$jsonTypeExists) {
            $message = __('Upload of JSON files not allowed.', 'formcreator');
            if ($canAddType) {
               $destination = PluginFormcreatorForm::getFormURL();
               $message .= __('You may allow JSON files right now.', 'formcreator');
               $button = Html::submit(_x('button', 'Create', 'formcreator'), ['name' => 'filetype_create']);
            } else {
               $destination = PluginFormcreatorForm::getSearchURL();
               $message .= __('Please contact your GLPI administrator.', 'formcreator');
               $button = Html::submit(_x('button', 'Back', 'formcreator'), ['name' => 'filetype_back']);
            }
         } else {
            $message = __('Upload of JSON files not enabled.', 'formcreator');
            if ($canUpdateType) {
               $destination = PluginFormcreatorForm::getFormURL();
               $message .= __('You may enable JSON files right now.', 'formcreator');
               $button = Html::submit(_x('button', 'Enable', 'formcreator'), ['name' => 'filetype_enable']);
            } else {
               $message .= __('You may enable JSON files right now.', 'formcreator');
               $message .= __('Please contact your GLPI administrator.', 'formcreator');
               $button = Html::submit(_x('button', 'Back', 'formcreator'), ['name' => 'filetype_back']);
            }
         }
         echo '<div class="spaced" id="tabsbody">';
         echo "<form name='form' method='post' action='". $destination."'>";
         echo '<table class="tab_cadre_fixe" id="mainformtable">';
         echo '<tr class="headerRow">';
         echo '<th>';
         echo __('Import forms');
         echo '</th>';
         echo '</tr>';
         echo '<tr>';
         echo '<td class="center">';
         echo $message;
         echo '</td>';
         echo '</tr>';
         echo '<td class="center">';
         echo $button;
         echo '</td>';
         echo '</tr>';
         echo '<tr>';
         echo '</table>';
         echo '</div>';
         Html::closeForm();
         echo '</div>';
      } else {
         echo "<form name='form' method='post' action='".
               PluginFormcreatorForm::getFormURL().
               "?import_send=1' enctype=\"multipart/form-data\">";

         echo "<div class='spaced' id='tabsbody'>";
         echo "<table class='tab_cadre_fixe' id='mainformtable'>";
         echo "<tr class='headerRow'>";
         echo "<th>";
         echo __("Import forms");
         echo "</th>";
         echo "</tr>";
         echo "<tr>";
         echo "<td>";
         echo Html::file(['name' => 'json_file']);
         echo "</td>";
         echo "</tr>";
         echo "<td class='center'>";
         echo Html::submit(_x('button', 'Send'), ['name' => 'import_send']);
         echo "</td>";
         echo "</tr>";
         echo "<tr>";
         echo "</table>";
         echo "</div>";

         Html::closeForm();
      }
   }

   /**
    * Process import of json file(s) sended by the submit of self::showImportForm
    * @param  array  $params GET/POST data that need to contain the filename(s) in _json_file key
    */
   public function importJson($params = []) {
      // parse json file(s)
      foreach ($params['_json_file'] as $filename) {
         if (!$json = file_get_contents(GLPI_TMP_DIR."/".$filename)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file is empty", 'formcreator'));
            continue;
         }
         if (!$forms_toimport = json_decode($json, true)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt", 'formcreator'));
            continue;
         }
         if (!isset($forms_toimport['forms'])) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt", 'formcreator'));
            continue;
         }
         if (isset($forms_toimport['schema_version'])) {
            if (!self::checkImportVersion($forms_toimport['schema_version'])) {
               Session::addMessageAfterRedirect(
                  __("Forms import impossible, the file was generated with another version", 'formcreator'),
                  false, ERROR
               );
               continue;
            }
         } else {
            Session::addMessageAfterRedirect(
               __("The file does not specifies the schema version. It was probably generated with a version older than 2.10. Giving up.", 'formcreator'),
               false, ERROR
            );
            continue;
         }

         // Get the total count of objects to import, for the progressbar
         $linker = new PluginFormcreatorLinker();
         foreach ($forms_toimport['forms'] as $form) {
            $linker->countItems($form, self::class);
         }
         $linker->initProgressBar();

         $success = true;
         foreach ($forms_toimport['forms'] as $form) {
            $linker->reset();
            set_time_limit(30);
            try {
               self::import($linker, $form);
            } catch (ImportFailureException $e) {
               // Import failed, give up
               $success = false;
               Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
               continue;
            }
            if (!$linker->linkPostponed()) {
               Session::addMessageAfterRedirect(sprintf(__("Failed to import %s", "formcreator"),
                                                           $form['name']));
            }
         }
         if ($success) {
            Session::addMessageAfterRedirect(sprintf(__("Forms successfully imported from %s", "formcreator"),
                                                      $filename));
         }
      }
   }

   /**
    * Check the version is compatible with the current one
    * for forms import
    *
    * @return boolean
    */
   public static function checkImportVersion($version) {
      // Convert version to X.Y
      $version = explode('.', $version);
      if (count($version) < 2) {
         return false;
      }
      $minorVersion = [array_shift($version)];
      $minorVersion[] = array_shift($version);
      $minorVersion = implode('.', $minorVersion);

      return version_compare(PLUGIN_FORMCREATOR_SCHEMA_VERSION, $minorVersion) == 0;
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', self::getTypeName(1)));
      }

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

      // Set entity of the form
      $entity = new Entity();
      $entityFk = Entity::getForeignKeyField();
      $entityId = Session::getActiveEntity();
      if (isset($input['_entity'])) {
         plugin_formcreator_getFromDBByField(
            $entity,
            'completename',
            $input['_entity']
         );
         // Check rights on the destination entity of the form
         if (!$entity->isNewItem()) {
            if (!$entity->canUpdateItem()) {
               if ($itemId !== false) {
                  // The form is in an entity where we don't have UPDATE right
                  Session::addMessageAfterRedirect(
                     sprintf(__('The form %1$s already exists and is in an unmodifiable entity.', 'formcreator'), $input['name']),
                     false,
                     WARNING
                  );
                  throw new ImportFailureException('Failed to add or update the item');
               }
               // The entity is not updatable
               Session::addMessageAfterRedirect(
                  sprintf(__('You don\'t have right to update the entity %1$s.', 'formcreator'), $input['_entity']),
                  false,
                  WARNING
               );
               throw new ImportFailureException('Failed to add or update the item');
            }
            $entityId = $entity->getID();
         } else {
            // The form is in an entity which does not exists yet
            Session::addMessageAfterRedirect(
               sprintf(__('The entity %1$s is required for the form %2$s.', 'formcreator'), $input['_entity'], $input['name']),
               false,
               WARNING
            );
            throw new ImportFailureException('Failed to add or update the item');
         }
      }
      $input[$entityFk] = $entityId;

      // Import form category
      $formCategory = new PluginFormcreatorCategory();
      $formCategoryFk = PluginFormcreatorCategory::getForeignKeyField();
      $formCategoryId = 0;
      if ($input['_plugin_formcreator_category'] != '') {
         $formCategoryId = $formCategory->import([
            'completename' => Toolbox::addslashes_deep($input['_plugin_formcreator_category']),
         ]);
      }
      $input[$formCategoryFk] = $formCategoryId;

      // Escape text fields
      foreach (['name', 'description', 'content', 'formanswer_name'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // Add or update the form
      $originalId = $input[$idKey];
      $item->skipChecks = true;
      if ($itemId !== false) {
         $input['id'] = $itemId;
         $item->update($input);
      } else {
         unset($input['id']);
         $itemId = $item->add($input);
      }
      $item->skipChecks = false;
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the form to the linker
      $linker->addObject($originalId, $item);

      $subItems = [
         '_profiles'     => PluginFormcreatorForm_Profile::class,
         '_users'        => PluginFormcreatorForm_User::class,
         '_groups'       => PluginFormcreatorForm_Group::class,
         '_sections'     => PluginFormcreatorSection::class,
         '_conditions'   => PluginFormcreatorCondition::class,
         '_targets'      => (new self())->getTargetTypes(),
         '_validators'   => PluginFormcreatorForm_Validator::class,
         '_translations' => PluginFormcreatorForm_Language::class,
      ];
      $item->importChildrenObjects($item, $linker, $subItems, $input);

      return $itemId;
   }

   public static function countItemsToImport(array $input) : int {
      // Code similar to ImportChildrenObjects
      $subItems = [
         '_profiles'   => PluginFormcreatorForm_Profile::class,
         '_users'      => PluginFormcreatorForm_User::class,
         '_groups'     => PluginFormcreatorForm_Group::class,
         '_sections'   => PluginFormcreatorSection::class,
         '_conditions' => PluginFormcreatorCondition::class,
         '_targets'    => (new self())->getTargetTypes(),
         '_validators' => PluginFormcreatorForm_Validator::class,
      ];
      return 1 + self::countChildren($input, $subItems);
   }

   public function createDocumentType() {
      $documentType = new DocumentType();
      $success = $documentType->add([
         'name'            => 'JSON file',
         'ext'             => 'json',
         'icon'            => '',
         'is_uploadable'   => '1'
      ]);
      if (!$success) {
         Session::addMessageAfterRedirect(__('Failed to create JSON document type', 'formcreator'));
      }
   }

   public function enableDocumentType() {
      $documentType = new DocumentType();
      if (!$documentType->getFromDBByCrit(['ext' => 'json'])) {
         Session::addMessageAfterRedirect(__('JSON document type not found', 'formcreator'));
      } else {
         $success = $documentType->update([
            'id'              => $documentType->getID(),
            'is_uploadable'   => '1'
         ]);
         if (!$success) {
            Session::addMessageAfterRedirect(__('Failed to update JSON document type', 'formcreator'));
         }
      }
   }

   /**
    * show list of available forms
    */
   public function showForCentral() {
      global $DB, $TRANSLATE;

      // Define tables
      $form_table = PluginFormcreatorForm::getTable();

      // Show form whithout category
      $formCategoryFk = PluginFormcreatorCategory::getForeignKeyField();

      // Show categories which have at least one form user can access
      $result = PluginFormcreatorCategory::getAvailableCategories();
      // For each categories, show the list of forms the user can fill
      $categories = [0 => __('Forms without category', 'formcreator')];
      foreach ($result as $category) {
         $categories[$category['id']] = $category['name'];
      }

      $form_query = PluginFormcreatorForm::getFormListQuery();
      $form_query['SELECT'] = [
         $form_table => ['id', 'name', 'description', $formCategoryFk],
      ];
      $form_query['WHERE']["$form_table.helpdesk_home"] = 1;
      $form_query['WHERE']["$form_table.$formCategoryFk"] = array_keys($categories);
      $form_query['ORDER'] = [
         "$form_table.$formCategoryFk ASC",
         "$form_table.name ASC",
      ];

      $result_forms = $DB->request($form_query);

      if ($result_forms->count() < 1) {
         echo '<table class="tab_cadrehov" id="plugin_formcreatorHomepageForms">';
         echo '<tr class="noHover">';
         echo '<th>' . __('No form available', 'formcreator') . '</th>';
         echo '</tr>';
         echo '</table>';
         return;
      }

      echo '<table class="tab_cadrehov" id="plugin_formcreatorHomepageForms">';
      echo '<tr class="noHover">';
      echo '<th><a href="' . FORMCREATOR_ROOTDOC . '/front/formlist.php">' . _n('Form', 'Forms', 2, 'formcreator') . '</a></th>';
      echo '</tr>';

      $currentCategoryId = -1;
      $i = 0;
      foreach ($result_forms as $row) {
         if ($currentCategoryId != $row[$formCategoryFk]) {
            // show header for the category
            $currentCategoryId = $row[$formCategoryFk];
            echo '<tr class="noHover" data-itemtype="PluginFormcreatorCategory" data-id="' . $currentCategoryId . '"><th>' . $categories[$currentCategoryId] . '</th></tr>';
         }

         // Show a row for the form
         $language = $_SESSION['glpilanguage'];
         $domain = PluginFormcreatorForm::getTranslationDomain($row['id'], $language);
         $phpfile = self::getTranslationFile($row['id'], $_SESSION['glpilanguage']);
         if (file_exists($phpfile)) {
            $TRANSLATE->addTranslationFile('phparray', $phpfile, $domain, $_SESSION['glpilanguage']);
         }

         echo '<tr class="tab_bg_' . ($i % 2 +1) . '" data-itemtype="PluginFormcreatorForm" data-id="' . $row['id'] . '">';
         echo '<td>';
         echo '<i class="fas fa-plus-circle" alt="+" title=""
               onclick="showDescription(' . $row['id'] . ', this)" align="absmiddle" style="cursor: pointer"></i>';
         echo '&nbsp;';
         echo '<a href="' . FORMCREATOR_ROOTDOC
            . '/front/formdisplay.php?id=' . $row['id'] . '"
               title="' . __($row['description'], $domain) . '">'
            . __($row['name'], $domain)
            . '</a></td>';
         echo '</tr>';
         echo '<tr id="desc' . $row['id'] . '" class="tab_bg_' . ($i % 2) . ' form_description">';
         echo '<td><div>' . __($row['description'], $domain) . '&nbsp;</div></td>';
         echo '</tr>';
      }
      echo '</table>';

      echo Html::scriptBlock('function showDescription(id, img) {
         if(img.getAttribute("alt") == "+") {
            img.setAttribute("alt", "-");
            img.classList.remove("fa-plus-circle");
            img.classList.add("fa-minus-circle");
            document.getElementById("desc" + id).style.display = "table-row";
         } else {
            img.setAttribute("alt", "+");
            img.classList.remove("fa-minus-circle");
            img.classList.add("fa-plus-circle");
            document.getElementById("desc" + id).style.display = "none";
         }
      }');
   }

   /**
    * Is the form accessible publicly (without being logged in) ?
    * @return boolean true if the form is accessible publicly
    */
   public function isPublicAccess() : bool {
      if ($this->isNewItem()) {
         return false;
      }
      return ($this->fields['access_rights'] == self::ACCESS_PUBLIC);
   }

   /**
    * Get a form a sub object (section, question, target)
    * Able to find a form from an item having only its parent FK and no ID
    *
    * @param CommonDBTM $item
    * @return null|self
    */
   public static function getByItem(?CommonDBTM $item): ?self {
      global $DB;

      if ($item instanceof self) {
         return $item;
      }

      $form = PluginFormcreatorCommon::getForm();
      $formFk = self::getForeignKeyField();

      if ($DB->fieldExists($item::getTable(), $formFk)) {
         if (!isset($item->fields[$formFk])) {
            return null;
         }
         $form->getFromDB($item->fields[$formFk]);
      } else {
         switch ($item::getType()) {
            case PluginFormcreatorQuestion::getType():
               $sectionFk = PluginFormcreatorSection::getForeignKeyField();
               if (!isset($item->fields[$sectionFk])) {
                  return null;
               }
               $iterator = $DB->request([
                  'SELECT' => self::getForeignKeyField(),
                  'FROM' => PluginFormcreatorSection::getTable(),
                  'WHERE' => [
                     'id' => $item->fields[$sectionFk],
                  ]
               ]);
               if ($iterator->count() !== 1) {
                  return null;
               }
               $form->getFromDB($iterator->current()[$formFk]);
               break;
         }
      }

      if ($form->isNewItem()) {
         return null;
      }

      return $form;
   }

   /**
    * Get an array of instances of all fields for the form
    *
    * @return PluginFormcreatorAbstractField[]
    */
   public function getFields() : array {
      $fields = [];
      if ($this->isNewItem()) {
         return $fields;
      }

      $question = new PluginFormcreatorQuestion();
      $found_questions = $question->getQuestionsFromForm($this->getID());
      foreach ($found_questions as $id => $question) {
         $fields[$id] = $question->getSubField();
      }

      return $fields;
   }

   /**
    * Get supported target itemtypes
    *
    * @return array
    */
   public static function getTargetTypes() : array {
      global $PLUGIN_HOOKS;

      $targets = [
         PluginFormcreatorTargetTicket::class,
         PluginFormcreatorTargetChange::class,
         PluginFormcreatorTargetProblem::class,
      ];

      foreach ($PLUGIN_HOOKS['formcreator_add_targets'] ?? [] as $plugin_targets) {
         array_push($targets, ...$plugin_targets);
      }

      return $targets;
   }

   /**
    * get all targets associated to the form
    *
    * @param int $formId
    * @return array
    */
   public function getTargetsFromForm() : array {
      global $DB;

      $targets = [];
      if ($this->isNewItem()) {
         return [];
      }

      foreach (PluginFormcreatorForm::getTargetTypes() as $targetType) {
         $request = [
            'SELECT' => 'id',
            'FROM' => $targetType::getTable(),
            'WHERE' => [
               self::getForeignKeyField() => $this->getID(),
            ]
         ];
         foreach ($DB->request($request) as $row) {
            $target = new $targetType();
            $target->getFromDB($row['id']);
            $targets[$targetType][$row['id']] = $target;
         }
      }

      return $targets;
   }

   public function showAddTargetForm() {
      echo '<form name="form_target" method="post" action="'. static::getFormURL() . '">';
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="4">'.__('Add a target', 'formcreator').'</th></tr>';

      echo '<tr>';
      echo '<td width="15%"><strong>'.__('Name').' <span style="color:red;">*</span></strong></td>';
      echo '<td width="40%">';
      echo Html::input('name', [
         'id' => 'name',
         'autofocus' => '',
         'required' => 'required',
      ]);
      echo '</td>';

      echo '<td width="15%"><strong>'._n('Type', 'Types', 1).' <span style="color:red;">*</span></strong></td>';
      echo '<td width="30%">';
      $targetTypes = [];
      foreach (PluginFormcreatorForm::getTargetTypes() as $targetType) {
         $targetTypes[$targetType] = $targetType::getTypeName(1);
      }
      Dropdown::showFromArray(
         'itemtype',
         $targetTypes,
         [
            'display_emptychoice' => true
         ]
      );
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td colspan="4" class="center">';
      echo Html::hidden('plugin_formcreator_forms_id', ['value' => $this->getID()]);
      echo Html::submit(__('Add'), ['name' => 'add_target']);
      echo '</td>';
      echo '</tr>';

      echo '</table>';
      Html::closeForm();
   }

   /**
    * Add a target item to the form
    *
    * @param string $input
    * @return integer|false ID of the new item or false on error
    */
   public function addTarget($input) {
      $itemtype = $input['itemtype'];
      if (!in_array($itemtype, PluginFormcreatorForm::getTargetTypes())) {
         Session::addMessageAfterRedirect(
            __('Unsupported target type.', 'formcreator'),
            false,
            ERROR
         );
         return false;
      }

      // Create the target
      $item = new $itemtype();
      unset($input['itemtype']);
      return $item->add($input);
   }

   /**
    * Duplicate a target for the form
    *
    * @param aray $input
    * @return boolean
    */
   public function duplicateTarget($input) {
      $itemtype = $input['itemtype'];
      if (!in_array($itemtype, PluginFormcreatorForm::getTargetTypes())) {
         Session::addMessageAfterRedirect(
            __('Unsupported target type.', 'formcreator'),
            false,
            ERROR
         );
         return false;
      }

      $item = $itemtype::getById($input['items_id']);
      $item->clone();
      return true;
   }

   /**
    * Delete a target for the form
    *
    * @param aray $input
    * @return boolean
    */
   public function deleteTarget($input) {
      $itemtype = $input['itemtype'];
      if (!in_array($itemtype, PluginFormcreatorForm::getTargetTypes())) {
         Session::addMessageAfterRedirect(
            __('Unsupported target type.', 'formcreator'),
            false,
            ERROR
         );
         return false;
      }

      $item = new $itemtype();
      $item->delete(['id' => $input['items_id']]);
      return true;
   }

   public function post_getFromDB() {
      global $TRANSLATE;

      // Load translation for the current language if different from origianl form's language
      $language = $_SESSION['glpilanguage'];
      $formId = $this->getID();
      if ($language != $this->fields['language']) {
         $eventManagerEnabled = $TRANSLATE->isEventManagerEnabled();
         $TRANSLATE->enableEventManager();
         $domain = PluginFormcreatorForm::getTranslationDomain($formId, $language);
         $TRANSLATE->getEventManager()->attach(
            Laminas\I18n\Translator\Translator::EVENT_MISSING_TRANSLATION,
            static function (Laminas\EventManager\EventInterface $event) use ($formId, $domain, $TRANSLATE) {
               if ($event->getParams()['text_domain'] == $domain) {
                  $file = PluginFormcreatorForm::getTranslationFile($formId);
                  if (!is_readable($file)) {
                     return;
                  }
                  $TRANSLATE->addTranslationFile('phparray', $file, $domain);
               }
            }
         );
         __('plugin_formcreator_load_check', $domain);

         if (!$eventManagerEnabled) {
            $TRANSLATE->disableEventManager();
         }
      }
      $_SESSION['formcreator']['languages'][$formId][$language] = true;

      // Load visibility restrictions
      $this->load1NTableData(PluginFormcreatorForm_User::class, 'users');
      $this->load1NTableData(PluginFormcreatorForm_Group::class, 'groups');
      $this->load1NTableData(PluginFormcreatorForm_Profile::class, 'profiles');
   }

   /**
    * Get the count of targets for this item
    */
   public function countTargets() {
      $nb = 0;
      foreach (PluginFormcreatorForm::getTargetTypes() as $targetType) {
         $nb += (new DbUtils())->countElementsInTable(
            $targetType::getTable(),
            [
               'WHERE' => [
                  self::getForeignKeyField() => $this->getID(),
               ]
            ]
         );
      }
      return $nb;
   }

   public static function getFormRestrictionSubQuery($helpdesk_home) {
      $form_table = PluginFormcreatorForm::getTable();

      $query = self::getFormListQuery();
      $query['SELECT'] = ["$form_table.id"];
      $query['WHERE']["$form_table.helpdesk_home"]  = "$helpdesk_home";

      return new QuerySubQuery($query);
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) : bool {
      return true;
   }

   /**
    * Get all translatable strings for a form, with optional search criterias
    *
    * @param array $options
    *              - language:      translation language when searching with is_translated criteria
    *              - id:
    *              - is_translated:
    *              - searchText:
    *
    * @return array
    */
   public function getTranslatableStrings(array $options = []) : array {
      $strings = [
         'itemlink' => [],
         'string'   => [],
         'text'     => [],
      ];

      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if is_translated is true or id is not empty
      ];
      $options = array_merge($params, $options);

      if ($this->isNewItem()) {
         return $strings;
      }

      $strings = $this->getMyTranslatableStrings($options);

      foreach ((new PluginFormcreatorSection())->getSectionsFromForm($this->getID()) as $section) {
         foreach ($section->getTranslatableStrings($options) as $type => $subStrings) {
            $strings[$type] = array_merge($strings[$type], $subStrings);
         }
      }

      foreach (self::getTargetTypes() as $targetType) {
         foreach ((new $targetType())->getTargetsForForm($this->getID()) as $target) {
            foreach ($target->getTranslatableStrings($options) as $type => $subStrings) {
               $strings[$type] = array_merge($strings[$type], $subStrings);
            }
         }
      }

      if ($options['is_translated'] !== null) {
         $translations = $this->getTranslations($options['language']);
         foreach ($strings as $type => $list) {
            if ($type == 'id') {
               continue;
            }
            foreach ($strings[$type] as $id => $original) {
               if ($options['is_translated'] === true && !isset($translations[$original])
                  || $options['is_translated'] === false && isset($translations[$original])) {
                  unset($strings[$type][$id]);
                  unset($strings['id'][$id]);
               }
            }
         }
      }

      $strings = $this->deduplicateTranslatable($strings);

      return $strings;
   }

   /**
    * Get the translation file for a form for a given language
    * @param int     $id         Form ID
    * @param string  $language   a language in the form fr_FR, rn_US
    * @return string             filename of the language resource
    */
   public static function getTranslationFile($id, $language = '') {
      $file = implode('/', [
         GLPI_LOCAL_I18N_DIR,
         'formcreator',
         self::getTranslationDomain($id, $language)
      ]) . '.php';

      if (!is_dir(dirname($file))) {
         mkdir(dirname($file), 0750, true);
      }

      return $file;
   }

   public static function getTranslationDomain($id, $language = '') {
      if ($language == '') {
         $language = $_SESSION['glpilanguage'];
      }
      return "form_{$id}_{$language}";
   }

   /**
    * get all translations for strings of the form
    * @param string $language the language to load (i.e. en_US)
    * @return array
    */
   public function getTranslations(string $language) : array {
      $file = $this->getTranslationFile($this->getID(), $language);
      if (!is_readable($file)) {
         return [];
      }

      if (function_exists('opcache_invalidate')) {
         opcache_invalidate($file, true);
      }
      $translations = include($file);
      if (!is_array($translations)) {
         return [];
      }
      return $translations;
   }

   /**
    * Overwrite translations with new data
    *
    * @param string $language
    * @param array  $translations array of translations
    *               - key original string
    *               - value: translated string
    * @return boolean true if sucess
    */
   public function setTranslations(string $language, array $translations) : bool {
      $file = $this->getTranslationFile($this->getID(), $language);
      if (is_file($file) && !is_writable($file)) {
         return false;
      }

      // CLeanup obsolete strings
      $existing_strings = $this->getTranslatableStrings();
      foreach (array_keys($translations) as $original) {
         if (!in_array($original, $existing_strings['itemlink'])
            && !in_array($original, $existing_strings['string'])
            && !in_array($original, $existing_strings['text'])) {
            unset($translations[$original]);
         }
      }

      $output = "<?php" . PHP_EOL . "return " . var_export($translations, true) . ";";
      $written = file_put_contents(
         $file,
         $output
      );
      return ($written == strlen($output));
   }

   /**
    * Choose the best language for publicly form
    *
    * @return string the best language for this form and session context
    */
   public function getBestLanguage() {
       global $DB;

      if ($this->isNewItem()) {
         return $_SESSION['glpilanguage'] ?? '';
      }

      // get original of the form, if any
      $availableLanguages = [];
      $defaultLanguage = '';
      if ($this->fields['language'] != '') {
         $availableLanguages = [$this->fields['language']];
         $defaultLanguage = $this->fields['language'];
      }
      if ($defaultLanguage == '') {
         $defaultLanguage = $_SESSION['glpilanguage'] ?? '';
      }

      //  get all available other languages for the form
      $formLanguageTable = PluginFormcreatorForm_Language::getTable();
      $formTable = PluginFormcreatorForm::getTable();
      $result = $DB->request([
         'SELECT'    => ["$formLanguageTable.name"],
         'FROM'      => $formLanguageTable,
         'LEFT JOIN' => [
            $formTable => [
               'FKEY' => [
                  $formTable => 'id',
                  $formLanguageTable => PluginFormcreatorForm::getForeignKeyField(),
               ],
            ],
         ],
         'WHERE'     => [
            "$formTable.id" => $this->getID()
         ],
      ]);
      foreach ($result as $row) {
         $availableLanguages[] = $row['name'];
      }

      if (count($availableLanguages) < 1) {
         return $defaultLanguage;
      }
      return \Locale::lookup($availableLanguages, $_SESSION['glpilanguage'], false, $defaultLanguage);
   }

   /**
    * Is validation required for the form ?
    *
    * @return bool true if valdiation required, false otherwise
    */
   public function validationRequired(): bool {
      return $this->fields['validation_required'] != self::VALIDATION_NONE;
   }

   /**
    * Can the current user show the form to fill an assistance request
    *
    * @return boolean true if the user can use the form
    */
   public function canViewForRequest(): bool {
      global $PLUGIN_HOOKS;

      if ($this->isNewItem()) {
         return false;
      }

      // Public forms -> always visible
      if ($this->fields['access_rights'] == self::ACCESS_PUBLIC) {
         return true;
      }

      // Restricted and private forms -> Check session
      if (Session::getLoginUserID() === false || !$this->checkEntity(true)) {
         return false;
      }

      // Form administrators can always access any forms
      if (self::canCreate()) {
         return true;
      }

      // Check restrictions if needed
      if ($this->fields['access_rights'] == self::ACCESS_RESTRICTED
         && !PluginFormcreatorFormAccessType::canSeeRestrictedForm($this)
      ) {
         return false;
      }

      // Check plugins restrictions
      if (isset($PLUGIN_HOOKS['formcreator_restrict_form'])) {
         foreach ($PLUGIN_HOOKS['formcreator_restrict_form'] as $plugin => $callable) {
            // Skip if invalid hook
            if (!is_callable($callable)) {
               trigger_error("formcreator_restrict_form[$plugin]: not a callable", E_USER_WARNING);
               continue;
            }

            if (!call_user_func($callable, $this)) {
               return false;
            }
         }
      }

      // All checks were succesful, display form
      return true;
   }

   public function showTagsList() {
      $extra_tags = Plugin::doHookFunction('formcreator_add_extra_tags', [
         'tags' => [],
         'form' => $this,
      ]);

      TemplateRenderer::getInstance()->display('@formcreator/components/form/form_taglist.html.twig', [
         'extra_tags' => $extra_tags['tags'],
         'item'   => $this,
      ]);

      return true;
   }

   /**
    * get the SQL joins and conditions to select the forms available for the current user
    */
   public static function getFormListQuery() {
      global $PLUGIN_HOOKS;

      $dbUtils           = new DbUtils();
      $formTable         = getTableForItemType(PluginFormcreatorForm::class);
      $formLanguageTable = getTableForItemType(PluginFormcreatorForm_Language::class);

      $entityRestrict = $dbUtils->getEntitiesRestrictCriteria($formTable, '', '', true, false, (new PluginFormcreatorForm())->maybeRecursive());
      $condition = [
         'FROM' => $formTable,
         'LEFT JOIN' => [
            $formLanguageTable => [
               'FKEY' => [
                  $formTable => 'id',
                  $formLanguageTable => PluginFormcreatorForm::getForeignKeyField(),
               ]
            ],
         ],
         'WHERE' => [
            'AND' => [
               "$formTable.is_active"  => '1',
               "$formTable.is_visible" => '1',
               "$formTable.is_deleted" => '0',
               [
                  'OR' => [
                     "$formTable.language" => [$_SESSION['glpilanguage'], '0', '', null],
                     "$formLanguageTable.name" => $_SESSION['glpilanguage']
                  ],
               ],
               [
                  'OR' => [
                     'access_rights' => ['!=', PluginFormcreatorForm::ACCESS_RESTRICTED],
                     PluginFormcreatorFormAccessType::getRestrictedFormListCriteria(),
                  ],
               ],
               ...array_values($PLUGIN_HOOKS['formcreator_restrict_forms'] ?? [])
            ] + $entityRestrict,
         ],
      ];

      return $condition;
   }

   /**
    * Get the extra content from other plugins to be added to the form header
    *
    * @return string
    */
   public function getExtraHeader(): string {
      global $PLUGIN_HOOKS;

      $extra_header = "";

      $callbacks = $PLUGIN_HOOKS['formcreator_insert_header'] ?? [];
      foreach ($callbacks as $plugin => $callback) {
         // Make sure the supplied hook is a valid callback
         if (!is_callable($callback)) {
            trigger_error(
               "Invalid 'formcreator_insert_header' hook for '$plugin' plugin",
               E_USER_WARNING
            );
         }

         // Insert extra plugin header if not empty
         if ($extra_plugin_header = call_user_func($callback, $this)) {
            $extra_header .= $extra_plugin_header;
         }
      }

      return $extra_header;
   }
}
