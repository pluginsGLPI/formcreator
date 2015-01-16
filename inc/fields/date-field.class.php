<?php
class dateField extends PluginFormcreatorField
{
   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         $required = ($canEdit && $this->fields['required']) ? ' required' : '';
         $rand     = mt_rand();

         Html::showDateField('formcreator_field_' . $this->fields['id'], array(
            'value' => $this->getValue(),
            'rand'  => $rand,
         ));
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     $( "#showdate' . $rand . '" ).on("change", function() {
                        formcreatorChangeValueOf(' . $this->fields['id'] . ', this.value);
                     });
                     $( "#resetdate' . $rand . '" ).on("click", function() {
                        formcreatorChangeValueOf(' . $this->fields['id'] . ', "");
                     });
                  });
               </script>';

      } else {
         echo $this->getAnswer();
      }
   }

   public function getValue()
   {
      if (isset($this->fields['answer'])) {
         $date = $this->fields['answer'];
      } else {
         $date = $this->fields['default_values'];
      }
      return (strtotime($date) != '') ? $date : null;
   }

   public function getAnswer()
   {
      return Html::convDate($this->getValue());
   }

   public function isValid($value)
   {
      // If the field is not visible, don't check it's value
      if (!PluginFormcreatorFields::isVisible($this->fields['id'], $this->fields['answer']))
         return true;

      // If the field is required it can't be empty
      if ($this->isRequired() && (strtotime($value) == '')) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public static function getName()
   {
      return __('Date');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['date'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
