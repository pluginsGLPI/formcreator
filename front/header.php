<?php
require_once ('../../../inc/includes.php');
$query_string = (!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '';
if (isset($_REQUEST['glpilist_limit'])) {
   $_SESSION['glpilist_limit'] = $_REQUEST['glpilist_limit'];
}
header('Location: config.form.php' . $query_string);
