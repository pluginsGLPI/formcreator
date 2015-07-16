<?php
class textareaField extends PluginFormcreatorField
{
   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         $required = $this->fields['required'] ? ' required' : '';

         echo '<textarea class="form-control"
                  rows="5"
                  name="formcreator_field_' . $this->fields['id'] . '"
                  id="formcreator_field_' . $this->fields['id'] . '"
                  onchange="formcreatorChangeValueOf(' . $this->fields['id'] . ', this.value);">'
                  . str_replace('\r\n', PHP_EOL, $this->getValue()) . '</textarea>';
         if ($GLOBALS['CFG_GLPI']["use_rich_text"]) {
            Html::initEditorSystem('formcreator_field_' . $this->fields['id']);
         }
      } else {
         if ($GLOBALS['CFG_GLPI']["use_rich_text"]) {
            echo plugin_formcreator_decode($this->getAnswer());
         } else {
            echo nl2br($this->getAnswer());
         }
      }
   }

   public function isValid($value)
   {
      if (!parent::isValid($value)) return false;

      // Min range not set or text length longer than min length
      if(!empty($this->fields['range_min']) && strlen($value) < $this->fields['range_min']) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too short (minimum %d characters):', 'formcreator'), $this->fields['range_min']) . ' ' . $this->fields['name'], false, ERROR);
         return false;

      // Max range not set or text length shorter than max length
      } elseif(!empty($this->fields['range_max']) && strlen($value) > $this->fields['range_max']) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too long (maximum %d characters):', 'formcreator'), $this->fields['range_max']) . ' ' . $this->fields['name'], false, ERROR);
         return false;

      // Specific format not set or well match
      } elseif(!empty($this->fields['regex']) && !preg_match($this->fields['regex'], $value)) {
         Session::addMessageAfterRedirect(__('Specific format does not match:', 'formcreator') . ' ' . $this->fields['name'], false, ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public static function getName()
   {
      return __('Textarea', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 1,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['textarea'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
