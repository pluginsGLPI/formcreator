<?php
class PluginFormcreatorDropdownField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         echo '<div class="form_field">';
         if (!empty($this->fields['values'])) {
            $rand     = mt_rand();
            $required = $this->fields['required'] ? ' required' : '';
            $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
            $itemtype = $decodedValues['itemtype'];

            $dparams = array('name'     => 'formcreator_field_' . $this->fields['id'],
                             'value'    => $this->getValue(),
                             'comments' => false,
                             'rand'     => $rand);

            if ($itemtype == "User") {
               $dparams['right'] = 'all';
            } else if ($itemtype == "ITILCategory") {
               if (isset ($_SESSION['glpiactiveprofile']['interface'])
                   && $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {
                  $dparams['condition'] = "`is_helpdeskvisible` = '1'";
               }
               switch ($decodedValues['show_ticket_categories']) {
                  case 'request':
                     $dparams['condition'] = "`is_request` = '1'";
                     break;
                  case 'incident':
                     $dparams['condition'] = "`is_incident` = '1'";
                     break;
                  case 'both':
                     $dparams['condition'] = "`is_request` = '1' AND `is_incident` = '1'";
               }
               if (isset($decodedValues['show_ticket_categories_depth'])
                   && $decodedValues['show_ticket_categories_depth'] > 0) {
                  $dparams['condition'] .= " AND `level` <= '" . $decodedValues['show_ticket_categories_depth'] . "'";
               }
            }

            $itemtype::dropdown($dparams);
         }
         echo '</div>' . PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("select2-selecting", function(e) {
                        formcreatorChangeValueOf (' . $this->fields['id']. ', e.val);
                     });
                  });
               </script>';
      } else {
         echo $this->getAnswer();
      }
   }

   public function getAnswer() {
      $value = $this->getValue();
      if ($this->fields['values'] == 'User') {
         return getUserName($value);
      } else {
         return Dropdown::getDropdownName(getTableForItemType($this->fields['values']), $value);
      }
   }

   public static function getName() {
      return _n('Dropdown', 'Dropdowns', 1);
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['dropdown_values'])) {
         if (empty($input['dropdown_values'])) {
            Session::addMessageAfterRedirect(
                  __('The field value is required:', 'formcreator') . ' ' . $input['name'],
                  false,
                  ERROR);
            return array();
         }
         $allowedDropdownValues = [];
         foreach (Dropdown::getStandardDropdownItemTypes() as $categoryOfTypes) {
            $allowedDropdownValues = array_merge($allowedDropdownValues, array_keys($categoryOfTypes));
         }
         if (!in_array($input['dropdown_values'], $allowedDropdownValues)) {
            Session::addMessageAfterRedirect(
                  __('Invalid dropdown type:', 'formcreator') . ' ' . $input['name'],
                  false,
                  ERROR);
            return [];
         }
         $input['values'] = [
            'itemtype' => $input['dropdown_values'],
         ];
         if ($input['dropdown_values'] == 'ITILCategory') {
            $input['values']['show_ticket_categories'] = $input['show_ticket_categories'];
            if ($input['show_ticket_categories_depth'] != (int) $input['show_ticket_categories_depth']) {
               $input['values']['show_ticket_categories_depth'] = 0;
            } else {
               $input['values']['show_ticket_categories_depth'] = $input['show_ticket_categories_depth'];
            }
         }
         $input['values'] = json_encode($input['values']);
         unset($input['show_ticket_categories']);
         unset($input['show_ticket_categories_depth']);
         $input['default_values'] = isset($input['dropdown_default_value']) ? $input['dropdown_default_value'] : '';
      }
      return $input;
   }

   public static function getPrefs() {
      return array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 1,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['dropdown'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
