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
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

include ('../../../inc/includes.php');
header('Content-Type: text/javascript');
?>
"use strict";

var modalWindow;
var rootDoc          = CFG_GLPI['root_doc'];
var currentCategory  = "0";
var sortByName = false;
var tiles = [];
var serviceCatalogEnabled = false;
var slinkyCategories;
var timers = [];

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

// === MENU ===
var link = '';
link += '<li id="menu7">';
link += '<a href="' + rootDoc + '/plugins/formcreator/front/formlist.php" class="itemP">';
link += "<?php echo Toolbox::addslashes_deep(_n('Form', 'Forms', 2, 'formcreator')); ?>";
link += '</a>';
link += '</li>';

$(function() {
   var target = $('body');
   modalWindow = $("<div></div>").dialog({
      width: 980,
      autoOpen: false,
      height: "auto",
      modal: true,
      position: ['center', 50],
      open: function( event, ui ) {
         //remove existing tinymce when reopen modal (without this, tinymce don't load on 2nd opening of dialog)
         modalWindow.find('.mce-container').remove();
      }
   });

   // toggle menu in desktop mode
   $('#formcreator-toggle-nav-desktop').change(function() {
      $('.plugin_formcreator_container').toggleClass('toggle_menu');
      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/homepage_wizard.php',
         data: {wizard: 'toggle_menu'},
         type: "POST",
         dataType: "json"
      })
   });

   serviceCatalogEnabled = $("#plugin_formcreator_serviceCatalog").length;

   // Prevent jQuery UI dialog from blocking focusin
   $(document).on('focusin', function(e) {
       if ($(e.target).closest(".mce-window, .moxman-window").length) {
         e.stopImmediatePropagation();
      }
   });

   <?php
   if (Session::getCurrentInterface() == 'helpdesk'
       && PluginFormcreatorForm::countAvailableForm() > 0) {
      echo "$('#c_menu #menu1:first-child').after(link);";
   }
   ?>

   if (location.pathname.indexOf("helpdesk.public.php") != -1) {

      $('.ui-tabs-panel:visible').ready(function() {
         showHomepageFormList();
      });

      $('#tabspanel + div.ui-tabs').on("tabsload", function(event, ui) {
         showHomepageFormList();
      });

      showHomepageFormList();

   } else if ($('#plugin_formcreator_wizard_categories').length > 0) {
      updateCategoriesView();
      updateWizardFormsView(0);
      $("#wizard_seeall").parent().addClass('category_active');

      // Setup events
      $('.plugin_formcreator_sort [value=mostPopularSort]').click(function () {
         sortByName = false;
         showTiles(tiles);
      });

      $('.plugin_formcreator_sort [value=alphabeticSort]').click(function () {
         sortByName = true;
         showTiles(tiles);
      });

      $('#plugin_formcreator_wizard_categories #wizard_seeall').click(function () {
         slinkyCategories.home();
         updateWizardFormsView(0);
         $('#plugin_formcreator_wizard_categories .category_active').removeClass('category_active');
         $(this).addClass('category_active');
      });
   }

   // Initialize search bar
   var searchInput = $('#plugin_formcreator_searchBar input:first');
   if (searchInput.length == 1) {
      // Dynamically update forms and faq items while the user types in the search bar
      var timer = getTimer(searchInput);
      var callback = function() {
         updateWizardFormsView(currentCategory);
      }
      timer(300, callback);
      timers.push(timer);

      // Clear the search bar if it gains focus
      $('#plugin_formcreator_searchBar input').focus(function(event) {
         if (searchInput.val().length > 0) {
            searchInput.val('');
            updateWizardFormsView(currentCategory);
            $.when(getFormAndFaqItems(0)).then(
               function (response) {
                  tiles = response;
                  showTiles(tiles.forms);
               }
            );
         }
      });

      // Initialize sort controls
      $('.plugin_formcreator_sort [value=mostPopularSort]')[0].checked = true;
   }

   // === Add better multi-select on form configuration validators ===
   // initialize the pqSelect widget.
      fcInitMultiSelect();

   $('#tabspanel + div.ui-tabs').on("tabsload", function( event, ui ) {
      fcInitMultiSelect();
   });

});

function fcInitMultiSelect() {
   $("#validator_users").select2();
   $("#validator_groups").select2();
}

function showHomepageFormList() {
   if ($('#plugin_formcreatorHomepageForms').length) {
      return;
   }

   $.ajax({
      url: rootDoc + '/plugins/formcreator/ajax/homepage_forms.php',
      type: "GET"
   }).done(function(response){
      if (!$('#plugin_formcreatorHomepageForms').length) {
         $('.central > tbody:first').first().prepend(response);
      }
   });
}

function updateCategoriesView() {
   $.ajax({
      url: rootDoc + '/plugins/formcreator/ajax/homepage_wizard.php',
      data: {wizard: 'categories'},
      type: "GET",
      dataType: "json"
   }).done(function(response) {
      var html = '<div class="slinky-menu">';
      html = html + buildCategoryList(response);
      html = html + '</div>';

      //Display categories
      $('#plugin_formcreator_wizard_categories .slinky-menu').remove();
      $('#plugin_formcreator_wizard_categories').append(html);

      // Setup slinky
      slinkyCategories = $('#plugin_formcreator_wizard_categories div:nth(2)').slinky({
         label: true
      });
      $('#plugin_formcreator_wizard_categories a.back').click(
         function(event) {
            parentItem = $(event.target).parentsUntil('#plugin_formcreator_wizard_categories > div', 'li')[1];
            parentAnchor = $(parentItem).children('a')[0];
            updateWizardFormsView(parentAnchor.getAttribute('data-parent-category-id'));
         }
      );

      $('#plugin_formcreator_wizard_categories a[data-category-id]').click(
         function (event) {
            $('#plugin_formcreator_wizard_categories .category_active').removeClass('category_active');
            $(this).addClass('category_active');
            updateWizardFormsView(event.target.getAttribute('data-category-id'));
         }
      );
   });
}

/**
 * get form and faq items from DB
 * Returns a promise
 */
function getFormAndFaqItems(categoryId) {
   var currentCategory = categoryId;
   var keywords = $('#plugin_formcreator_searchBar input:first').val();
   var deferred = jQuery.Deferred();
   $.ajax({
      url: rootDoc + '/plugins/formcreator/ajax/homepage_wizard.php',
      data: {wizard: 'forms', categoriesId: categoryId, keywords: keywords, helpdeskHome: 0},
      type: "GET",
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
            return 1
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
            return 1
         }
         return 0;
      });
   }
   return items;
}

function showTiles(tiles, defaultForms) {
   var tiles = sortFormAndFaqItems(tiles, sortByName);
   var html = '';
   if (defaultForms) {
      html += '<p><?php echo Toolbox::addslashes_deep(__('No form found. Please choose a form below instead', 'formcreator'))?></p>'
   }
   html += buildTiles(tiles);

   //Display tiles
   $('#plugin_formcreator_wizard_forms').empty();
   $('#plugin_formcreator_wizard_forms').prepend(html);
   $('#plugin_formcreator_formlist').masonry({
      horizontalOrder: true
   });
   $('#plugin_formcreator_faqlist').masonry({
      horizontalOrder: true
   });
}

function updateWizardFormsView(categoryId) {
   $.when(getFormAndFaqItems(categoryId)).done(
      function (response) {
         tiles = response.forms;
         showTiles(tiles, response.default);
      }
   ).fail(
      function () {
         var html = '<p><?php echo Toolbox::addslashes_deep(__('An error occured while querying forms', 'formcreator'))?></p>'
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

function buildCategoryList(tree) {
   var html = '';
   if (tree.id != 0) {
      html = '<a href="#" data-parent-category-id="' + tree.parent +'"'
         + ' data-category-id="' + tree.id + '"'
         + ' onclick="updateWizardFormsView(' + tree.id + ')">'
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
      html = '<p id="plugin_formcreator_formlist">'
      + "<?php echo Toolbox::addslashes_deep(__('No form yet in this category', 'formcreator')) ?>"
      + '</p>'
      +'<p id="plugin_formcreator_faqlist"></p>';
   } else {
      var forms = [];
      var faqs = [];
      $.each(list, function (key, item) {
         // Build a HTML tile
         var url = rootDoc + '/plugins/formcreator/front/formdisplay.php?id=' + item.id;
         if (item.type != 'form') {
            if (serviceCatalogEnabled) {
               url = rootDoc + '/plugins/formcreator/front/knowbaseitem.form.php?id=' + item.id;
            } else {
               url = rootDoc + '/front/knowbaseitem.form.php?id=' + item.id;
            }
         }

         var description = '';
         if (item.description) {
            description = '<div class="plugin_formcreator_formTile_description">'
                          +item.description
                          +'</div>';
         }

         var default_class = '';
         if (JSON.parse(item.is_default)) {
            default_class = 'default_form';
         }

         if (item.icon == '') {
            if (item.type == 'form') {
               item.icon = 'fa fa-question-circle';
            } else {
               item.icon = 'fa fa-clipboard-list';
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
               '<div style="background-color: ' + item.background_color + '" class="plugin_formcreator_formTile '+item.type+' '+default_class+'" title="'+item.description+'">'
               + '<i class="' + item.icon + '" style="color: ' + item.icon_color+ '"></i>'
               + '<a href="' + url + '" class="plugin_formcreator_formTile_title">'
               + item.name
               + '</a>'
               + description
               + '</div>'
            );
         } else {
            faqs.push(
               '<div style="background-color: ' + item.background_color + '" class="plugin_formcreator_formTile '+item.type+' '+default_class+'" title="'+item.description+'">'
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
   }

   return html;
}

var plugin_formcreator = new function() {
   // Properties of the item when the user begins to change it
   this.initialPosition = {};
   this.changingItemId = 0;
   this.questionsColumns = <?php echo PluginFormcreatorSection::COLUMNS; ?>;
   this.dirty = false;

   this.setupGridStack = function (group) {
      var that = this;
      group
      .on('resizestart', this.startChangeItem)
      .on('dragstart', this.startChangeItem)
      .on('change', function(event, item) {
         that.changeItems(event, item)
      })
      .on('dragstop', function(event, item) {
         setTimeout(function() {
            item.helper.find('a').off('click.prevent');
         },
         300);
         // Remove empty rows
         plugin_formcreator.moveUpItems(group);
      });
      group.on('dropped', function (event, previousWidget, newWidget) {
         var changes = {};
         var section = $(newWidget.el).closest('[data-itemtype="PluginFormcreatorSection"]');
         var itemId = $(newWidget.el).attr('data-id');
         changes[itemId] = {
            plugin_formcreator_sections_id: section.attr('data-id'),
            width: newWidget.width,
            height: newWidget.height,
            x: newWidget.x,
            y: newWidget.y
         };
         $.ajax({
            'url': rootDoc + '/plugins/formcreator/ajax/question_move.php',
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
      var group = $('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"][data-id="' + sectionId + '"] .grid-stack');
      group.gridstack({
         width:          this.questionsColumns,
         column:         this.questionsColumns,
         cellHeight:     '32px',
         verticalMargin: '5px',
         float:          false,
         acceptWidgets:  true,
         resizeable:     {
            handles: 'e, w'
         }
      });
      $.get({
         url: rootDoc + '/plugins/formcreator/ajax/question_get.php',
         dataType: 'json',
         data: {
            id: sectionId,
            design: true
         }
      }).success(function(data, httpCode) {
         var grid = group.data('gridstack');
         that.dirty = true;
         $.each(data, function(index, question) {
            grid.addWidget(
               question.html,
               Number(question.x),
               Number(question.y),
               Number(question.width),
               Number(question.height),
               false,
               1,
               this.questionColumns,
               1,
               1
            );
         });
         that.dirty = false;
      }).complete(this.setupGridStack(group));
   };

   /**
   * Event handler : when an item is about to move or resize
   */
  this.startChangeItem = function (event, item) {
      item.helper.find('a').on('click.prevent', function(event) {
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
               width:  item.width,
               height: item.height,
               x:      item.x,
               y:      item.y
            };
         }
      });
      if (changes.length < 1) {
         return;
      }
      $.ajax({
         'url': rootDoc + '/plugins/formcreator/ajax/question_move.php',
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
         $(event.target).data('gridstack').update(
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
      var item = $(target).closest('.grid-stack-item');
      var id = item.attr('data-id');
      if (typeof(id) === 'undefined') {
         return;
      }
      if (confirm("<?php echo Toolbox::addslashes_deep(__('Are you sure you want to delete this question?', 'formcreator')); ?> ")) {
         jQuery.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/question_delete.php',
         type: "POST",
         data: {
               id: id,
            }
         }).fail(function(data) {
            alert(data.responseText);
         }).done(function() {
            var container = item.closest('.grid-stack');
            var gridstack = container.data('gridstack');
            var row = $(item).attr('data-gs-y');
            gridstack.removeWidget(item);
            //plugin_formcreator.moveUpItems(container);
         });
      }
   };

   /**
    * Move up items in a grid when row is empty
    * @param grid stack container
    * @param row  row to fill with items after it
    */
   this.moveUpItems = function (grid) {
      return;
      // Disabled cause it does not fully works with dropped elements
      var lastRow = grid.find('.grid-stack-item:not(.grid-stack-placeholder)').last().attr('data-gs-y');
      for (let y = 0; y < lastRow; y++) {
         var movable = grid.find('.grid-stack-item:not(.grid-stack-placeholder)').filter(function(index, element) {
            return ($(element).attr('data-gs-y') > y);
         }).first();
         if (movable.length > 0) {
            grid.data('gridstack').move(movable, Number(movable.attr('data-gs-x')), y)
         }
      }
   };

   this.toggleRequired = function (target) {
      var item = $(target).closest('.grid-stack-item');
      var id = item.attr('data-id');
      if (typeof(id) === 'undefined') {
         return;
      }
      var required = $(target).hasClass('fa-dot-circle');
      jQuery.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/question_toggle_required.php',
         type: "POST",
         data: {
            id: id,
            required: required ? '0' : '1'
         }
      }).fail(function(data) {
         alert(data.responseText);
      }).done(function() {
         $(target)
            .removeClass('fa-circle fa-dot-circle')
            .addClass(required ? 'fa-circle' : 'fa-dot-circle');
      });
   };

   this.plugin_formcreator_scrollToModal = function (modalWindow) {
   $('html, body').animate({
        scrollTop: $(modalWindow).closest('.ui-dialog').offset().top
    }, 300);
}

   this.addQuestion = function () {
      var form = $('form[data-itemtype="PluginFormcreatorQuestion"]');
      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/question_add.php',
         type: "POST",
         data: form.serializeArray(),
         dataType: 'json'
      }).fail(function(data) {
         // Closing and opening the modal workarounds
         // the whole modal being disabled when alert is shown
         // modalWindow.dialog('close');
         // alert(data.responseText);
         // modalWindow.dialog('open');
         $('#plugin_formcreator_error').text(data.responseText);
         $('#plugin_formcreator_error').show();
      }).done(function(data) {
         var sectionId = form.find('select[name="plugin_formcreator_sections_id"]').val();
         var container = $('[data-itemtype="PluginFormcreatorSection"][data-id="' + sectionId + '"] .grid-stack');
         var grid = container.data('gridstack');
         grid.addWidget(
            data.html,
            Number(data.x),
            Number(data.y),
            Number(data.width),
            Number(data.height),
            false,
            1,
            this.questionColumns,
            1,
            1
         );
         modalWindow.dialog('close');
      });
   }

   this.editQuestion = function () {
      var form = $('form[data-itemtype="PluginFormcreatorQuestion"]');
      var questionId = form.find('[name="id"]').val();
      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/question_update.php',
         type: "POST",
         data: form.serializeArray(),
         dataType: 'html'
      }).fail(function(data) {
         $('#plugin_formcreator_error').text(data.responseText);
         $('#plugin_formcreator_error').show();
      }).done(function(data) {
         var question = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorQuestion"][data-id="' + questionId + '"]');
         question.find('[data-field="name"]').text(data)
         modalWindow.dialog('close');
      });
   }

   this.duplicateQuestion = function (target) {
      var item = $(target).closest('.grid-stack-item');
      var id = item.attr('data-id');
      if (typeof(id) === 'undefined') {
         return;
      }

      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/question_duplicate.php',
         type: "POST",
         dataType: 'json',
         data: {
            id: id
         }
      }).fail(function(data) {
         alert(data.responseText);
      }).done(function(question) {
         var container = item.closest('[data-itemtype="PluginFormcreatorSection"] .grid-stack');
         var grid = container.data('gridstack');
         grid.addWidget(
            question.html,
            Number(question.x),
            Number(question.y),
            Number(question.width),
            Number(question.height),
            false,
            1,
            this.questionColumns,
            1,
            1
         );
      });
   };

   this.showFields = function (form) {
      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/showfields.php',
         type: "POST",
         data: form.serializeArray()
      }).done(function(response){
         try {
            var itemToShow = JSON.parse(response);
            var questionToShow = itemToShow['PluginFormcreatorQuestion'];
            var sectionToShow = itemToShow['PluginFormcreatorSection'];
            var submitButtonToShow = itemToShow['PluginFormcreatorForm'];
         } catch (e) {
            // Do nothing for now
         }
         for (var sectionKey in sectionToShow) {
            var sectionId = parseInt(sectionKey);
            if (!isNaN(sectionId)) {
               if (sectionToShow[sectionId]) {
                  $('#plugin_formcreator_form.plugin_formcreator_form [data-itemtype = "PluginFormcreatorSection"][data-id="' + sectionId+ '"]').removeAttr('hidden', '');
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
                  $('#plugin_formcreator_form.plugin_formcreator_form [data-itemtype = "PluginFormcreatorQuestion"][data-id="' + questionKey + '"]').removeAttr('hidden', '');
               } else {
                  $('#plugin_formcreator_form.plugin_formcreator_form [data-itemtype = "PluginFormcreatorQuestion"][data-id="' + questionKey + '"]').attr('hidden', '');
               }
            }
         }

         $('[name="submit_formcreator"]').toggle(submitButtonToShow == true);
      });
   };

   // === SECTIONS ===

   this.deleteSection = function (item) {
      if(confirm("<?php echo Toolbox::addslashes_deep(__('Are you sure you want to delete this section?', 'formcreator')); ?> ")) {
         var section = $(item).closest('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]');
         var sectionId = section.attr('data-id');
         $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/section_delete.php',
         type: "POST",
         data: {
               id: sectionId
            }
         }).done(function() {
            section.remove();
            plugin_formcreator.updateSectionControls();
         }).fail(function(data) {
            alert(data.responseText);
         });
      }
   };

   this.moveSection = function (item, action) {
      var section = $(item).closest('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]');
      var sectionId = section.attr('data-id');
      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/section_move.php',
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
      modalWindow.load(rootDoc + '/plugins/formcreator/ajax/question.php', {
         question_id: questionId,
         plugin_formcreator_sections_id: sectionId
      }).dialog('open');
      this.plugin_formcreator_scrollToModal($(modalWindow));
   };

   this.duplicateSection = function (item) {
      var section = $(item).closest('#plugin_formcreator_form.plugin_formcreator_form_design [data-itemtype="PluginFormcreatorSection"]');
      var sectionId = section.attr('data-id');
      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/section_duplicate.php',
      type: "POST",
      data: {
         id: sectionId
      },
      dataType: 'html'
      }).done(function(data) {
         var lastSection = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorSection"]').last();
         lastSection.after(data);
         sectionId = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorSection"]').last().attr('data-id');
         plugin_formcreator.initGridStack(sectionId);
         plugin_formcreator.updateSectionControls();
      }).fail(function(data) {
         alert(data.responseText);
      });
   };

   this.showSectionForm = function (formId, sectionId = 0) {
      modalWindow.load(
         rootDoc + '/plugins/formcreator/ajax/section.php', {
            section_id: sectionId,
            plugin_formcreator_forms_id: formId
         }
      ).dialog('open');
      this.plugin_formcreator_scrollToModal($(modalWindow));
   }

   this.addSection = function () {
      var form = $('form[data-itemtype="PluginFormcreatorSection"]');
      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/section_add.php',
         type: "POST",
         data: form.serializeArray(),
         dataType: 'html'
      }).fail(function(data) {
         alert(data.responseText);
      }).done(function(data) {
         var addSectionRow = $('[data-itemtype="PluginFormcreatorForm"] li').last();
         addSectionRow.before(data);
         var sectionId = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorSection"]').last().attr('data-id');
         plugin_formcreator.initGridStack(sectionId);
         plugin_formcreator.updateSectionControls();
         modalWindow.dialog('close');
      });
   }

   this.editSection = function () {
      var form = $('form[data-itemtype="PluginFormcreatorSection"]');
      var sectionId = form.find('[name="id"]').val();
      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/section_update.php',
         type: "POST",
         data: form.serializeArray(),
         dataType: 'html'
      }).fail(function(data) {
         alert(data.responseText);
      }).done(function(data) {
         var section = $('.plugin_formcreator_form_design[data-itemtype="PluginFormcreatorForm"] [data-itemtype="PluginFormcreatorSection"][data-id="' + sectionId + '"]');
         section.find('> [data-field="name"]').text(data);
         modalWindow.dialog('close');
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
}

// === TARGETS ===

function plugin_formcreator_addTarget(items_id, token) {
   modalWindow.load(rootDoc + '/plugins/formcreator/ajax/target.php', {
      plugin_formcreator_forms_id: items_id
   }).dialog("open");
}

function plugin_formcreator_editTarget(itemtype, items_id) {
   modalWindow.load(rootDoc + '/plugins/formcreator/ajax/target_edit.php', {
      itemtype: itemtype,
      id: items_id
   }).dialog("open");
}

function plugin_formcreator_deleteTarget(itemtype, target_id, token) {
   if(confirm("<?php echo Toolbox::addslashes_deep(__('Are you sure you want to delete this destination:', 'formcreator')); ?> ")) {
      jQuery.ajax({
        url: rootDoc + '/plugins/formcreator/front/form.form.php',
        type: "POST",
        data: {
            delete_target: 1,
            itemtype: itemtype,
            items_id: target_id,
            _glpi_csrf_token: token
         }
      }).done(function () {
         location.reload();
      });

   }
}

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

function plugin_formcreator_ChangeActorRequester(value) {
   $('#block_requester_user').hide();
   $('#block_requester_question_user').hide();
   $('#block_requester_group').hide();
   $('#block_requester_question_group').hide();
   $('#block_requester_group_from_object').hide();
   $('#block_requester_tech_group_from_object').hide();
   $('#block_requester_question_actors').hide();
   $('#block_requester_supplier').hide();
   $('#block_requester_question_supplier').hide();

   switch (value) {
      case '3' : $('#block_requester_user').show();                   break;
      case '4' : $('#block_requester_question_user').show();          break;
      case '5' : $('#block_requester_group').show();                  break;
      case '6' : $('#block_requester_question_group').show();         break;
      case '10': $('#block_requester_group_from_object').show();      break;
      case '11': $('#block_requester_tech_group_from_object').show(); break;
      case '9' : $('#block_requester_question_actors').show();        break;
      case '7' : $('#block_requester_supplier').show();               break;
      case '8' : $('#block_requester_question_supplier').show();      break;
   }
}

function plugin_formcreator_ChangeActorWatcher(value) {
   $('#block_watcher_user').hide();
   $('#block_watcher_question_user').hide();
   $('#block_watcher_group').hide();
   $('#block_watcher_question_group').hide();
   $('#block_watcher_group_from_object').hide();
   $('#block_watcher_tech_group_from_object').hide();
   $('#block_watcher_question_actors').hide();
   $('#block_watcher_supplier').hide();
   $('#block_watcher_question_supplier').hide();

   switch (value) {
      case '3' : $('#block_watcher_user').show();                   break;
      case '4' : $('#block_watcher_question_user').show();          break;
      case '5' : $('#block_watcher_group').show();                  break;
      case '6' : $('#block_watcher_question_group').show();         break;
      case '9' : $('#block_watcher_question_actors').show();        break;
      case '10': $('#block_watcher_group_from_object').show();      break;
      case '11': $('#block_watcher_tech_group_from_object').show(); break;
      case '9' : $('#block_watcher_question_actors').show();        break;
      case '7' : $('#block_watcher_supplier').show();               break;
      case '8' : $('#block_watcher_question_supplier').show();      break;
   }
}

function plugin_formcreator_ChangeActorAssigned(value) {
   $('#block_assigned_user').hide();
   $('#block_assigned_question_user').hide();
   $('#block_assigned_group').hide();
   $('#block_assigned_question_group').hide();
   $('#block_assigned_group_from_object').hide();
   $('#block_assigned_tech_group_from_object').hide();
   $('#block_assigned_question_actors').hide();
   $('#block_assigned_supplier').hide();
   $('#block_assigned_question_supplier').hide();

   // The numbers match PluginFormcreatorTarget_Actor::ACTOR_TYPE_* constants
   switch (value) {
      case '3' : $('#block_assigned_user').show();                   break;
      case '4' : $('#block_assigned_question_user').show();          break;
      case '5' : $('#block_assigned_group').show();                  break;
      case '6' : $('#block_assigned_question_group').show();         break;
      case '9' : $('#block_assigned_question_actors').show();        break;
      case '10': $('#block_assigned_group_from_object').show();      break;
      case '11': $('#block_assigned_tech_group_from_object').show(); break;
      case '9' : $('#block_assigned_question_actors').show();        break;
      case '7' : $('#block_assigned_supplier').show();               break;
      case '8' : $('#block_assigned_question_supplier').show();      break;
   }
}

// === FIELDS EDITION ===

function plugin_formcreator_changeDropdownItemtype(rand) {
   var dropdown_type = $('[data-itemtype="PluginFormcreatorQuestion"] [name="dropdown_values"]').val();
   var dropdown_id   = $('[data-itemtype="PluginFormcreatorQuestion"] [name="id"]').val();

   $.ajax({
      url: rootDoc + '/plugins/formcreator/ajax/dropdown_values.php',
      type: 'GET',
      data: {
         dropdown_itemtype: dropdown_type,
         'id': dropdown_id
      },
   }).done(function(response) {
      var showTicketCategorySpecific = false;
      if (dropdown_type == 'ITILCategory') {
         showTicketCategorySpecific = true;
      }
      $('#dropdown_default_value_field').html(response);
      $('.plugin_formcreator_dropdown_ticket').toggle(showTicketCategorySpecific);

      $.ajax({
         url: rootDoc + '/plugins/formcreator/ajax/commontree.php',
         type: 'GET',
         data: {
            itemtype: dropdown_type,
            root: $("#commonTreeDropdownRoot").val(),
            maxDepth: $("#commonTreeDropdownMaxDepth").val(),
         },
      }).done(function(response) {
         $('.plugin_formcreator_dropdown').html(response);
         $('.plugin_formcreator_dropdown').toggle(true);
      }).fail(function() {
         $('.plugin_formcreator_dropdown').html("");
         $('.plugin_formcreator_dropdown').toggle(false);
      });
   });
}

function plugin_formcreator_changeGlpiObjectItemType() {
   var glpi_object    = $('[data-itemtype="PluginFormcreatorQuestion"] [name="glpi_objects"]').val();
   var glpi_object_id = $('[data-itemtype="PluginFormcreatorQuestion"] [name="id"]').val();

   $.ajax({
      url: rootDoc + '/plugins/formcreator/ajax/dropdown_values.php',
      type: 'GET',
      data: {
         dropdown_itemtype: glpi_object,
         id: glpi_object_id
      },
   }).done(function(response) {
      $('#dropdown_default_value_field').html(response);
   });
}

// === CONDITIONS ===

function plugin_formcreator_toggleCondition(target) {
   var form = $(target).closest('form');

   var selector = 'tr[data-itemtype="PluginFormcreatorCondition"]';
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
   var form     = $(target).closest('form');
   var itemtype = form.attr('data-itemtype');
   // value if the hidden id input field
   var id       = form.find('[name="id"]').val();
   var parentKey;
   var parentId;
   var data = form.serializeArray();
   data.push({
      name: 'itemtype',
      value: itemtype
   });
   data.push({
      name: 'items_id',
      value: id
   });
   $.ajax({
      type: 'POST',
      url: rootDoc + '/plugins/formcreator/ajax/condition.php',
      data: data
   }).done(function (data) {
      $(target).parents('tr').after(data);
      $('.plugin_formcreator_logicRow .div_show_condition_logic').first().hide();
   });
}

function plugin_formcreator_removeNextCondition(target) {
   $(target).parents('tr').remove();
   $('[data-itemtype="PluginFormcreatorCondition"] .div_show_condition_logic').first().hide();
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
function pluginFormcreatorInitializeActor(fieldName, rand, initialValue) {
   var field = $('[name="' + fieldName + '[]"]');
   var dropdownMax = <?php echo $CFG_GLPI['dropdown_max'] ?>;
   field.select2({
      width: '80%',
      minimumInputLength: 0,
      quietMillis: 100,
      dropdownAutoWidth: true,
      minimumResultsForSearch: 0,
      tokenSeparators: [",", ";"],
      tags: true,
      ajax: {
         url: rootDoc + "/ajax/getDropdownUsers.php",
         type: "POST",
         dataType: "json",
         data: function (params) {
            return {
               entity_restrict: -1,
               searchText: params.term,
               page_limit: dropdownMax,
               page: params.page || 1
            }
         },
         processResults: function (data, params) {
            params.page = params.page || 1;

            var more = (data.count >= dropdownMax);
            return {results: data.results, pagination: {"more": more}};
         }
      },
      createTag: function(params) {
         var term = $.trim(params.term);

         if (term == '') {
            return null;
         }

         return {
            id: term,
            text: term,
            newTag: true
         }
      },
   });
   initialValue = JSON.parse(initialValue);
   for (var i = 0; i < initialValue.length; i++) {
      var option = new Option(initialValue[i].text, initialValue[i].id, true, true);
      field.append(option).trigger('change');
      field.trigger({
         type: 'select2.select',
         params: {
            data: initialValue[i]
         }
      });
   }
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
   var field = $('[name="_' + fieldName + '"]');
   field.on("change", function() {
      plugin_formcreator.showFields($(field[0].form));
   });
   $('#resetdate' + rand).on("click", function() {
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
   var field = $('[name="' + fieldName + '"]');
   field.on("change", function(e) {
      plugin_formcreator.showFields($(field[0].form));
   });
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

function plugin_formcreator_changeQuestionType(rand) {
   var questionId = $('form[name="form"][data-itemtype="PluginFormcreatorQuestion"] [name="id"]').val();
   var questionType = $('form[name="form"][data-itemtype="PluginFormcreatorQuestion"] [name="fieldtype"]').val();

   $.ajax({
      url: rootDoc + '/plugins/formcreator/ajax/question_design.php',
      type: 'GET',
      data: {
         questionId: questionId,
         questionType: questionType,
      },
   }).done(function(response) {
      try {
         var response = $.parseJSON(response);
      } catch (e) {
         console.log('Plugin Formcreator: Failed to get subtype fields');
         return;
      }

      $('#plugin_formcreator_subtype_label').html(response.label);
      $('#plugin_formcreator_subtype_value').html(response.field);

      $('.plugin_formcreator_required').toggle(response.may_be_required);
      $('.plugin_formcreator_mayBeEmpty').toggle(response.may_be_empty);
      $('#plugin_formcreator_subtype_label').html(response.label);
      $('#plugin_formcreator_subtype_value').html(response.field);
      plugin_formcreator_updateQuestionSpecific(response.additions);
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
   url: rootDoc + '/plugins/formcreator/ajax/ldap_filter.php',
   type: 'POST',
   data: {
         value: ldap_directory,
      },
   }).done(function(response) {
      document.getElementById('ldap_filter').value = response;
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
function plugin_formcreator_updateCompositePeerType(rand) {
   if ($('#dropdown__link_itemtype' + rand).val() == 'Ticket') {
      $('#plugin_formcreator_link_ticket').show();
      $('#plugin_formcreator_link_target').hide();
   } else {
      $('#plugin_formcreator_link_ticket').hide();
      $('#plugin_formcreator_link_target').show();
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
      case '3' : // PluginFormcreatorTargetBase::CATEGORY_RULE_ANSWER
         $('#location_question_title').show();
         $('#location_question_value').show();
         break;
      case '2' : // PluginFormcreatorTargetBase::CATEGORY_RULE_SPECIFIC
         $('#location_specific_title').show();
         $('#location_specific_value').show();
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
      case '7' : // PluginFormcreatorTargetBase::DESTINATION_ENTITY_SPECIFIC
         $('#entity_specific_title').show();
         $('#entity_specific_value').show();
         break;
      case '8' : // PluginFormcreatorTargetBase::DESTINATION_ENTITY_USER
         $('#entity_user_title').show();
         $('#entity_user_value').show();
         break;
      case '9' : // PluginFormcreatorTargetBase::DESTINATION_ENTITY_ENTITY
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

function plugin_formcreator_updateCompositePeerType(rand) {
   if ($('#dropdown__link_itemtype' + rand).val() == 'Ticket') {
      $('#plugin_formcreator_link_ticket').show();
      $('#plugin_formcreator_link_target').hide();
   } else {
      $('#plugin_formcreator_link_ticket').hide();
      $('#plugin_formcreator_link_target').show();
   }
}

function plugin_formcreator_cancelMyTicket(id) {
   $.ajax({
      url: rootDoc + '/plugins/formcreator/ajax/cancelticket.php',
      data: {id: id},
      type: "POST",
      dataType: "text"
   }).done(function(response) {
      window.location.replace(rootDoc + '/plugins/formcreator/front/issue.php?reset=reset');
   }).error(function(response) {
      alert("<?php echo __('Failed to cancel the ticket', 'formcreator'); ?>");
   });
}