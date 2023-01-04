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

use Gregwar\Captcha\CaptchaBuilder;
use Glpi\Plugin\Hooks;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorCommon {
   /**
    * Get enum values for a field in the DB
    *
    * @param string $table table name
    * @param string $field field name
    * @return array enum values extracted from the CREATE TABLE statement
    */
   public static function getEnumValues(string $table, string $field) : array {
      global $DB;

      $enum = [];
      if ($res = $DB->query( "SHOW COLUMNS FROM `$table` WHERE Field = '$field'" )) {
         $data = $DB->fetchArray($res);
         $type = $data['Type'];
         $matches = null;
         preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
         if (!isset($matches[1])) {
            return [];
         }
         $enum = explode("','", $matches[1]);
      }

      return $enum;
   }

   /**
    * Get status of notifications
    *
    * @return boolean
    */
   public static function isNotificationEnabled() : bool {
      global $CFG_GLPI;
      $notification = $CFG_GLPI['use_notifications'];

      return ($notification == '1');
   }

   /**
    * Enable or disable notifications
    *
    * @param bool $enable
    * @return void
    */
   public static function setNotification(bool $enable) {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = $enable ? '1' : '0';
   }

   /**
    * Gets the ID of Formcreator request type
    *
    * @return int
    */
   public static function getFormcreatorRequestTypeId() : int {
      global $DB;

      $requesttypes_id = 0;
      $request = $DB->request(
         RequestType::getTable(),
         ['name' => ['LIKE', 'Formcreator']]
      );
      if (count($request) === 1) {
         $row = $request->current();
         $requesttypes_id = $row['id'];
      }

      return $requesttypes_id;
   }

   /**
    * Get the maximum value of a column for a given itemtype
    * @param CommonDBTM $item
    * @param array $condition
    * @param string $fieldName
    * @return null|integer
    */
   public static function getMax(CommonDBTM $item, array $condition, string $fieldName) {
      global $DB;

      $line = $DB->request([
         'SELECT' => [$fieldName],
         'FROM'   => $item::getTable(),
         'WHERE'  => $condition,
         'ORDER'  => "$fieldName DESC",
         'LIMIT'  => 1
      ])->current();

      if (!isset($line[$fieldName])) {
         return null;
      }
      return (int) $line[$fieldName];
   }

   /**
    * Prepare keywords for a fulltext search in boolean mode
    * takes into account strings in double quotes
    *
    * @param string $keywords
    * @return string
    */
   public static function prepareBooleanKeywords(string $keywords) : string {
      // @see https://stackoverflow.com/questions/2202435/php-explode-the-string-but-treat-words-in-quotes-as-a-single-word
      preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $keywords, $matches);
      $matches = $matches[0];
      foreach ($matches as &$keyword) {
         if (strpos($keyword, '"') !== 0) {
            // keyword does not begins with a double quote (assume it does not ends with this char)
            $keyword = rtrim($keyword, '*');
            $keyword .= '*';
         }
      }

      return implode(' ', $matches);
   }

   /**
    * Get the list of pictograms available for the current version of GLPI
    *
    * @return array
    */
   public static function getFontAwesomePictoNames(): array {
      static $list = null;

      $list = $list ?? require_once(Plugin::getPhpDir('formcreator') . '/data/' . self::getPictoFilename());
      return $list;
   }

   /**
    * get the name of the php file containing the pictogram list depending on the version of GLPI
    *
    * @param $version string GLPI version
    * @return string
    */
   public static function getPictoFilename() : string {
      return 'font-awesome.php';
   }

   /**
    * Show a dropdown with Font Awesome pictograms
    *
    * @param string $name name of the HTML input
    * @param array $options
    * @return string
    */
   public static function showFontAwesomeDropdown(string $name, array $options = []) {
      $items = static::getFontAwesomePictoNames();

      $options = [
         'noselect2'           => true, // we will instanciate it later
         'display_emptychoice' => true,
         'rand'                => mt_rand(),
      ] + $options;
      $options['value'] ?? '';
      Dropdown::showFromArray($name, $items, $options);

      // templates for select2 dropdown
      $js = <<<JAVASCRIPT
      $(function() {
         formatFormIcon{$options['rand']} = function(icon) {
            if (!icon.id) {
               return icon.text;
            }

            return $('<span><i class="fa-lg '+icon.id+'"></i>&nbsp;<span>'+icon.text+'</span></span>');
         };

         $("#dropdown_{$name}{$options['rand']}").select2({
            width: '60%',
            templateSelection: formatFormIcon{$options['rand']},
            templateResult: formatFormIcon{$options['rand']}
         });
      });
JAVASCRIPT;
      echo Html::scriptBlock($js);
   }

   /**
    * Cancel a new ticketn while it is still allowed
    *
    * In case of error, a message is added to the session
    *
    * @param int $id
    * @return boolean true on success, false otherwise
    */
   public static function cancelMyTicket(int $id) : bool {
      $ticket = new Ticket();
      $ticket->getFromDB($id);
      if (!$ticket->canRequesterUpdateItem()) {
         Session::addMessageAfterRedirect(__('You cannot delete this issue. Maybe it is taken into account.', 'formcreator'), true, ERROR);
         return false;
      }

      if (!$ticket->delete($ticket->fields)) {
         Session::addMessageAfterRedirect(__('Failed to delete this issue. An internal error occured.', 'formcreator'), true, ERROR);
         return false;
      }

      return true;
   }

   /**
    * Get the status to set for an issue matching a ticket
    * Tightly related to SQL query in SyncIssues automatic actions
    *
    * Conversion matrix
    *
    *                               Validation Status
    *                +-------------+---------+---------+----------+
    *                |NULL or NONE | WAITING | REFUSED | ACCEPTED |
    *     + ---------+-------------+---------+---------+----------+
    * T S | INCOMING |      T           V         V          T
    * i t | ASSIGNED |      T           V         V          T
    * c a | PLANNED  |      T           V         V          T
    * k t | WAITING  |      T           V         V          T
    * e u | SOLVED   |      T           T         T          T
    * t s | CLOSED   |      T           T         T          T
    *
    * T = status picked from Ticket
    * V = status picked from Validation
    *
    * @param Ticket $item
    * @return int
    */
   public static function getTicketStatusForIssue(Ticket $item) : int {
      $ticketValidations = (new TicketValidation())->find([
         'tickets_id' => $item->getID(),
      ], [
         'timeline_position ASC'
      ], 1);
      $ticketValidationCount = count($ticketValidations);

      $status = $item->fields['status'];
      if ($ticketValidationCount > 0 && !in_array($item->fields['global_validation'], [TicketValidation::ACCEPTED, TicketValidation::NONE])) {
         switch ($item->fields['global_validation']) {
            case CommonITILValidation::WAITING:
               if (!in_array($item->fields['status'], [Ticket::SOLVED, Ticket::CLOSED])) {
                  $status = PluginFormcreatorFormAnswer::STATUS_WAITING;
               }
               break;
            case CommonITILValidation::REFUSED:
               if (!in_array($item->fields['status'], [Ticket::SOLVED, Ticket::CLOSED])) {
                  $status = PluginFormcreatorFormAnswer::STATUS_REFUSED;
               }
               break;
         }
      }

      return (int) $status;
   }

   /**
    * Undocumented function
    *
    * @return boolean
    */
   public static function canValidate() : bool {
      return Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
         || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST);
   }

   /*
    * Create Ajax dropdown to clean JS
    * Code copied and modified from Html::jsAjaxDropdown to allow
    * item creation in dropdown
    *
    * @param $name
    * @param $field_id   string   id of the dom element
    * @param $url        string   URL to get datas
    * @param $params     array    of parameters
    *            must contains :
    *                if single select
    *                   - 'value'       : default value selected
    *                   - 'valuename'   : default name of selected value
    *                if multiple select
    *                   - 'values'      : default values selected
    *                   - 'valuesnames' : default names of selected values
    *
    * @since 0.85.
    *
    * @return String
   **/
   public static function jsAjaxDropdown($name, $field_id, $url, $params = []) {
      global $CFG_GLPI;

      $default_options = [
         'value'               => 0,
         'valuename'           => Dropdown::EMPTY_VALUE,
         'multiple'            => false,
         'values'              => [],
         'valuesnames'         => [],
         'on_change'           => '',
         'width'               => '80%',
         'placeholder'         => '',
         'display_emptychoice' => false,
         'specific_tags'       => [],
         'parent_id_field'     => null,
         'multiple'            => false,
      ];
      $params = array_merge($default_options, $params);

      $value = $params['value'];
      $width = $params["width"];
      $valuename = $params['valuename'];
      $on_change = $params["on_change"];
      $placeholder = $params['placeholder'] ?? '';
      $multiple = $params['multiple'];
      unset($params["on_change"]);
      unset($params["width"]);

      $allowclear =  "false";
      if (strlen($placeholder) > 0 && !$params['display_emptychoice']) {
         $allowclear = "true";
      }

      $options = [
         'id'        => $field_id,
         'selected'  => $value
      ];

       // manage multiple select (with multiple values)
      if ($params['multiple']) {
         $values = array_combine($params['values'], $params['valuesnames']);
         $options['multiple'] = 'multiple';
         $options['selected'] = $params['values'];
      } else {
         $values = [];

         // simple select (multiple = no)
         if ($value !== null) {
               $values = ["$value" => $valuename];
         }
      }
      $parent_id_field = $params['parent_id_field'];

      unset($params['placeholder']);
      unset($params['value']);
      unset($params['valuename']);

      foreach ($params['specific_tags'] as $tag => $val) {
         if (is_array($val)) {
            $val = implode(' ', $val);
         }
         $options[$tag] = $val;
      }

      // display select tag
      $output = '';

      $js = "
         var params_$field_id = {";
      foreach ($params as $key => $val) {
         // Specific boolean case
         if (is_bool($val)) {
            $js .= "$key: ".($val?1:0).",\n";
         } else {
            $js .= "$key: ".json_encode($val).",\n";
         }
      }
      $js.= "};

         $('#$field_id').select2({
            width: '$width',
            multiple: '$multiple',
            placeholder: '$placeholder',
            allowClear: $allowclear,
            minimumInputLength: 0,
            quietMillis: 100,
            dropdownAutoWidth: true,
            minimumResultsForSearch: ".$CFG_GLPI['ajax_limit_count'].",
            tokenSeparators: [',', ';'],
            tags: true,
            ajax: {
               url: '$url',
               dataType: 'json',
               type: 'POST',
               data: function (params) {
                  query = params;
                  return $.extend({}, params_$field_id, {
                     searchText: params.term,";
      if ($parent_id_field !== null) {
         $js .= "
                     parent_id : document.getElementById('" . $parent_id_field . "').value,";
      }
      $js .= "
                     page_limit: ".$CFG_GLPI['dropdown_max'].", // page size
                     page: params.page || 1, // page number
                  });
               },
               processResults: function (data, params) {
                  params.page = params.page || 1;
                  var more = (data.count >= ".$CFG_GLPI['dropdown_max'].");

                  return {
                     results: data.results,
                     pagination: {
                           more: more
                     }
                  };
               }
            },
            templateResult: templateResult,
            templateSelection: templateSelection
         })
         .bind('setValue', function(e, value) {
            $.ajax('$url', {
               data: $.extend({}, params_$field_id, {
                  _one_id: value,
               }),
               dataType: 'json',
               type: 'POST',
            }).done(function(data) {

               var iterate_options = function(options, value) {
                  var to_return = false;
                  $.each(options, function(index, option) {
                     if (option.hasOwnProperty('id')
                         && option.id == value) {
                        to_return = option;
                        return false; // act as break;
                     }

                     if (option.hasOwnProperty('children')) {
                        to_return = iterate_options(option.children, value);
                     }
                  });

                  return to_return;
               };

               var option = iterate_options(data.results, value);
               if (option !== false) {
                  var newOption = new Option(option.text, option.id, true, true);
                   $('#$field_id').append(newOption).trigger('change');
               }
            });
         });
         ";
      if (!empty($on_change)) {
         $js .= " $('#$field_id').on('change', function(e) {".
                  stripslashes($on_change)."});";
      }

      $js .= " $('label[for=$field_id]').on('click', function(){ $('#$field_id').select2('open'); });";
      $js .= " $('#$field_id').on('select2:open', function(e){";
      $js .= "    const search_input = document.querySelector(`.select2-search__field[aria-controls='select2-\${e.target.id}-results']`);";
      $js .= "    if (search_input) {";
      $js .= "       search_input.focus();";
      $js .= "    }";
      $js .= " });";

      $output .= Html::scriptBlock('$(function() {' . $js . '});');

      // display select tag
      $options['class'] = $params['class'] ?? 'form-select';
      $output .= Html::select($name, $values, $options);

      return $output;
   }

   public static function getCaptcha($captchaId = null) {
      $captchaBuilder = new CaptchaBuilder();
      $captchaBuilder->build();
      $inlineImg = 'data:image/png;base64,' . base64_encode($captchaBuilder->get());

      $_SESSION['plugin_formcreator']['captcha'][$captchaId] = [
         'time'   => time(),
         'phrase' => $captchaBuilder->getPhrase()
      ];

      return ['img' => $inlineImg, 'phrase' => $captchaBuilder->getPhrase()];
   }

   public static function checkCaptcha($captchaId, $challenge, $expiration = 600) {
      self::cleanOldCaptchas($expiration);
      if (!isset($_SESSION['plugin_formcreator']['captcha'][$captchaId])) {
         return false;
      }

      if ($_SESSION['plugin_formcreator']['captcha'][$captchaId]['time'] + $expiration < time()) {
         unset($_SESSION['plugin_formcreator']['captcha'][$captchaId]);
         return false;
      }

      $result = strtolower($_SESSION['plugin_formcreator']['captcha'][$captchaId]['phrase']) == strtolower((string) $challenge);

      return $result;
   }

   public static function cleanOldCaptchas($expiration = 600) {
      // cleanup expired captchas
      $now = time();
      $count = 10; // Cleanup at most 10 captchas
      foreach ($_SESSION['plugin_formcreator']['captcha'] as &$captcha) {
         if ($captcha['time'] + $expiration < $now) {
            unset($captcha);
            $count--;
            if ($count <= 0) {
               break;
            }
         }
      }
   }

   /**
    * get path to CSS file
    *
    * @return string
    */
   public static function getCssFilename() : string {
      if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
         return 'css/styles.scss';
      }
      $scssFile = Plugin::getPhpDir('formcreator', false) . '/css/styles.scss';
      $compiled_path =  Plugin::getPhpDir('formcreator') . "/css_compiled/" . basename($scssFile, '.scss') . ".min.css";
      if (!file_exists($compiled_path)) {
         $css = Html::compileScss(
            [
               'file'    => $scssFile,
               'nocache' => true,
               'debug'   => true,
            ]
         );
         if (strlen($css) === @file_put_contents($compiled_path, $css)) {
            return 'css/styles.scss';
         }
      }
      return 'css_compiled/styles.min.css';
   }

   /**
    * Validate a regular expression
    *
    * @param string $regex
    * @return boolean true if the regex is valid, false otherwise
    */
   public static function checkRegex($regex) {
      // Avoid php notice when validating the regular expression
      set_error_handler(function ($errno, $errstr, $errfile = null, $errline = null) {
      });
      $isValid = !(preg_match($regex, null) === false);
      restore_error_handler();

      return $isValid;
   }

   /**
    * Find documents data matching the tags found in the string
    * Tags are deduplicated
    *
    * @param string $content_text String to search tags from
    *
    * @return array data from documents having tags found
    */
   public static function getDocumentsFromTag(string $content_text): array {
      preg_match_all(
         '/'.Document::getImageTag('(([a-z0-9]+|[\.\-]?)+)').'/',
         $content_text,
         $matches, PREG_PATTERN_ORDER
      );
      if (!isset($matches[1]) || count($matches[1]) == 0) {
         return [];
      }

      $document = new Document();
      return $document->find(['tag' => array_unique($matches[1])]);
   }

   /**
    * find a document with a file attached, with respect of blacklisting
    *
    * @param integer $entity    entity of the document
    * @param string  $path      path of the searched file
    *
    * @return false|Document
    */
   public static function getDuplicateOf(int $entities_id, string $filename) {
      $document = new Document();
      if (!$document->getFromDBbyContent($entities_id, $filename)) {
         return false;
      }

      if ($document->fields['is_blacklisted']) {
         return false;
      }

      return $document;
   }

   /**
    * Get an empty form answer object from Formcreator or Advanced Formcreator
    * Advanced Formcreator redefines some methods of thos class
    *
    * TODO: This method is unful as lon tehre is no dependency injection in
    * GLPI
    *
    * @return PluginFormcreatorFormAnswer
    */
   public static function getFormAnswer(): PluginFormcreatorFormAnswer {
      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         return new PluginAdvformFormAnswer();
      }

      return new PluginFormcreatorFormAnswer();
   }

   /**
    * Get the real itemtype for form answer implementation, depending on the availability of Advanced Formcreator
    *
    * @return string
    */
   public static function getFormanswerItemtype() {
      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         return PluginAdvformFormAnswer::class;
      }

      return PluginFormcreatorFormAnswer::class;
   }

   public static function getForm() {
      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         return new PluginAdvformForm();
      }

      return new PluginFormcreatorForm();
   }

   public static function getInterface() {
      if (Session::getCurrentInterface() == 'helpdesk') {
         if (plugin_formcreator_replaceHelpdesk()) {
            return 'servicecatalog';
         }
         return 'self-service';
      }
      if (!empty($_SESSION['glpiactiveprofile'])) {
         return 'central';
      }

      return 'public';
   }

   public static function header() {
      switch (self::getInterface()) {
         case "servicecatalog":
         case "self-service":
            return Html::helpHeader(
               __('Form list', 'formcreator'),
               'seek_assistance',
               PluginFormcreatorForm::class
            );
         case "central":
            return Html::header(
               __('Form Creator', 'formcreator'),
               $_SERVER['PHP_SELF'],
               'helpdesk',
               'PluginFormcreatorFormlist'
            );
         case "public":
         default:
            Html::nullHeader(__('Form Creator', 'formcreator'), $_SERVER['PHP_SELF']);
            Html::displayMessageAfterRedirect();
            return true;
      }
   }

   /**
    * Gets the footer HTML
    *
    * @return string HTML to show a footer
    */
   public static function footer() {
      switch (self::getInterface()) {
         case "servicecatalog";
         case "self-service";
            return Html::helpFooter();
         case "central";
            return Html::footer();
         case "public";
         default:
            return Html::nullFooter();
      }
   }

   /**
    * remove form answer from associatable items to tickets when viewing a form answer
    * this removes the button "add a ticket for this item"
    *
    * @param array $options
    * @return void
    */
   public static function hookPreShowTab(array $options) {
      if ($options['item']::getType() != PluginFormcreatorFormAnswer::getType()) {
         return;
      }

      $_SESSION['plugin_formcreator']['helpdesk_item_type_backup'] = $_SESSION["glpiactiveprofile"]["helpdesk_item_type"];
      $_SESSION["glpiactiveprofile"]["helpdesk_item_type"] = array_diff(
         $_SESSION["glpiactiveprofile"]["helpdesk_item_type"],
         [PluginFormcreatorFormAnswer::getType()]
      );
   }

   /**
   * Restore the associatable items to tickets into the session
   *
   * @param array $options
   * @return void
   */
   public static function hookPostShowTab(array $options) {
      if ($options['item']::getType() != PluginFormcreatorFormAnswer::getType()) {
         return;
      }

      $_SESSION["glpiactiveprofile"]["helpdesk_item_type"] = $_SESSION['plugin_formcreator']['helpdesk_item_type_backup'];
   }

   public static function hookRedefineMenu($menus) {
      global $DB;

      if (Session::getCurrentInterface() != 'helpdesk') {
         return $menus;
      }

      if (plugin_formcreator_replaceHelpdesk() === false) {
         $newMenu = [];
         foreach ($menus as $menuKey => $menuItem) {
            if ($menuKey != 'tickets') {
               $newMenu[$menuKey] = $menuItem;
               continue;
            }
            $newMenu['seek_assistance'] = [
               'default' => Plugin::getWebDir('formcreator', false) . '/front/wizard.php',
               'title'   => PluginFormcreatorForm::getTypeName(Session::getPluralNumber()),
               'icon'    => 'fa-fw ti ti-headset',
            ];
            $newMenu[$menuKey] = $menuItem;
         }
         return $newMenu;
      }

      $newMenu = [];
      $newMenu['seek_assistance'] = [
         'default' => Plugin::getWebDir('formcreator', false) . '/front/wizard.php',
         'title'   => __('Seek assistance', 'formcreator'),
         'icon'    => 'fa-fw ti ti-headset',
      ];
      if (Ticket::canView()) {
         $newMenu['my_assistance_requests'] = [
            'default' => PluginFormcreatorIssue::getSearchURL(false),
            'title'   => __('My requests for assistance', 'formcreator'),
            'icon'    => 'fa-fw ti ti-list',
            'content' => [
               PluginFormcreatorIssue::class => [
                  'title' => __('My requests for assistance', 'formcreator'),
                  'icon'  => 'fa-fw ti ti-list',
                  'links'   => [
                     'lists' => '',
                  ],
               ],
            ],
         ];
      }

      if (PluginFormcreatorEntityConfig::getUsedConfig('is_kb_separated', Session::getActiveEntity()) == PluginFormcreatorEntityConfig::CONFIG_KB_DISTINCT
         && Session::haveRight('knowbase', KnowbaseItem::READFAQ)
      ) {
         $newMenu['faq'] = $menus['faq'];
         $newMenu['faq']['default'] = Plugin::getWebDir('formcreator', false) . '/front/knowbaseitem.php';
      }
      if (Session::haveRight("reservation", ReservationItem::RESERVEANITEM)) {
         if (isset($menus['reservation'])) {
            $newMenu['reservation'] = $menus['reservation'];
         }
      }
      $rssFeedTable = RSSFeed::getTable();
      $criteria = [
         'SELECT'   => "$rssFeedTable.*",
         'DISTINCT' => true,
         'FROM'     => $rssFeedTable,
         'ORDER'    => "$rssFeedTable.name"
      ];
      $criteria = $criteria + RSSFeed::getVisibilityCriteria();
      $criteria['WHERE']["$rssFeedTable.users_id"] = ['<>', Session::getLoginUserID()];
      $iterator = $DB->request($criteria);
      $hasRssFeeds = $iterator->count() > 0;

      if (RSSFeed::canView() && $hasRssFeeds) {
         $newMenu['feeds'] = [
            'default' => Plugin::getWebDir('formcreator', false) . '/front/wizardfeeds.php',
            'title'   => __('Consult feeds', 'formcreator'),
            'icon'    => 'fa-fw ti ti-rss',
         ];
      }

      // Add plugins menus
      $plugin_menus = $menus['plugins']['content'] ?? [];
      foreach ($plugin_menus as $menu_name => $menu_data) {
         $menu_data['default'] = $menu_data['page'] ?? '#';
         $newMenu[$menu_name] = $menu_data;
      }

      return $newMenu;
   }

   /**
    * Show a mini dashboard
    *
    * @return void
    */
   public static function showMiniDashboard(): void {
      Plugin::doHook(Hooks::DISPLAY_CENTRAL);

      if (PluginFormcreatorEntityconfig::getUsedConfig('is_dashboard_visible', Session::getActiveEntity()) == PluginFormcreatorEntityconfig::CONFIG_DASHBOARD_VISIBLE) {
         if (version_compare(GLPI_VERSION, '10.0.3') > 0) {
            $dashboard = new Glpi\Dashboard\Grid('plugin_formcreator_issue_counters', 33, 1, 'mini_core');
         } else {
            $dashboard = new Glpi\Dashboard\Grid('plugin_formcreator_issue_counters', 33, 0, 'mini_core');
         }
         echo "<div class='formcreator_dashboard_container'>";
         $dashboard->show(true);
         echo "</div>";
      }
   }
}
