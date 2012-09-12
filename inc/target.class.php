<?php

class PluginFormcreatorTarget extends CommonDBTM {

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
               $target = new self;
               $target->showAddTarget($item);
            break;
         }
         
      return true;
   }
      
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      return $LANG['plugin_formcreator']["headings"][1];
   }

   function showAddTarget($form) {
      global $LANG, $CFG_GLPI;
      
      echo "<div id='viewaddtarget'></div>\n";
      
      echo "<script type='text/javascript' >\n";
      echo "function viewAddTarget () {\n";
      $params = array('type'       => __CLASS__,
                      'parenttype' => 'PluginFormcreatorForm',
                      'plugin_formcreator_forms_id'    => $form->fields['id'],
                      'id'         => -1);
      Ajax::updateItemJsCode("viewaddtarget",
                             $CFG_GLPI["root_doc"]."/plugins/formcreator/ajax/viewaddobject.php", 
                             $params);
      echo "};";
      echo "</script>\n";

      echo "<div class='center'>".
           "<a href='javascript:viewAddTarget();'>";
      echo $LANG['plugin_formcreator']["target"][0]."</a></div><br/>\n";
      
      self::getListTarget($form->fields['id']);
   }   
   
   function showForm($params,$options=array()) {
      global $LANG, $CFG_GLPI;
      
      if ($params['id'] > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }
             
      echo "<form method='POST' 
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/target.form.php'>";
      
      echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='".$params['plugin_formcreator_forms_id']."' />";
      
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["target"][1]."</th>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["target"][3]."</th>";
         echo "</tr>";
         
      
         echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['plugin_formcreator']["target"][4]."&nbsp;:</td>";
         echo '<td><input type="text" name="name" 
               value="'.$this->fields['name'].'" size="54"/></td>';
         echo "</td>";
         echo "<td>".$LANG['common'][36]."&nbsp;:</td>";
         echo "<td>";
         Dropdown::show('ITILCategory', array('value' => $this->fields["itilcategories_id"]));
         echo "</td>";
         echo "</tr>";
         
         echo "<tr>";
         echo "<td rowspan='2'>";
         echo $LANG['joblist'][6]." :";
         echo "</td><td rowspan='2'>";
         echo "<textarea name='content' cols='65' rows='12'>".$this->fields['content']."</textarea>";
         echo "</td>";
         echo "<td>".$LANG['joblist'][29]." :</td>";
         echo "<td>";
         Ticket::dropdownUrgency("urgency",$this->fields["urgency"]);
         echo "</td>";
         echo "</tr><tr>";
         echo "<td rownspan='2'>".$LANG['joblist'][2]." :</td>";
         echo "<td>";
         CommonITILObject::dropdownPriority("priority",$this->fields["priority"]);
         echo "</td>";
         echo "</tr>";

         
         echo "<tr>";
         echo "<td class='center' colspan='2'>";
            echo "<input class='submit' type='submit' value='".$LANG['buttons'][8]."' name='add'>";
         echo "</td>";
         echo "</tr>";
         
      echo "</table>";
      echo "</div>";
      
      Html::closeForm();
      
      self::popupContent($params['plugin_formcreator_forms_id']);
   }

   function prepareInputForAdd($input) {
      global $CFG_GLPI, $LANG;
      
      if (empty($input['name'])) {
         
         Session::addMessageAfterRedirect($LANG['plugin_formcreator']["error_form"][5], false, ERROR);
         return false;
      }
      
      return $input;
   }
   
   static function getListTarget($formID) {
      global $LANG, $CFG_GLPI;
      
      $target = new self;
      $listTarget = $target->find("plugin_formcreator_forms_id = '$formID'");
      
      if(!empty($listTarget)) {
         echo '<div class="center">';
         echo '<table class="tab_cadrehov" border="0" >';
            echo '<th width="20">';
               echo 'ID';
            echo '</th>';
            echo '<th>';
               echo $LANG['plugin_formcreator']["target"][2];
            echo '</th>';
         
         foreach($listTarget as $target_id => $values) {
            echo '<tr>';
               echo '<td class="center">';
                  echo $target_id;
               echo '</td>';
               echo '<td>';
                  echo '<a id="target'.$target_id.'">'.$values['name'].'</a>';
               echo '</td>';
            echo '</tr>';

         }
      
         echo '</table>';
         echo '</div>';
         
         foreach($listTarget as $target_id => $values) {
            Ajax::updateItemOnEvent('target'.$target_id,
                                    'editTarget',
                                    $CFG_GLPI["root_doc"].
                                    '/plugins/formcreator/ajax/vieweditobject.php',
                                    array('id' => $target_id, 'type' => __CLASS__),
                                    array('click'));
         }
         
         echo '<br /><div id="editTarget"></div>';

      }
   }
   
   function showFormEdit($params,$options=array()) {
      global $LANG, $CFG_GLPI;
      
      if ($params['id'] > 0) {
         $this->check($params['id'],'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }
             
      echo "<form method='POST' 
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/target.form.php'>";
      
      echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='".$this->fields['plugin_formcreator_forms_id']."' />";

      echo "<input type='hidden' name='id' 
            value='".$this->fields['id']."' />";
                     
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["target"][5]."</th>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["target"][3]."</th>";
         echo "</tr>";
         
      
         echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['plugin_formcreator']["target"][4]."&nbsp;:</td>";
         echo '<td><input type="text" name="name" 
               value="'.$this->fields['name'].'" size="54"/></td>';
         echo "</td>";
         echo "<td>".$LANG['common'][36]."&nbsp;:</td>";
         echo "<td>";
         Dropdown::show('ITILCategory', array('value' => $this->fields["itilcategories_id"]));
         echo "</td>";
         echo "</tr>";
         
         echo "<tr>";
         echo "<td rowspan='2'>";
         echo $LANG['joblist'][6]." :";
         echo "</td><td rowspan='2'>";
         echo "<textarea name='content' cols='65' rows='12'>".$this->fields['content']."</textarea>";
         echo "</td>";
         echo "<td>".$LANG['joblist'][29]." :</td>";
         echo "<td>";
         Ticket::dropdownUrgency("urgency",$this->fields["urgency"]);
         echo "</td>";
         echo "</tr><tr>";
         echo "<td rownspan='2'>".$LANG['joblist'][2]." :</td>";
         echo "<td>";
         CommonITILObject::dropdownPriority("priority",$this->fields["priority"]);
         echo "</td>";
         echo "</tr>";

         
         echo "<tr>";
         echo "<td class='center' colspan='2'>";
            echo "<input class='submit' type='submit' value='".$LANG['buttons'][7]."' name='update'>";
         echo "</td>";
         echo "</tr>";
         
      echo "</table>";
      echo "</div>";
      
      Html::closeForm();
      
      self::popupContent($this->fields['plugin_formcreator_forms_id']);
   }
   
   static function getSelectTarget($formID,
                                   $selectName='plugin_formcreator_targets_id',
                                   $selected='') {
      
      $target = new self;
      $listTarget = $target->find("plugin_formcreator_forms_id = '$formID'");  
      
      echo '<select name="'.$selectName.'">';
      
      foreach($listTarget as $target_id => $values) {
         if($selected == $target_id) {
            echo '<option value="'.$target_id.'" selected="selected">'.$values['name'].'</option>';
         } else {
            echo '<option value="'.$target_id.'">'.$values['name'].'</option>';
         }
      } 
      
      echo '</select>';
   }
   
   static function getTargetName($target_ID) {
      
      $target = new self;
      $listTarget = $target->find("id = '$target_ID'"); 
      
      foreach($listTarget as $target) {
         $targetName = $target['name'];
      }
      
      return $targetName;      
   }
   
   static function popupContent($formID) {
      global $LANG;
      
      echo "<h1>".$LANG['plugin_formcreator']["headings"][5]."</h1>";
      
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th>".$LANG['plugin_formcreator']["question"][2]."</th>";
            echo "<th>".$LANG['log'][44]."</th>";
            echo "<th>".$LANG['plugin_formcreator']["target"][6]."</th>";
            echo "<th>".$LANG['plugin_formcreator']["section"][3]."</th>";
         echo "</tr>";
         
         $question = new PluginFormcreatorQuestion;
         $listQuestion = $question->find("plugin_formcreator_forms_id = '".$formID."'");
         
         if(!empty($listQuestion)) {

            foreach ($listQuestion as $question_id => $value) {
               if($value['type'] != '5' && $value['type'] != 6) {
                  echo "<tr>";
                     echo "<td>".$value['name']."</td>";
                     echo "<td>##question_".$value['id']."##</td>";
                     echo "<td>##answer_".$value['id']."##</td>";
                     echo "<td>".PluginFormcreatorSection::getSectionName(
                                                   $value['plugin_formcreator_sections_id'])."</td>";
                  echo "</tr>";
               }
            }
         } else {
            echo "<tr>";
               echo "<td colspan='4' class='center'>".$LANG['plugin_formcreator']["target"][7]."</td>";
            echo "</tr>";
         }
      echo "</table><br/>";
          
   }
   
}
