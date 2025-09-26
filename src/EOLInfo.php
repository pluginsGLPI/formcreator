<?php

/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - End of Life Information
 * ---------------------------------------------------------------------
 * This class provides information about the EOL status and migration
 * options for Formcreator.
 * ---------------------------------------------------------------------
 */

namespace Glpi\Plugin\Formcreator;

use CommonGLPI;
use Glpi\Application\View\TemplateRenderer;
use Session;

/**
 * Class to display End of Life information for Formcreator
 */
class EOLInfo extends CommonGLPI
{

    static public $rightname = 'config';

    /**
     * Get menu name
     *
     * @return string
     */
    static function getMenuName()
    {
        return __('Formcreator EOL Info', 'formcreator');
    }

    /**
     * Get menu content
     *
     * @return array
     */
    static function getMenuContent()
    {
        $menu = [];

        if (static::canView()) {
            $menu['title'] = static::getMenuName();
            $menu['page'] = '/plugins/formcreator/front/eol_info.php';
            $menu['icon'] = 'ti ti-alert-triangle';
            $menu['links']['search'] = '/plugins/formcreator/front/eol_info.php';
        }

        return $menu;
    }

    /**
     * Check if user can view EOL info
     *
     * @return bool
     */
    static function canView(): bool
    {
        return Session::haveRight('config', READ);
    }

    /**
     * Get type name
     *
     * @param int $nb
     * @return string
     */
    static function getTypeName($nb = 0)
    {
        return __('Formcreator End of Life Information', 'formcreator');
    }

    /**
     * Show EOL information form using Twig template
     *
     * @return void
     */
    function showForm()
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        TemplateRenderer::getInstance()->display('@formcreator/eol_info.html.twig', [
            'plugin_version' => PLUGIN_FORMCREATOR_VERSION,
            'plugin_web_dir' => $CFG_GLPI['root_doc'] . '/plugins/formcreator',
        ]);
    }

    /**
     * Display EOL warning on central dashboard using Twig template
     *
     * @return void
     */
    static function displayCentralEOLWarning()
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;
        
        if (!static::canView()) {
            return;
        }

        $_SESSION['formcreator_eol_central_shown'] = true;

        TemplateRenderer::getInstance()->display('@formcreator/central_eol_warning.html.twig', [
            'plugin_version' => PLUGIN_FORMCREATOR_VERSION,
            'root_doc' => $CFG_GLPI['root_doc'],
        ]);
    }
}
