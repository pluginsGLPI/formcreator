<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

/**
 * This class represents the supervisor of a requester in the list of
 * validators of a form
 *
 * @see PluginFormcreatorForm_Validator
 */
class PluginFormcreatorSupervisorValidator extends CommonDBTM
implements PluginFormcreatorSpecificValidator
{
    protected static $notable = true;

    public static function getTypeName($nb = 0) {
        return _n('Requester supervisor', 'Requester supervisors', $nb, 'formcreator');
    }

    public function getID() {
        return 'supervisor';
    }

    public function computeFriendlyName() {
        return __('My supervisor', 'formcreator');
    }

    public function MayBeResolvedIntoOneValidator(): bool {
        return true;
    }

    public function getOneValidator($current_user_id): ?CommonDBTM {
        $user = new User();
        $user->getFromDB($current_user_id);
        $supervisor = User::getById($user->fields['users_id_supervisor']);
        if (!($supervisor instanceof User)) {
            return null;
        }
        return $supervisor;
    }
}