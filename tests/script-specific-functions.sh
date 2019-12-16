#!/bin/sh

install_plugin() {
    : # nothing to do for this plugin
}

init_plugin() {
    vendor/bin/robo build:fa-data
}

