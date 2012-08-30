<?php

class PluginFormcreatorQuestion extends CommonDBTM {
   
   const TEXT_FIELD = 1;
   const SELECT_FIELD = 2;
   const CHECKBOX_FIELD = 3;
   const TEXTAREA_FIELD = 4;
   const UPLOAD_FIELD = 5;
   const VALIDATION_FIELD = 6;
   const MULTIPLICATION_ITEM_FIELD = 7;
   
   function canCreate() {
      return Session::haveRight('config', 'w');
   }

   function canView() {
      return Session::haveRight('config', 'r');
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $LANG;
         
         switch($item->getType()) {
            case 'PluginFormcreatorForm': 
               $question = new self;
               $question->showAddQuestion($item);
            break;
         }
         
      return true;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      return $LANG['plugin_formcreator']["headings"][3];
   }      
   
   function showAddQuestion($form) {
      global $LANG, $CFG_GLPI;
      
      $section = new PluginFormcreatorSection;
      $listSection = $section->find("plugin_formcreator_forms_id = '".$form->fields['id']."'");

      if(!empty($listSection)) {
         
         echo "<div id='viewaddquestion'></div>\n";
      
         echo "<script type='text/javascript' >\n";
         echo "function viewAddQuestion () {\n";
         $params = array('type'       => __CLASS__,
                         'parenttype' => 'PluginFormcreatorForm',
                         'plugin_formcreator_forms_id'    => $form->fields['id'],
                         'id'         => -1);
         Ajax::updateItemJsCode("viewaddquestion",
                                $CFG_GLPI["root_doc"]."/plugins/formcreator/ajax/viewaddobject.php", 
                                $params);
         echo "};";
         echo "</script>\n";

         echo "<div class='center'>".
              "<a href='javascript:viewAddQuestion();'>";
         echo $LANG['plugin_formcreator']["question"][1]."</a></div><br/>\n";
      
         self::getListQuestion($form->fields['id']);
         
      } else {
         echo "<div class='center'>";
            echo $LANG['plugin_formcreator']['question'][9];
         echo "</div>";
      }
      
   }   
   
   function showForm($params,$options=array()) {
      global $LANG, $CFG_GLPI;
      
      if ($params['id'] > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }
      
      echo "<script type='text/javascript'>";
      echo 'var editDiv = document.getElementById("editQuestion");';
      echo 'if(editDiv == "undefined") {';
      echo 'document.getElementById("editQuestion").innerHTML = "";';
      echo '}';
      echo "</script>";
      
      $paramsType = array('type'       => __CLASS__,
                      'value'      => '__VALUE__');
      Ajax::updateItemOnSelectEvent('typeQuestion',
                              "viewValues",
                             $CFG_GLPI["root_doc"].
                             "/plugins/formcreator/ajax/viewformtypequestion.php", 
                             $paramsType);
       
      echo "<form method='POST' 
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/question.form.php'>";
      
      echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='".$params['plugin_formcreator_forms_id']."' />";
      
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["question"][7]."</th>";
            echo "<th colspan='2'>&nbsp;</th>";
         echo "</tr>";
         
         echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['common'][17]."&nbsp;:</td><td>";
         echo "<select name='type' id='typeQuestion'>";
            echo "<option value='-1'>----</option>";
            for($i = 1; $i <= 7; $i++) {   
               echo "<option value='".$i."'>
                     ".$LANG['plugin_formcreator']["type_question"][$i]."</option>";
            }
         echo "</select>";
         echo "</td>";
         echo "<td>".$LANG['plugin_formcreator']["question"][2]."&nbsp;:</td>";
         echo "<td>";
         echo "<input type='text' name='name' value='' size='54'/>";
         echo "</td>";
         echo "</tr>";
         
         echo "<tr>";
         echo "<td>";
            echo $LANG['plugin_formcreator']["section"][3]." :";
         echo "</td>";
         echo "<td>";
         PluginFormcreatorSection::getSelectSection($params['plugin_formcreator_forms_id']);
         echo '&nbsp;<a href="'.$CFG_GLPI["root_doc"].
               '/plugins/formcreator/front/form.form.php?id='.
               $params['plugin_formcreator_forms_id'].
               '&itemtype=PluginFormcreatorForm&glpi_tab=PluginFormcreatorSection$1">
               <img style="cursor:pointer; 
               margin-left:2px;" src="/glpi/dev/pics/add_dropdown.png" 
               title="'.$LANG['plugin_formcreator']["section"][0].'" alt=""/></a>';
         echo "</td>";
         
         echo "<td>".$LANG['joblist'][6]."&nbsp;:</td>";
         echo "<td>";
         echo "<textarea name='content' cols='55' rows='6'>";
         echo $this->fields["content"];
         echo "</textarea>";
         echo "</td>";
         
         echo "</tr>";
         
         echo "<tr>";
         echo '<td>'.$LANG['plugin_formcreator']["question"][11].' :</td>';
         echo '<td><input type="text" name="position" value="0" size="3" /></td>';
         echo "<td colspan='2'>";
            
            echo "<div id='viewValues'></div>";
         
         echo "</td>";
         echo "</tr>";

         
         echo "<tr>";
         echo "<td class='center' colspan='2'>";
            echo "<input class='submit' type='submit' value='".$LANG['buttons'][8]."' name='add'>";
         echo "</td>";
         echo "</tr>";
         
      echo "</table>";
      echo "</div>";
      
      echo "</form>";
   }
   
   function prepareInputForAdd($input) {
      global $CFG_GLPI, $LANG;
      
      if (empty($input['name'])) {
         
         Session::addMessageAfterRedirect($LANG['plugin_formcreator']["error_form"][4], false, ERROR);
         return false;
      }
      
      return $input;
   }
   
   
   static function changeAddTypeQuestion($type) {
      
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
      }
      
   }
   
   static function getValidation() {
      global $LANG;
      
      echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>"; 
      
      echo "<td>".$LANG['setup'][46]."<br /></td>";
      echo '<td><textarea name="value_1" cols="60" rows="6"></textarea>';
      echo '<br /><span style="font-size:10px;">
            '.$LANG['plugin_formcreator']["question"][3].'
            </span>';
      echo '</td>';
   }
   
   static function getUpload() {
      
      echo "<input type='hidden' id='nbValue' name='nbValue' value='0'/>";
      
   }
   
   static function getTextField() {
      global $LANG, $CFG_GLPI;
      
      echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";   
      
      $paramsType = array('type'       => __CLASS__,
                      'value'      => '__VALUE__');
      Ajax::updateItemOnSelectEvent('option',
                              "otherType",
                             $CFG_GLPI["root_doc"].
                             "/plugins/formcreator/ajax/viewothertypequestion.php", 
                             $paramsType);
                             
      echo "<td>".$LANG['setup'][46]."&nbsp;</td>";
      echo '<td><input type="text" name="value_1" value="" size="30"/>';
      echo '&nbsp;<span style="font-size:10px;">
            '.$LANG['plugin_formcreator']["question"][3].'
            </span>';
      echo "<br/><p>".$LANG['plugin_formcreator']["question"][12]." : ";
      echo "<select name='option' id='option'>";
      for($i=1;$i<=6;$i++) { 
         echo "<option value='".$i."'>".$LANG['plugin_formcreator']["regex_type"][$i]."</option>";
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

      echo "<td>".$LANG['setup'][46]."<br /></td>";
      echo '<td><textarea name="value_1" cols="60" rows="6"></textarea>';
      echo '<br /><span style="font-size:10px;">
            '.$LANG['plugin_formcreator']["question"][3].'
            </span>';
      echo '</td>';
   }
   
   static function getMultiplication($valueId=1) {
      
      if($valueId == 1) {
         echo "<input type='hidden' id='nbValue' name='nbValue' value='".$valueId."'/>";
      } else {
         echo "<script type='text/javascript'>";
         echo "changeNbValue(".$valueId.");";
         echo "</script>";
      }  
         
      self::getNextMultiplication($valueId);
   }
   
   static function getNextMultiplication($valueId) {
      global $LANG, $CFG_GLPI;
      
      Ajax::updateItemOnEvent('addField'.$valueId,
                              'nextValue'.$valueId,
                              $CFG_GLPI["root_doc"].
                              '/plugins/formcreator/ajax/addnewmultiplication.php',
                              array('id' => $valueId),
                              array('click'));
                              
      echo "<p>".$LANG['common'][17]." ".$valueId." : ";
      echo '<input type="text" name="typeMat_'.$valueId.'" value="" size="30"/>&nbsp;';
      echo $LANG['financial'][21]." ".$valueId." : ";
      echo '<input type="text" name="value_'.$valueId.'" value="" size="5"/>&#8364;</p>';
      echo '<div id="nextValue'.$valueId.'">';
      echo '<input class="submit" type="button" id="addField'.$valueId.'" 
            value="'.$LANG['plugin_formcreator']["question"][6].'">';
      echo '</div>';
      
   }
      
   static function getValue($valueId=1) {
      
      if($valueId == 1) {
         echo "<input type='hidden' id='nbValue' name='nbValue' value='".$valueId."'/>";
      } else {
         echo "<script type='text/javascript'>";
         echo "changeNbValue(".$valueId.");";
         echo "</script>";
      }  
         
      self::getNextValue($valueId);
      
   }
   
   static function getNextValue($valueId) {
      global $LANG, $CFG_GLPI;
      
      Ajax::updateItemOnEvent('addField'.$valueId,
                              'nextValue'.$valueId,
                              $CFG_GLPI["root_doc"].
                              '/plugins/formcreator/ajax/addnewvalue.php',
                              array('id' => $valueId),
                              array('click'));
                              
      echo "<p>".$LANG['financial'][21]." ".$valueId." : ";
      echo '<input type="text" name="value_'.$valueId.'" value="" size="30"/></p>';
            
      echo '<div id="nextValue'.$valueId.'">';
      echo '<input class="submit" type="button" id="addField'.$valueId.'" 
            value="'.$LANG['plugin_formcreator']["question"][6].'">';
      echo '</div>';
      
   }
   
   static function getQuestionArray($params=array()) {

      $result = array();
            
      if(isset($params['update'])) {
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
      
      switch($type) {
         
         case self::TEXT_FIELD: // Text
            $result['data']['value'] = $params['value_1'];
            break;
         
         case self::SELECT_FIELD: // Select
            for($i = 1; $i <= $nbValue; $i++) {
               $result['data']['value'][$i] = $params['value_'.$i];
            }
            
            break;
            
         case self::CHECKBOX_FIELD: // Checkbox
            for($i = 1; $i <= $nbValue; $i++) {
               $result['data']['value'][$i] = $params['value_'.$i];
            }

            break;  
                      
         case self::TEXTAREA_FIELD: // Textarea
            $result['data']['value'] = $params['value_1'];
            break;   
            
         case self::UPLOAD_FIELD: // Upload
            $result['data']['value'] = '';
            break;
            
         case self::VALIDATION_FIELD: // Validation
            $result['data']['value'] = $params['value_1'];
            break;
        
        case self::MULTIPLICATION_ITEM_FIELD: // Sum
            for($i = 1; $i <= $nbValue; $i++) {
               $result['data']['typeMat'][$i] = $params['typeMat_'.$i];
               $result['data']['value'][$i] = $params['value_'.$i];
            }
            
            break;
      }
      
      $result['data'] = self::_serialize($result['data']);
      
      if(isset($params['option']) && $type == 1) {
         if(isset($params['otherOption'])) {
            $result['option'] = self::getOptionValue($params['option'],$params['otherOption']);
         } else {
            $result['option'] = self::getOptionValue($params['option']);
         }
      }
      
      return $result;      
   }
   
   static function getOptionValue($typeID,$expression='') {
      //àáâãäåçèéêëìíîïðòóôõöùúûüýÿ
      $tab = array();
      $tab['type'] = $typeID;
      switch($typeID) {
         case 1: // All
            $tab['value'] = '';
            break;
         case 2: // Alphanumérique
            $tab['value'] = "#^[0-9a-zA-Z -]*$#";
            break;
         case 3: // Alphabétique
            $tab['value'] = "#^[a-zA-Z -]*$#";
            break;
         case 4: // Numérique
            $tab['value'] = "#^[0-9]*$#";
            break;
         case 5: // Autre
            $tab['value'] = $expression;
            break;
         case 6: //Email
            $tab['value'] = "#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#";
            break;
      }
      
      $tab['value'] = urlencode($tab['value']);
      
      return json_encode($tab);      
      
   }
   
   static function getListQuestion($formID) {
      global $LANG, $CFG_GLPI;
      
      $question = new self;
      $listQuestion = $question->find("plugin_formcreator_forms_id = '$formID' ORDER BY plugin_formcreator_sections_id, position");
      
      if(!empty($listQuestion)) {
         echo '<div class="center">';
         echo '<table class="tab_cadrehov" border="0" >';
            echo '<th width="20">';
               echo 'ID';
            echo '</th>';
            echo '<th>';
               echo $LANG['plugin_formcreator']["question"][2];
            echo '</th>';
            echo '<th>';
               echo $LANG['common'][17];
            echo '</th>';
            echo '<th>';
               echo $LANG['plugin_formcreator']["section"][3];
            echo '</th>';
            echo '<th>';
               echo $LANG['plugin_formcreator']["question"][11];
            echo '</th>';
                     
         foreach($listQuestion as $question_id => $values) {
            echo '<tr>';
               echo '<td class="center">';
                  echo $question_id;
               echo '</td>';
               echo '<td>';
                  echo '<a id="question'.$question_id.'">'.$values['name'].'</a>';
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
         
         foreach($listQuestion as $question_id => $values) {
            Ajax::updateItemOnEvent('question'.$question_id,
                                    'editQuestion',
                                    $CFG_GLPI["root_doc"].
                                    '/plugins/formcreator/ajax/vieweditobject.php',
                                    array('id' => $question_id, 'type' => __CLASS__),
                                    array('click'));
         }
         
         echo '<br /><div id="editQuestion"></div>';

      }
   }

   function showFormEdit($params,$options=array()) {
        
      //question modification
      global $LANG, $CFG_GLPI;
      
      if ($params['id'] > 0) {
         $this->check($params['id'],'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }
      
      echo "<script type='text/javascript'>";
      echo 'var addQuestion = document.getElementById("viewaddquestion");';
      echo 'if(addQuestion != "undefined") {';
      echo 'document.getElementById("viewaddquestion").innerHTML = "";';
      echo '}';
      echo "</script>";
      
      $paramsType = array('type'       => __CLASS__,
                      'value'      => '__VALUE__');
      Ajax::updateItemOnSelectEvent('typeQuestion',
                              "viewValues",
                             $CFG_GLPI["root_doc"].
                             "/plugins/formcreator/ajax/viewformtypequestion.php", 
                             $paramsType);
       
      echo "<form method='POST' 
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/question.form.php'>";
      
      echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='".$this->fields['plugin_formcreator_forms_id']."' />";
      echo "<input type='hidden' name='id' 
            value='".$this->fields['id']."' />";
               
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["question"][8]."</th>";
            echo "<th colspan='2'>&nbsp;</th>";
         echo "</tr>";
         
      
         echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['common'][17]."&nbsp;:</td><td>";
         echo "<select name='type' id='typeQuestion'>";
            echo "<option value='-1'>----</option>";
            
            for($i = 1; $i <= 7; $i++) {
               
               if($i == $this->fields['type']) { 
                  echo "<option value='".$i."' selected='selected'>
                        ".$LANG['plugin_formcreator']["type_question"][$i]."</option>";
               } else {
                  echo "<option value='".$i."'>
                        ".$LANG['plugin_formcreator']["type_question"][$i]."</option>";                  
               }
            }
            
         echo "</select>";
         echo "</td>";
         echo "<td>".$LANG['plugin_formcreator']["question"][2]."&nbsp;:</td>";
         echo "<td>";
         echo '<input type="text" name="name" value="'.$this->fields['name'].'" size="54"/>';
         echo "</td>";
         echo "</tr>";
         
         echo "<tr>";
         echo "<td>";
            echo $LANG['plugin_formcreator']["section"][3]." :";
         echo "</td>";
         echo "<td>";
         
         PluginFormcreatorSection::getSelectSection($this->fields['plugin_formcreator_forms_id'],
                                                  'plugin_formcreator_sections_id',
                                                  $this->fields['plugin_formcreator_sections_id']);
        echo '&nbsp;<a href="'.$CFG_GLPI["root_doc"].
              '/plugins/formcreator/front/form.form.php?id='.
              $this->fields['plugin_formcreator_forms_id'].
              '&itemtype=PluginFormcreatorForm&glpi_tab=PluginFormcreatorSection$1">
              <img style="cursor:pointer; 
              margin-left:2px;" src="/glpi/dev/pics/add_dropdown.png" 
              title="'.$LANG['plugin_formcreator']["section"][0].'" alt=""/></a>';
         echo "</td>";
         
         echo "<td>".$LANG['joblist'][6]."&nbsp;:</td>";
         echo "<td>";
         echo "<textarea name='content' cols='55' rows='6'>";
         echo $this->fields["content"];
         echo "</textarea>";
         echo "</td>";
         
         echo "</tr><tr>";
         
         echo '<td>'.$LANG['plugin_formcreator']["question"][11].' :</td>';
         echo '<td><input type="text" name="position" 
                           value="'.$this->fields['position'].'" size="3" /></td>';
         
         echo "<td colspan='2'>";

            $datas = self::_unserialize($this->fields['data']);
            
            echo "<div id='viewValues'>";
            self::getEditValue($this->fields['type'],$datas,$this->fields['option']);
            echo "</div>";
            
         echo "</td>";
         echo "</tr>";

         
         echo "<tr>";
         echo "<td class='center' colspan='2'>";
            echo "<input class='submit' type='submit' value='".$LANG['buttons'][7]."' name='update'>";
         echo "</td>";
         echo "<td class='center' colspan='2'>";
            echo "<input class='submit' type='submit' value='".$LANG['buttons'][22]."' name='delete'>";
         echo "</td>";
         echo "</tr>";
         
      echo "</table>";
      echo "</div>";
      
      echo "</form>";
   }
   
   static function getEditValue($type, $values=array(),$option=array()) {
      global $LANG, $CFG_GLPI;

      $nbValue = $values['nbValue'];
      
      switch($type) {
         case self::TEXT_FIELD: // Text
            
            echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";
            
            $paramsType = array('type'       => __CLASS__,
                            'value'      => '__VALUE__');
            Ajax::updateItemOnSelectEvent('option',
                                    "otherType",
                                   $CFG_GLPI["root_doc"].
                                   "/plugins/formcreator/ajax/viewothertypequestion.php", 
                                   $paramsType);
                                   
            if(isset($values['value'])) {
               $val = $values['value'];
            } else { $val = ''; }
            
            $tab_option = json_decode($option,true);

            echo $LANG['setup'][46]."&nbsp;";
            echo '<input type="text" name="value_1" value="'.$val.'" size="30"/>';
            echo '&nbsp;<span style="font-size:10px;">
                  '.$LANG['plugin_formcreator']["question"][3].'
                  </span>';
            echo "<br/><p>".$LANG['plugin_formcreator']["question"][12]." : ";
            echo "<select name='option' id='option'>";
            
            for($i=1;$i<=6;$i++) { 
               
               if($i == $tab_option['type']) {
                  echo "<option value='".$i."' selected='selected'>".
                           $LANG['plugin_formcreator']["regex_type"][$i]."</option>";
               } else {
                  echo "<option value='".$i."'>".
                           $LANG['plugin_formcreator']["regex_type"][$i]."</option>";                  
               }
            }
            
            echo "</select>&nbsp;<span id='otherType'>";
            if($tab_option['type'] == 5) {
               echo '&nbsp;&nbsp;<input type="text" name="otherOption" 
                                    value="'.urldecode($tab_option['value']).'" />';
			   echo '&nbsp;&nbsp;'.$LANG['plugin_formcreator']["information"][0];
            }
            echo "</span></p>";
            echo '</td>';
            
            break;
                     
         case self::SELECT_FIELD: // Select
         
            echo "<input type='hidden' id='nbValue' name='nbValue' value='$nbValue'/>";  
                      
            for($i = 1; $i <= $nbValue; $i++) {
               echo "<p>".$LANG['financial'][21]." ".$i." : ";
               echo '<input type="text" 
                     name="value_'.$i.'" 
                     value="'.$values['value'][$i].'" size="30"/></p>';
            }

            self::getNextValueEdit($nbValue);
            
            break;
            
         case self::CHECKBOX_FIELD: // Checkbox
            
            echo "<input type='hidden' id='nbValue' name='nbValue' value='$nbValue'/>";  
            
            for($i = 1; $i <= $nbValue; $i++) {
               echo "<p>".$LANG['financial'][21]." ".$i." : ";
               echo '<input type="text" 
                     name="value_'.$i.'" 
                     value="'.$values['value'][$i].'" size="30"/></p>';
            }

            self::getNextValueEdit($nbValue);
            
            break;
         
         case self::TEXTAREA_FIELD: // Textarea
            
            echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";
            
            if(isset($values['value'])) {
               $val = $values['value'];
            } else { $val = ''; }
            
            echo $LANG['setup'][46]."&nbsp;";
            echo '<textarea name="value_1" cols="60" rows="6">'.$val.'</textarea>';
            echo '<br /><span style="font-size:10px;">
                  '.$LANG['plugin_formcreator']["question"][3].'
                  </span>';       
            break;
            
         case self::UPLOAD_FIELD: // Upload
            echo "<input type='hidden' id='nbValue' name='nbValue' value='0'/>";
            
            break;
            
         case self::VALIDATION_FIELD: // Validation

            echo "<input type='hidden' id='nbValue' name='nbValue' value='1'/>";

            if(isset($values['value'])) {
               $val = $values['value'];
            } else { $val = ''; }

            echo $LANG['setup'][46]."&nbsp;";
            echo '<textarea name="value_1" cols="60" rows="6">'.$val.'</textarea>';
            echo '<br /><span style="font-size:10px;">
                  '.$LANG['plugin_formcreator']["question"][3].'
                  </span>';       
            break;
            
         case self::MULTIPLICATION_ITEM_FIELD: // Sum
         
            echo "<input type='hidden' id='nbValue' name='nbValue' value='$nbValue'/>";  
                      
            for($i = 1; $i <= $nbValue; $i++) {
               echo "<p>".$LANG['common'][17]." ".$i." : ";
               echo '<input type="text" name="typeMat_'.$i.'" value="'.$values['typeMat'][$i].'" size="30"/>&nbsp;';
               echo $LANG['financial'][21]." ".$i." : ";
               echo '<input type="text" name="value_'.$i.'" value="'.$values['value'][$i].'" size="5"/>&#8364;</p>';
            }

            self::getNextMultiplicationEdit($nbValue);
            
            break;
      }   
      
   }
   
   static function getNextMultiplicationEdit($valueId) {
      global $LANG, $CFG_GLPI;
      
      Ajax::updateItemOnEvent('addField'.$valueId,
                              'nextValue'.$valueId,
                              $CFG_GLPI["root_doc"].
                              '/plugins/formcreator/ajax/addnewmultiplication.php',
                              array('id' => $valueId),
                              array('click'));
                                          
      echo '<div id="nextValue'.$valueId.'">';
      echo '<input class="submit" type="button" id="addField'.$valueId.'" value="'.$LANG['plugin_formcreator']["question"][6].'">';
      echo '</div>';
      
   }
   
   static function getNextValueEdit($valueId) {
      global $LANG, $CFG_GLPI;
      
      Ajax::updateItemOnEvent('addField'.$valueId,
                              'nextValue'.$valueId,
                              $CFG_GLPI["root_doc"].
                              '/plugins/formcreator/ajax/addnewvalue.php',
                              array('id' => $valueId),
                              array('click'));
                                          
      echo '<div id="nextValue'.$valueId.'">';
      echo '<input class="submit" type="button" id="addField'.$valueId.'" 
            value="'.$LANG['plugin_formcreator']["question"][6].'">';
      echo '</div>';
      
   }
   
   static function _serialize($input) {

      if($input['nbValue'] > 1) {
         foreach($input['value'] as $key => &$value) {
            $value = urlencode($value);
         }
      } else {
         $input['value'] = urlencode($input['value']);
      }

      $output = json_encode($input);
      return $output;
   }
   
   static function _unserialize($input) {

      $output = json_decode($input,true);
      
      if($output['nbValue'] > 1) {
         foreach($output['value'] as $key => &$value) {
            $value = urldecode($value);
         }
      } else {
         $output['value'] = urldecode($output['value']);
      }

      return $output;

   }
   
   static function getNameType($type) {
      global $LANG;
      
      switch($type) {
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
            
         case self::VALIDATION_FIELD: // Validation
            return $LANG['plugin_formcreator']["type_question"][6];                        
         
            break;
        case self::MULTIPLICATION_ITEM_FIELD: // calcul between box
            return $LANG['plugin_formcreator']["type_question"][7];                        
         
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