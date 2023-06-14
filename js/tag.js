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

/* global tinymce */

var GLPI = GLPI || {};
GLPI.RichText = GLPI.RichText || {};

GLPI.RichText.FormcreatorTag = class {

    /**
    * @param {Editor} editor
    * @param {number} activeForm
    * @param {string} idorToken
    */
    constructor(editor, activeForm, idorToken) {
        this.editor = editor;
        this.activeForm = activeForm;
        this.idorToken = idorToken;
    }


    /**
     * Register as autocompleter to editor.
     *
     * @returns {void}
     */
    register() {
        const that = this;

        // Register autocompleter
        this.editor.ui.registry.addAutocompleter(
            'user_mention',
            {
                ch: '##',
                minChars: 0,
                fetch: function (pattern) {
                    return that.fetchItems(pattern);
                },
                onAction: function (autocompleteApi, range, value) {
                    that.mentionTag(autocompleteApi, range, value);
                }
            }
        );
    }

    /**
     * Fetch autocompleter items.
     *
     * @private
     *
     * @param {string} pattern
     *
     * @returns {Promise}
     */
    fetchItems(pattern) {
        const that = this;
        return new Promise(
            function (resolve) {
                $.post(
                    CFG_GLPI.root_doc + '/' + GLPI_PLUGINS_PATH.formcreator + '/ajax/get_form_tags.php',
                    {
                        id: that.activeForm,
                        display_emptychoice: 0,
                        searchText: pattern,
                    }
                ).done(
                    function(data) {
                        data = JSON.parse(data);
                        const items = data.map(
                            function (tag) {
                                return {
                                    type: 'autocompleteitem',
                                    value: JSON.stringify({id: tag.id, name: tag.text}),
                                    text: tag.text + ' (' + tag.q_name + ')',
                                    // TODO tag picture icon: ''
                                };
                            }
                        );
                        resolve(items);
                    }
                );
            }
        );
    }

   /**
     * Add mention to selected user in editor.
     *
     * @private
     *
     * @param {AutocompleterInstanceApi} autocompleteApi
     * @param {Range} range
     * @param {string} value
     *
     * @returns {void}
     */
    mentionTag(autocompleteApi, range, value) {
        const tag = JSON.parse(value);

        this.editor.selection.setRng(range);
        this.editor.insertContent(this.generateTagMentionHtml(tag));

        autocompleteApi.hide();
    }

    /**
     * Generates HTML code to insert in editor.
     *
     * @private
     *
     * @param {Object} tag
     *
     * @returns {string}
     */
    generateTagMentionHtml(tag) {
        return `<span contenteditable="false"
                    data-tag-id="${tag.name}">##${tag.name}##</span>`;
    }
};