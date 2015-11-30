<?php
include ('../../../inc/includes.php');
Session::checkRight("entity", UPDATE);

$question = new PluginFormcreatorQuestion();
if(empty($_REQUEST['question_id'])) {
   $question_id = 0;
   $question->getEmpty();
} else {
   $question_id = (int) $_REQUEST['question_id'];
   $question->getFromDB($question_id);
}
$form_id = (int) $_REQUEST['form_id'];

$rand = mt_rand();

?>

<form name="form_question" method="post"
      action="<?php echo $GLOBALS['CFG_GLPI']['root_doc']; ?>/plugins/formcreator/front/question.form.php">

   <table class="tab_cadre_fixe">
      <tr>
         <th colspan="4">
            <?php
               echo (0 == $question_id)
                  ? __('Add a question', 'formcreator')
                  : __('Edit a question', 'formcreator');
            ?>
         </th>
      </tr>

      <tr class="line0">
         <td width="20%">
            <label for="name" id="label_name">
               <?php echo  __('Title'); ?>&nbsp;
               <span style="color:red;">*</span>
            </label>
         </td>
         <td width="30%">
            <input type="text" name="name" id="name" style="width:90%;" autofocus
               value="<?php echo $question->fields['name']; ?>" class="required">
         </td>
         <td width="20%">
            <label for="dropdown_fieldtype<?php echo $rand; ?>" id="label_fieldtype">
               <?php echo _n('Type', 'Types', 1); ?>&nbsp;
               <span style="color:red;">*</span>
            </label>
         </td>
         <td width="30%">
            <?php
            $fieldtypes = PluginFormcreatorFields::getNames();
            Dropdown::showFromArray('fieldtype', $fieldtypes, array(
               'value'       => $question->fields['fieldtype'],
               'on_change'   => 'changeQuestionType();',
               'rand'        => $rand,
            ));
            ?>
         </td>
      </tr>
      <tr class="line1">
         <td>
            <label for="dropdown_plugin_formcreator_sections_id<?php echo $rand; ?>" id="label_name">
               <?php echo  _n('Section', 'Sections', 1, 'formcreator'); ?>&nbsp;
               <span style="color:red;">*</span>
            </label>
         </td>
         <td>
            <?php
            $table = getTableForItemtype('PluginFormcreatorSection');
            $sections = array();
            $sql = "SELECT `id`, `name`
                    FROM $table
                    WHERE `plugin_formcreator_forms_id` = $form_id
                    ORDER BY `order`";
            $result = $GLOBALS['DB']->query($sql);
            while ($section = $GLOBALS['DB']->fetch_array($result)) {
               $sections[$section['id']] = $section['name'];
            }
            Dropdown::showFromArray('plugin_formcreator_sections_id', $sections, array(
               'value' => ($question->fields['plugin_formcreator_sections_id']) ?: (int) $_REQUEST['section_id'],
               'rand'  => $rand,
            ));
            ?>
         </td>
         <td>
            <label for="dropdown_dropdown_values<?php echo $rand; ?>" id="label_dropdown_values">
               <?php echo _n('Dropdown', 'Dropdowns', 1); ?>
            </label>
            <label for="dropdown_glpi_objects<?php echo $rand; ?>" id="label_glpi_objects">
               <?php echo _n('GLPI object', 'GLPI objects', 1, 'formcreator'); ?>
            </label>
            <label for="dropdown_ldap_auth<?php echo $rand; ?>" id="label_glpi_ldap">
               <?php echo _n('LDAP directory', 'LDAP directories', 1); ?>
            </label>
         </td>
         <td>
            <div id="dropdown_values_field">
               <?php
                  $optgroup = Dropdown::getStandardDropdownItemTypes();
                  array_unshift($optgroup, '---');
                  Dropdown::showFromArray('dropdown_values', $optgroup, array(
                     'value'     => $question->fields['values'],
                     'rand'      => $rand,
                     'on_change' => 'change_dropdown();',
                  ));
               ?>
            </div>
            <div id="glpi_objects_field">
               <?php
                  $optgroup = array(
                                 __("Assets") => array(
                                    'Computer'           => _n("Computer", "Computers", 2),
                                    'Monitor'            => _n("Monitor", "Monitors", 2),
                                    'Software'           => _n("Software", "Software", 2),
                                    'Networkequipment'   => _n("Network", "Networks", 2),
                                    'Peripheral'         => _n("Device", "Devices", 2),
                                    'Printer'            => _n("Printer", "Printers", 2),
                                    'Cartridgeitem'      => _n("Cartridge", "Cartridges", 2),
                                    'Consumableitem'     => _n("Consumable", "Consumables", 2),
                                    'Phone'              => _n("Phone", "Phones", 2)),
                                 __("Assistance") => array(
                                    'Ticket'             => _n("Ticket", "Tickets", 2),
                                    'Problem'            => _n("Problem", "Problems", 2),
                                    'TicketRecurrent'    => __("Recurrent tickets")),
                                 __("Management") => array(
                                    'Budget'             => _n("Budget", "Budgets", 2),
                                    'Supplier'           => _n("Supplier", "Suppliers", 2),
                                    'Contact'            => _n("Contact", "Contacts", 2),
                                    'Contract'           => _n("Contract", "Contracts", 2),
                                    'Document'           => _n("Document", "Documents", 2)),
                                 __("Tools") => array(
                                    'Reminder'           => __("Notes"),
                                    'RSSFeed'            => __("RSS feed")),
                                 __("Administration") => array(
                                    'User'               => _n("User", "Users", 2),
                                    'Group'              => _n("Group", "Groups", 2),
                                    'Entity'             => _n("Entity", "Entities", 2),
                                    'Profile'            => _n("Profile", "Profiles", 2))
                              );;
                  array_unshift($optgroup, '---');
                  Dropdown::showFromArray('glpi_objects', $optgroup, array(
                     'value'     => $question->fields['values'],
                     'rand'      => $rand,
                     'on_change' => 'change_glpi_objects();',
                  ));
               ?>
            </div>
            <div id="glpi_ldap_field">
            <?php
            $ldap_values = json_decode(plugin_formcreator_decode($question->fields['values']));
            Dropdown::show('AuthLDAP', array(
               'name'      => 'ldap_auth',
               'rand'      => $rand,
               'value'     => (isset($ldap_values->ldap_auth)) ? $ldap_values->ldap_auth : '',
               'on_change' => 'change_LDAP(this)',
            ));
            ?>
            </div>
         </td>
      </tr>

      <tr class="line0" id="required_tr">
         <td>
            <label for="dropdown_required<?php echo $rand; ?>" id="label_required">
               <?php echo __('Required', 'formcreator'); ?>
            </label>
         </td>
         <td>
            <?php
            dropdown::showYesNo('required', $question->fields['required'], -1, array(
               'rand'  => $rand,
            ));
            ?>
         </td>
         <td>
            <label for="dropdown_show_empty<?php echo $rand; ?>" id="label_show_empty">
               <?php echo __('Show empty', 'formcreator'); ?>
            </label>
         </td>
         <td>
            <div id="show_empty">
               <?php
               dropdown::showYesNo('show_empty', $question->fields['show_empty'], -1, array(
                  'rand'  => $rand,
               ));
               ?>
            </div>
         </td>
      </tr>

      <tr class="line1" id="values_tr">
         <td>
            <label for="dropdown_default_values<?php echo $rand; ?>" id="label_default_values">
               <?php echo __('Default values'); ?><br />
               <small>(<?php echo __('One per line for lists', 'formcreator'); ?>)</small>
            </label>
            <label for="dropdown_dropdown_default_value<?php echo $rand; ?>" id="label_dropdown_default_value">
               <?php echo __('Default value'); ?>
            </label>
         </td>
         <td>
            <textarea name="default_values" id="default_values" rows="4" cols="40"
               style="width: 90%"><?php echo $question->fields['default_values']; ?></textarea>
            <div id="dropdown_default_value_field">
               <?php
               if((($question->fields['fieldtype'] == 'dropdown')
                     || ($question->fields['fieldtype'] == 'glpiselect'))
                   && !empty($question->fields['values'])
                   && class_exists($question->fields['values'])) {
                  Dropdown::show($question->fields['values'], array(
                     'name'  => 'dropdown_default_value',
                     'value' => $question->fields['default_values'],
                     'rand'  => $rand,
                  ));
               }
               ?>

            </div>
         </td>
         <td>
            <label for="values" id="label_values">
               <?php echo __('Values', 'formcreator'); ?><br />
               <small>(<?php echo __('One per line', 'formcreator'); ?>)</small>
            </label>
         </td>
         <td>
            <textarea name="values" id="values" rows="4" cols="40"
               style="width: 90%"><?php echo $question->fields['values']; ?></textarea>
         </td>
      </tr>

      <tr class="line1" id="ldap_tr">
         <td>
            <label for="ldap_filter">
               <?php echo __('Filter', 'formcreator'); ?>
            </label>
         </td>
         <td>
            <input type="text" name="ldap_filter" id="ldap_filter" style="width:98%;"
               value="<?php echo (isset($ldap_values->ldap_filter)) ? $ldap_values->ldap_filter : ''; ?>" />
         </td>
         <td>
            <label for="ldap_attribute">
               <?php echo __('Attribute', 'formcreator'); ?>
            </label>
         </td>
         <td>
            <?php
            $rand2 = mt_rand();
            Dropdown::show('RuleRightParameter', array(
               'name'  => 'ldap_attribute',
               'rand'  => $rand2,
               'value' => (isset($ldap_values->ldap_attribute)) ? $ldap_values->ldap_attribute : '',
            ));
            ?>
         </td>
      </tr>
      <tr class="line0" id="ldap_tr2">
         <td>
         </td>
         <td>
         </td>
         <td colspan="2">&nbsp;</td>
      </tr>

      <tr class="line0" id="range_tr">
         <td>
            <span id="label_range"><?php echo __('Range', 'formcreator'); ?></span>
         </td>
         <td>
            <label for="range_min" id="label_range_min">
               <?php echo __('Min', 'formcreator'); ?>
            </label>
            <input type="text" name="range_min" id="range_min" class="small_text"
               style="width: 90px" value="<?php echo $question->fields['range_min']; ?>" />
            &nbsp;
            <label for="range_max" id="label_range_max">
               <?php echo __('Max', 'formcreator'); ?>
            </label>
            <input type="text" name="range_max" id="range_max" class="small_text"
               style="width: 90px" value="<?php echo $question->fields['range_max']; ?>" />
         </td>
         <td colspan="2">&nbsp;</td>
      </tr>

      <tr class="line1" id="description_tr">
         <td>
            <label for="description" id="label_description">
               <?php echo __('Description'); ?>
            </label>
         </td>
         <td width="80%" colspan="3">
            <textarea name="description" id="description" rows="6" cols="108"
               style="width: 97%"><?php echo $question->fields['description']; ?></textarea>
            <?php Html::initEditorSystem('description'); ?>
         </td>
      </tr>

      <tr class="line0" id="regex_tr">
         <td>
            <label for="regex" id="label_regex">
               <?php echo __('Additional validation', 'formcreator'); ?><br />
               <small>
                  <a href="http://php.net/manual/reference.pcre.pattern.syntax.php" target="_blank">
                     (<?php echo __('Regular expression', 'formcreator'); ?>)</a>
               </small>
            </label>
         </td>
         <td width="80%" colspan="3">
            <input type="text" name="regex" id="regex" style="width:98%;"
               value="<?php echo $question->fields['regex']; ?>" />
               <em><?php echo __('Specify the additional validation conditions in the description of the question to help users.', 'formcreator'); ?></em>
         </td>
      </tr>

      <tr>
         <th colspan="4">
            <label for="dropdown_show_rule<?php echo $rand; ?>" id="label_show_type">
               <?php echo __('Show field', 'formcreator'); ?>
            </label>
         </th>
      </tr>

      <tr>
         <td>
            <?php
            Dropdown::showFromArray('show_rule', array(
               'always'    => __('Always displayed', 'formcreator'),
               'hidden'    => __('Hidden unless', 'formcreator'),
               'shown'     => __('Displayed unless', 'formcreator'),
            ), array(
               'value'     => $question->fields['show_rule'],
               'on_change' => 'toggleCondition(this);',
               'rand'      => $rand,
            ));
            $hide = (empty($question->fields['show_rule']) || ($question->fields['show_rule'] == 'always')) ? ' style="display:none"' : '';
            ?>
         </td>
         <td colspan="3">
            <div id="div_show_condition"<?php echo $hide; ?>>
               <?php
               // ===============================================================
               // TODO : Mettre en place l'interface multi-conditions
               // Ci-dessous une solution temporaire qui affiche uniquement la 1ere condition
               $sql = "SELECT `show_field`, `show_condition`, `show_value`
                       FROM glpi_plugin_formcreator_questions_conditions
                       WHERE `plugin_formcreator_questions_id` = $question_id
                       LIMIT 0, 1";
               $result = $GLOBALS['DB']->query($sql);
               list($show_field, $show_condition, $show_value) = $GLOBALS['DB']->fetch_array($result);
               // ===============================================================

               $table_question = getTableForItemtype('PluginFormcreatorQuestion');
               $table_section  = getTableForItemtype('PluginFormcreatorSection');
               $questions_tab  = array();
               $sql = "SELECT q.`id`, q.`name`
                       FROM $table_question q
                       LEFT JOIN $table_section s ON q.`plugin_formcreator_sections_id` = s.`id`
                       WHERE s.`plugin_formcreator_forms_id` = $form_id
                       AND q.`id` != $question_id
                       ORDER BY s.`order`, q.`order`";
               $result = $GLOBALS['DB']->query($sql);
               while ($line = $GLOBALS['DB']->fetch_array($result)) {
                  $questions_tab[$line['id']] = (strlen($line['name']) < 30)
                     ? $line['name']
                     : substr($line['name'], 0, strrpos(substr($line['name'], 0, 30), ' ')) . '...';
               }
               echo '<div id="div_show_condition_field">';
               Dropdown::showFromArray('show_field', $questions_tab, array(
                  'value' => $show_field
               ));
               echo '</div>';

               echo '<div id="div_show_condition_operator">';
               Dropdown::showFromArray('show_condition', array(
                  '=='    => '=',
                  '!='    => '&ne;',
                  '<'     => '&lt;',
                  '>'     => '&gt;',
                  '<='    => '&le;',
                  '>='    => '&ge;',
               ), array(
                  'value' => $show_condition,
                  'rand'  => $rand,
               ));
               echo '</div>';
               ?>

               <div id="div_show_condition_value">
                  <input type="text" name="show_value" id="show_value" class="small_text"
                     value="<?php echo $show_value; ?>" size="8">
               </div>
            </div>
         </td>
</tr>


      <tr class="line1">
         <td colspan="4" class="center">
            <input type="hidden" name="id" value="<?php echo $question_id; ?>" />
            <input type="hidden" name="plugin_formcreator_forms_id" value="<?php echo (int) $_REQUEST['form_id']; ?>" />
            <?php if(0 == $question_id) : ?>
               <input type="submit" name="add" class="submit_button" value="<?php echo __('Add'); ?>" />
            <?php else : ?>
               <input type="submit" name="update" class="submit_button" value="<?php echo __('Save'); ?>" />
            <?php endif; ?>
         </td>
      </tr>

   </table>

   <script type="text/javascript">
      function changeQuestionType() {
         var value = document.getElementById('dropdown_fieldtype<?php echo $rand; ?>').value;

         if(value != "") {
            var tab_fields_fields = [];
            <?php PluginFormcreatorFields::printAllTabFieldsForJS(); ?>

            eval(tab_fields_fields[value]);
         } else {
            showFields(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
         }
      }
      changeQuestionType();

      function showFields(required, default_values, values, range, show_empty, regex, show_type, dropdown_value, glpi_object, ldap_values) {
         if(required) {
            document.getElementById('dropdown_required<?php echo $rand; ?>').style.display   = 'inline';
            document.getElementById('label_required').style.display                          = 'inline';
         } else {
            document.getElementById('dropdown_required<?php echo $rand; ?>').style.display   = 'none';
            document.getElementById('label_required').style.display                          = 'none';
         }
         if(default_values) {
            document.getElementById('default_values').style.display                          = 'inline';
            document.getElementById('label_default_values').style.display                    = 'inline';
         } else {
            document.getElementById('default_values').style.display                          = 'none';
            document.getElementById('label_default_values').style.display                    = 'none';
         }
         if(show_type) {
            document.getElementById('dropdown_show_rule<?php echo $rand; ?>').style.display  = 'inline';
            document.getElementById('label_show_type').style.display                         = 'inline';
         } else {
            document.getElementById('dropdown_show_rule<?php echo $rand; ?>').style.display  = 'none';
            document.getElementById('label_show_type').style.display                         = 'none';
         }
         if(values) {
            document.getElementById('values').style.display                                  = 'inline';
            document.getElementById('label_values').style.display                            = 'inline';
         } else {
            document.getElementById('values').style.display                                  = 'none';
            document.getElementById('label_values').style.display                            = 'none';
         }
         if(dropdown_value) {
            document.getElementById('dropdown_values_field').style.display = 'inline';
            document.getElementById('label_dropdown_values').style.display                   = 'inline';
         } else {
            document.getElementById('dropdown_values_field').style.display = 'none';
            document.getElementById('label_dropdown_values').style.display                   = 'none';
         }
         if(glpi_object) {
            document.getElementById('glpi_objects_field').style.display = 'inline';
            document.getElementById('label_glpi_objects').style.display                   = 'inline';
         } else {
            document.getElementById('glpi_objects_field').style.display = 'none';
            document.getElementById('label_glpi_objects').style.display                   = 'none';
         }
         if (dropdown_value || glpi_object) {
            document.getElementById('dropdown_default_value_field').style.display = 'inline';
            document.getElementById('label_dropdown_default_value').style.display            = 'inline';
         } else {
            document.getElementById('dropdown_default_value_field').style.display = 'none';
            document.getElementById('label_dropdown_default_value').style.display            = 'none';
         }
         if(range) {
            document.getElementById('range_min').style.display                               = 'inline';
            document.getElementById('range_max').style.display                               = 'inline';
            document.getElementById('label_range_min').style.display                         = 'inline';
            document.getElementById('label_range_max').style.display                         = 'inline';
            document.getElementById('label_range').style.display                             = 'inline';
            document.getElementById('range_tr').style.display                                = 'table-row';
         } else {
            document.getElementById('range_min').style.display                               = 'none';
            document.getElementById('range_max').style.display                               = 'none';
            document.getElementById('label_range_min').style.display                         = 'none';
            document.getElementById('label_range_max').style.display                         = 'none';
            document.getElementById('label_range').style.display                             = 'none';
            document.getElementById('range_tr').style.display                                = 'none';
         }
         if(show_empty) {
            document.getElementById('show_empty').style.display = 'inline';
            document.getElementById('label_show_empty').style.display                        = 'inline';
         } else {
            document.getElementById('show_empty').style.display = 'none';
            document.getElementById('label_show_empty').style.display                        = 'none';
         }
         if(regex) {
            document.getElementById('regex').style.display                                   = 'inline';
            document.getElementById('label_regex').style.display                             = 'inline';
            document.getElementById('regex_tr').style.display                                = 'table-row';
         } else {
            document.getElementById('regex').style.display                                   = 'none';
            document.getElementById('label_regex').style.display                             = 'none';
            document.getElementById('regex_tr').style.display                                = 'none';
         }
         if(values || default_values || dropdown_value || glpi_object) {
            document.getElementById('values_tr').style.display                               = 'table-row';
         } else {
            document.getElementById('values_tr').style.display                               = 'none';
         }
         if(required || show_empty) {
            document.getElementById('required_tr').style.display                             = 'table-row';
         } else {
            document.getElementById('required_tr').style.display                             = 'none';
         }
         if(ldap_values) {
            document.getElementById('glpi_ldap_field').style.display                         = 'inline';
            document.getElementById('label_glpi_ldap').style.display                         = 'inline';
            document.getElementById('ldap_tr').style.display                                 = 'table-row';
         } else {
            document.getElementById('glpi_ldap_field').style.display                         = 'none';
            document.getElementById('label_glpi_ldap').style.display                         = 'none';
            document.getElementById('ldap_tr').style.display                                 = 'none';
         }
      }

      function toggleCondition(field) {
         if(field.value == "always") {
            document.getElementById("div_show_condition").style.display = "none";
         } else {
            document.getElementById("div_show_condition").style.display = "block";
         }
      }

      function change_dropdown() {
         dropdown_type = document.getElementById('dropdown_dropdown_values<?php echo $rand; ?>').value;

         jQuery.ajax({
            url: "<?php echo $GLOBALS['CFG_GLPI']['root_doc']; ?>/plugins/formcreator/ajax/dropdown_values.php",
            type: "GET",
            data: {
               dropdown_itemtype: dropdown_type,
               rand: "<?php echo $rand; ?>"
            },
         }).done(function(response){
            jQuery("#dropdown_default_value_field").html(response);
         });
      }

      function change_glpi_objects() {
         glpi_object = document.getElementById('dropdown_glpi_objects<?php echo $rand; ?>').value;

         jQuery.ajax({
            url: "<?php echo $GLOBALS['CFG_GLPI']['root_doc']; ?>/plugins/formcreator/ajax/dropdown_values.php",
            type: "GET",
            data: {
               dropdown_itemtype: glpi_object,
               rand: "<?php echo $rand; ?>"
            },
         }).done(function(response){
            jQuery("#dropdown_default_value_field").html(response);
         });
      }

      function change_LDAP(ldap) {
         var ldap_directory = ldap.value;

         jQuery.ajax({
           url: "<?php echo $GLOBALS['CFG_GLPI']['root_doc']; ?>/plugins/formcreator/ajax/ldap_filter.php",
           type: "POST",
           data: {
               value: ldap_directory,
               _glpi_csrf_token: "<?php Session::getNewCSRFToken(); ?>"
            },
         }).done(function(response){
            document.getElementById('ldap_filter').value = response;
         });
      }
   </script>

<?php
Html::closeForm();
