<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

$section       = new PluginFormcreatorSection();
if(empty($_REQUEST['section_id'])) {
   $section_id    = 0;
} else {
   $section_id    = intval($_REQUEST['section_id']);
   $section->getFromDB($section_id);
}

$section->showSubForm($section_id);