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
class PluginFormcreatorQuestionRange
extends PluginFormcreatorQuestionParameter
{

   protected $domId = 'plugin_formcreator_questionRange';

   public function getParameterFormSize() {
      return 0;
   }

   public function getParameterForm(PluginFormcreatorForm $form, PluginFormcreatorQuestion $question) {
      // get the name of the HTML input field
      $name = '_parameters[' . $this->field->getFieldTypeName() . '][' . $this->fieldName . ']';

      // get the selected value in the dropdown
      $rangeMin = '';
      $rangeMax = '';
      $this->getFromDBByCrit([
         'plugin_formcreator_questions_id' => $question->getID(),
         'fieldname' => $this->fieldName,
      ]);
      if (!$this->isNewItem()) {
         $rangeMin = $this->fields['range_min'];
         $rangeMax = $this->fields['range_max'];
      }

      // build HTML code
      $selector = $this->domId;
      $out = '';
      $out.= '<td id="' . $selector . '">' . $this->label . '</td>';
      $out.= '<td>';
      $out.= '<label for="' . $name . '[range_min]" id="label_range_min">' . __('Min', 'formcreator') . '</label>&nbsp;';
      $out.= '<input type="text" name="'. $name . '[range_min]" id="range_min" class="small_text" style="width:90px;" value="'.$rangeMin.'" />';
      $out.= '&nbsp;<label for="' . $name . '[range_max]" id="label_range_max">' . __('Max', 'formcreator') . '</label>&nbsp;';
      $out.= '<input type="text" name="'. $name . '[range_max]" id="range_max" class="small_text" style="width:90px;" value="'.$rangeMax.'" />';
      $out.= '</td>';

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
}