#!/bin/sh

set -xe

SELF=`readlink -f $0`
SELFDIR=`dirname $SELF`
PHP=`which php`

oldpath=`pwd`
cd $SELFDIR/..

if [ -f $SELFDIR/env.sh ]; then
  . $SELFDIR/env.sh
fi
. ./tests/script-functions.sh
. ./tests/script-specific-functions.sh

$SKIP_INIT || init_databases
$SKIP_INIT || install_glpi 
$SKIP_INIT || init_plugin

#php -S localhost:8088 -t ../.. ../../tests/router.php &>/dev/null &
#PID=$!
#echo $PID

# upgrade test
export TEST_GLPI_CONFIG_DIR="tests/config-$OLD_DB_NAME"
$SKIP_UPGRADE_GLPI_INSTALL || init_glpi $OLD_DB_NAME $DB_USER $DB_PASSWD
echo "=== Upgrade tests ==="
$SKIP_UPGRADE_TESTS || plugin_test_upgrade

# fresh install test
export TEST_GLPI_CONFIG_DIR="tests/config-$DB_NAME"
$SKIP_FRESH_GLPI_INSTALL || init_glpi $DB_NAME $DB_USER $DB_PASSWD
echo "=== Install tests ==="
$SKIP_INSTALL_TESTS || plugin_test_install

echo "=== Integration & Unit tests ==="
$SKIP_TESTS || plugin_test

echo "=== Uninstall tests ==="
$SKIP_UNINSTALL_TESTS || plugin_test_uninstall

echo "=== Lint tests ==="
$SKIP_LINT_TESTS || plugin_test_lint

echo "=== CS tests ==="
$SKIP_CS_TESTS || plugin_test_cs
cd $oldpath
