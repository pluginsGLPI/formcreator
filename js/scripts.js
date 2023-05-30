"use strict";

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

var modalWindow;
var rootDoc          = CFG_GLPI['root_doc'];
var tiles = [];
var slinkyCategories;
var timers = [];
var formcreatorRootDoc = rootDoc + '/' + GLPI_PLUGINS_PATH.formcreator;


// @see https://www.tiny.cloud/docs/integrations/bootstrap/
// Prevent Bootstrap dialog from blocking focusin
document.addEventListener('focusin', (e) => {
   if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
     e.stopImmediatePropagation();
   }
 });

// === COMMON ===

function getTimer(object) {
   return function(timeout, action) {
      var timer;
      object.keyup(
         function(event) {
            if (typeof timer != 'undefined') {
               clearTimeout(timer);
            }
            if (event.which == 13) {
               action();
            } else {
               timer = setTimeout(function() {
                  action();
               }, timeout);
            }
         }
      );
   }
}

$(function() {
   // Prevent jQuery UI dialog from blocking focusin
   $(document).on('focusin', function(e) {
       if ($(e.target).closest(".mce-window, .moxman-window").length) {
         e.stopImmediatePropagation();
      }
   });

   if (location.pathname.indexOf("helpdesk.public.php") != -1) {
      plugin_formcreator.showHomepageFormList();

   } else if ($('#plugin_formcreator_wizard_categories').length > 0) {
      updateCategoriesView();

      $('#plugin_formcreator_wizard_categories #wizard_seeall').on('click', function (event) {
         slinkyCategories.home();
         plugin_formcreator.updateWizardFormsView(event.target);
         $('#plugin_formcreator_wizard_categories .category_active').removeClass('category_active');
         $(this).addClass('category_active');
      });
   } else if ($('#plugin_formcreator_kb_categories').length > 0) {
      updateKbCategoriesView();

      $('#plugin_formcreator_kb_categories #wizard_seeall').on('click', function (event) {
         slinkyCategories.home();
         plugin_formcreator.updateKbitemsView(event.target);
         $('#plugin_formcreator_kb_categories .category_active').removeClass('category_active');
         $(this).addClass('category_active');
      });
   }

   // Initialize search bar
   var searchInput = $('#plugin_formcreator_searchBar input:first');
   if (searchInput.length == 1) {
      // Dynamically update forms and faq items while the user types in the search bar
      var timer = getTimer(searchInput);
      var callbackFunc;
      if ($('#plugin_formcreator_kb_categories').length > 0) {
         callbackFunc = plugin_formcreator.updateKbitemsView.bind(plugin_formcreator);
      } else {
         callbackFunc = plugin_formcreator.updateWizardFormsView.bind(plugin_formcreator);
      }
      timer(300, callbackFunc);
      timers.push(timer);

      // Clear the search bar if it gains focus
      $('#plugin_formcreator_searchBar input').focus(function(event) {
         if (searchInput.val().length > 0) {
            searchInput.val('');
            if ($('#plugin_formcreator_kb_categories').length > 0) {
               plugin_formcreator.updateKbitemsView(null);
               $.when(getFaqItems(0))
               .then(
                  function (response) {
                     tiles = response;
                     showTiles(tiles.forms);
                  }
               );
            } else {
               plugin_formcreator.updateWizardFormsView(null);
               $.when(getFormAndFaqItems(0))
               .then(
                  function (response) {
                     tiles = response;
                     showTiles(tiles.forms);
                  }
               );
            }
         }
      });
   }
});

function updateCategoriesView() {
   $.post({
      url: formcreatorRootDoc + '/ajax/homepage_wizard.php',
      data: {wizard: 'categories'},
      dataType: "json"
   }).done(function(response) {
      var html = buildCategoryList(response);

      //Display categories
      $('#plugin_formcreator_wizard_categories .slinky-menu').html(html);

      // Setup slinky
      slinkyCategories = $('#plugin_formcreator_wizard_categories .slinky-menu').slinky({
         label: true
      });

      // Show label of parent in the 'back' item
      document.querySelectorAll('#plugin_formcreator_wizard_categories .slinky-menu a.back').forEach(item => {
         var parentLabel = item.closest('ul').closest('li').querySelector('a').innerText;
         item.innerText = parentLabel;
      });

      $('#plugin_formcreator_wizard_categories a.back').on('click',
         function(event) {
            var parentItem = $(event.target).parentsUntil('#plugin_formcreator_wizard_categories .slinky-menu > ul', 'li')[1];
            var parentAnchor = $(parentItem).children('a')[0];
            plugin_formcreator.updateWizardFormsView(parentAnchor);
         }
      );

      $('#plugin_formcreator_wizard_categories a[data-category-id]').on('click',
         function (event) {
            $('#plugin_formcreator_wizard_categories .category_active').removeClass('category_active');
            $(this).addClass('category_active');
         }
      );
   });
}

function updateKbCategoriesView() {
   $.get({
      url: formcreatorRootDoc + '/ajax/kb_category.php',
      dataType: "json"
   }).done(function(response) {
      var html = '<div class="slinky-menu">';
      html = html + buildKbCategoryList(response);
      html = html + '</div>';

      //Display categories
      $('#plugin_formcreator_kb_categories .slinky-menu').remove();
      $('#plugin_formcreator_kb_categories').append(html);

      // Setup slinky
      slinkyCategories = $('.slinky-menu').slinky({
         label: true
      });
      $('#plugin_formcreator_kb_categories a.back').on('click',
         function(event) {
            var parentItem = $(event.target).parentsUntil('#plugin_formcreator_kb_categories .slinky-menu > ul', 'li')[1];
            var parentAnchor = $(parentItem).children('a')[0];
            plugin_formcreator.updateKbitemsView(parentAnchor);
         }
      );

      $('#plugin_formcreator_kb_categories a[data-category-id]').on('click',
         function (event) {
            $('#plugin_formcreator_kb_categories .category_active').removeClass('category_active');
            $(this).addClass('category_active');
         }
      );

      //preselect see all
      $('#wizard_seeall').click();
   });
}

function getFaqItems(categoryId) {
   var keywords = $('#plugin_formcreator_searchBar input:first').val();
   var deferred = jQuery.Deferred();
   $.post({
      url: formcreatorRootDoc + '/ajax/knowbaseitem.php',
      data: {
         categoriesId: categoryId,
         keywords: keywords,
         helpdeskHome: 0
      },
      dataType: "json"
   }).done(function (response) {
      deferred.resolve(response);
   }).fail(function (response) {
      deferred.reject();
   });
   return deferred.promise();
}

/**
 * get form and faq items from DB
 * Returns a promise
 */
function getFormAndFaqItems(categoryId) {
   var keywords = $('#plugin_formcreator_searchBar input:first').val();
   var deferred = jQuery.Deferred();
   $.post({
      url: formcreatorRootDoc + '/ajax/homepage_wizard.php',
      data: {
         wizard: 'forms',
         categoriesId: categoryId,
         keywords: keywords,
         helpdeskHome: 0
      },
      dataType: "json"
   }).done(function (response) {
      deferred.resolve(response);
   }).fail(function (response) {
      deferred.reject();
   });
   return deferred.promise();
}

function sortFormAndFaqItems(items, byName) {
   if (byName == true) {
      // sort by name
      items.sort(function (a, b) {
         if (a.name < b.name) {
            return -1;
         }
         if (a.name > b.name) {
            return 1;
         }
         return 0;
      });
   } else {
      // sort by view or usage count
      items.sort(function (a, b) {
         if (a.usage_count > b.usage_count) {
            return -1;
         }
         if (a.usage_count < b.usage_count) {
            return 1;
         }
         return 0;
      });
   }
   return items;
}

function showTiles(tiles, defaultForms) {
   var sortByName = $('#plugin_formcreator_alphabetic').prop('checked')
   var tiles = sortFormAndFaqItems(tiles, sortByName);
   var html = '';
   if (defaultForms) {
      if (tiles.length > 0) {
         html += '<p>' + i18n.textdomain('formcreator').__('No form found. Please choose a form below instead.', 'formcreator') + '</p>';
      } else {
         html += '<p>' + i18n.textdomain('formcreator').__('No form found.', 'formcreator') + '</p>';
      }
   } else {
      if (tiles.length < 1) {
         html += '<p>' + i18n.textdomain('formcreator').__('No FAQ item found.', 'formcreator') + '</p>';
      }
   }
   html += buildTiles(tiles);

   //Display tiles
   $('#plugin_formcreator_wizard_forms').empty();
   $('#plugin_formcreator_wizard_forms').prepend(html);
   $('#plugin_formcreator_formlist').masonry({
      horizontalOrder: true,
      gutter: 10
   });
   $('#plugin_formcreator_faqlist').masonry({
      horizontalOrder: true,
      gutter: 10
   });


   $(".plugin_formcreator_formTile_description.tile_design_uniform_height").each(function( index ) {
      var length = 150;
      //decrease length if contain icon
      if ($(this).parent().find(".fa").length > 0) {
         length = length - 35;
      }

      var parent_title = $(this).parent().find('.plugin_formcreator_formTile_title').text();
      if (parent_title.length + $(this).text().length > length) {
         var short = jQuery.trim($(this).text())
                  .substring(0, length)
                  .split(" ")
                  .slice(0, -1)
                  .join(" ") + " ...";
         $(this).html(short);
      }
   });


}

function buildKbCategoryList(tree) {
   var html = '';
   if (tree.id != 0) {
      html += '<a href="#" data-parent-category-id="' + tree.parent +'"'
         + ' data-category-id="' + tree.id + '"'
         + ' onclick="plugin_formcreator.updateKbitemsView(this)"'
         + ' title="' + tree.comment + '">'
         + tree.name
         + '</a>';
   }
   if (Object.keys(tree.subcategories).length == 0) {
      return html;
   }
   html = html + '<ul>';
   $.each(tree.subcategories, function (key, val) {
      html = html + '<li>' + buildKbCategoryList(val) + '</li>';
   });
   html = html + '</ul>';
   return html;
}

function buildCategoryList(tree) {
   var html = '';
   if (tree.id != 0) {
      html = '<a href="#" data-parent-category-id="' + tree.parent +'"'
         + ' data-category-id="' + tree.id + '"'
         + ' onclick="plugin_formcreator.updateWizardFormsView(this)"'
         + ' title="' + tree.comment + '">'
         + tree.name
         + '</a>';
   }
   if (Object.keys(tree.subcategories).length == 0) {
      return html;
   }
   html = html + '<ul>';
   $.each(tree.subcategories, function (key, val) {
      html = html + '<li>' + buildCategoryList(val) + '</li>';
   });
   html = html + '</ul>';
   return html;
}

function buildTiles(list) {
   $(document).on('click', '.plugin_formcreator_formTile', function(){
      document.location = $(this).children('a').attr('href');
   });

   var html = '';
   if (list.length == 0) {
      return html;
   }

   var forms = [];
   var faqs = [];
   $.each(list, function (key, item) {
      // Build a HTML tile
      var url = formcreatorRootDoc + '/front/formdisplay.php?id=' + item.id;
      if (item.type != 'form') {
         url = rootDoc + '/front/knowbaseitem.form.php?id=' + item.id;
      }

      var tiles_design = "";
      if (item.tile_template == "1") { // @see PluginFormcreatorEntityConfig::CONFIG_UI_FORM_UNIFORM_HEIGHT
         tiles_design = "tile_design_uniform_height";
      }

      var description = '';
      if (item.description) {
         description = '<div class="plugin_formcreator_formTile_description '+ tiles_design +'">'
                        +item.description
                        +'</div>';
      }

      var default_class = '';
      if (JSON.parse(item.is_default)) {
         default_class = 'default_form';
      }

      if (item.icon == '') {
         if (item.type == 'form') {
            item.icon = 'fa fa-clipboard-list';
         } else {
            item.icon = 'fa fa-question-circle';
         }
      }

      if (item.icon_color == '') {
         item.icon_color = '#999999';
      }

      if (item.background_color == '') {
         item.background_color = '#e7e7e7';
      }

      if (item.type == 'form') {
         forms.push(
            '<div data-itemtype="PluginFormcreatorForm" data-id="' + item.id + '" style="background-color: ' + item.background_color + '" class="plugin_formcreator_formTile '+item.type+' '+tiles_design+' '+default_class+'" title="'+item.description+'">'
            + '<i class="' + item.icon + '" style="color: ' + item.icon_color+ '"></i>'
            + '<a href="' + url + '" class="plugin_formcreator_formTile_title">'
            + item.name
            + '</a>'
            + description
            + '</div>'
         );
      } else {
         faqs.push(
            '<div style="background-color: ' + item.background_color + '" class="plugin_formcreator_formTile '+item.type+' '+tiles_design+' '+default_class+'" title="'+item.description+'">'
            + '<i class="fa ' + item.icon + '" style="color: ' + item.icon_color+ '"></i>'
            + '<a href="' + url + '" class="plugin_formcreator_formTile_title">'
            + item.name
            + '</a>'
            + description
            + '</div>'
         );
      }
   });

   // concatenate all HTML parts
   html = '<div id="plugin_formcreator_formlist">'
   + forms.join("")
   + '</div><div id="plugin_formcreator_faqlist">'
   + faqs.join("")
   + '</div>'

   return html;
}

var plugin_formcreator = new function() {
   this.spinner = '<div"><img src="../../../pics/spinner.48.gif" style="margin-left: auto; margin-right: auto; display: block;" width="48px"></div>'

   this.questionColumns = 4;

   this.activeCategory = 0;

   this.modalSetings = {
      autoOpen: false,
      height: 'auto',
      width: '600px',
      minWidth: '600px',
      modal: true,
      position: {my: 'center'},
      close: function() {
         $(this).dialog('close');
         $(this).remove();
      }
   }

   // Properties of the item when the user begins to change it
   this.initialPosition = {};
   this.changingItemId = 0;
   this.questionsColumns = 4; // @see PluginFormcreatorSection::COLUMNS
   this.dirty = false;


   this.showHomepageFormList = function () {
      if ($('#plugin_formcreatorHomepageForms').length) {
         return;
      }

      $.get({
         url: formcreatorRootDoc + '/ajax/homepage_forms.php',
      }).done(function(response){
         // $('.central').first().prepend(response);
         var card = $(response);
         $('table.central').append(card)
      });
   }

   this.setupGridStack = function(group) {
      var that = this;
      group.gridstack
      .on('resizestart', this.startChangeItem)
      .on('dragstart', this.startChangeItem)
      .on('change', function(event, item) {
         that.changeItems(event, item)
      })
      .on('dragstop', function(event, item) {
         setTimeout(function() {
            $(item.ddElement.el).find('a').off('click.prevent');
         },
         300);
      })
      .on('dropped', function (event, previousWidget, newWidget) {
         var changes = {};
         var section = $(newWidget.el).closest('[data-itemtype="PluginFormcreatorSection"]');
         var itemId = $(newWidget.el).attr('data-id');
         changes[itemId] = {
            plugin_formcreator_sections_id: section.attr('data-id'),
            width: newWidget.w,
            height: newWidget.h,
            x: newWidget.x,
            y: newWidget.y
         };
         $.ajax({
            'url': formcreatorRootDoc + '/ajax/question_move.php',
            type: 'POST',
            data: {
               move: changes,
            }
         }).fail(function() {
            plugin_formcreator.cancelChangeItems(event, items);
            plugin_formcreator.dirty = false;
         }).done(function(response) {
            plugin_formcreator.dirty = false;
         });
      });
   };

   this.initGridStack = function (sectionId) {
      var that = this;
      var selector = '#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"][data-id="' + sectionId + '"] .grid-stack';
      var group = document.querySelector(selector);
      var options = {
         column:               this.questionsColumns,
         disableOneColumnMode: true,
         cellHeight:           '32px',
         margin:               '2px',
         float:                true,
         acceptWidgets:        true,
         resizeable: {
            handles: 'e, w'
         }
      };
      GridStack.init(options, group);
      $.get({
         url: formcreatorRootDoc + '/ajax/question_get.php',
         dataType: 'json',
         data: {
            id: sectionId,
            design: true
         }
      }).success(function(data, httpCode) {
         var grid = group.gridstack;
         that.dirty = true;
         $.each(data, function(index, question) {
            grid.addWidget(
               question.html,
               {
                  autoPosition: false,
                  x: Number(question.x),
                  y: Number(question.y),
                  w: Number(question.width),
                  h: Number(question.height),
                  minW: 1,
                  maxW: that.questionColumns,
                  minH: 1,
                  maxH: 1,
               }
            );
         });
         that.dirty = false;
      }).complete(function () {
         that.setupGridStack(group);
         group.gridstack.float(false);
      });
   };

   /**
   * Event handler : when an item is about to move or resize
   */
  this.startChangeItem = function (event, item) {
      $(item.ddElement.el).find('a').on('click.prevent', function(event) {
         return false;
      });
      var items = $(event.currentTarget).find('> .grid-stack-item');
      this.initialPosition = {};
      var that = this;
      $.each(items, function(index, item) {
         var id = $(item).attr('data-id');
         that.initialPosition[id] = {
            x:      Number($(item).attr('data-gs-x')),
            y:      Number($(item).attr('data-gs-y')),
            width:  Number($(item).attr('data-gs-width')),
            height: Number($(item).attr('data-gs-height')),
         }
      });
      this.changingItemId = Number($(event.target).attr('data-id'));
   };

   /**
    * Event handler : change an item (resize or move)
    */
   this.changeItems = function (event, items) {
      if (this.dirty === true) {
         return;
      }
      var that = this;
      var changes = {};
      $.each(items, function(index, item) {
         var id     = $(item.el).attr('data-id');
         if (typeof(id) !== 'undefined') {
            changes[id] = {
               width:  item.w,
               height: item.h,
               x:      item.x,
               y:      item.y
            };
         }
      });
      if (changes.length < 1) {
         return;
      }
      $.ajax({
         'url': formcreatorRootDoc + '/ajax/question_move.php',
         type: 'POST',
         data: {
            move: changes,
         }
      }).fail(function() {
         plugin_formcreator.cancelChangeItems(event, items);
         plugin_formcreator.dirty = false;
      }).done(function(response) {
         plugin_formcreator.dirty = false;
         that.resetTabs();
      });
   };

   this.cancelChangeItems = function (event, items) {
      var that = this;
      $.each(items, function(index, item) {
         var id = $(item.el).attr('data-id');
         if (typeof(that.initialPosition[id]) === 'undefined') {
            // this is the placeholder
            return;
         }
         if (id != that.changingItemId) {
            return;
         }
         event.target.gridstack.update(
            item.el,
            that.initialPosition[id]['x'],
            that.initialPosition[id]['y'],
            that.initialPosition[id]['width'],
            that.initialPosition[id]['height'],
         );
      });
   };

   // === QUESTIONS ===

   this.deleteQuestion = function (target) {
      var item = target.closest('.grid-stack-item');
      var id = item.getAttribute('data-id');
      if (typeof(id) === 'undefined') {
         return;
      }
      var that = this;
      if (confirm(i18n.textdomain('formcreator').__('Are you sure you want to delete this question?', 'formcreator'))) {
         jQuery.ajax({
         url: formcreatorRootDoc + '/ajax/question_delete.php',
         type: "POST",
         data: {
               id: id,
            }
         }).fail(function(data) {
            alert(data.responseText);
         }).done(function() {
            var container = item.closest('.grid-stack');
            var gridstack = container.gridstack;
            var row = $(item).attr('data-gs-y');
            gridstack.removeWidget(item);
            that.resetTabs();
         });
      }
   };


   this.toggleRequired = function (target) {
      var item = $(target).closest('.grid-stack-item');
      var id = item.attr('data-id');
      if (typeof(id) === 'undefined') {
         return;
      }
      var required = $(target).hasClass('fa-check-circle');
      jQuery.ajax({
         url: formcreatorRootDoc + '/ajax/question_toggle_required.php',
         type: "POST",
         data: {
            id: id,
            required: required ? '0' : '1'
         }
      }).fail(function(data) {
         alert(data.responseText);
      }).done(function() {
         $(target)
            .removeClass('fa-circle fa-check-circle')
            .addClass(required ? 'fa-circle' : 'fa-check-circle');
      });
   };

   this.plugin_formcreator_scrollToModal = function (modalWindow) {
   $('html, body').animate({
        scrollTop: $(modalWindow).closest('.ui-dialog').offset().top
      }, 300);
   }

   this.addQuestion = function () {
      var form = document.querySelector('form[data-itemtype="PluginFormcreatorQuestion"]');
      var that = this;
      tinyMCE.triggerSave();
      $.post({
         url: formcreatorRootDoc + '/ajax/question_add.php',
         data: new FormData(form),
         processData: false,
         contentType: false,
         dataType: 'json',
      }).fail(function(data) {
         displayAjaxMessageAfterRedirect();
      }).done(function(data) {
         var sectionId = form.querySelector('select[name="plugin_formcreator_sections_id"]').value;
         var container = document.querySelector('[data-itemtype="PluginFormcreatorSection"][data-id="' + sectionId + '"] .grid-stack');
         var grid = container.gridstack;
         grid.addWidget(
            data.html,
            {
               autoPosition: false,
               x: Number(data.x),
               y: Number(data.y),
               w: Number(data.width),
               h: Number(data.height),
               minW: 1,
               maxW: that.questionColumns,
               minH: 1,
               maxH: 1,
            }
         );
         that.resetTabs();
      });
   }

   this.editQuestion = function () {
      var form = $('form[data-itemtype="PluginFormcreatorQuestion"]');
      var questionId = form.find('[name="id"]').val();
      var that = this;
      tinyMCE.triggerSave();
      $.post({
         url: formcreatorRootDoc + '/ajax/question_update.php',
         data: form.serializeArray(),
         dataType: 'json'
      }).fail(function(data) {
         displayAjaxMessageAfterRedirect();
      }).done(function(data) {
         var question = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorQuestion"][data-id="' + questionId + '"]');
         $(question.find('[data-field="name"]')).replaceWith(data['name']);
         that.resetTabs();
      });

   }

   this.duplicateQuestion = function (target) {
      var item = target.closest('.grid-stack-item');
      var id = item.getAttribute('data-id');
      var that = this;
      if (typeof(id) === 'undefined') {
         return;
      }

      $.ajax({
         url: formcreatorRootDoc + '/ajax/question_duplicate.php',
         type: "POST",
         dataType: 'json',
         data: {
            id: id
         }
      }).fail(function(data) {
         alert(data.responseText);
      }).done(function(question) {
         var container = item.closest('[data-itemtype="PluginFormcreatorSection"] .grid-stack');
         var grid = container.gridstack;
         grid.addWidget(
            question.html,
            {
               autoPosition: false,
               x: Number(question.x),
               y: Number(question.y),
               w: Number(question.width),
               h: Number(question.height),
               minW: 1,
               maxW: that.questionColumns,
               minH: 1,
               maxH: 1,
            }
         );
         that.resetTabs();
      });
   };

   this.showFields = function (form) {
      var data = form.serializeArray();
      data = this.serializeForAjax(form);

      $.ajax({
         url: formcreatorRootDoc + '/ajax/showfields.php',
         type: "POST",
         dataType: 'json',
         data: data
      }).done(function(response){
         try {
            var questionToShow = response['PluginFormcreatorQuestion'];
            var sectionToShow = response['PluginFormcreatorSection'];
            var submitButtonToShow = response['PluginFormcreatorForm'];
         } catch (e) {
            // Do nothing for now
         }
         for (var sectionKey in sectionToShow) {
            var sectionId = parseInt(sectionKey);
            if (!isNaN(sectionId)) {
               if (sectionToShow[sectionId]) {
                  $('#plugin_formcreator_form.plugin_formcreator_form [data-itemtype = "PluginFormcreatorSection"][data-id="' + sectionId+ '"]').removeAttr('hidden');
               } else {
                  $('#plugin_formcreator_form.plugin_formcreator_form [data-itemtype = "PluginFormcreatorSection"][data-id="' + sectionId+ '"]').attr('hidden', '');
               }
            }
         }
         var i = 0;
         for (var questionKey in questionToShow) {
            var questionId = questionKey;
            questionId = parseInt(questionKey.replace('formcreator_field_', ''));
            if (!isNaN(questionId)) {
               if (questionToShow[questionKey]) {
                  $('#plugin_formcreator_form.plugin_formcreator_form [data-itemtype = "PluginFormcreatorQuestion"][data-id="' + questionKey + '"]').removeAttr('hidden');
               } else {
                  $('#plugin_formcreator_form.plugin_formcreator_form [data-itemtype = "PluginFormcreatorQuestion"][data-id="' + questionKey + '"]').attr('hidden', '');
               }
            }
         }

         $('#plugin_formcreator_form.plugin_formcreator_form button[name="add"]').toggle(submitButtonToShow == true);
      });
   };

   this.showFieldsDebounced = _.debounce(this.showFields, 400, false);

   // === SECTIONS ===

   this.deleteSection = function (item) {
      if(confirm(i18n.textdomain('formcreator').__('Are you sure you want to delete this section?', 'formcreator'))) {
         var section = $(item).closest('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]');
         var sectionId = section.attr('data-id');
         var that = this;
         $.ajax({
         url: formcreatorRootDoc + '/ajax/section_delete.php',
         type: "POST",
         data: {
               id: sectionId
            }
         }).done(function() {
            section.remove();
            plugin_formcreator.updateSectionControls();
            that.resetTabs();
         }).fail(function(data) {
            alert(data.responseText);
         });
      }
   };

   this.moveSection = function (item, action) {
      var section = $(item).closest('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]');
      var sectionId = section.attr('data-id');
      $.ajax({
         url: formcreatorRootDoc + '/ajax/section_move.php',
         type: "POST",
         data: {
            id: sectionId,
            way: action
         }
      }).done(function() {
         if (action == 'up') {
            var otherSection = section.prev('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]').detach();
            section.after(otherSection);
         }
         if (action == 'down') {
            var otherSection = section.next('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]').detach();
            section.before(otherSection);
         }
         $.each([section, otherSection], function(index, section) {
            if (section.prev('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]').length < 1) {
               section.children('.moveUp').hide();
            } else {
               section.children('.moveUp').show();
            }
            if (section.next('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]').length < 1) {
               section.children('.moveDown').hide();
            } else {
               section.children('.moveDown').show();
            }
         });
      });
   };

   this.showQuestionForm = function (sectionId, questionId = 0) {
      var modalId = glpi_ajax_dialog({
         dialogclass: 'modal-xl',
         url: formcreatorRootDoc + '/ajax/question.php',
         autoShow: true,
         params: {
            id: questionId,
            plugin_formcreator_sections_id: sectionId
         }
      });
   };

   this.submitQuestion = function (target) {
      var idInput = target.querySelector('[name="id"]');
      var questionId = null;
      if (idInput) {
         questionId = idInput.getAttribute('value');
      }
      if (questionId === null) {
         plugin_formcreator.addQuestion(target);
      } else {
         plugin_formcreator.editQuestion(target);
      }
      $(target).closest('div.modal').modal('hide');
   }

   this.duplicateSection = function (item) {
      var section = $(item).closest('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]');
      var sectionId = section.attr('data-id');
      var that = this;
      $.ajax({
         url: formcreatorRootDoc + '/ajax/section_duplicate.php',
      type: "POST",
      data: {
         id: sectionId
      },
      dataType: 'html'
      }).done(function(data) {
         var lastSection = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorSection"]').last();
         lastSection.after(data);
         sectionId = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorSection"]').last().attr('data-id');
         that.resetTabs();
      }).fail(function(data) {
         alert(data.responseText);
      });
   };

   this.showSectionForm = function (formId, sectionId = 0) {
      var modalId = glpi_ajax_dialog({
         dialogclass: 'modal-xl',
         url: formcreatorRootDoc + '/ajax/section.php',
         autoShow: true,
         params: {
            section_id: sectionId,
            plugin_formcreator_forms_id: formId
         },
         done: function () {
            document.querySelector('#' + modalId + ' form[name="asset_form"]').addEventListener('submit', function(event) {
               var idInput = event.target.querySelector('[name="id"]');
               var sectionId = null;
               if (idInput) {
                  sectionId = idInput.getAttribute('value');
               }
               if (sectionId === null) {
                  plugin_formcreator.addSection(event);
               } else {
                  plugin_formcreator.editSection(event);
               }
               $('#' + modalId).modal('hide');
            });
         }
      });
   }

   this.addSection = function (event) {
      var form = event.target;
      var that = this;
      tinyMCE.triggerSave();
      $.post({
         url: formcreatorRootDoc + '/ajax/section_add.php',
         processData: false,
         contentType: false,
         data: new FormData(form),
         dataType: 'json'
      }).fail(function () {
         displayAjaxMessageAfterRedirect();
      }).done(function (data) {
         var addSectionRow = $('[data-itemtype="PluginFormcreatorForm"] li').last();
         addSectionRow.before(data['html']);
         var sectionId = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorSection"]').last().attr('data-id');
         plugin_formcreator.initGridStack(sectionId);
         plugin_formcreator.updateSectionControls();
         that.resetTabs();
      }).complete(function () {
         var myModal = form.closest('div.modal');
         $(myModal).modal('hide');
      });

   }

   this.editSection = function (event) {
      var form = event.target;
      var sectionId = form.querySelector('[name="id"]').value;
      var that = this;
      tinyMCE.triggerSave();
      $.post({
         url: formcreatorRootDoc + '/ajax/section_update.php',
         processData: false,
         contentType: false,
         data: new FormData(form),
         dataType: 'json'
      }).fail(function () {
         displayAjaxMessageAfterRedirect();
      }).done(function (data) {
         var section = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorSection"][data-id="' + sectionId + '"]');
         section.find('[data-field="name"]').replaceWith(data['name']);
         that.resetTabs();
      }).complete(function () {
         var myModal = form.closest('div.modal');
         $(myModal).modal('hide');
      });
   }

   /**
    * Show / hide controls for sections
    */
   this.updateSectionControls = function () {
      var sections = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorSection"]');
      sections.find('.moveUp').show();
      sections.first().find('.moveUp').hide();
      sections.find('.moveDown').show();
      sections.last().find('.moveDown').hide();
   }

   this.createLanguage = function (formId, id = -1) {
      var placeholder = $('#plugin_formcreator_formLanguage');
      this.showSpinner(placeholder);
      $.post({
         url: rootDoc + '/ajax/viewsubitem.php',
         data: {
            type: "PluginFormcreatorForm_Language",
            parenttype: "PluginFormcreatorForm",
            plugin_formcreator_forms_id: formId,
            id: id
         }
      }).done(function (data) {
         $(placeholder).html(data);
      });
   }

   /**
    * Put a spinner inside the given selector
    */
   this.showSpinner = function (selector) {
      return $(selector).html('<img class="plugin_formcreator_spinner" src="../../../pics/spinner.48.gif">');
   }

   /**
    * destroy hidden tabs. Useful when their content is obsoleted
    */
   this.resetTabs = function () {
      // $('.glpi_tabs [role="tabpanel"][aria-hidden="true"] ').empty();
      var tabs = document.querySelectorAll('.tab-content div[role="tabpanel"]:not(.show)')
      tabs.forEach(item => {
            while (item.lastChild) {
               item.removeChild(item.lastChild);
            }
      });
   }

   this.showTranslationEditor = function (object) {
      var formlanguageId = $(object).closest('[data-itemtype="PluginFormcreatorForm_Language"][data-id]').attr('data-id');
      var plugin_formcreator_translations_id = $(object).find('input[name="id"]').val();
      $('#plugin_formcreator_editTranslation').load(formcreatorRootDoc + '/ajax/edit_translation.php', {
         plugin_formcreator_form_languages_id: formlanguageId,
         plugin_formcreator_translations_id: ''
      });
   }

   this.newTranslation = function (formLanguageId) {
      glpi_ajax_dialog({
         dialogclass: 'modal-xl',
         url: '../ajax/form_language.php',
         params: {
            action: 'newTranslation',
            id: formLanguageId,
         },
         title: i18n.textdomain('formcreator').__('Add translations', 'formcreator'),
         close: function () {
            reloadTab();
         },
         fail: function () {
            displayAjaxMessageAfterRedirect();
         }
      });
   }

   this.saveNewTranslation = function (element) {
      var that = this;
      var form = document.querySelector('form[name="plugin_formcreator_translation"]');
      tinyMCE.triggerSave();
      $.post({
         url: '../ajax/translation.php',
         data: $(element).closest('form').serializeArray()
      }).fail(function () {
         displayAjaxMessageAfterRedirect();
      }).done(function () {
         // Remove unclosed TinyMCE toolbar
         var tinyToolbar = document.querySelector('.tox-tinymce-aux');
         if (tinyToolbar) {
            tinyToolbar.parentNode.removeChild(tinyToolbar);
         }
         that.showTranslationEditor(form);
      });
   }

   this.updateTranslation = function (element) {
      tinyMCE.triggerSave();
      $.post({
         url: '../ajax/translation.php',
         data: $(element).closest('form').serializeArray()
      }).fail(function () {
         displayAjaxMessageAfterRedirect();
      }).done(function () {
         $(element).closest('div.modal').modal('hide');
      });
   }

   this.showUpdateTranslationForm = function (element) {
      var formLanguageId = $(element).closest('[data-itemtype="PluginFormcreatorForm_Language"][data-id]').attr('data-id');
      var translationId = $(element.closest('[data-itemtype="PluginFormcreatorTranslation"]')).attr('data-id');
      var modal;
      modal = glpi_ajax_dialog({
         dialogclass: 'modal-xl',
         url: '../ajax/form_language.php',
         params: {
            action: 'translation',
            id: formLanguageId,
            plugin_formcreator_translations_id: translationId
         },
         title: i18n.textdomain('formcreator').__('Update a translation', 'formcreator'),
         close: function () {
            // Remove unclosed TinyMCE toolbar
            var tinyToolbar = document.querySelector('.tox-tinymce-aux');
            if (tinyToolbar) {
               tinyToolbar.parentNode.removeChild(tinyToolbar);
            }
            // Reload the tab
            reloadTab();
         },
         fail: function () {
            displayAjaxMessageAfterRedirect();
         }
      });
   }

   // make a new selector equivalent to :contains(...) but case insensitive
   jQuery.expr[":"].icontains = jQuery.expr.createPseudo(function (arg) {
      return function (elem) {
         return jQuery(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
      };
   });

   // filter override results
   var debounce;
   $(document).on('change paste keyup', '.plugin_formcreator_filter_translations > input', function() {
      var text = $(this).val();

      // delay event by a little time to avoid trigger on each key press
      window.clearTimeout(debounce);
      debounce = window.setTimeout(function() {
         // reshow all tr
         $(".translation_list tbody tr").show();

         // find tr with searched text inside
         var tr_with_text = $(".translation_list tbody tr:has(td:icontains("+text+"))");

         // hide other tr
         var tr_inverse = $(".translation_list tbody tr").not(tr_with_text);
         tr_inverse.hide();
      }, 200);
   });

   this.toggleForm = function(id) {
      $.ajax({
         url: formcreatorRootDoc + '/ajax/form.php',
         type: 'POST',
         data: {
            action: 'toggle_active',
            id: id
         }
      }).success(function () {
         location.reload();
      });
   }

   this.toggleDefaultForm = function(id) {
      $.ajax({
         url: formcreatorRootDoc + '/ajax/form.php',
         type: 'POST',
         data: {
            action: 'toggle_default',
            id: id
         }
      }).success(function () {
         location.reload();
      });
   }

   this.changeActor = function(type, value) {
      $('div[data-actor-type^=' + type + ']').hide();
      $('div[data-actor-type=' + type + '_' + value + ']').show();
   }

   this.updateWizardFormsView = function (item) {
      if (item) {
         this.activeCategory = item.getAttribute('data-category-id');
      }
      $.when(getFormAndFaqItems(this.activeCategory))
      .done(
         function (response) {
            tiles = response.forms;
            showTiles(tiles, response.default);
         }
      ).fail(
         function () {
            var html = '<p>' + i18n.textdomain('formcreator').__('An error occured while querying forms', 'formcreator') + '</p>'
            $('#plugin_formcreator_wizard_forms').empty();
            $('#plugin_formcreator_wizard_forms').prepend(html);
            $('#plugin_formcreator_formlist').masonry({
               horizontalOrder: true
            });
            $('#plugin_formcreator_faqlist').masonry({
               horizontalOrder: true
            });
         }
      );
   }

   this.updateKbitemsView = function (item) {
      if (item) {
         this.activeCategory = item.getAttribute('data-category-id');
      }
      $.when(getFaqItems(this.activeCategory)).done(
         function (response) {
            tiles = response.forms;
            showTiles(tiles, false);
         }
      ).fail(
         function () {
            var html = '<p>' + i18n.textdomain('formcreator').__('An error occured while querying forms', 'formcreator') + '</p>'
            $('#plugin_formcreator_wizard_forms').empty();
            $('#plugin_formcreator_wizard_forms').prepend(html);
            $('#plugin_formcreator_formlist').masonry({
               horizontalOrder: true
            });
            $('#plugin_formcreator_faqlist').masonry({
               horizontalOrder: true
            });
         }
      );
   }

   this.deleteActor = function (item) {
      var item = item.closest('div[data-itemtype="PluginFormcreatorTarget_Actor"][data-id]');
      var id = item.getAttribute('data-id');
      $.post({
         url: formcreatorRootDoc + '/ajax/target_actor.php',
         data: {
            action: 'delete',
            id: id
         }
      }).fail(function () {
         displayAjaxMessageAfterRedirect();
      }).success(function () {
         reloadTab();
      });
   }

   this.addActor = function (item) {
      var form = item.closest('form');
      var target = form.closest('[data-itemtype][data-id]');
      var data = new FormData(form);
      data.append('action', 'add');
      data.append('itemtype', target.getAttribute('data-itemtype'));
      data.append('items_id', target.getAttribute('data-id'));
      $.post({
         url: formcreatorRootDoc + '/ajax/target_actor.php',
         processData: false,
         contentType: false,
         data: data
      }).fail(function () {
         displayAjaxMessageAfterRedirect();
      }).success(function () {
         reloadTab();
      });
   }

   this.changeQuestionType = function (target) {
      var form = document.querySelector('form[name="asset_form"][data-itemtype="PluginFormcreatorQuestion"]');
      var questionId = 0;
      if (document.querySelector('form[name="asset_form"][data-itemtype="PluginFormcreatorQuestion"] [name="id"]')) {
         questionId = document.querySelector('form[name="asset_form"][data-itemtype="PluginFormcreatorQuestion"] [name="id"]').value;
      }
      var data = new FormData(form);
      data.append('id', questionId);
      $.post({
         url: formcreatorRootDoc + '/ajax/question_design.php',
         processData: false,
         contentType: false,
         data: data,
      }).done(function(response) {
         try {
            // The response may contain script tags, to be interpreted
            // We cannot use document.querySelector here
            $('form[name="asset_form"][data-itemtype="PluginFormcreatorQuestion"]')
            .closest('div.asset')
            .replaceWith(response);
         } catch (e) {
            console.log('Plugin Formcreator: Failed to get subtype fields');
            return;
         }
      });
   };

   this.submitUserForm = function (event) {
      var form     = document.querySelector('form[role="form"][data-itemtype]');
      var data     = new FormData(form);
      data.append('add', '');
      $.post({
         url: formcreatorRootDoc + '/ajax/formanswer.php',
         processData: false,
         contentType: false,
         data: data,
         dataType: 'json'
      }).done(function (data) {
         if (typeof(data.redirect) == 'string') {
            window.location = data.redirect;
         }
      }).fail(function (xhr, data) {
         $(form).find('[type="submit"]')
            .html(i18n.textdomain('formcreator').__('Send', 'formcreator'))
            .off('click');
         $(form).removeAttr('data-submitted');

         if (xhr.responseText == '') {
            displayAjaxMessageAfterRedirect();
            return;
         }
         if (typeof(xhr.responseJSON) == 'undefined') {
            alert(i18n.textdomain('formcreator').__('An internal error occurred. Please report it to administrator.', 'formcreator'));
         }
         if (typeof(xhr.responseJSON.message) == 'undefined') {
            displayAjaxMessageAfterRedirect();
            return;
         }
         var display_container = ($('#messages_after_redirect').length  == 0);
         var html = xhr.responseJSON.message;
         if (display_container) {
            $('body').append(html);
         } else {
            $('#messages_after_redirect').append(html);
            initMessagesAfterRedirectToasts();
         }
      });
      event.preventDefault();
      blockFormSubmit($(form), event);
      return false;
   };

   this.submitUserFormByKeyPress = function (event) {
      var keyPressed = event.keyCode || event.which;
      if (keyPressed === 13 && $('#plugin_formcreator_form.plugin_formcreator_form button[name="add"]').is(':hidden')) {
         event.preventDefault();
         return false;
      }

      return true;
   };

   /**
    * Serialize a form without its csrf token
    * @param {*} form
    * @returns
    */
   this.serializeForAjax = function (form) {
      var serialized = form.serializeArray()
      return serialized.filter( function( item ) {
         return item.name != '_glpi_csrf_token';
      });
   }

   this.showMassiveRestrictions = function (item) {
      document.querySelector('#plugin_formcreator_restrictions_head').style.display = 'none';
      document.querySelector('#plugin_formcreator_restrictions').style.display = 'none';
      document.querySelector('#plugin_formcreator_captcha').style.display = 'none';
      if (item.value == 2 /* PluginFormcreatorForm::ACCESS_RESTRICTED */) {
         document.querySelector('#plugin_formcreator_restrictions').style.display = 'block';
         document.querySelector('#plugin_formcreator_restrictions_head').style.display = 'block';
      } else if (item.value == 0 /* PluginFormcreatorForm::ACCESS_PUBLIC */) {
         document.querySelector('#plugin_formcreator_captcha').style.display = 'block';
      }
   }
}

// === TARGETS ===

function plugin_formcreator_addTarget(items_id) {
   glpi_ajax_dialog({
      dialogclass: 'modal-xl',
      url: formcreatorRootDoc + '/ajax/target.php',
      params: {
         plugin_formcreator_forms_id: items_id
      },
   });
}

$(document).on('click', '.plugin_formcreator_duplicate_target', function() {
   if(confirm(i18n.textdomain('formcreator').__('Are you sure you want to duplicate this target?', 'formcreator'))) {
      $.post({
        url: formcreatorRootDoc + '/ajax/form_duplicate_target.php',
        data: {
            action: 'duplicate_target',
            itemtype: $(this).data('itemtype'),
            items_id: $(this).data('items-id'),
         }
      }).done(function () {
         reloadTab();
      }).fail(function () {
         displayAjaxMessageAfterRedirect();
      });
   }
});

$(document).on('click', '.plugin_formcreator_delete_target', function() {
   if(confirm(i18n.textdomain('formcreator').__('Are you sure you want to delete this target?', 'formcreator'))) {
      $.post({
        url: formcreatorRootDoc + '/ajax/form_delete_target.php',
        data: {
            action: 'delete_target',
            itemtype: $(this).data('itemtype'),
            items_id: $(this).data('items-id'),
         }
      }).done(function () {
         reloadTab();
      }).fail(function () {
         displayAjaxMessageAfterRedirect();
      });
   }
});

// DESTINATION
function plugin_formcreator_formcreatorChangeDueDate(value) {
   $('#due_date_questions').hide();
   $('#due_date_time').hide();
   switch (value) {
      case '2' :
         $('#due_date_questions').show();
         break;
      case '3' :
         $('#due_date_time').show();
         break;
      case '4' :
         $('#due_date_questions').show();
         $('#due_date_time').show();
         break;
   }
}

function plugin_formcreator_formcreatorChangeSla(value) {
   switch (value) {
      default:
      case '1' :
         $('#sla_specific_title').hide();
         $('#sla_specific_value').hide();
         $('#sla_question_title').hide();
         $('#sla_questions').hide();
         break;
      case '2' :
         $('#sla_question_title').hide();
         $('#sla_questions').hide();
         $('#sla_specific_title').show();
         $('#sla_specific_value').show();
         break;
      case '3' :
         $('#sla_specific_title').hide();
         $('#sla_specific_value').hide();
         $('#sla_question_title').show();
         $('#sla_questions').show();
         break;
   }
}

function plugin_formcreator_formcreatorChangeOla(value) {
   switch (value) {
      default:
      case '1' :
         $('#ola_specific_title').hide();
         $('#ola_specific_value').hide();
         $('#ola_question_title').hide();
         $('#ola_questions').hide();
         break;
      case '2' :
         $('#ola_question_title').hide();
         $('#ola_questions').hide();
         $('#ola_specific_title').show();
         $('#ola_specific_value').show();
         break;
      case '3' :
         $('#ola_specific_title').hide();
         $('#ola_specific_value').hide();
         $('#ola_question_title').show();
         $('#ola_questions').show();
         break;
   }
}

function plugin_formcreator_displayRequesterForm() {
   $('#form_add_requester').show();
   $('#btn_add_requester').hide();
   $('#btn_cancel_requester').show();
}

function plugin_formcreator_hideRequesterForm() {
   $('#form_add_requester').hide();
   $('#btn_add_requester').show();
   $('#btn_cancel_requester').hide();
}

function plugin_formcreator_displayWatcherForm() {
   $('#form_add_watcher').show();
   $('#btn_add_watcher').hide();
   $('#btn_cancel_watcher').show();
}

function plugin_formcreator_hideWatcherForm() {
   $('#form_add_watcher').hide();
   $('#btn_add_watcher').show();
   $('#btn_cancel_watcher').hide();
}

function plugin_formcreator_displayAssignedForm() {
   $('#form_add_assigned').show();
   $('#btn_add_assigned').hide();
   $('#btn_cancel_assigned').show();
}

function plugin_formcreator_hideAssignedForm() {
   $('#form_add_assigned').hide();
   $('#btn_add_assigned').show();
   $('#btn_cancel_assigned').hide();
}

// === FIELDS EDITION ===

function plugin_formcreator_changeGlpiObjectItemType() {
   var glpi_object    = $('[data-itemtype="PluginFormcreatorQuestion"] [name="glpi_objects"]').val();
   var glpi_object_id = $('[data-itemtype="PluginFormcreatorQuestion"] [name="id"]').val();

   $.post({
      url: formcreatorRootDoc + '/ajax/dropdown_values.php',
      data: {
         dropdown_itemtype: glpi_object,
         id: glpi_object_id
      },
   }).done(function(response) {
      $('#dropdown_default_value_field').html(response);
   });

   $.post({
      url: formcreatorRootDoc + '/ajax/commontree.php',
      data: {
         itemtype: glpi_object,
         root: $("#commonTreeDropdownRoot").val(),
         maxDepth: $("#commonTreeDropdownMaxDepth").val(),
         selectableRoot: $("#commonTreeDropdownSelectableRoot").val(),
      },
   }).done(function(response) {
      $('.plugin_formcreator_dropdown').html(response);
      $('.plugin_formcreator_dropdown').toggle(true);
   }).fail(function() {
      $('.plugin_formcreator_dropdown').html("");
      $('.plugin_formcreator_dropdown').toggle(false);
   });
}

// === CONDITIONS ===

function plugin_formcreator_toggleCondition(target) {
   var form = $(target).closest('form');

   var selector = 'div[data-itemtype="PluginFormcreatorCondition"]';
   if (target.value == '1') {
      form.find(selector).hide();
   } else {
      if (form.find(selector).length < 1) {
         plugin_formcreator_addEmptyCondition(target);
      }
      form.find(selector).show();
   }
}

function plugin_formcreator_addEmptyCondition(target) {
   var form     = target.closest('form[data-itemtype]');
   var itemtype = form.getAttribute('data-itemtype');
   var id       = form.getAttribute('data-id') || null;
   var data     = new FormData(form);
   data.append('itemtype', itemtype);
   data.append('items_id', id);
   $.post({
      url: formcreatorRootDoc + '/ajax/condition.php',
      processData: false,
      contentType: false,
      data: data,
   }).done(function (data) {
      target.closest('div.row').after(document.createRange().createContextualFragment(data));
   });
}

function plugin_formcreator_removeNextCondition(target) {
   target.closest('div.row').remove();
}

// === FIELDS ===

/**
 * Initialize a simple field
 */
function pluginFormcreatorInitializeField(fieldName, rand) {
   var field = $('[name="' + fieldName + '"]');
   var timer = getTimer(field);
   var callback = function() {
      plugin_formcreator.showFields($(field[0].form));
   }
   timer(300, callback);
   timers.push(timer);
}

/**
 * Initialize an actor field
 */
function pluginFormcreatorInitializeActor(fieldName, rand) {
   var field = $('select[name="' + fieldName + '[]"]');
   field.on("change", function(e) {
      plugin_formcreator.showFields($(field[0].form));
   });
}


/**
 * Initialize a checkboxes field
 */
function pluginFormcreatorInitializeCheckboxes(fieldName, rand) {
   var field = $('[name="' + fieldName + '[]"]');
   field.on("change", function() {
      plugin_formcreator.showFields($(field[0].form));
   });
}

/**
 * Initialize a date field
 */
function pluginFormcreatorInitializeDate(fieldName, rand) {
   var field = $('[name="' + fieldName + '"]');
   field.on("change", function() {
      plugin_formcreator.showFields($(field[0].form));
   });
}

/**
 * Initialize a dropdown field
 */
function pluginFormcreatorInitializeDropdown(fieldName, rand) {
   var field = $('[name="' + fieldName + '"]');
   field.on("change", function(e) {
      plugin_formcreator.showFields($(field[0].form));
   });
}

/**
 * Initialize a email field
 */
function pluginFormcreatorInitializeEmail(fieldName, rand) {
   var field = $('[name="' + fieldName + '"]');
   var timer = getTimer(field);
   var callback = function() {
      plugin_formcreator.showFields($(field[0].form));
   }
   timer(300, callback);
   timers.push(timer);
}

/**
 * Initialize a radios field
 */
function pluginFormcreatorInitializeRadios(fieldName, rand) {
   var field = $('[name="' + fieldName + '"]');
   field.on("change", function() {
      plugin_formcreator.showFields($(field[0].form));
   });
}

/**
 * Initialize a multiselect field
 */
function pluginFormcreatorInitializeMultiselect(fieldName, rand) {
   var field = $('select[name="' + fieldName + '[]"]');
   field.on("change", function() {
      plugin_formcreator.showFields($(field[0].form));
   });
}

/**
 * Initialize a request type field
 */
function pluginFormcreatorInitializeRequestType(fieldName, rand) {
   var field = $('select[name="' + fieldName + '"]');
   field.on("change", function(e) {
      plugin_formcreator.showFields($(field[0].form));
   });
}

/**
 * Initialize a select field
 */
function pluginFormcreatorInitializeSelect(fieldName, rand) {
   var field = $('[name="' + fieldName + '"]');
   field.on("change", function() {
      plugin_formcreator.showFields($(field[0].form));
   });
}

/**
 * Initialize a tag field
 */
function pluginFormcreatorInitializeTag(fieldName, rand) {
   var field = $('[name="' + fieldName + '[]"]');
   field.on("change", function(e) {
      plugin_formcreator.showFields($(field[0].form));
   });
}

/**
 * Initialize a textarea field
 */
function pluginFormcreatorInitializeTextarea(fieldName, rand) {
   var i = 0;
   var e;
   while (e = tinymce.get(i++)) {
      if ($(e.targetElm).prop('name') != fieldName) {
         continue;
      }

      const field = $('[name="' + fieldName + '"]');
      const form = field[0].form
      // https://stackoverflow.com/a/63342064
      e.on('input NodeChange', function(e) {
         tinyMCE.triggerSave();
         plugin_formcreator.showFieldsDebounced($(form));
      });
      return;
  }
}

/**
 * Initialize a time field
 */
function pluginFormcreatorInitializeTime(fieldName, rand) {
   var field = $('[name="_' + fieldName + '"]');
   field.on("change", function() {
      plugin_formcreator.showFields($(field[0].form));
   });
   $('#resetdate' + rand).on("click", function() {
      plugin_formcreator.showFields($(field[0].form));
   });
}

/**
 * Initialize a urgency field
 */
function pluginFormcreatorInitializeUrgency(fieldName, rand) {
   var field = $('[name="' + fieldName + '"]');
   field.on("change", function(e) {
      plugin_formcreator.showFields($(field[0].form));
   });
}

function plugin_formcreator_updateQuestionSpecific(html) {
   $('.plugin_formcreator_question_specific').slice(1).remove();
   if (html == '') {
      $('.plugin_formcreator_question_specific').hide();
      return;
   }
   $('.plugin_formcreator_question_specific').replaceWith(html);
}

function plugin_formcreator_changeLDAP(ldap) {
   var ldap_directory = ldap.value;

   jQuery.ajax({
   url: formcreatorRootDoc + '/ajax/ldap_filter.php',
   type: 'POST',
   data: {
         value: ldap_directory,
      },
   }).done(function(response) {
         var selector = '$slashSelector';
      document.querySelector('form[data-itemtype=\"PluginFormcreatorQuestion\"] [name="ldap_filter"]').value = response;
   });
}

/**
 * preview of the selected pictogram
 */
function plugin_formceator_showPictogram(id, preview) {
   var value = $(id).val();
   $('#' + preview).removeClass().addClass(value);
}

/**
 * update composite ticket (links between tickets) in target ticket (design mode)
 */
function plugin_formcreator_updateCompositePeerType(type) {
   $('#plugin_formcreator_link_ticket').hide();
   $('#plugin_formcreator_link_target').hide();
   $('#plugin_formcreator_link_question').hide();

   switch ($(type).val()) {
      case 'Ticket':
         $('#plugin_formcreator_link_ticket').show();
         break;
      case 'PluginFormcreatorTargetTicket':
         $('#plugin_formcreator_link_target').show();
         break;
      case 'PluginFormcreatorQuestion':
         $('#plugin_formcreator_link_question').show();
         break;
   }
}

/**
 * update category settings in a form of a target object (design mode)
 */
 function plugin_formcreator_changeCategory(rand) {
   $('#category_specific_title').hide();
   $('#category_specific_value').hide();
   $('#category_question_title').hide();
   $('#category_question_value').hide();

   switch($('#dropdown_category_rule' + rand).val()) {
      case '3' :
         $('#category_question_title').show();
         $('#category_question_value').show();
         break;
      case '2' :
         $('#category_specific_title').show();
         $('#category_specific_value').show();
         break;
   }
}

/**
 * change request type of a target item (design mode)
 */
function plugin_formcreator_changeRequestType(rand) {
   $('#requesttype_specific_title').hide();
   $('#requesttype_specific_value').hide();
   $('#requesttype_question_title').hide();
   $('#requesttype_question_value').hide();

   switch($('#dropdown_type_rule' + rand).val()) {
      case '1': // PluginFormcreatorTargetTicket::REQUESTTYPE_SPECIFIC
         $('#requesttype_specific_title').show();
         $('#requesttype_specific_value').show();
         break;
      case '2': // PluginFormcreatorTargetTicket::REQUESTTYPE_ANSWER
         $('#requesttype_question_title').show();
         $('#requesttype_question_value').show();
         break;
   }
}

/**
 * change urgency of a target item (design mode)
 */
function plugin_formcreator_changeUrgency(rand) {
   $('#urgency_specific_title').hide();
   $('#urgency_specific_value').hide();
   $('#urgency_question_title').hide();
   $('#urgency_question_value').hide();

   switch($('#dropdown_urgency_rule' + rand).val()) {
      case '2' :
         $('#urgency_specific_title').show();
         $('#urgency_specific_value').show();
         break;
      case '3':
         $('#urgency_question_title').show();
         $('#urgency_question_value').show();
         break;
   }
}

function plugin_formcreator_change_associate(rand) {
   $('#plugin_formcreator_associate_specific_title').hide();
   $('#plugin_formcreator_associate_specific_value').hide();
   $('#plugin_formcreator_associate_question_title').hide();
   $('#plugin_formcreator_associate_question_value').hide();

   switch($('#dropdown_associate_rule' + rand).val()) {
      case '3': // PluginFormcreatorTargetTicket::ASSOCIATE_RULE_ANSWER
         $('#plugin_formcreator_associate_question_title').show();
         $('#plugin_formcreator_associate_question_value').show();
         break;
      case '2': // PluginFormcreatorTargetTicket::ASSOCIATE_RULE_SPECIFIC
         $('#plugin_formcreator_associate_specific_title').show();
         $('#plugin_formcreator_associate_specific_value').show();
         break;
   }
}

function plugin_formcreator_change_location(rand) {
   $('#location_specific_title').hide();
   $('#location_specific_value').hide();
   $('#location_question_title').hide();
   $('#location_question_value').hide();

   switch($('#dropdown_location_rule' + rand).val()) {
      case '3' : // PluginFormcreatorAbstractTarget::CATEGORY_RULE_ANSWER
         $('#location_question_title').show();
         $('#location_question_value').show();
         break;
      case '2' : // PluginFormcreatorAbstractTarget::CATEGORY_RULE_SPECIFIC
         $('#location_specific_title').show();
         $('#location_specific_value').show();
         break;
   }
}

function plugin_formcreator_change_validation(rand) {
   switch($('#dropdown_commonitil_validation_rule' + rand).val()) {
      case '1' : // PluginFormcreatorAbstractTarget::COMMONITIL_VALIDATION_RULE_NONE
         $('#commonitil_validation_specific_title').hide();
         $('#commonitil_validation_specific').hide();
         $('#commonitil_validation_from_question_title').hide();
         $('#commonitil_validation_answer_user').hide();
         $('#commonitil_validation_answer_group').hide();
         break;

      case '2' : // PluginFormcreatorAbstractTarget::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP
         $('#commonitil_validation_specific_title').show();
         $('#commonitil_validation_specific').show();
         $('#commonitil_validation_from_question_title').hide();
         $('#commonitil_validation_answer_user').hide();
         $('#commonitil_validation_answer_group').hide();
         break;

      case '3' : // PluginFormcreatorAbstractTarget::COMMONITIL_VALIDATION_RULE_ANSWER_USER
         $('#commonitil_validation_from_question_title').show();
         $('#commonitil_validation_answer_user').show();
         $('#commonitil_validation_answer_group').hide();
         $('#commonitil_validation_specific_title').hide();
         $('#commonitil_validation_specific').hide();
         break;

      case '4' : // PluginFormcreatorAbstractTarget::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP
         $('#commonitil_validation_from_question_title').show();
         $('#commonitil_validation_answer_group').show();
         $('#commonitil_validation_answer_user').hide();
         $('#commonitil_validation_specific_title').hide();
         $('#commonitil_validation_specific').hide();
         break;
   }
}

function plugin_formcreator_change_entity(rand) {
   $('#entity_specific_title').hide();
   $('#entity_user_title').hide();
   $('#entity_entity_title').hide();
   $('#entity_specific_value').hide();
   $('#entity_user_value').hide();
   $('#entity_entity_value').hide();

   switch($('#dropdown_destination_entity' + rand).val()) {
      case '7' : // PluginFormcreatorAbstractTarget::DESTINATION_ENTITY_SPECIFIC
         $('#entity_specific_title').show();
         $('#entity_specific_value').show();
         break;
      case '8' : // PluginFormcreatorAbstractTarget::DESTINATION_ENTITY_USER
         $('#entity_user_title').show();
         $('#entity_user_value').show();
         break;
      case '9' : // PluginFormcreatorAbstractTarget::DESTINATION_ENTITY_ENTITY
         $('#entity_entity_title').show();
         $('#entity_entity_value').show();
         break;
   }
}

function plugin_formcreator_changeValidators(value) {
   if (value == 1) {
      document.getElementById("validators_users").style.display  = "block";
      document.getElementById("validators_groups").style.display = "none";
   } else if (value == 2) {
      document.getElementById("validators_users").style.display  = "none";
      document.getElementById("validators_groups").style.display = "block";
   } else {
      document.getElementById("validators_users").style.display  = "none";
      document.getElementById("validators_groups").style.display = "none";
   }
}

function plugin_formcreator_cancelMyTicket(id) {
   $.ajax({
      url: formcreatorRootDoc + '/ajax/cancelticket.php',
      data: {id: id},
      type: "POST",
      dataType: "text"
   }).done(function(response) {
      window.location.replace(formcreatorRootDoc + '/front/issue.php?reset=reset');
   }).error(function(response) {
      alert(response.responseText);
   });
}

function plugin_formcreator_refreshCaptcha() {
   var captchaId = $('input[name="plugin_formcreator_captcha_id"]').val();
   $('form[name="plugin_formcreator_form"] button[type="submit"]').attr('disabled', 'disabled');
   $.ajax({
      url : formcreatorRootDoc + '/ajax/getcaptcha.php',
      data: {captcha_id: captchaId},
      type: 'POST',
      dataType: 'text'
   }).done(function(response) {
      $('#plugin_formcreator_captcha_section img').attr('src', response);
   }).complete(function(response) {
      $('form[name="plugin_formcreator_form"] button[type="submit"]').removeAttr('disabled');
   });
}
