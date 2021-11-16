#!/bin/sh

init_plugin() {
   cd plugins/$PLUGINNAME
   composer install --no-interaction
   yarn install --non-interactive --prod
}

install_plugin_dependencies() {
    : # nothing to do
}

