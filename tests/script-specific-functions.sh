#!/bin/sh

install_plugin() {
   cd plugins/$PLUGINNAME
   composer install --no-interaction
   yarn install --non-interactive --prod
}

init_plugin() {
    : # nothing to do
}

