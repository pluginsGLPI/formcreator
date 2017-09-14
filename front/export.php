<?php
include ('../../../inc/includes.php');

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

$form = new PluginFormcreatorForm;
$export_array = ['forms' => []];
foreach ($_GET['plugin_formcreator_forms_id'] as $id) {
   $form->getFromDB($id);
   $export_array['forms'][] = $form->export();
}

$export_json = json_encode($export_array, JSON_UNESCAPED_UNICODE
                                        | JSON_UNESCAPED_SLASHES
                                        | JSON_NUMERIC_CHECK
                                        | ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE
                                             ? JSON_PRETTY_PRINT
                                             : 0));

header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
header('Pragma: private');
header('Cache-control: private, must-revalidate');
header("Content-disposition: attachment; filename=\"export_formcreator_".date("Ymd_Hi").".json\"");
header("Content-type: application/json");

echo $export_json;