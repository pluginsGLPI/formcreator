<?php
include ('../../../inc/includes.php');
Session::checkRight("config", "w");

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
         <td width="17%">
            <label for="name" id="label_name">
               <?php echo  __('Title', 'formcreator'); ?>&nbsp;
               <span style="color:red;">*</span>
            </label>
         </td>
         <td width="33%">
            <input type="text" name="name" id="name" style="width:90%;" value="<?php echo $question->fields['name']; ?>" class="required">
         </td>
         <td width="17%">
            <label for="fieldtype" id="label_fieldtype">
               <?php echo __('Type', 'formcreator'); ?>&nbsp;
               <span style="color:red;">*</span>
            </label>
         </td>
         <td width="33%">
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
         <td width="17%">
            <label for="section" id="label_name">
               <?php echo  __('Section', 'formcreator'); ?>&nbsp;
               <span style="color:red;">*</span>
            </label>
         </td>
         <td width="33%">
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
               'value' => $question->fields['plugin_formcreator_sections_id']
            ));
            ?>
         </td>
         <td width="17%">
            <label for="show_type" id="label_show_type">
               <?php echo __('Show field', 'formcreator'); ?>
            </label>
         </td>
         <td width="33%">
            <?php
            Dropdown::showFromArray('show_type', array(
               'show'        => __('Always', 'formcreator'),
               'hide'        => __('Only if field', 'formcreator'),
            ), array(
               'value'       => $question->fields['show_type'],
               'on_change'   => 'toggleCondition(this);',
               'rand'        => $rand,
            ));
            ?>

            <div id="div_show_condition"<?php if($question->fields['show_type'] != 'hide') echo ' style="display:none"'; ?>>
               <?php
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
               Dropdown::showFromArray('show_field', $questions_tab, array(
                  'value' => $question->fields['show_field']
               ));

               Dropdown::showFromArray('show_condition', array(
                  'equal'    => '=',
                  'notequal' => '&lt;&gt;',
                  'lower'    => '&lt;',
                  'greater'  => '&gt;',
               ), array(
                  'value'    => $question->fields['show_condition'],
                  'rand'     => $rand,
               ));
               ?>

               <input type="text" name="show_value" id="show_value" value="<?php echo $question->fields['show_value']; ?>" class="small_text" size="8">
            </div>
         </td>
      </tr>

      <tr class="line0" id="required_tr">
         <td width="17%">
            <label for="required" id="label_required">
               <?php echo __('Required', 'formcreator'); ?>
            </label>
         </td>
         <td width="33%">
            <?php
            dropdown::showYesNo('required', $question->fields['required'], -1, array(
               'rand'  => $rand,
            ));
            ?>
         </td>
         <td width="17%">
            <label for="show_empty" id="label_show_empty">
               <?php echo __('Show empty', 'formcreator'); ?>
            </label>
         </td>
         <td width="33%">
            <?php
            dropdown::showYesNo('show_empty', $question->fields['show_empty'], -1, array(
               'rand'  => $rand,
            ));
            ?>
         </td>
      </tr>

      <tr class="line1" id="values_tr">
         <td width="17%">
            <label for="default_values" id="label_default_values">
               <?php echo __('Default value(s)', 'formcreator'); ?><br />
               <small>(<?php echo __('One per line for lists', 'formcreator'); ?>)</small>
            </label>
            <label for="dropdown_default_value" id="label_dropdown_default_value">
               <?php echo __('Default value', 'formcreator'); ?>
            </label>
         </td>
         <td width="33%">
            <textarea name="default_values" id="default_values" rows="4" cols="40" style="width: 90%"><?php echo $question->fields['default_values']; ?></textarea>
            <div id="dropdown_default_value_field">
               <?php
               if(($question->fields['fieldtype'] == 'dropdown') && !empty($question->fields['values'])
                   && class_exists($question->fields['values'])) {
                  Dropdown::show($question->fields['values'], array(
                     'name'  => 'dropdown_default_value',
                     'value' => $question->fields['default_values'],
                     'rand'  => $rand,
                  ));
               } else {
                  echo '<select name="dropdown_default_value" id="dropdown_dropdown_default_value' . $rand . '">
                           <option value="">---</option>
                        </select>';
               }
               ?>

            </div>
         </td>
         <td width="17%">
            <label for="values" id="label_values">
               <?php echo __('Values', 'formcreator'); ?><br />
               <small>(<?php echo __('One per line', 'formcreator'); ?>)</small>
            </label>
            <label for="dropdown_values" id="label_dropdown_values">
               <?php echo __('Dropdown', 'formcreator'); ?>
            </label>
         </td>
         <td width="33%">
            <textarea name="values" id="values" rows="4" cols="40" style="width: 90%"><?php echo $question->fields['values']; ?></textarea>
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
         </td>
      </tr>

      <tr class="line0" id="range_tr">
         <td width="17%">
            <span id="label_range"><?php echo __('Range', 'formcreator'); ?></span>
         </td>
         <td width="33%">
            <label for="range_min" id="label_range_min">
               <?php echo __('Min', 'formcreator'); ?>
            </label>
            <input type="text" name="range_min" id="range_min" class="small_text" value="<?php echo $question->fields['range_min']; ?>" />
            &nbsp;
            <label for="range_max" id="label_range_max">
               <?php echo __('Max', 'formcreator'); ?>
            </label>
            <input type="text" name="range_max" id="range_max" class="small_text" value="<?php echo $question->fields['range_max']; ?>" />
         </td>
         <td colspan="2">&nbsp;</td>
      </tr>

      <tr class="line1" id="description_tr">
         <td width="17%">
            <label for="description" id="label_description">
               <?php echo __('Description', 'formcreator'); ?>
            </label>
         </td>
         <td width="80%" colspan="3">
            <textarea name="description" id="description" rows="6" cols="108" style="width: 97%"><?php echo $question->fields['description']; ?></textarea>
            <?php Html::initEditorSystem('description'); ?>
         </td>
      </tr>

      <tr class="line0" id="regex_tr">
         <td width="17%">
            <label for="regex" id="label_regex">
               <?php echo __('Additional validation', 'formcreator'); ?><br />
               <small>
                  <a href="http://php.net/manual/reference.pcre.pattern.syntax.php" target="_blank">
                     (<?php echo __('Regular expression', 'formcreator'); ?>)</a>
               </small>
            </label>
         </td>
         <td width="80%" colspan="3">
            <input type="text" name="regex" id="regex" style="width:98%;" value="<?php echo $question->fields['regex']; ?>" />
         </td>
      </tr>

      <tr class="line1">
         <td colspan="4" class="center">
            <input type="hidden" name="id" value="<?php echo $question_id; ?>" />
            <input type="reset" name="reset" class="submit_button" onclick="resetAll()"
                   value="<?php echo __('Cancel', 'formcreator'); ?>" /> &nbsp;
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
            showFields(0, 0, 0, 0, 0, 0, 0, 0);
         }
      }
      changeQuestionType();

      function showFields(required, default_values, values, range, show_empty, regex, show_type, dropdown_value) {
         console.log('test');
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
            document.getElementById('dropdown_show_type<?php echo $rand; ?>').style.display  = 'inline';
            document.getElementById('label_show_type').style.display                         = 'inline';
         } else {
            document.getElementById('dropdown_show_type<?php echo $rand; ?>').style.display  = 'none';
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
            document.getElementById('dropdown_dropdown_values<?php echo $rand; ?>').style.display = 'inline';
            document.getElementById('label_dropdown_values').style.display                   = 'inline';
            document.getElementById('dropdown_dropdown_default_value<?php echo $rand; ?>').style.display = 'inline';
            document.getElementById('label_dropdown_default_value').style.display                   = 'inline';
         } else {
            document.getElementById('dropdown_dropdown_values<?php echo $rand; ?>').style.display = 'none';
            document.getElementById('label_dropdown_values').style.display                   = 'none';
            document.getElementById('dropdown_dropdown_default_value<?php echo $rand; ?>').style.display = 'none';
            document.getElementById('label_dropdown_default_value').style.display                   = 'none';
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
            document.getElementById('dropdown_show_empty<?php echo $rand; ?>').style.display = 'inline';
            document.getElementById('label_show_empty').style.display                        = 'inline';
         } else {
            document.getElementById('dropdown_show_empty<?php echo $rand; ?>').style.display = 'none';
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
         if(values || default_values || dropdown_value) {
            document.getElementById('values_tr').style.display                               = 'table-row';
         } else {
            document.getElementById('values_tr').style.display                               = 'none';
         }
         if(required || show_empty) {
            document.getElementById('required_tr').style.display                             = 'table-row';
         } else {
            document.getElementById('required_tr').style.display                             = 'none';
         }
      }

      function toggleCondition(field) {
         if(field.value == "show") {
            document.getElementById("div_show_condition").style.display = "none";
         } else {
            document.getElementById("div_show_condition").style.display = "block";
         }
      }

      function change_dropdown() {
         dropdown_itemtype = document.getElementById('dropdown_dropdown_values<?php echo $rand; ?>').value;

         Ext.get("dropdown_default_value_field").load({
            url: "<?php echo $GLOBALS['CFG_GLPI']['root_doc']; ?>/plugins/formcreator/ajax/dropdown_values.php",
            scripts: true,
            params: "dropdown_itemtype=" + dropdown_itemtype + "&rand=<?php echo $rand; ?>"
         });

      }
   </script>

<?php
Html::closeForm();
