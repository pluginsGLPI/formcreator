<?php
class PluginFormcreatorDropdownField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         echo '<div class="form_field">';
         if (!empty($this->fields['values'])) {
            $rand     = mt_rand();
            $required = $this->fields['required'] ? ' required' : '';
            $itemtype = $this->fields['values'];

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
         $input['values']         = $input['dropdown_values'];
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
