<?php
class PluginFormcreatorTextareaField extends PluginFormcreatorTextField
{
   public function displayField($canEdit = true) {
      global $CFG_GLPI;

      if ($canEdit) {
         $required = $this->fields['required'] ? ' required' : '';

         echo '<textarea class="form-control"
                  rows="5"
                  name="formcreator_field_'.$this->fields['id'].'"
                  id="formcreator_field_'.$this->fields['id'].'"
                  onchange="formcreatorChangeValueOf('.$this->fields['id'].', this.value);">'
                 .str_replace('\r\n', PHP_EOL, $this->getValue()).'</textarea>';
         if ($CFG_GLPI["use_rich_text"]) {
            Html::initEditorSystem('formcreator_field_'.$this->fields['id']);
         }
      } else {
         if ($CFG_GLPI["use_rich_text"]) {
            echo plugin_formcreator_decode($this->getAnswer());
         } else {
            echo nl2br($this->getAnswer());
         }
      }
   }

   public static function getName() {
      return __('Textarea', 'formcreator');
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['textarea'] = 'showFields(".implode(', ', $prefs).");';";
   }
}
