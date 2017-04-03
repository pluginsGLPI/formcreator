<?php
include ('../../../inc/includes.php');
Session::checkRight("entity", UPDATE);

$question = new PluginFormcreatorQuestion();
if (empty($_REQUEST['question_id'])) {
   $question_id = 0;
   $question->getEmpty();
} else {
   $question_id = intval($_REQUEST['question_id']);
   $question->getFromDB($question_id);
}
$form_id = (int) $_REQUEST['form_id'];
$question->showForm($question_id);
