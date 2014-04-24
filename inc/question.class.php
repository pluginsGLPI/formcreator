<?php

class PluginFormcreatorQuestion extends CommonDBTM {

    const TEXT_FIELD = 1;
    const SELECT_FIELD = 2;
    const CHECKBOX_FIELD = 3;
    const TEXTAREA_FIELD = 4;
    const UPLOAD_FIELD = 5;
    const VALIDATION_FIELD = 6;
    const MULTIPLICATION_ITEM_FIELD = 7;
    const DYNAMIC_FIELD = 8;
    const DYNAMIC_SECTION = 9;
    const ITEM = 10;

    static function canCreate() {
        return Session::haveRight('config', 'w');
    }

    static function canView() {
        return Session::haveRight('config', 'r');
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        global $LANG;

        switch ($item->getType()) {
            case 'PluginFormcreatorForm':
                $question = new self;
                $question->showAddQuestion($item);
                break;
        }

        return true;
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        global $LANG;

        return $LANG['plugin_formcreator']["headings"][3];
    }

    function showAddQuestion($form) {
        global $LANG, $CFG_GLPI;

        $section = new PluginFormcreatorSection;
        $listSection = $section->find("plugin_formcreator_forms_id = '" . $form->fields['id'] . "'");

        if (!empty($listSection)) {

            echo "<div id='viewaddquestion'></div>\n";

            echo "<script type='text/javascript' >\n";
            echo "function viewAddQuestion () {\n";
            $params = array('type' => __CLASS__,
                'parenttype' => 'PluginFormcreatorForm',
                'plugin_formcreator_forms_id' => $form->fields['id'],
                'id' => -1);
            Ajax::updateItemJsCode("viewaddquestion", $CFG_GLPI["root_doc"] . "/plugins/formcreator/ajax/viewaddobject.php", $params);
            echo "};";
            echo "</script>\n";

            echo "<div class='center'>" .
            "<a href='javascript:viewAddQuestion();'>";
            echo $LANG['plugin_formcreator']["question"][1] . "</a></div><br/>\n";

            self::getListQuestion($form->fields['id']);
        } else {
            echo "<div class='center'>";
            echo $LANG['plugin_formcreator']['question'][9];
            echo "</div>";
        }
    }

    function showForm($params, $options = array()) {
        global $LANG, $CFG_GLPI;

        if ($params['id'] > 0) {
            $this->check($ID, 'r');
        } else {
            // Create item
            $this->check(-1, 'w');
        }

        echo "<script type='text/javascript'>";
        echo 'var editDiv = document.getElementById("editQuestion");';
        echo 'if(editDiv == "undefined") {';
        echo 'document.getElementById("editQuestion").innerHTML = "";';
        echo '}';
        echo "</script>";

        $paramsType = array('type' => __CLASS__,
            'value' => '__VALUE__',
            'id' => $params['plugin_formcreator_forms_id']);
        Ajax::updateItemOnSelectEvent('typeQuestion', "viewValues", $CFG_GLPI["root_doc"] .
                "/plugins/formcreator/ajax/viewformtypequestion.php", $paramsType);

        echo "<form method='POST' 
      action='" . $CFG_GLPI["root_doc"] . "/plugins/formcreator/front/question.form.php'>";

        echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='" . $params['plugin_formcreator_forms_id'] . "' />";

        echo "<div class='spaced' id='tabsbody'>";
        echo"<table class='tab_cadre_fixe fix_tab_height'>";
        echo "<tr>";
        echo "<th colspan='3'>" . $LANG['plugin_formcreator']["question"][7] . "</th>";
        echo "<th colspan='3'>&nbsp;</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>";
		echo __('Type');
		echo "&nbsp;:</td><td>";
        echo "<select name='type' id='typeQuestion'>";
        echo "<option value='-1'>----</option>";
        for ($i = 1; $i <= 10; $i++) {
            echo "<option value='" . $i . "'>
                     " . $LANG['plugin_formcreator']["type_question"][$i] . "</option>";
        }
        echo "</select>";
        echo "</td>";
        echo "<td>" . $LANG['plugin_formcreator']["question"][2] . "&nbsp;:</td>";
        echo "<td>";
        echo "<input type='text' id='name_question' name='name' value='' size='54'/>";
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>";
        echo $LANG['plugin_formcreator']["section"][3] . " :";
        echo "</td>";
        echo "<td>";
        PluginFormcreatorSection::getSelectSection($params['plugin_formcreator_forms_id']);
        echo '&nbsp;<a href="' . $CFG_GLPI["root_doc"] .
        '/plugins/formcreator/front/form.form.php?id=' .
        $params['plugin_formcreator_forms_id'] .
        '&itemtype=PluginFormcreatorForm&glpi_tab=PluginFormcreatorSection$1">
               <img style="cursor:pointer; 
               margin-left:2px;" src="../../../pics/add_dropdown.png" 
               title="' . $LANG['plugin_formcreator']["section"][0] . '" alt=""/></a>';
        echo "</td>";

        echo "<td>";
		echo __('Description');
		echo "&nbsp;:</td>";
        echo "<td>";
        echo "<textarea name='content' cols='55' rows='6'>";
        echo $this->fields["content"];
        echo "</textarea>";
        echo "</td>";
		echo '<td>';
		echo $LANG['plugin_formcreator']["bbcode"][0].'<br/>'.$LANG['plugin_formcreator']["bbcode"][1].'<br/>'.$LANG['plugin_formcreator']["bbcode"][2].'<br/>'.$LANG['plugin_formcreator']["bbcode"][3].'<br/>'.$LANG['plugin_formcreator']["bbcode"][4];
		echo '</td>';

        echo "</tr>";

        echo "<tr>";
        echo '<td>' . $LANG['plugin_formcreator']["question"][11] . ' :</td>';
        echo '<td><input type="text" name="position" value="0" size="3" /></td>';
        echo "<td colspan='2'>";

        echo "<div id='viewValues'></div>";

        echo "</td>";
        echo "</tr>";


        echo "<tr>";
        echo "<td class='center' colspan='2'>";
        echo "<input class='submit' type='submit' value='";
		echo __('Add');
		echo "' name='add'>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        echo "</div>";

        Html::closeForm();
    }

    static function getSelectQuestionDynamic($formID, $questionName = 'plugin_formcreator_questions_id', $selected = '') {

        $question = new self;
        $listQuestion = $question->find("plugin_formcreator_forms_id = '$formID' ORDER BY plugin_formcreator_sections_id,position");

        echo '<select name="' . $questionName . '">';
        echo '<option value="-1"></option>';
        foreach ($listQuestion as $question_id => $values) {
            if ($selected == $question_id) {
                echo '<option value="' . $question_id . '" selected="selected">' . $values['name'] . '</option>';
            } else {
                echo '<option value="' . $question_id . '">' . $values['name'] . '</option>';
            }
        }

        echo '</select>';
    }

    static function getSelectSectionDynamic($formID, $sectionName = 'plugin_formcreator_section_id', $selected = '') {

        $section = new PluginFormcreatorSection;
        $listSection = $section->find("plugin_formcreator_forms_id = '$formID' ORDER BY position");

		//fonction qui permet de savoir s'il s'agit d'une question ou section pour le selected
		if (preg_match("/sec_/", $selected)) {
			$select_choix = 1;
		} else {
			$select_choix = 0;
		}
		
        echo '<select name="' . $sectionName . '">';
        echo '<option value="-1"></option>';
        foreach ($listSection as $section_id => $values) {
            if ($selected == "sec_".$section_id) {
                echo '<option value="sec_' . $section_id . '" selected="selected">' . $values['name'] . '</option>';
            } else {
                echo '<option value="sec_' . $section_id . '">' . $values['name'] . '</option>';
            }
        }
		 echo '<option value="-1">------</option>';
		$question = new self;
        $listQuestion = $question->find("plugin_formcreator_forms_id = '$formID' ORDER BY plugin_formcreator_sections_id,position");
		foreach ($listQuestion as $question_id => $values) {
            if (($selected == $question_id) && ($select_choix == 0)) {
                echo '<option value="' . $question_id . '" selected="selected">' . $values['name'] . '</option>';
            } else {
                echo '<option value="' . $question_id . '">' . $values['name'] . '</option>';
            }
        }
        echo '</select>';
    }

    function prepareInputForAdd($input) {
        global $CFG_GLPI, $LANG;

        if (empty($input['name'])) {

            Session::addMessageAfterRedirect($LANG['plugin_formcreator']["error_form"][4], false, ERROR);
            return false;
        }

        return $input;
    }

    static function changeAddTypeQuestion($type, $formID) {

        switch ($type) {

            case self::TEXT_FIELD: // Text
                self::getTextField();
                break;

            case self::SELECT_FIELD: // Select
                self::getValue();
                break;

            case self::CHECKBOX_FIELD: // Checkbox
                self::getValue();
                break;

            case self::TEXTAREA_FIELD: // Textarea
                self::getTextarea();
                break;

            case self::UPLOAD_FIELD: // Upload
                self::getUpload();
                break;

            case self::VALIDATION_FIELD: // Validation
                self::getValidation();
                break;

            case self::MULTIPLICATION_ITEM_FIELD: // two fields sum
                self::getMultiplication();
                break;

            case self::DYNAMIC_FIELD: //select for dynamic question
                self::getValueDynamic($formID);
                break;

            case self::DYNAMIC_SECTION: //select for dynamic section
                self::getValueSection($formID);
                break;
				
			case self::ITEM: //select for dynamic section
                self::getItem();
                break;
        }
    }

	static function getItem() {
        global $LANG;
		$nb_item = 2;
        echo "<input type='hidden' id='nbValue' name='nbValue' value='1' />";
        echo '<table><tr><td>' . $LANG['plugin_formcreator']["item"][0] . '</td><td>';
		echo '<select name="item_liste">';
        for ($i = 1; $i <= $nb_item; $i++) {
            echo '<option value="' . $LANG['plugin_formcreator']["item_table"][$i] . '">' . $LANG['plugin_formcreator']["item"][$i] . '</option>';
        }
        echo '</select></td></tr></table>';
    }

    static function getValidation() {
        global $LANG;

        echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";

        echo "<td>";
		echo __('Default value');
		echo "<br /></td>";
        echo '<td><textarea name="value_1" cols="60" rows="6"></textarea>';
        echo '<br /><span style="font-size:10px;">
            ' . $LANG['plugin_formcreator']["question"][3] . '
            </span>';
        echo '</td>';
    }

    static function getUpload() {

        echo "<input type='hidden' id='nbValue' name='nbValue' value='0'/>";
    }

    static function getTextField() {
        global $LANG, $CFG_GLPI;

        echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";

        $paramsType = array('type' => __CLASS__,
            'value' => '__VALUE__');
        Ajax::updateItemOnSelectEvent('option', "otherType", $CFG_GLPI["root_doc"] .
                "/plugins/formcreator/ajax/viewothertypequestion.php", $paramsType);

        echo "<td>";
		echo __('Default value');
		echo "&nbsp;</td>";
        echo '<td><input type="text" name="value_1" value="" size="30"/>';
        echo '&nbsp;<span style="font-size:10px;">
            ' . $LANG['plugin_formcreator']["question"][3] . '
            </span>';
        echo "<br/><p>" . $LANG['plugin_formcreator']["question"][12] . " : ";
        echo "<select name='option' id='option'>";
        for ($i = 1; $i <= 7; $i++) {
            echo "<option value='" . $i . "'>" . $LANG['plugin_formcreator']["regex_type"][$i] . "</option>";
        }
        echo "</select>&nbsp;<span id='otherType'></span></p>";
        echo '</td>';
    }

    function getInputOtherType() {
        global $LANG;

        echo '&nbsp;&nbsp;<input type="text" name="otherOption" value="" />';
    }

    static function getTextarea() {
        global $LANG;

        echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";
		echo $LANG['plugin_formcreator']["regex_type"][2]." : <input type='checkbox' id='obli' name='obli' value='1'/><br/>";

        echo "<tr><td>";
		echo __('Default value');
		echo "<br /></td>";
        echo '<td><textarea name="value_1" cols="60" rows="6"></textarea>';
        echo '<br /><span style="font-size:10px;">
            ' . $LANG['plugin_formcreator']["question"][3] . '
            </span>';
        echo '</td>';
    }

    static function getMultiplication($valueId = 1) {

        if ($valueId == 1) {
            echo "<input type='hidden' id='nbValue' name='nbValue' value='" . $valueId . "'/>";
        } else {
            echo "<script type='text/javascript'>";
            echo "changeNbValue(" . $valueId . ");";
            echo "</script>";
        }

        self::getNextMultiplication($valueId);
    }

    static function getNextMultiplication($valueId) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId, 'nextValue' . $valueId, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewmultiplication.php', array('id' => $valueId), array('click'));

        echo "<p>";
		echo __('Type');
		echo " " . $valueId . " : ";
        echo '<input type="text" name="typeMat_' . $valueId . '" value="" size="30"/>&nbsp;';
        echo __('Value') . " " . $valueId . " : ";
        echo '<input type="text" name="value_' . $valueId . '" value="" size="5"/>&#8364;</p>';
        echo '<div id="nextValue' . $valueId . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '" 
            value="' . $LANG['plugin_formcreator']["question"][6] . '">';
        echo '</div>';
    }

    static function getValueDynamic($formID, $valueId = 1) {
		 global $LANG;
        if ($valueId == 1) {
            echo "<input type='hidden' id='nbValue' name='nbValue' value='" . $valueId . "'/>";
			echo $LANG['plugin_formcreator']["regex_type"][2]." : <input type='checkbox' id='obli' name='obli' value='1'/> ".$LANG['plugin_formcreator']["information"][3];
        } else {
            echo "<script type='text/javascript'>";
            echo "changeNbValue(" . $valueId . ");";
            echo "</script>";
        }

        self::getNextValueDynamic($formID, $valueId);
    }

    static function getNextValueDynamic($formID, $valueId, $valueIdQuestion = 1) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId, 'nextValue' . $valueId, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewdynamic.php', array('id' => $valueId, 'formID' => $formID), array('click'));

        //valeur du select
        echo "<p>" . __('Value') . " " . $valueId . " : ";
        echo '<input type="text" name="value_' . $valueId . '" value="" size="30"/></p>';

        //input caché permettant de savoir le nombre de questions relié au select
        if ($valueIdQuestion == 1) {
            echo "<input type='hidden' id='nbValue" . $valueId . "' name='nbValue" . $valueId . "' value='" . $valueIdQuestion . "'/>";
        } else {
            echo "<script type='text/javascript'>";
            echo "changeNbValueQuestion(" . $valueId . "," . $valueIdQuestion . ");";
            echo "</script>";
        }

        self::getNextValueDynamicQuestion($formID, $valueId, $valueIdQuestion);

        echo '<div id="nextValue' . $valueId . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '" 
            value="' . $LANG['plugin_formcreator']["question"][6] . '">';
        echo '</div>';
    }

    static function getNextValueDynamicQuestion($formID, $valueId, $valueIdQuestion) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId . '_' . $valueIdQuestion, 'nextValue' . $valueId . '_' . $valueIdQuestion, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewquestion.php', array('valueIdQuestion' => $valueIdQuestion, 'formID' => $formID, 'valueId' => $valueId), array('click'));

        echo "<script type='text/javascript'>";
        echo "changeNbValueQuestion(" . $valueId . "," . $valueIdQuestion . ");";
        echo "</script>";

        echo '<p align="right">';
        echo '<tr>' . $LANG['plugin_formcreator']["question"][2] . " " . $valueIdQuestion . " : </tr><tr>";
        $questionName = "question_" . $valueId . "_" . $valueIdQuestion;
        self::getSelectQuestionDynamic($formID, $questionName);
        echo '</tr>';

        echo '<div align="right" id="nextValue' . $valueId . '_' . $valueIdQuestion . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '_' . $valueIdQuestion . '" 
            value="' . $LANG['plugin_formcreator']["question"][7] . '">';
        echo '</div>';
    }

    static function getValueSection($formID, $valueId = 1) {
		 global $LANG;
        if ($valueId == 1) {
            echo "<input type='hidden' id='nbValue' name='nbValue' value='" . $valueId . "'/>";
            echo $LANG['plugin_formcreator']["regex_type"][2]." : <input type='checkbox' id='obli' name='obli' value='1'/> ".$LANG['plugin_formcreator']["information"][3];
        } else {
            echo "<script type='text/javascript'>";
            echo "changeNbValue(" . $valueId . ");";
            echo "</script>";
        }

        self::getNextValueSection($formID, $valueId);
    }

    static function getNextValueSection($formID, $valueId, $valueIdSection = 1) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId, 'nextValue' . $valueId, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewdynamicsection.php', array('id' => $valueId, 'formID' => $formID), array('click'));

        //valeur du select
        echo "<p>" . __('Value') . " " . $valueId . " : ";
        echo '<input type="text" name="value_' . $valueId . '" value="" size="30"/></p>';

        //input caché permettant de savoir le nombre de questions relié au select
        if ($valueIdSection == 1) {
            echo "<input type='hidden' id='nbValue" . $valueId . "' name='nbValue" . $valueId . "' value='" . $valueIdSection . "'/>";
        } else {
            echo "<script type='text/javascript'>";
            echo "changeNbValueQuestion(" . $valueId . "," . $valueIdSection . ");";
            echo "</script>";
        }

        self::getNextValueDynamicSection($formID, $valueId, $valueIdSection);

        echo '<div id="nextValue' . $valueId . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '" 
            value="' . $LANG['plugin_formcreator']["question"][6] . '">';
        echo '</div>';
    }

    static function getNextValueDynamicSection($formID, $valueId, $valueIdSection) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId . '_' . $valueIdSection, 'nextValue' . $valueId . '_' . $valueIdSection, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewsection.php', array('valueIdSection' => $valueIdSection, 'formID' => $formID, 'valueId' => $valueId), array('click'));

        echo "<script type='text/javascript'>";
        echo "changeNbValueQuestion(" . $valueId . "," . $valueIdSection . ");";
        echo "</script>";

        echo '<p align="right">';
        echo '<tr>' . $LANG['plugin_formcreator']["section"][3] . " " . $valueIdSection . " : </tr><tr>";
        $sectionName = "section_" . $valueId . "_" . $valueIdSection;
        self::getSelectSectionDynamic($formID, $sectionName);
        echo '</tr>';

        echo '<div align="right" id="nextValue' . $valueId . '_' . $valueIdSection . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '_' . $valueIdSection . '" 
            value="' . $LANG['plugin_formcreator']["section"][1] . '">';
        echo '</div>';
    }

    static function getValue($valueId = 1) {

        if ($valueId == 1) {
            echo "<input type='hidden' id='nbValue' name='nbValue' value='" . $valueId . "'/>";
        } else {
            echo "<script type='text/javascript'>";
            echo "changeNbValue(" . $valueId . ");";
            echo "</script>";
        }

        self::getNextValue($valueId);
    }

    static function getNextValue($valueId) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId, 'nextValue' . $valueId, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewvalue.php', array('id' => $valueId), array('click'));

        echo "<p>" . __('Value') . " " . $valueId . " : ";
        echo '<input type="text" name="value_' . $valueId . '" value="" size="30"/></p>';

        echo '<div id="nextValue' . $valueId . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '" 
            value="' . $LANG['plugin_formcreator']["question"][6] . '">';
        echo '</div>';
    }

    static function getQuestionArray($params = array()) {

        $result = array();

        if (isset($params['update'])) {
            $result['id'] = $params['id'];
        }

        $type = $params['type'];
        $question = $params['name'];
        $nbValue = $params['nbValue'];

        $question = self::protectData($question);
        $result['content'] = self::protectData($params['content']);

        $result['name'] = $question;
        $result['type'] = $type;
        $result['plugin_formcreator_forms_id'] = $params['plugin_formcreator_forms_id'];
        $result['plugin_formcreator_sections_id'] = $params['plugin_formcreator_sections_id'];

        $result['position'] = $params['position'];

        $result['data'] = array();
        $result['data']['nbValue'] = $nbValue;

        switch ($type) {

            case self::TEXT_FIELD: // Text
                $result['data']['value'] = $params['value_1'];
                break;

            case self::SELECT_FIELD: // Select
                for ($i = 1; $i <= $nbValue; $i++) {
                    $result['data']['value'][$i] = $params['value_' . $i];
                }
                break;

            case self::CHECKBOX_FIELD: // Checkbox
                for ($i = 1; $i <= $nbValue; $i++) {
                    $result['data']['value'][$i] = $params['value_' . $i];
                }
                break;

            case self::TEXTAREA_FIELD: // Textarea
                $result['data']['value'] = $params['value_1'];
				$result['data']['obli'] = $params['obli'];
                break;

            case self::UPLOAD_FIELD: // Upload
                $result['data']['value'] = '';
                break;

            case self::VALIDATION_FIELD: // Validation
                $result['data']['value'] = $params['value_1'];
                break;

            case self::MULTIPLICATION_ITEM_FIELD: // Sum
                for ($i = 1; $i <= $nbValue; $i++) {
                    $result['data']['typeMat'][$i] = $params['typeMat_' . $i];
                    $result['data']['value'][$i] = $params['value_' . $i];
                }
                break;

            case self::DYNAMIC_FIELD: // dynamic question
				$result['data']['obli'] = $params['obli'];
                for ($i = 1; $i <= $nbValue; $i++) {
                    $result['data']['value'][$i] = $params['value_' . $i];
                    $nbValueQuestion = $params['nbValue' . $i];

                    if ($params['question_' . $i . '_1'] != "-1") {
                        $result['data']['nbQuestion'][$i] = $nbValueQuestion;
                        for ($j = 1; $j <= $nbValueQuestion; $j++) {
                            $result['data']['question'][$i][$j] = $params['question_' . $i . '_' . $j];
                        }
                    }
                }
                break;

            case self::DYNAMIC_SECTION: // dynamic question
				if (isset($params['obli']))
					$result['data']['obli'] = $params['obli'];
                for ($i = 1; $i <= $nbValue; $i++) {
                    $result['data']['value'][$i] = $params['value_' . $i];
                    $nbValueSection = $params['nbValue' . $i];
					

                    if ($params['section_' . $i . '_1'] != "-1") {
                        $result['data']['nbSection'][$i] = $nbValueSection;
                        for ($j = 1; $j <= $nbValueSection; $j++) {
                            $result['data']['section'][$i][$j] = $params['section_' . $i . '_' . $j];
                        }
                    }
                }
                break;
				
			case self::ITEM: // purchase item listing
				$result['data']['value'] = $params['item_liste'];
                break;
        }

        $result['data'] = self::_serialize($result['data']);

        if (isset($params['option']) && ($type == 1)) {
            if (isset($params['otherOption'])) {
                $result['option'] = self::getOptionValue($params['option'], $params['otherOption']);
            } else {
                $result['option'] = self::getOptionValue($params['option']);
            }
        }

        return $result;
    }

    static function getOptionValue($typeID, $expression = '') {
        //àáâãäåçèéêëìíîïðòóôõöùúûüýÿ
        $tab = array();
        $tab['type'] = $typeID;
        switch ($typeID) {
            case 1: // All
                $tab['value'] = '';
                break;
            case 2: // Alphanumérique
                $tab['value'] = ".";
                break;
            case 3: // Alphabétique
                $tab['value'] = "[a-zA-Z]|\s";
                break;
            case 4: // Numérique
                $tab['value'] = "[0-9]|\d";
                break;
            case 5: // Autre
                $tab['value'] = $expression;
                break;
            case 6: //Email
                $tab['value'] = "[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}";
                break;
            case 7: //Date
                $tab['value'] = ".";
                break;
        }

        $tab['value'] = urlencode($tab['value']);

        return json_encode($tab);
    }

    static function getListQuestion($formID) {
        global $LANG, $CFG_GLPI;

        $question = new self;
        $listQuestion = $question->find("plugin_formcreator_forms_id = '$formID' ORDER BY plugin_formcreator_sections_id, position");

        if (!empty($listQuestion)) {
            echo '<div class="center">';
            echo '<table class="tab_cadrehov" border="0" >';
            echo '<th width="20">';
            echo 'ID';
            echo '</th>';
            echo '<th>';
            echo $LANG['plugin_formcreator']["question"][2];
            echo '</th>';
            echo '<th>';
            echo __('Type');
            echo '</th>';
            echo '<th>';
            echo $LANG['plugin_formcreator']["section"][3];
            echo '</th>';
            echo '<th>';
            echo $LANG['plugin_formcreator']["question"][11];
            echo '</th>';

            foreach ($listQuestion as $question_id => $values) {
                echo '<tr>';
                echo '<td class="center">';
                echo $question_id;
                echo '</td>';
                echo '<td>';
                echo '<a id="question' . $question_id . '">' . $values['name'] . '</a>';
                echo '</td>';
                echo '<td>';
                echo self::getNameType($values['type']);
                echo '</td>';
                echo '<td>';
                echo PluginFormcreatorSection::getSectionName(
                        $values['plugin_formcreator_sections_id']);
                echo '</td>';
                echo '<td class="center">';
                echo $values['position'];
                echo '</td>';
                echo '</tr>';
            }

            echo '</table>';
            echo '</div>';

            foreach ($listQuestion as $question_id => $values) {
                Ajax::updateItemOnEvent('question' . $question_id, 'editQuestion', $CFG_GLPI["root_doc"] .
                        '/plugins/formcreator/ajax/vieweditobject.php', array('id' => $question_id, 'type' => __CLASS__), array('click'));
            }

            echo '<br /><div id="editQuestion"></div>';
        }
    }

    function showFormEdit($params, $options = array()) {

        //question modification
        global $LANG, $CFG_GLPI;

        if ($params['id'] > 0) {
            $this->check($params['id'], 'r');
        } else {
            // Create item
            $this->check(-1, 'w');
        }

        echo "<script type='text/javascript'>";
        echo 'var addQuestion = document.getElementById("viewaddquestion");';
        echo 'if(addQuestion != "undefined") {';
        echo 'document.getElementById("viewaddquestion").innerHTML = "";';
        echo '}';
        echo "</script>";

        $paramsType = array('type' => __CLASS__,
            'value' => '__VALUE__',
            'id' => $this->fields['plugin_formcreator_forms_id']);
        Ajax::updateItemOnSelectEvent('typeQuestion', "viewValues", $CFG_GLPI["root_doc"] .
                "/plugins/formcreator/ajax/viewformtypequestion.php", $paramsType);

        echo "<form method='POST' 
      action='" . $CFG_GLPI["root_doc"] . "/plugins/formcreator/front/question.form.php'>";

        echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='" . $this->fields['plugin_formcreator_forms_id'] . "' />";
        echo "<input type='hidden' name='id' 
            value='" . $this->fields['id'] . "' />";

        echo "<div class='spaced' id='tabsbody'>";
        echo"<table class='tab_cadre_fixe fix_tab_height'>";
        echo "<tr>";
        echo "<th colspan='3'>" . $LANG['plugin_formcreator']["question"][8] . "</th>";
        echo "<th colspan='3'>&nbsp;</th>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Type') . "&nbsp;:</td><td>";
        echo "<select name='type' id='typeQuestion'>";
        echo "<option value='-1'>----</option>";

        for ($i = 1; $i <= 10; $i++) {

            if ($i == $this->fields['type']) {
                echo "<option value='" . $i . "' selected='selected'>
                        " . $LANG['plugin_formcreator']["type_question"][$i] . "</option>";
            } else {
                echo "<option value='" . $i . "'>
                        " . $LANG['plugin_formcreator']["type_question"][$i] . "</option>";
            }
        }

        echo "</select>";
        echo "</td>";
        echo "<td>" . $LANG['plugin_formcreator']["question"][2] . "&nbsp;:</td>";
        echo "<td>";
        echo '<input type="text" id="name_question" name="name" value="' . $this->fields['name'] . '" size="54"/>';
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>";
        echo $LANG['plugin_formcreator']["section"][3] . " :";
        echo "</td>";
        echo "<td>";

        PluginFormcreatorSection::getSelectSection($this->fields['plugin_formcreator_forms_id'], 'plugin_formcreator_sections_id', $this->fields['plugin_formcreator_sections_id']);
        echo '&nbsp;<a href="' . $CFG_GLPI["root_doc"] .
        '/plugins/formcreator/front/form.form.php?id=' .
        $this->fields['plugin_formcreator_forms_id'] .
        '&itemtype=PluginFormcreatorForm&glpi_tab=PluginFormcreatorSection$1">
              <img style="cursor:pointer; 
              margin-left:2px;" src="../../../pics/add_dropdown.png" 
              title="' . $LANG['plugin_formcreator']["section"][0] . '" alt=""/></a>';
        echo "</td>";

        echo "<td>" . __('Description') . "&nbsp;:</td>";
        echo "<td>";
        echo "<textarea name='content' cols='55' rows='6'>";
        echo $this->fields["content"];
        echo "</textarea>";
        echo "</td>";
		
		echo '<td>';
		echo $LANG['plugin_formcreator']["bbcode"][0].'<br/>'.$LANG['plugin_formcreator']["bbcode"][1].'<br/>'.$LANG['plugin_formcreator']["bbcode"][2].'<br/>'.$LANG['plugin_formcreator']["bbcode"][3].'<br/>'.$LANG['plugin_formcreator']["bbcode"][4];
		echo '</td>';
		
        echo "</tr><tr>";

        echo '<td>' . $LANG['plugin_formcreator']["question"][11] . ' :</td>';
        echo '<td><input type="text" name="position" 
                           value="' . $this->fields['position'] . '" size="3" /></td>';

        echo "<td colspan='2'>";

        $datas = self::_unserialize($this->fields['data']);

        echo "<div id='viewValues'>";
        self::getEditValue($this->fields['type'], $datas, $this->fields['option'], $this->fields['plugin_formcreator_forms_id']);
        echo "</div>";

        echo "</td>";
        echo "</tr>";


        echo "<tr>";
        echo "<td class='center' colspan='2'>";
        echo "<input class='submit' type='submit' value='" . __('Update') . "' name='update'>";
        echo "</td>";
        echo "<td class='center' colspan='2'>";
        echo "<input class='submit' type='submit' value='" . __('Purge') . "' name='delete'>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        echo "</div>";

        Html::closeForm();
    }

    static function getEditValue($type, $values = array(), $option = array(), $formID) {
        global $LANG, $CFG_GLPI;

        $nbValue = $values['nbValue'];

        switch ($type) {
            case self::TEXT_FIELD: // Text

                echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";

                $paramsType = array('type' => __CLASS__,
                    'value' => '__VALUE__');
                Ajax::updateItemOnSelectEvent('option', "otherType", $CFG_GLPI["root_doc"] .
                        "/plugins/formcreator/ajax/viewothertypequestion.php", $paramsType);

                if (isset($values['value'])) {
                    $val = $values['value'];
                } else {
                    $val = '';
                }

                $tab_option = json_decode($option, true);

                echo __('Default value') . "&nbsp;";
                echo '<input type="text" name="value_1" value="' . $val . '" size="30"/>';
                echo '&nbsp;<span style="font-size:10px;">
                  ' . $LANG['plugin_formcreator']["question"][3] . '
                  </span>';
                echo "<br/><p>" . $LANG['plugin_formcreator']["question"][12] . " : ";
                echo "<select name='option' id='option'>";

                for ($i = 1; $i <= 7; $i++) {

                    if ($i == $tab_option['type']) {
                        echo "<option value='" . $i . "' selected='selected'>" .
                        $LANG['plugin_formcreator']["regex_type"][$i] . "</option>";
                    } else {
                        echo "<option value='" . $i . "'>" .
                        $LANG['plugin_formcreator']["regex_type"][$i] . "</option>";
                    }
                }

                echo "</select>&nbsp;<span id='otherType'>";
                if ($tab_option['type'] == 5) {
                    echo '&nbsp;&nbsp;<input type="text" name="otherOption" 
                                    value="' . urldecode($tab_option['value']) . '" />';
                }
                echo "</span></p>";
                echo '</td>';

                break;

            case self::SELECT_FIELD: // Select

                echo "<input type='hidden' id='nbValue' name='nbValue' value='$nbValue'/>";

                for ($i = 1; $i <= $nbValue; $i++) {
                    echo "<p>" . __('Value') . " " . $i . " : ";
                    echo '<input type="text" 
                     name="value_' . $i . '" 
                     value="' . $values['value'][$i] . '" size="30"/></p>';
                }

                self::getNextValueEdit($nbValue);

                break;

            case self::CHECKBOX_FIELD: // Checkbox

                echo "<input type='hidden' id='nbValue' name='nbValue' value='$nbValue'/>";

                for ($i = 1; $i <= $nbValue; $i++) {
                    echo "<p>" . __('Value') . " " . $i . " : ";
                    echo '<input type="text" 
                     name="value_' . $i . '" 
                     value="' . $values['value'][$i] . '" size="30"/></p>';
                }

                self::getNextValueEdit($nbValue);

                break;

            case self::TEXTAREA_FIELD: // Textarea

                echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";
				if (isset($values['obli']) && $values['obli'] == 1)
					echo $LANG['plugin_formcreator']["regex_type"][2]." : <input type='checkbox' id='obli' name='obli' value='1' CHECKED/><br/>";
				else
					echo $LANG['plugin_formcreator']["regex_type"][2]." : <input type='checkbox' id='obli' name='obli' value='1'/><br/>";

                if (isset($values['value'])) {
                    $val = $values['value'];
                } else {
                    $val = '';
                }

                echo __('Default value') . "&nbsp;";
                echo '<textarea name="value_1" cols="60" rows="6">' . $val . '</textarea>';
                echo '<br /><span style="font-size:10px;">
                  ' . $LANG['plugin_formcreator']["question"][3] . '
                  </span>';
                break;

            case self::UPLOAD_FIELD: // Upload
                echo "<input type='hidden' id='nbValue' name='nbValue' value='0'/>";

                break;

            case self::VALIDATION_FIELD: // Validation

                echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";

                if (isset($values['value'])) {
                    $val = $values['value'];
                } else {
                    $val = '';
                }

                echo __('Default value') . "&nbsp;";
                echo '<textarea name="value_1" cols="60" rows="6">' . $val . '</textarea>';
                echo '<br /><span style="font-size:10px;">
                  ' . $LANG['plugin_formcreator']["question"][3] . '
                  </span>';
                break;

            case self::MULTIPLICATION_ITEM_FIELD: // Sum

                echo "<input type='hidden' id='nbValue' name='nbValue' value='$nbValue'/>";
				
				if (isset($values['obli']) && $values['obli'] == 1)
					echo "<input type='checkbox' id='obli' name='obli' value='1' CHECKED/>";
				else
					echo "<input type='checkbox' id='obli' name='obli' value='1'/>";

                for ($i = 1; $i <= $nbValue; $i++) {
                    echo "<p>" . __('Type') . " " . $i . " : ";
                    echo '<input type="text" name="typeMat_' . $i . '" value="' . $values['typeMat'][$i] . '" size="30"/>&nbsp;';
                    echo __('Value') . " " . $i . " : ";
                    echo '<input type="text" name="value_' . $i . '" value="' . $values['value'][$i] . '" size="5"/>&#8364;</p>';
                }

                self::getNextMultiplicationEdit($nbValue);

                break;

            case self::DYNAMIC_FIELD: // select for one dynamic question

                echo "<input type='hidden' id='nbValue' name='nbValue' value='$nbValue'/>";
				
				if (isset($values['obli']) && $values['obli'] == 1)
					echo $LANG['plugin_formcreator']["regex_type"][2]." : <input type='checkbox' id='obli' name='obli' value='1' CHECKED/> ".$LANG['plugin_formcreator']["information"][3];
				else
					echo $LANG['plugin_formcreator']["regex_type"][2]." : <input type='checkbox' id='obli' name='obli' value='1'/> ".$LANG['plugin_formcreator']["information"][3];

                for ($i = 1; $i <= $nbValue; $i++) {
                    echo "<p>" . __('Value') . " " . $i . " : ";
                    echo '<input type="text" name="value_' . $i . '" value="' . $values['value'][$i] . '" size="30"/>';

                    $nbValueQuestion = $values['nbQuestion'][$i];
                    if ($nbValueQuestion == 0)
                        $nbValueQuestion = 1;
                    echo "<input type='hidden' id='nbValue" . $i . "' name='nbValue" . $i . "' value='" . $nbValueQuestion . "'/></p><br/>";
                    for ($j = 1; $j <= $nbValueQuestion; $j++) {
                        echo '<p align="right">';
                        echo $LANG["plugin_formcreator"]["question"][2] . ' ' . $j . ' : ';
                        $questionName = "question_" . $i . "_" . $j;
                        self::getSelectQuestionDynamic($formID, $questionName, $values['question'][$i][$j]);
                    }
                    if (isset($j))
                        $j--;
                    self::getNextValueDynamicQuestionEdit($formID, $i, $j);
                }

                self::getNextValueDynamicEdit($formID, $nbValue);

                break;


            case self::DYNAMIC_SECTION: // select for dynamic section

                echo "<input type='hidden' id='nbValue' name='nbValue' value='$nbValue'/>";
				
				if (isset($values['obli']) && $values['obli'] == 1)
					echo $LANG['plugin_formcreator']["regex_type"][2]." : <input type='checkbox' id='obli' name='obli' value='1' CHECKED/> ".$LANG['plugin_formcreator']["information"][3];
				else
					echo $LANG['plugin_formcreator']["regex_type"][2]. " : <input type='checkbox' id='obli' name='obli' value='1'/> ".$LANG['plugin_formcreator']["information"][3];

                for ($i = 1; $i <= $nbValue; $i++) {
                    echo "<p>" . __('Value') . " " . $i . " : ";
                    echo '<input type="text" name="value_' . $i . '" value="' . $values['value'][$i] . '" size="30"/>';

                    $nbValueSection = $values['nbSection'][$i];
                    if ($nbValueSection == 0)
                        $nbValueSection = 1;
                    echo "<input type='hidden' id='nbValue" . $i . "' name='nbValue" . $i . "' value='" . $nbValueSection . "'/></p><br/>";
                    for ($j = 1; $j <= $nbValueSection; $j++) {
                        echo '<p align="right">';
                        echo $LANG['plugin_formcreator']["section"][3] . ' ' . $j . ' : ';
                        $sectionName = "section_" . $i . "_" . $j;
                        self::getSelectSectionDynamic($formID, $sectionName, $values['section'][$i][$j]);
                    }
                    if (isset($j))
                        $j--;
                    self::getNextValueDynamicSectionEdit($formID, $i, $j);
                }

                self::getNextValueSectionEdit($formID, $nbValue);

                break;
				
			case self::ITEM: // Liste item
                echo "<input type='hidden' id='nbValue' name='nbValue' value='".$nbValue."'/>";
                echo $LANG['plugin_formcreator']["item"][0] . '<select name="item_liste">';
                for ($i = 1; $i <= $nbValue; $i++) {
                    if ($values['value'] == $LANG['plugin_formcreator']["item_table"][$i])
                        echo '<option value="' . $LANG['plugin_formcreator']["item_table"][$i] . '" SELECTED>'.$LANG['plugin_formcreator']["item"][$i].'</option>';
                    else
						echo '<option value="' . $LANG['plugin_formcreator']["item_table"][$i] . '">'.$LANG['plugin_formcreator']["item"][$i].'</option>';
                }
				echo '</select>';
                break;
        }
    }

    static function getNextValueSectionEdit($formID, $valueId) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId, 'nextValue' . $valueId, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewdynamicsection.php', array('id' => $valueId, 'formID' => $formID), array('click'));

        echo '<div id="nextValue' . $valueId . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '" 
            value="' . $LANG['plugin_formcreator']["question"][6] . '">';
        echo '</div>';
    }

    static function getNextValueDynamicSectionEdit($formID, $valueId, $valueIdSection) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId . '_' . $valueIdSection, 'nextValue' . $valueId . '_' . $valueIdSection, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewsection.php', array('valueIdSection' => $valueIdSection, 'formID' => $formID, 'valueId' => $valueId), array('click'));

        echo '<div align="right" id="nextValue' . $valueId . '_' . $valueIdSection . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '_' . $valueIdSection . '" 
            value="' . $LANG['plugin_formcreator']["section"][1] . '">';
        echo '</div>';
    }

    static function getNextValueDynamicEdit($formID, $valueId) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId, 'nextValue' . $valueId, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewdynamic.php', array('id' => $valueId, 'formID' => $formID), array('click'));

        echo '<div id="nextValue' . $valueId . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '" 
            value="' . $LANG['plugin_formcreator']["question"][6] . '">';
        echo '</div>';
    }

    static function getNextValueDynamicQuestionEdit($formID, $valueId, $valueIdQuestion) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId . '_' . $valueIdQuestion, 'nextValue' . $valueId . '_' . $valueIdQuestion, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewquestion.php', array('valueIdQuestion' => $valueIdQuestion, 'formID' => $formID, 'valueId' => $valueId), array('click'));

        echo '<div align="right" id="nextValue' . $valueId . '_' . $valueIdQuestion . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '_' . $valueIdQuestion . '" 
            value="' . $LANG['plugin_formcreator']["question"][7] . '">';
        echo '</div>';
    }

    static function getNextMultiplicationEdit($valueId) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId, 'nextValue' . $valueId, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewmultiplication.php', array('id' => $valueId), array('click'));

        echo '<div id="nextValue' . $valueId . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '" value="' . $LANG['plugin_formcreator']["question"][6] . '">';
        echo '</div>';
    }

    static function getNextValueEdit($valueId) {
        global $LANG, $CFG_GLPI;

        Ajax::updateItemOnEvent('addField' . $valueId, 'nextValue' . $valueId, $CFG_GLPI["root_doc"] .
                '/plugins/formcreator/ajax/addnewvalue.php', array('id' => $valueId), array('click'));

        echo '<div id="nextValue' . $valueId . '">';
        echo '<input class="submit" type="button" id="addField' . $valueId . '" 
            value="' . $LANG['plugin_formcreator']["question"][6] . '">';
        echo '</div>';
    }

    static function _serialize($input) {

        if ($input['nbValue'] > 1) {
            foreach ($input['value'] as $key => &$value) {
                $value = urlencode($value);
            }
        } else {
            $input['value'] = urlencode($input['value']);
        }

        $output = json_encode($input);
        return $output;
    }

    static function _unserialize($input) {

        $output = json_decode($input, true);

        if ($output['nbValue'] > 1) {
            foreach ($output['value'] as $key => &$value) {
                $value = urldecode($value);
            }
        } else {
            $output['value'] = urldecode($output['value']);
        }

        return $output;
    }

    static function getNameType($type) {
        global $LANG;

        switch ($type) {
            case self::TEXT_FIELD: // Text
                return $LANG['plugin_formcreator']["type_question"][1];
                break;

            case self::SELECT_FIELD: // Select
                return $LANG['plugin_formcreator']["type_question"][2];
                break;

            case self::CHECKBOX_FIELD: // Checkbox
                return $LANG['plugin_formcreator']["type_question"][3];
                break;

            case self::TEXTAREA_FIELD: // Textarea
                return $LANG['plugin_formcreator']["type_question"][4];
                break;

            case self::UPLOAD_FIELD: // Upload
                return $LANG['plugin_formcreator']["type_question"][5];
                break;

            case self::VALIDATION_FIELD: // Validation
                return $LANG['plugin_formcreator']["type_question"][6];
                break;

            case self::MULTIPLICATION_ITEM_FIELD: // calcul between box
                return $LANG['plugin_formcreator']["type_question"][7];
                break;

            case self::DYNAMIC_FIELD: // dynamic question
                return $LANG['plugin_formcreator']["type_question"][8];
                break;

            case self::DYNAMIC_SECTION: // dynamic question
                return $LANG['plugin_formcreator']["type_question"][9];
                break;
				
			case self::ITEM: // item listing
                return $LANG['plugin_formcreator']["type_question"][10];
                break;
        }
    }

    static function protectData($data) {

        if (Toolbox::get_magic_quotes_gpc()) {
            $data = Toolbox::stripslashes_deep($data);
        }

        $data = Toolbox::addslashes_deep($data);
        $data = Toolbox::clean_cross_side_scripting_deep($data);

        return $data;
    }

}

?>