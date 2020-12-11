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

class PluginFormcreatorCommon {
   public static function getEnumValues($table, $field) {
      global $DB;

      $enum = [];
      if ($res = $DB->query( "SHOW COLUMNS FROM `$table` WHERE Field = '$field'" )) {
         if (version_compare(GLPI_VERSION, '9.5') >= 0) {
            $fa = 'fetchArray';
         } else {
            $fa = 'fetch_array';
         }
         $data = $DB->$fa($res);
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
   public static function isNotificationEnabled() {
      global $CFG_GLPI;
      $notification = $CFG_GLPI['use_notifications'];

      return ($notification == '1');
   }

   /**
    * Enable or disable notifications
    *
    * @param boolean $enable
    * @return void
    */
   public static function setNotification($enable) {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = $enable ? '1' : '0';
   }

   public static function getGlpiVersion() {
      return defined('GLPI_PREVER')
             ? GLPI_PREVER
             : GLPI_VERSION;
   }

   /**
    * Gets the ID of Formcreator request type
    */
   public static function getFormcreatorRequestTypeId() {
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
   public static function getMax(CommonDBTM $item, array $condition, $fieldName) {
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
   public static function prepareBooleanKeywords($keywords) {
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
    * get the list of pictograms available for the current version of GLPI
    *
    * @return array
    */
   public static function getFontAwesomePictoNames() {
      $list = require_once(__DIR__ . '/../' . self::getPictoFilename(GLPI_VERSION));
      return $list;
   }

   /**
    * get the name of the php file containing the pictogram list depending on the version of GLPI
    *
    * @param $version string GLPI version
    * @return string
    */
   public static function getPictoFilename($version) {
      if (version_compare($version, '9.4') < 0) {
         return 'data/font-awesome_9.3.php';
      }
      if (version_compare($version, '9.5') < 0) {
         return 'data/font-awesome_9.4.php';
      }
      if (version_compare($version, '9.6') < 0) {
         return 'data/font-awesome_9.5.php';
      }

      return '';
   }

   /**
    * Show a dropdown with Font Awesome pictograms
    *
    * @param string $name name of the HTML input
    * @param array $options
    * @return mixed
    */
   public static function showFontAwesomeDropdown($name, $options = []) {
      $items = [];
      foreach (static::getFontAwesomePictoNames() as $key => $value) {
         $items[$key] = /* '<i class="' . $key . '"></i>' . */ $value;
      }

      $previewId = $name . '_preview';
      $options['on_change'] = 'plugin_formceator_showPictogram(this, "' . $previewId . '")';
      $options['display'] = false;
      $options['display_emptychoice'] = true;
      if (!isset($options['value'])) {
         $options['value'] = '';
      }
      $output = Dropdown::showFromArray($name, $items, $options);
      $output .= '<i id="' . $previewId . '" class="'. $options['value'] . '"></i>';
      echo $output;
   }

   /**
    * Cancel a new ticketn while it is still allowed
    *
    * In case of error, a message is added to the session
    *
    * @param integer $id
    * @return boolean true on success, false otherwise
    */
   public static function cancelMyTicket($id) {
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
    *                           Ticket Validation Status
    *                +-------------+---------+---------+----------+
    *                |NULL or NONE | WAITING | REFUSED | ACCEPTED |
    *     + ---------+-------------+---------+---------+----------+
    * T S | INCOMING |     T            V          V         T
    * i t | ASSIGNED |     T            V          V         T
    * c a | PLANNED  |     T            V          V         T
    * k t | WAITING  |     T            V          V         T
    * e u | SOLVED   |     T            V          T         T
    * t s | CLOSED   |     T            V          T         T
    *
    * T = status picked from Ticket
    * V = status picked from Ticket Validation
    *
    * @param Ticket $item
    * @return integer
    */
   public static function getTicketStatusForIssue(Ticket $item) {
      $ticketValidations = (new TicketValidation())->find([
         'tickets_id' => $item->getID(),
      ], [
         'timeline_position ASC'
      ], 1);
      $ticketValidation = new TicketValidation();
      if (count($ticketValidations)) {
         $row = array_shift($ticketValidations);
         $ticketValidation->getFromDB($row['id']);
      }

      $status = $item->fields['status'];
      $user = 0;
      if (!$ticketValidation->isNewItem()) {
         $user = $ticketValidation->fields['users_id_validate'];
         switch ($ticketValidation->fields['status']) {
            case CommonITILValidation::WAITING:
               $status = PluginFormcreatorFormAnswer::STATUS_WAITING;
               break;

            case CommonITILValidation::REFUSED:
               if ($item->fields['status'] != Ticket::SOLVED && $item->fields['status'] != Ticket::CLOSED) {
                  $status = PluginFormcreatorFormAnswer::STATUS_REFUSED;
               }
               break;
         }
      }

      return ['status' => $status, 'user' => $user];
   }

   /**
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
}
