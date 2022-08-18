#!/bin/sh

init_plugin() {
   cd plugins/$PLUGINNAME
   composer install --no-interaction
   yarn install --non-interactive --prod
}

install_plugin_dependencies() {
    # go to GLPI directory
    cd ../..

    git clone --depth 1 -b $PLUGIN_FIELDS_REF https://github.com/pluginsGLPI/fields plugins/fields
    cd plugins/fields
    composer install --no-dev --no-interaction
    cd ../..

    git clone --depth 1 -b $PLUGIIN_TAG_REF https://github.com/pluginsGLPI/tag plugins/tag
    cd plugins/tag
    composer install --no-dev --no-interaction
    cd ../..

    # go back to plugin directory of this project
    cd plugins/formcreator
}

init_plugins() {
    ../../bin/console glpi:plugin:install fields --config-dir=../../$TEST_GLPI_CONFIG_DIR -u glpi --no-interaction
    ../../bin/console glpi:plugin:install tag --config-dir=../../$TEST_GLPI_CONFIG_DIR -u glpi --no-interaction
    ../../bin/console glpi:plugin:activate fields --config-dir=../../$TEST_GLPI_CONFIG_DIR --no-interaction
    ../../bin/console glpi:plugin:activate tag --config-dir=../../$TEST_GLPI_CONFIG_DIR --no-interaction
}
