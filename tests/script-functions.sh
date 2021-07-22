#!/bin/bash

NOCOVERAGE="-ncc"
COVERAGE="--nccfc CommonTreeDropdown CommonDropdown CommonDBTM CommonGLPI CommonDBConnexity CommonDBRelation"
COMPOSER=`which composer`

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
   sudo rm -rf ../glpi
   git clone --depth=35 $GLPI_SOURCE -b $GLPI_BRANCH ../glpi && cd ../glpi
   composer install --no-dev --no-interaction
   php bin/console dependencies install composer-options=--no-dev
   php bin/console glpi:system:check_requirements
   rm .atoum.php
   mkdir -p tests/files/_cache
   cp -r ../formcreator plugins/$PLUGINNAME
}


# assume the current dir is the root folder of a plugin
# $1 : database name
# $2 : database user
# $3 : database password
init_glpi() {
   echo Initializing GLPI
   echo Dropping the database $1
   mysql -u$2 -p$3 -h$DB_HOST --execute "DROP DATABASE IF EXISTS \`$1\`;"
   echo Cleaning up cache directory
   rm -r ../../tests/files/_cache/* || true
   rm ../../$TEST_GLPI_CONFIG_DIR/config_db.php || true
   echo Installing GLPI on database $1
   mkdir -p ../../$TEST_GLPI_CONFIG_DIR
   php ../../bin/console glpi:database:install --db-host=$DB_HOST --db-user=$2 --db-password=$3 --db-name=$1 --config-dir=../../$TEST_GLPI_CONFIG_DIR --no-interaction --no-plugins --force
}

# Plugin upgrade test
plugin_test_upgrade() {
   mysql -h$DB_HOST -u$DB_USER -p$DB_PASSWD $OLD_DB_NAME < tests/plugin_formcreator_config_2.5.0.sql
   mysql -h$DB_HOST -u$DB_USER -p$DB_PASSWD $OLD_DB_NAME < tests/plugin_formcreator_empty_2.5.0.sql
   php ../../bin/console glpi:plugin:install formcreator --username=glpi --config-dir=../../$TEST_GLPI_CONFIG_DIR
}

# Plugin test
plugin_test_install() {
   ./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/1-install $NOCOVERAGE $ATOUM_ARG
}

plugin_test() {
   ./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/2-integration -mcn 1 $COVERAGE $ATOUM_ARG
   ./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/3-unit $COVERAGE $ATOUM_ARG
}

plugin_test_functional() {
   if [ "$SKIP_FUNCTIONAL_TESTS" = "true" ]; then echo "skipping functional tests"; return; fi
   # symfony requires PHP 7.2+, but the project is still compatible with older versions
   composer global require --dev symfony/panther
   RESOURCE="tests/4-functional"
   if [ "$1" != "" ]; then
      RESOURCE=$1
      shift
   fi

   if [ -f $RESOURCE ]; then
      RESOURCE_TYPE="-f"
   elif [ -d $RESOURCE ]; then
      RESOURCE_TYPE="-d"
   fi
   echo $@
   EXTRA=$@
   #export GLPI_CONFIG_DIR=$TEST_GLPI_CONFIG_DIR
   php -S 127.0.0.1:8000 -t ../.. tests/router.php > /dev/null 2>&1 &
   PROCESS=$!
   echo php started with PID=$PROCESS
   vendor/bin/atoum -ft -bf tests/bootstrap.php $NOCOVERAGE -mcn 1 $RESOURCE_TYPE $RESOURCE $EXTRA $ATOUM_ARG
}

plugin_test_uninstall() {
   ./vendor/bin/atoum -ft -bf tests/bootstrap.php -d tests/5-uninstall $NOCOVERAGE $ATOUM_ARG
}

plugin_test_lint() {
   composer run lint
}

# GLPI Coding Standards
plugin_test_cs() {
   composer run cs
}

# please set $TX_USER and $TX_TOKEN in your CI dashboard
plugin_after_success() {
   # for Travis CI
   if [ "$TRAVIS_PULL_REQUEST" = true ]; then
      echo "This is a Pull Request: skipping after success"
      return 0
   fi
   # for Gitlab CI
   if [ -n "$CI_EXTERNAL_PULL_REQUEST_IID" ]; then
      echo "This is a Pull Request: skipping after success"
      return 0
   fi

   GENERATE_LOCALES=false
   GENERATE_DOCS=false
   # for Travis CI
   if echo "$TRAVIS_BRANCH" | grep -q -P '^(master|develop|support/|release/)'; then
      GENERATE_LOCALES=true
      GENERATE_DOCS=true
      BUILD_BRANCH=$TRAVIS_BRANCH
      PROJECT_PATH_SLUG=$TRAVIS_REPO_SLUG
   fi
   # for Gitlab CI
   if echo "$CI_COMMIT_BRANCH" | grep -q -P '^(master|develop|support/|release/)'; then
      GENERATE_LOCALES=true
      GENERATE_DOCS=true
      BUILD_BRANCH=$CI_COMMIT_BRANCH
      PROJECT_PATH_SLUG=$CI_PROJECT_PATH_SLUG
   fi


   if [ "$GENERATE_LOCALES" = false ]; then
      echo "skipping source language update"
   else
      echo "updating source language"
      if [ -z "$TX_USER" ] || [ -z "$TX_TOKEN" ]; then
         echo "Missing or incomplete Transifex authentication"
      else
         sudo apt update
         sudo apt install transifex-client python3-six
         echo "[https://www.transifex.com]" > ~/.transifexrc
         echo "api_hostname = https://api.transifex.com" >> ~/.transifexrc
         echo "hostname = https://www.transifex.com" >> ~/.transifexrc
         echo "token = ${TX_TOKEN}" >> ~/.transifexrc
         echo "password = ${TX_TOKEN}" >> ~/.transifexrc
         echo "username = ${TX_USER}" >> ~/.transifexrc
         php vendor/bin/robo locales:send
      fi
   fi

   if [ "$GENERATE_DOCS" = false ]; then
      echo "skipping documentation update"
   else
      # setup_git only for the main repo and not forks
      echo "Configuring git user"
      git config --global user.email "apps@teclib.com"
      git config --global user.name "Teclib' bot"
      echo "adding a new remote"
      # please set a personal token in https://github.com/settings/tokens
      # enable "public_repo" for a public repository or "repo" otherwise
      # then set the $GH_TOKEN to this value in your travis dashboard
      git remote add origin-pages https://"$GH_TOKEN"@github.com/"$PROJECT_PATH_SLUG".git > /dev/null 2>&1
      echo "fetching from the new remote"
      git fetch origin-pages

      # check if gh-pages exist in remote
      if [ "git branch -r --list origin-pages/gh-pages" ]; then
         echo "generating the docs"
         # clean the repo and generate the docs
         git checkout .
         echo "code coverage"
         find development/coverage/"$BUILD_BRANCH"/ -type f -name "*.html" -exec sed -i "1s/^/---\\nlayout: coverage\\n---\\n/" "{}" \;
         find development/coverage/"$BUILD_BRANCH"/ -type f -name "*.html" -exec sed -i "/bootstrap.min.css/d" "{}" \;
         find development/coverage/"$BUILD_BRANCH"/ -type f -name "*.html" -exec sed -i "/report.css/d" "{}" \;

         # commit_website_files
         echo "adding the coverage report"
         git add development/coverage/"$BUILD_BRANCH"/*
         echo "creating a branch for the new documents"
         git checkout -b localCi
         git commit -m "changes to be merged"
         git checkout -f -b gh-pages origin-pages/gh-pages
         git rm -r development/coverage/"$BUILD_BRANCH"/*
         git checkout localCi development/coverage/"$BUILD_BRANCH"/
         git add development/coverage/"$BUILD_BRANCH"/*

         # upload_files
         echo "pushing the up to date documents"
         git commit --message "docs: update test reports"
         git fetch origin-pages
         git rebase origin-pages/gh-pages
         git push --quiet --set-upstream origin-pages gh-pages --force
      fi
   fi
}
