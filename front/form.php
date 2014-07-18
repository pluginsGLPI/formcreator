<?php
$query_string = (!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: config.form.php' . $query_string);
