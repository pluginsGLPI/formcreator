<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../inc/includes.php'));
require_once('field.interface.php');

abstract class PluginFormcreatorField implements Field
{
   const IS_MULTIPLE = false;

   protected $fields = array();

   public function __construct($fields, $datas = array())
   {
      $this->fields           = $fields;
      $this->fields['answer'] = $datas;
   }

   public function show($canEdit = true)
   {
      $required = ($canEdit && $this->fields['required']) ? ' required' : '';

      echo '<div class="form-group ' . $required . '" id="form-group-field' . $this->fields['id'] . '">';
      echo '<label for="formcreator_field_' . $this->fields['id'] . '">';
      echo $this->getLabel();
      if($canEdit && $this->fields['required']) {
         echo ' <span class="red">*</span>';
      }
      echo '</label>';

      echo '<div class="form_field">';
      $this->displayField($canEdit);
      echo '</div>';

      echo '<div class="help-block">' . html_entity_decode($this->fields['description']) . '</div>';
      echo '</div>';
      $value = is_array($this->getAnswer()) ? json_encode($this->getAnswer()) : $this->getAnswer();
      // $value = json_encode($this->getAnswer());
      echo '<script type="text/javascript">formcreatorAddValueOf(' . $this->fields['id'] . ', "'
         . str_replace("\r\n", "\\r\\n", addslashes($value)) . '");</script>';
   }

   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         echo '<input type="text" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '"
                  id="formcreator_field_' . $this->fields['id'] . '"
                  value="' . $this->getAnswer() . '"
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
         if(!is_array($this->fields['answer']) && is_array(json_decode($this->fields['answer']))) {
            return json_decode($this->fields['answer']);
         }
         return $this->fields['answer'];
      } else {
         if (static::IS_MULTIPLE) {
            return explode("\r\n", $this->fields['default_values']);
         }
         return $this->fields['default_values'];
      }
   }

   public function getAnswer()
   {
      return $this->getValue();
   }

   public function getAvailableValues()
   {
      return explode("\r\n", $this->fields['values']);
   }

   public function isValid($value)
   {
      // If the field is not visible, don't check it's value
      if (!PluginFormcreatorFields::isVisible($this->fields['id'], $this->fields['answer']))
         return true;

      // If the field is required it can't be empty
      if ($this->isRequired() && empty($value)) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public function isRequired()
   {
      $is_visible = PluginFormcreatorFields::isVisible($this->fields['id'], $this->fields['answer']);
      return ($is_visible && $this->fields['required']);
   }

}
