#!/bin/bash

#
# Before script for Travis CI
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

# config composer
if [ "$TRAVIS_SECURE_ENV_VARS" = "true" ]; then
  mkdir ~/.composer -p
  touch ~/.composer/composer.json
  composer config -g github-oauth.github.com $GH_OAUTH
fi

# setup GLPI and its plugins
mysql -u root -e 'create database $DBNAME;'
mysql -u root -e 'create database $OLDDBNAME;'
git clone --depth=35 $GLPI_SOURCE -b $GLPI_BRANCH ../glpi && cd ../glpi
composer install --no-dev --no-interaction
mkdir -p tests/files/_cache
IFS=/ read -a repo <<< $TRAVIS_REPO_SLUG
mv ../${repo[1]} plugins/formcreator

# prepare plugin to test
cd plugins/formcreator
composer install
