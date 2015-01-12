<?php
abstract class PluginFormcreatorField implements Field
{
   const IS_MULTIPLE    = false;

   private $fields      = array();
   private $form_values = array();

   public function __construct($fields, $datas = array())
   {
      $this->fields                = $fields;
      $this->fields['form_values'] = $datas;
   }

   public function show($canEdit = true)
   {
      if($canEdit && $this->isRequired())    $required = ' required';
      else                                   $required = '';

      echo '<div class="form-group ' . $required . '" id="form-group-field' . $this->fields['id'] . '">';
      echo '<label>';
      echo $this->getLabel();
      if($canEdit && $this->isRequired()) {
         echo ' <span class="red">*</span>';
      }
      echo '</label>';
      $this->displayField($canEdit);
      echo '</div>';
      echo '<script type="text/javascript">formcreatorAddValueOf(' . $this->fields['id'] . ', "' . addslashes(json_encode(explode("\r\n", $this->getValue()))) . '");</script>';
   }

   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         if($canEdit && $this->isRequired()) $required = ' required';
         else                                $required = '';

         echo '<input type="text" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '"
                  id="formcreator_field_' . $this->fields['id'] . '"
                  value="' . $this->getValue() . '"' . $required . '
                  onchange="formcreatorChangeValueOf(' . $this->fields['id'] . ', this.value);" />';
      } else {
         echo $this->getAnswer();
      }
   }

   public function getLabel()
   {
      return $this->fields['name'];
   }

   public function getField()
   {

   }

   public function getValue()
   {
      if (isset($this->fields['answer'])) {
         return $this->fields['answer'];
      } else {
         return $this->fields['default_values'];
      }
   }

   public function getAnswer()
   {
      return $this->getSelectedValues();
   }

   public function getAvailableValues()
   {
      return $this->fields['values'];
   }

   public function isValid($value)
   {
      // If the field is not visible, don't check it's value
      if (!PluginFormcreatorFields::isVisible($this->fields, $this->fields['form_values'])) return true;

      // If the field is required it can't be empty
      if ($this->isRequired() && empty($value)) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public function isRequired()
   {
      return (PluginFormcreatorFields::isVisible($this->fields, $this->fields['form_values']) && $this->fields['required']);
   }

}
