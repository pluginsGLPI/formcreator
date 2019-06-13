#!/bin/bash

#
# Script for Travis CI
#

# defined in travis.yml
# DBNAME      : database name for tests
# OLDDBNAME   : database name for upgrade test of the plugin
# GLPI_SOURCE : URL to GLPI GIT repository
# GLPI_BRANCH : branch of GLPI to test with the project

# defined by Travis CI
# TRAVIS_REPO_SLUG : see Travis CI: https://docs.travis-ci.com/user/environment-variables

# defined in travis settings / environment variables
# GH_OAUTH

# assume the current dir is the root folder of a plugin
# $1 : database name
# $2 : database user
# $3 : database password
function installGlpi {
   DATABASE=$1
   DBUSER=$2
   DBPASSWD=$3
   DBPASSWDARG=""
   if [ -e ../../scripts/cliinstall.php ] && [ "$DBPASSWD" != "" ]; then DBPASSWDARG="--pass=$DBPASSWD"; fi
   if [ -e ../../bin/console ] && [ "$DBPASSWD" != "" ]; then DBPASSWDARG="--db-password=$DBPASSWD"; fi
   echo Installing GLPI on database $DATABASE
   rm -rf ../../tests/files/_cache/cache_db/* ../../tests/files/_cache_/cache_trans/* ../../tests/files/_cache/*.json || true
   rm ../../tests/config_db.php > /dev/null 2>&1 || true
   if [ -e ../../scripts/cliinstall.php ]; then php ../../scripts/cliinstall.php --db=$DATABASE --user=$DBUSER $DBPASSWDARG --tests ; fi
   if [ -e ../../bin/console ]; then php ../../bin/console glpi:database:install --db-name=$DATABASE --db-user=$DBUSER $DBPASSWDARG --config-dir=../../tests --no-interaction --no-plugins --force; fi
}

# setup code coverage
COVERAGE="-ncc"
if [ "${TRAVIS_PHP_VERSION:0:3}" = "$CS" ] && [ "$GLPI_BRANCH" = "$AFTER_SUCCESS_BRANCH" ]; then COVERAGE="--nccfc CommonTreeDropdown CommonDropdown CommonDBTM CommonGLPI CommonDBChild CommonDBConnexity CommonDBRelation"; fi

# install GLPI with an old schema of the plugin and upgrade it
export PASSWORDARG=""
if [ "$DBPASSWD" != "" ]; then export PASSWORDARG="-p$DBPASSWD"; fi
installGlpi $OLDDBNAME $DBUSER $DBPASSWD
mysql -u $DBUSER $PASSWORDARG $OLDDBNAME < tests/plugin_formcreator_empty_2.5.0.sql
mysql -u $DBUSER $PASSWORDARG $OLDDBNAME < tests/plugin_formcreator_config_2.5.0.sql
# upgrade test
php scripts/cliinstall.php --tests

# install GLPI with a fresh install of the plugin
installGlpi $DBNAME $DBUSER $DBPASSWD
# fresh install test
./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/suite-install -ncc
./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/suite-integration $COVERAGE
./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/suite-unit $COVERAGE
./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/suite-uninstall -ncc
if [ "${TRAVIS_PHP_VERSION:0:3}" = "$CS" ] && [ "$GLPI_BRANCH" = "$AFTER_SUCCESS_BRANCH" ]; then vendor/bin/robo --no-interaction code:cs; fi
