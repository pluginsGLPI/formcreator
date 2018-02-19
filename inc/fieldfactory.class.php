<?php
class PluginFormcreatorFieldFactory {

   /**
    * Returns a new instance of a field
    *
    * @param string $type
    * @param array $fields
    * @param array $data
    *
    * @throws PluginFormcreatorUnknownFieldException
    *
    * @return PluginFormcreatorActorField|PluginFormcreatorCheckboxesField|PluginFormcreatorDateField|PluginFormcreatorDatetimeField|PluginFormcreatorDescriptionField|PluginFormcreatorDropdownField|PluginFormcreatorEmailField|PluginFormcreatorFileField|PluginFormcreatorFloatField|PluginFormcreatorGlpiselectField|PluginFormcreatorhiddenField|PluginFormcreatorIntegerField|PluginFormcreatorIpField|PluginFormcreatorLdapselectField|PluginFormcreatorMultiselectField|PluginFormcreatorRadiosField|PluginFormcreatorSelectField|PluginFormcreatorTagField|PluginFormcreatorTextareaField|PluginFormcreatorTextField|PluginFormcreatorUrgencyField
    */
   public function createField($type, $fields, $data = []) {
      switch ($type) {
         case 'actor':
            return new PluginFormcreatorActorField($fields, $data);
            break;

         case 'checkboxes':
            return new PluginFormcreatorCheckboxesField($fields, $data);
            break;

         case 'date':
            return new PluginFormcreatorDateField($fields, $data);
            break;

         case 'datetime':
            return new PluginFormcreatorDatetimeField($fields, $data);
            break;

         case 'description':
            return new PluginFormcreatorDescriptionField($fields, $data);
            break;

         case 'dropdown':
            return new PluginFormcreatorDropdownField($fields, $data);
            break;

         case 'email':
            return new PluginFormcreatorEmailField($fields, $data);
            break;

         case 'file':
            return new PluginFormcreatorFileField($fields, $data);
            break;

         case 'float':
            return new PluginFormcreatorFloatField($fields, $data);
            break;

         case 'glpiselect':
            return new PluginFormcreatorGlpiselectField($fields, $data);
            break;

         case 'hidden':
            return new PluginFormcreatorhiddenField($fields, $data);
            break;

         case 'integer':
            return new PluginFormcreatorIntegerField($fields, $data);
            break;

         case 'ip':
            return new PluginFormcreatorIpField($fields, $data);
            break;

         case 'ldapselect':
            return new PluginFormcreatorLdapselectField($fields, $data);
            break;

         case 'multiselect':
            return new PluginFormcreatorMultiselectField($fields, $data);
            break;

         case 'radios':
            return new PluginFormcreatorRadiosField($fields, $data);
            break;

         case 'select':
            return new PluginFormcreatorSelectField($fields, $data);
            break;

         case 'tag':
            return new PluginFormcreatorTagField($fields, $data);
            break;

         case 'textarea':
            return new PluginFormcreatorTextareaField($fields, $data);
            break;

         case 'text':
            return new PluginFormcreatorTextField($fields, $data);
            break;

         case 'urgency':
            return new PluginFormcreatorUrgencyField($fields, $data);
            break;

         default:
            throw new PluginFormcreatorUnknownFieldException("Unknown field type '$type'");
      }
   }
}
