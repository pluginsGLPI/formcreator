<?php
include ('../../../inc/includes.php');
Session::checkRight("entity", UPDATE);

if (!isset($_REQUEST['plugin_formcreator_questions_id'])) {
   exit;
}
$questionId = (int) $_REQUEST['plugin_formcreator_questions_id'];

if (isset($_REQUEST['_empty'])) {
   // get an empty condition HTML table row
   $questionCondition = new PluginFormcreatorQuestion_Condition();
   echo $questionCondition->getConditionHtml($questionId);
}