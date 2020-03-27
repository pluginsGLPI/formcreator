#!/bin/bash

NOCOVERAGE="-ncc"
COVERAGE="--nccfc CommonTreeDropdown CommonDropdown CommonDBTM CommonGLPI CommonDBConnexity CommonDBRelation"

# init databases
init_databases() {
   MYSQL_PASSWD_ARG=''
   if [ ! $MYSQL_ROOT_PASSWD == '' ]; then MYSQL_PASSWD_ARG="-p$MYSQL_ROOT_PASSWD"; fi
   mysql -u$MYSQL_ROOT_USER $MYSQL_PASSWD_ARG -h$DB_HOST --execute "CREATE USER '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWD';"
   mysql -u$MYSQL_ROOT_USER $MYSQL_PASSWD_ARG -h$DB_HOST --execute "GRANT USAGE ON *.* TO '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWD';"
   mysql -u$MYSQL_ROOT_USER $MYSQL_PASSWD_ARG -h$DB_HOST --execute "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';"
   mysql -u$MYSQL_ROOT_USER $MYSQL_PASSWD_ARG -h$DB_HOST --execute "GRANT ALL PRIVILEGES ON $OLD_DB_NAME.* TO '$DB_USER'@'%';"
   mysql -u$MYSQL_ROOT_USER $MYSQL_PASSWD_ARG -h$DB_HOST --execute "FLUSH PRIVILEGES";
   MYSQL_PASSWD_ARG=''
}

# GLPI install
install_glpi() {
   echo Installing GLPI
   pwd
   sudo rm -rf ../glpi
   git clone --depth=35 $GLPI_SOURCE -b $GLPI_BRANCH ../glpi && cd ../glpi
   composer install --no-dev --no-interaction
   if [ -e bin/console ]; then php bin/console dependencies install; fi
   if [ -e bin/console ]; then php bin/console glpi:system:check_requirements; fi
   if [ ! -e bin/console ]; then composer install --no-dev; fi
   mkdir -p tests/files/_cache
   cp -r ../formcreator plugins/$PLUGINNAME
   cd plugins/$PLUGINNAME
   composer install
}


# assume the current dir is the root folder of a plugin
# $1 : database name
# $2 : database user
# $3 : database password
init_glpi() {
   echo Initializing GLPI
   pwd
   echo Dropping the database $1
   mysql -u$2 -p$3 -h$DB_HOST --execute "DROP DATABASE IF EXISTS \`$1\`;"
   echo Cleaning up cache directory
   rm -r ../../tests/files/_cache/* || true
   rm ../../$TEST_GLPI_CONFIG_DIR/config_db.php || true
   echo Installing GLPI on database $1
   mkdir -p ../../$TEST_GLPI_CONFIG_DIR
   if [ -e ../../tools/cliinstall.php ] ; then php  ./tests/install_glpi.php --host $DB_HOST --db=$1 --user=$2 --pass=$3 ; fi
   if [ -e ../../scripts/cliinstall.php ] ; then php ./tests/install_glpi.php --host $DB_HOST --db=$1 --user=$2 --pass=$3 ; fi
   if [ -e ../../bin/console ]; then php ../../bin/console glpi:database:install --db-host=$DB_HOST --db-user=$2 --db-password=$3 --db-name=$1 --config-dir=../../$TEST_GLPI_CONFIG_DIR --no-interaction --no-plugins --force; fi
}

# Plugin upgrade test
plugin_test_upgrade() {
   mysql -h$DB_HOST -u$DB_USER -p$DB_PASSWD $OLD_DB_NAME < tests/plugin_formcreator_config_2.5.0.sql
   mysql -h$DB_HOST -u$DB_USER -p$DB_PASSWD $OLD_DB_NAME < tests/plugin_formcreator_empty_2.5.0.sql
   php scripts/cliinstall.php --tests $TEST_GLPI_CONFIG_DIR
}

# Plugin test
plugin_test_install() {
   ./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/suite-install $NOCOVERAGE
}

plugin_test() {
   ./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/suite-integration -mcn 1 $COVERAGE
   ./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/suite-unit $COVERAGE
}

plugin_test_uninstall() {
   ./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/suite-uninstall $NOCOVERAGE
}

plugin_test_lint() {
   # ./vendor/bin/parallel-lint --exclude vendor .
   echo parallel lint disabled
}

# GLPI Coding Standards
plugin_test_cs() {
   vendor/bin/phpcs -p --standard=vendor/glpi-project/coding-standard/GlpiStandard/ --standard=tests/rulest.xml *.php install/ inc/ front/ ajax/ tests/ RoboFile.php
}
