#!/bin/sh

install_plugin() {
   cd plugins/$PLUGINNAME
   composer install --no-interaction
}

init_plugin() {
    vendor/bin/robo build:fa-data
}

