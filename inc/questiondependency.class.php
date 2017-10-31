<?php
/**
 *
 */

/**
 * A question parameter to handle a depdency to an other question. For example
 * the content og the question A is computed from the content of the question B. In
 * this case the question A has this parameter to maitnain the dependency to the
 * question B
 */
class PluginFormcreatorQuestionDependency
extends PluginFormcreatorQuestionParameter
{

   /** @var string $fieldtype type of field useable for the dependency */
   protected $fieldType;

   protected $domId = 'plugin_formcreator_questionDependency';

   /**
    * @param PluginFormcreatorFieldInterface $field Field
    * @param array $options
    *                - fieldName: name of the HTML input tag
    *                - label    : label for the parameter
    *                - fieldType: array of field types the dependency should filter
    */
   public function __construct(PluginFormcreatorFieldInterface $field, array $options) {
      parent::__construct($field, $options);
      $this->fieldtype = isset($options['fieldType']) ? $options['fieldType'] : [];
   }

   public function getParameterFormSize() {
      return 0;
   }

   public function getParameterForm(PluginFormcreatorForm $form, PluginFormcreatorQuestion $question) {
      // get questions of type text in the form
      $eligibleQuestions = [];
      $criteria = "`fieldtype` IN ('" . implode("', '", $this->fieldtype) . "')";
      foreach ($question->getQuestionsFromForm($form->getID(), $criteria) as $item) {
         $eligibleQuestions[$item->getID()] = $item->getField('name');
      }

      // get the name of the HTML input field
      $name = '_parameters[' . $this->field->getFieldTypeName() . '][' . $this->fieldName . ']';

      //  get the selected value in the dropdown
      $selected = 0;
      if (!$question->isNewItem()) {
         $this->getFromDBByCrit([
            'plugin_formcreator_questions_id' => $question->getID(),
            'fieldname' => $this->fieldName,
         ]);
         if (!$this->isNewItem()) {
            $selected = $this->fields['plugin_formcreator_questions_id_2'];
         }
      }

      // get the HTML for the dropdown
      $questionsDropdown = Dropdown::showFromArray(
         $name . '[plugin_formcreator_questions_id_2]',
         $eligibleQuestions,
         [
            'display'               => false,
            'display_emptychoice'   => true,
            'value'                 => $selected,
            'used'                  => [$question->getID() => ''],
         ]);

      // build HTML code
      $selector = $this->domId;
      $out = '';
      $out.= '<td id="' . $selector . '">' . $this->label . '</td>';
      $out.= '<td>' . $questionsDropdown . '</td>';

      return $out;
   }

   public function getJsShowHideSelector() {
      return "#" . $this->domId;
   }

   public function prepareInputForAdd($input) {
      $input = parent::prepareInputForAdd($input);
      $input['fieldname'] = $this->fieldName;

      return $input;
   }

   public function getFieldName() {
      return $this->fieldName;
   }

   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $parameter = $this->fields;
      $this->convertIds($parameter);
      unset($parameter['id'],
            $parameter[PluginFormcreatorQuestion::getForeignKeyField()]);

      if ($remove_uuid) {
         $parameter['uuid'] = '';
      }

      return $parameter;
   }

   public function duplicate(PluginFormcreatorQuestion $newQuestion, array $tab_questions) {
      $parameter = parent::duplicate($newQuestion, $tab_questions);

      // update the question ID the parameter depends on
      if (isset($tab_questions[$parameter->fields['plugin_formcreator_questions_id_2']])) {
         $parameter->fields['plugin_formcreator_questions_id_2'] = $tab_questions[$parameter->fields['plugin_formcreator_questions_id_2']];
      }

      return $parameter;
   }

   protected function convertIds(&$parameter) {
      $question = new PluginFormcreatorQuestion();
      $question->getFromDB($this->fields['plugin_formcreator_questions_id_2']);
      $parameter['plugin_formcreator_questions_id_2'] = $question->getField('uuid');
   }


   protected function convertUuids(&$parameter) {
      if ($questionId2
          = plugin_formcreator_getFromDBByField(new PluginFormcreatorQuestion(),
                                                  'uuid',
                                                  $parameter['plugin_formcreator_questions_id_2'])) {
         $parameter['plugin_formcreator_questions_id_2'] = $questionId2;
         return true;
      }
      return false;
   }
}