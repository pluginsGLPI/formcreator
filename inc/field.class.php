<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

require_once(realpath(dirname(__FILE__ ) . '/../../../inc/includes.php'));

abstract class PluginFormcreatorField implements PluginFormcreatorFieldInterface
{
   const IS_MULTIPLE = false;

   /** @var array $fields Fields of an instance of PluginFormcreatorQuestion */
   protected $fields = [];

   /**
    *
    * @param array $fields fields of a PluginFormcreatorQuestion instance
    * @param array $data value of all fields
    */
   public function __construct($fields, $data = []) {
      $this->fields           = $fields;
      $this->fields['answer'] = $data;
   }

   public function prepareQuestionInputForSave($input) {
      return  $input;
   }

   /**
    *
    * @param boolean $canEdit
    */
   public function show($canEdit = true) {
      $required = ($canEdit && $this->fields['required']) ? ' required' : '';

      echo '<div class="form-group ' . $required . '" id="form-group-field' . $this->fields['id'] . '">';
      echo '<label for="formcreator_field_' . $this->fields['id'] . '">';
      echo $this->getLabel();
      if ($canEdit && $this->fields['required']) {
         echo ' <span class="red">*</span>';
      }
      echo '</label>';
      echo '<div class="help-block">' . html_entity_decode($this->fields['description']) . '</div>';

      echo '<div class="form_field">';
      $this->displayField($canEdit);
      echo '</div>';

      echo '</div>';
      $value = is_array($this->getAnswer()) ? json_encode($this->getAnswer()) : $this->getAnswer();
      // $value = json_encode($this->getAnswer());
      if ($this->fields['fieldtype'] == 'dropdown') {
         echo Html::scriptBlock('$(function() {
            formcreatorAddValueOf(' . $this->fields['id'] . ', "'
            . str_replace("\r\n", "\\r\\n", addslashes($this->fields['answer'])) . '");
         })');
      } else {
         echo Html::scriptBlock('$(function() {
            formcreatorAddValueOf(' . $this->fields['id'] . ', "'
               . str_replace("\r\n", "\\r\\n", addslashes(html_entity_decode($value))) . '");
         })');
      }
   }

   /**
    * Outputs the HTML representing the field
    *
    * @param string $canEdit
    */
   public function displayField($canEdit = true) {
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

   /**
    * Gets the label of the field
    *
    * @return string
    */
   public function getLabel() {
      return $this->fields['name'];
   }

   public function getValue() {
      if (isset($this->fields['answer'])) {
         if (!is_array($this->fields['answer']) && is_array(json_decode($this->fields['answer']))) {
            return json_decode($this->fields['answer']);
         }
         return $this->fields['answer'];
      } else {
         if (static::IS_MULTIPLE) {
            return explode("\r\n", $this->fields['default_values']);
         }
         if (!$this->fields['show_empty'] && empty($this->fields['default_values'])) {
            $availableValues = $this->getAvailableValues();
            return array_shift($availableValues);
         }
         return $this->fields['default_values'];
      }
   }

   public function getAnswer() {
      return $this->getValue();
   }

   /**
    * Gets the available values for the field
    *
    * @return array
    */
   public function getAvailableValues() {
      return explode("\r\n", $this->fields['values']);
   }

   /**
    * Is the field valid for thegiven value ?
    *
    * @param string $value
    *
    * @return boolean True if the field has a valid value, false otherwise
    */
   public function isValid($value) {
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

   /**
    * Is the field required ?
    *
    * @return boolean
    */
   public function isRequired() {
      return $this->fields['required'];
   }

   /**
    * trim values separated by \r\n
    *
    * @param string $value a value or default value
    *
    * @return string
    */
   protected function trimValue($value) {
      $value = explode('\\r\\n', $value);
      $value = array_map('trim', $value);
      return implode('\\r\\n', $value);
   }

   public function getFieldTypeName() {
      $classname = get_called_class();
      $matches = null;
      preg_match("#^PluginFormcreator(.+)Field$#", $classname, $matches);
      return strtolower($matches[1]);
   }

   public function getUsedParameters() {
      return [];
   }

   public final function addParameters(PluginFormcreatorQuestion $question, array $input) {
      $fieldTypeName = $this->getFieldTypeName();
      if (!isset($input['_parameters'][$fieldTypeName])) {
         return;
      }

      foreach ($this->getUsedParameters() as $fieldName => $parameter) {
         $input['_parameters'][$fieldTypeName][$fieldName]['plugin_formcreator_questions_id'] = $question->getID();
         $parameter->add($input['_parameters'][$fieldTypeName][$fieldName]);
      }
   }

   public final function updateParameters(PluginFormcreatorQuestion $question, array $input) {
      $fieldTypeName = $this->getFieldTypeName();
      if (!isset($input['_parameters'][$fieldTypeName])) {
         return;
      }

      foreach ($this->getUsedParameters() as $fieldName => $parameter) {
         $parameter->getFromDBByCrit([
            'plugin_formcreator_questions_id' => $question->getID(),
            'fieldname' => $fieldName,
         ]);
         $input['_parameters'][$fieldTypeName][$fieldName]['plugin_formcreator_questions_id'] = $question->getID();
         if ($parameter->isNewItem()) {
            // In case of the parameter vanished in DB, just recreate it
            $parameter->add($input['_parameters'][$fieldTypeName][$fieldName]);
         } else {
            $input['_parameters'][$fieldTypeName][$fieldName]['id'] = $parameter->getID();
            $parameter->update($input['_parameters'][$fieldTypeName][$fieldName]);
         }
      }
   }

   public final function deleteParameters(PluginFormcreatorQuestion $question) {
      foreach ($this->getUsedParameters() as $parameter) {
         if (!$parameter->deleteByCriteria(['plugin_formcreator_questions_id' => $question->getID()])) {
            // Don't make  this error fatal, but log it anyway
            Toolbox::logInFile('php-errors', 'failed to delete parameter for question ' . $question->getID() . PHP_EOL);
         }
      }
      return true;
   }
}
