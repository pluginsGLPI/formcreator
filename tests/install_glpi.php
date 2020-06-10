<?php

/**
 * This script wraps the installer of GLPI to allow a custom CONFIG_DIR path similarly to cli installer in
 * GLPI 9.5
 */
if (!$glpiConfigDir = getenv('TEST_GLPI_CONFIG_DIR')) {
    echo "Environment var TEST_GLPI_CONFIG_DIR is not set" . PHP_EOL;
    exit(1);
}

define('GLPI_ROOT', realpath(__DIR__ . '/../../../'));
define("GLPI_CONFIG_DIR", GLPI_ROOT . "/$glpiConfigDir");
unset($glpiConfigDir);

if (is_readable('../../tools/cliinstall.php')) {
    require_once('../../tools/cliinstall.php');
    exit();
}
if (is_readable('../../scripts/cliinstall.php')) {
    require_once('../../scripts/cliinstall.php');
    exit();
}

echo "Install script not found" . PHP_EOL;
exit(1);