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
         $row = $request->next();
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
      ])->next();

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
   public static function getFontAwesomePictoNames() : array {
      $list = require_once(GLPI_PLUGIN_DOC_DIR . '/formcreator/' . self::getPictoFilename(GLPI_VERSION));
      return $list;
   }

   /**
    * get the name of the php file containing the pictogram list depending on the version of GLPI
    *
    * @param $version string GLPI version
    * @return string
    */
   public static function getPictoFilename(string $version) : string {
      return 'font-awesome.php';;
   }

   /**
    * Show a dropdown with Font Awesome pictograms
    *
    * @param string $name name of the HTML input
    * @param array $options
    * @return void
    */
   public static function showFontAwesomeDropdown(string $name, array $options = []) {
      $items = static::getFontAwesomePictoNames();

      $options = [
         'noselect2'           => true, // we will instanciate it later
         'display_emptychoice' => true,
         'rand'                => mt_rand(),
      ] + $options;
      if (!isset($options['value'])) {
         $options['value'] = '';
      }
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
    * @return array
    */
   public static function getTicketStatusForIssue(Ticket $item) : array {
      $ticketValidations = (new TicketValidation())->find([
         'tickets_id' => $item->getID(),
      ], [
         'timeline_position ASC'
      ], 1);
      $user = 0;
      $ticketValidationCount = count($ticketValidations);
      if ($ticketValidationCount) {
         $row = array_shift($ticketValidations);
         $user = $row['users_id_validate'];
      }

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

      return ['status' => $status, 'user' => $user];
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

      if (!isset($params['value'])) {
         $value = 0;
      } else {
         $value = $params['value'];
      }
      if (!isset($params['value'])) {
         $valuename = Dropdown::EMPTY_VALUE;
      } else {
         $valuename = $params['valuename'];
      }
      $on_change = '';
      if (isset($params["on_change"])) {
         $on_change = $params["on_change"];
         unset($params["on_change"]);
      }
      $width = '80%';
      if (isset($params["width"])) {
         $width = $params["width"];
         unset($params["width"]);
      }

      $placeholder = isset($params['placeholder']) ? $params['placeholder'] : '';
      $allowclear =  "false";
      if (strlen($placeholder) > 0 && !$params['display_emptychoice']) {
         $allowclear = "true";
      }

      unset($params['placeholder']);
      unset($params['value']);
      unset($params['valuename']);

      $options = [
         'id'        => $field_id,
         'selected'  => $value
      ];
      if (!empty($params['specific_tags'])) {
         foreach ($params['specific_tags'] as $tag => $val) {
            if (is_array($val)) {
               $val = implode(' ', $val);
            }
            $options[$tag] = $val;
         }
      }

      // manage multiple select (with multiple values)
      if (isset($params['values']) && count($params['values'])) {
         $values = array_combine($params['values'], $params['valuesnames']);
         $options['multiple'] = 'multiple';
         $options['selected'] = $params['values'];
      } else {
         $values = [];

         // simple select (multiple = no)
         if ((isset($params['display_emptychoice']) && $params['display_emptychoice'])
             || isset($params['toadd'][$value])
             || $value > 0) {
            $values = ["$value" => $valuename];
         }
      }

      // display select tag
      $output = Html::select($name, $values, $options);

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
                     searchText: params.term,
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

      $output .= Html::scriptBlock('$(function() {' . $js . '});');
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
      unset($_SESSION['plugin_formcreator']['captcha'][$captchaId]);

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

   public static function buildFontAwesomeData() {
      $fontAwesomeDir = GLPI_ROOT . '/public/lib/fortawesome/fontawesome-free/webfonts';
      $outFile = GLPI_PLUGIN_DOC_DIR . '/formcreator/font-awesome.php';
      @mkdir(dirname($outFile));
      if (!is_readable($fontAwesomeDir) || !is_writable(dirname($outFile))) {
         return false;
      }

      $faSvgFiles = [
            'fa' => "$fontAwesomeDir/fa-regular-400.svg",
            'fab' => "$fontAwesomeDir/fa-brands-400.svg",
            'fas' => "$fontAwesomeDir/fa-solid-900.svg",
         ];

      $fanames = [];
      $searchRegex = '#glyph-name=\"([^\"]*)\"#i';
      foreach ($faSvgFiles as $key => $svgSource) {
         $svg = file_get_contents($svgSource);
         $matches = null;
         preg_match_all($searchRegex, $svg, $matches);
         foreach ($matches[1] as $name) {
            $fanames["$key fa-$name"] = $name;
         }
         $list = '<?php' . PHP_EOL . 'return ' . var_export($fanames, true) . ';';
         $size = file_put_contents($outFile, $list);
         if ($size != strlen($list)) {
            return false;
         }
      }

      return true;
   }

   /**
    * get path to CSS file
    *
    * @return string
    */
   public static function getCssFilename() : string {
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
      set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
      });
      $isValid = !(preg_match($regex, null) === false);
      restore_error_handler();

      return $isValid;
   }

   public static function saveLayout() {
      $_SESSION['plugin_formcreator']['layout_backup'] =  $_SESSION['glpilayout'];
   }

   public static function restoreLayout() {
      $_SESSION['glpilayout'] = $_SESSION['plugin_formcreator']['layout_backup'] ?? $_SESSION['glpilayout'];
   }
}
