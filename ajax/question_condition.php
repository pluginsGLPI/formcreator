<?php
include ('../../../inc/includes.php');
Session::checkRight("entity", UPDATE);

if (!isset($_REQUEST['plugin_formcreator_questions_id'])) {
   exit;
}
$questionId = (int) $_REQUEST['plugin_formcreator_questions_id'];
if ($questionId == 0) {
   $formId = (int) $_REQUEST['plugin_formcreator_forms_id'];
} else {
   $form = new PluginFormcreatorForm();
   $form->getByQuestionId($questionId);
   $formId = $form->getID();
}

if (isset($_REQUEST['_empty'])) {
   // get an empty condition HTML table row
   $form = new PluginFormcreatorForm();
   $form->getByQuestionId($questionId);
   $questionCondition = new PluginFormcreatorQuestion_Condition();
   echo $questionCondition->getConditionHtml($formId, $questionId);
}