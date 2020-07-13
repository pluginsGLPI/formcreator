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

class PluginFormcreatorTimeField extends PluginFormcreatorField
{
   public function isPrerequisites() {
      return true;
   }

   public function getDesignSpecializationField() {
      $rand = mt_rand();

      $label = '';
      $field = '';

      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values'.$rand.'">';
      $additions .= __('Default values');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $value = Html::entities_deep($this->question->fields['default_values']);
      if (version_compare(GLPI_VERSION, '9.5') >= 0 && method_exists(Html::class, 'showTimeField')) {
         $additions .= Html::showTimeField('default_values', [
            'type'    => 'text',
            'id'      => 'default_values',
            'value'   => $value,
            'display' => false,
         ]);
      } else {
         $additions .= static::showTimeField('default_values', [
            'type'    => 'text',
            'id'      => 'default_values',
            'value'   => $value,
            'display' => false,
         ]);
      }
      $additions .= '</td>';
      $additions .= '<td></td>';
      $additions .= '<td></td>';
      $additions .= '</tr>';

      $common = parent::getDesignSpecializationField();
      $additions .= $common['additions'];

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => false,
         'may_be_required' => true,
      ];
   }

   public function displayField($canEdit = true) {
      if ($canEdit) {
         $id        = $this->question->getID();
         $rand      = mt_rand();
         $fieldName = 'formcreator_field_' . $id;

         if (version_compare(GLPI_VERSION, '9.5') >= 0 && method_exists(Html::class, 'showTimeField')) {
            Html::showTimeField($fieldName, [
               'value' => (strtotime($this->value) != '') ? $this->value : '',
               'rand'  => $rand,
            ]);
         } else {
            // TODO : drop when GLPI 9.4 compatibility is dropped
            static::showTimeField($fieldName, [
               'value' => (strtotime($this->value) != '') ? $this->value : '',
               'rand'  => $rand,
            ]);
         }
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeTime('$fieldName', '$rand');
         });");

      } else {
         echo $this->value;
      }
   }

   public function serializeValue() {
      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = $value;
   }

   public function getValueForDesign() {
      return $this->value;
   }

   public function getValueForTargetText($richText) {
      $date = DateTime::createFromFormat("H:i:s", $this->value);
      if ($date === false) {
         return ' ';
      }
      return Toolbox::addslashes_deep($date->format('H:i'));
   }

   public function moveUploads() {}

   public function getDocumentsForTarget() {
      return [];
   }

   public function isValid() {
      // If the field is required it can't be empty
      if ($this->isRequired() && (strtotime($this->value) === false)) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public static function getName() {
      return __('Time', 'formcreator');
   }

   public function hasInput($input) {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public static function canRequire() {
      return true;
   }

   public function equals($value) {
      if ($this->value === '') {
         $answer = '00:00';
      } else {
         $answer = $this->value;
      }
      $answerDatetime = DateTime::createFromFormat("HH:mm", $answer);
      $compareDatetime = DateTime::createFromFormat("HH:mm", $value);
      return $answerDatetime == $compareDatetime;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      if (empty($this->value)) {
         $answer = '00:00';
      } else {
         $answer = $this->value;
      }
      $answerDatetime = DateTime::createFromFormat("HH:mm", $answer);
      $compareDatetime = DateTime::createFromFormat("HH:mm", $value);
      return $answerDatetime > $compareDatetime;
   }

   public function lessThan($value) {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function parseAnswerValues($input, $nonDestructive = false) {

      $key = 'formcreator_field_' . $this->question->getID();
      if (!is_string($input[$key])) {
         return false;
      }

      $this->value = $input[$key];
      return true;
   }

   public function isAnonymousFormCompatible() {
      return true;
   }

   /**
    * Display TimeField form
    * @see Html::dateTime()
    *
    * @param string $name
    * @param array  $options
    *   - value      : default value to display (default '')
    *   - timestep   : step for time in minute (-1 use default config) (default -1)
    *   - maybeempty : may be empty ? (true by default)
    *   - canedit    : could not modify element (true by default)
    *   - mintime    : minimum allowed time (default '')
    *   - maxtime    : maximum allowed time (default '')
    *   - display    : boolean display or get string (default true)
    *   - rand       : specific random value (default generated one)
    *   - required   : required field (will add required attribute)
    * @return void
    */
   protected static function showTimeField($name, $options = []) {
      global $CFG_GLPI;

      $p = [];
      $p['value']      = '';
      $p['maybeempty'] = true;
      $p['canedit']    = true;
      $p['mintime']    = '';
      $p['maxtime']    = '';
      $p['timestep']   = -1;
      $p['display']    = true;
      $p['rand']       = mt_rand();
      $p['required']   = false;

      foreach ($options as $key => $val) {
         if (isset($p[$key])) {
            $p[$key] = $val;
         }
      }

      if ($p['timestep'] < 0) {
         $p['timestep'] = $CFG_GLPI['time_step'];
      }

      // Those vars are set but not used ...
      // check Hml::showDateTimeField()

      // $minHour   = 0;
      // $maxHour   = 23;
      // $minMinute = 0;
      // $maxMinute = 59;

      $hour_value = '';
      if (!empty($p['value'])) {
         $hour_value = $p['value'];
      }

      if (!empty($p['mintime'])) {
         // list($minHour, $minMinute) = explode(':', $p['mintime']);
         // $minMinute = 0;

         // Check time in interval
         if (!empty($hour_value) && ($hour_value < $p['mintime'])) {
            $hour_value = $p['mintime'];
         }
      }

      if (!empty($p['maxtime'])) {
         // list($maxHour, $maxMinute) = explode(':', $p['maxtime']);
         // $maxMinute = 59;

         // Check time in interval
         if (!empty($hour_value) && ($hour_value > $p['maxtime'])) {
            $hour_value = $p['maxtime'];
         }
      }

      // reconstruct value to be valid
      if (!empty($hour_value)) {
         $p['value'] = $hour_value;
      }

      $output = "<span class='no-wrap'>";
      $output .= "<input id='showtime".$p['rand']."' type='text' name='_$name' value='".
                   trim($p['value'])."'";
      if ($p['required'] == true) {
         $output .= " required='required'";
      }
      $output .= ">";
      $output .= Html::hidden($name, ['value' => $p['value'], 'id' => "hiddentime".$p['rand']]);
      if ($p['maybeempty'] && $p['canedit']) {
         $output .= "<span class='fa fa-times-circle pointer' title='".__s('Clear').
                      "' id='resettime".$p['rand']."'>" .
                      "<span class='sr-only'>" . __('Clear') . "</span></span>";
      }
      $output .= "</span>";

      $js = "$(function(){";
      if ($p['maybeempty'] && $p['canedit']) {
         $js .= "$('#resettime".$p['rand']."').click(function(){
                  $('#showtime".$p['rand']."').val('');
                  $('#hiddentime".$p['rand']."').val('');
                  });";
      }

      $js .= "$( '#showtime".$p['rand']."' ).timepicker({
         altField: '#hiddentime".$p['rand']."',
         altFormat: 'yy-mm-dd',
         altTimeFormat: 'HH:mm:ss',
         pickerTimeFormat : 'HH:mm',
         altFieldTimeOnly: false,
         firstDay: 1,
         parse: 'loose',
         showAnim: '',
         stepMinute: ".$p['timestep'].",
         showSecond: false,
         showOtherMonths: true,
         selectOtherMonths: true,
         showButtonPanel: true,
         changeMonth: true,
         changeYear: true,
         showOn: 'both',
         showWeek: true,
         controlType: 'select',
         buttonText: '<i class=\'far fa-calendar-alt\'></i>'";

      if (!$p['canedit']) {
         $js .= ",disabled: true";
      }

      $js .= ",timeFormat: 'HH:mm'";
      $js .= "}).next('.ui-datepicker-trigger').addClass('pointer');";
      $js .= "});";
      $output .= Html::scriptBlock($js);

      if ($p['display']) {
         echo $output;
         return $p['rand'];
      }
      return $output;
   }

   public function getHtmlIcon() {
      return '<i class="fa fa-clock" aria-hidden="true"></i>';
   }
}