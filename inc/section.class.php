<?php

class PluginFormcreatorSection extends CommonDBTM {

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
               $section = new self;
               $section->showAddSection($item);
            break;
         }
         
      return true;
   }
      
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      return $LANG['plugin_formcreator']["headings"][2];
   }

   function showAddSection($form) {
      global $LANG, $CFG_GLPI;
      
      $target = new PluginFormcreatorTarget;
      $listTarget = $target->find("plugin_formcreator_forms_id = '".$form->fields['id']."'");
      
      if(!empty($listTarget)) {

         echo "<div id='viewaddsection'></div>\n";
      
         echo "<script type='text/javascript' >\n";
         echo "function viewAddSection () {\n";
         $params = array('type'       => __CLASS__,
                         'parenttype' => 'PluginFormcreatorForm',
                         'plugin_formcreator_forms_id'    => $form->fields['id'],
                         'id'         => -1);
         Ajax::updateItemJsCode("viewaddsection",
                                $CFG_GLPI["root_doc"]."/plugins/formcreator/ajax/viewaddobject.php", 
                                $params);
         echo "};";
         echo "</script>\n";

         echo "<div class='center'>".
              "<a href='javascript:viewAddSection();'>";
         echo $LANG['plugin_formcreator']["section"][0]."</a></div><br/>\n";
      
         self::getListSection($form->fields['id']);
         
      } else {
         echo "<div class='center'>";
            echo $LANG['plugin_formcreator']['section'][2];
         echo "</div>";
      }
   }   
   
   function showForm($params,$options=array()) {
      global $LANG, $CFG_GLPI;
      
      if ($params['id'] > 0) {
         $this->check($params['id'],'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }
      
      echo "<form method='POST' 
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/section.form.php'>";
      
      echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='".$params['plugin_formcreator_forms_id']."' />";
      
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["section"][1]."</th>";
            echo "<th colspan='2'>&nbsp;</th>";
         echo "</tr>";
         
      
         echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['plugin_formcreator']["target"][8]."&nbsp;:</td><td>";
         
         PluginFormcreatorTarget::getSelectTarget($params['plugin_formcreator_forms_id']);
         
         echo "</td>";
         echo "<td>".$LANG['common'][16]."&nbsp;:</td>";
         echo "<td>";
         echo "<input type='text' name='name' value='' size='54'/>";
         echo "</td>";
         echo "</tr>";
         
         echo '<tr class="tab_bg_1">';
         echo '<td>'.$LANG['plugin_formcreator']["question"][11].' :</td>';
         echo '<td><input type="text" name="position" value="0" size="3" /></td>';
         echo "<td>".$LANG['joblist'][6]."&nbsp;:</td>";
         echo "<td>";
         echo "<textarea name='content' cols='55' rows='6'>";
         echo $this->fields["content"];
         echo "</textarea>";
         echo "</td>";
         echo '</tr>';
         
         echo "<tr>";
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
         
         Session::addMessageAfterRedirect($LANG['plugin_formcreator']["error_form"][3], false, ERROR);
         return false;
      }
      
      return $input;
   }
   
   static function getListSection($formID) {
      global $LANG, $CFG_GLPI;
      
      $section = new self;
      $listSection = $section->find("plugin_formcreator_forms_id = '$formID' ORDER BY position");
      
      if(!empty($listSection)) {
         echo '<div class="center">';
         echo '<table class="tab_cadrehov" border="0" >';
            echo '<th width="20">';
               echo 'ID';
            echo '</th>';
            echo '<th>';
               echo $LANG['plugin_formcreator']["section"][3];
            echo '</th>';
            echo '<th>';
               echo $LANG['plugin_formcreator']["target"][2];
            echo '</th>';
            echo '<th>';
               echo $LANG['plugin_formcreator']["question"][11];
            echo '</th>';
         
         foreach($listSection as $section_id => $values) {
            echo '<tr>';
               echo '<td class="center">';
                  echo $section_id;
               echo '</td>';
               echo '<td>';
                  echo '<a id="section'.$section_id.'">'.$values['name'].'</a>';
               echo '</td>';
               echo '<td>';
                  echo PluginFormcreatorTarget::getTargetName(
                                 $values['plugin_formcreator_targets_id']);
               echo '</td>';
               echo '<td class="center">';
                  echo $values['position'];
               echo '</td>';
            echo '</tr>';

         }
      
         echo '</table>';
         echo '</div>';
         
         foreach($listSection as $section_id => $values) {
            Ajax::updateItemOnEvent('section'.$section_id,
                                    'editSection',
                                    $CFG_GLPI["root_doc"].
                                    '/plugins/formcreator/ajax/vieweditobject.php',
                                    array('id' => $section_id, 'type' => __CLASS__),
                                    array('click'));
         }
         
         echo '<br /><div id="editSection"></div>';

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
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/section.form.php'>";
      
      echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='".$this->fields['plugin_formcreator_forms_id']."' />";

      echo "<input type='hidden' name='id' 
            value='".$this->fields['id']."' />";
                     
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["target"][1]."</th>";
            echo "<th colspan='2'>&nbsp;</th>";
         echo "</tr>";
         
      
         echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['plugin_formcreator']["target"][8]."&nbsp;:</td><td>";
         
         PluginFormcreatorTarget::getSelectTarget($this->fields['plugin_formcreator_forms_id'],
                                                  'plugin_formcreator_targets_id',
                                                  $this->fields['plugin_formcreator_targets_id']);
         
         echo "</td>";
         echo "<td>".$LANG['common'][16]."&nbsp;:</td>";
         echo "<td>";
         echo '<input type="text" name="name" value="'.$this->fields['name'].'" size="54"/>';
         echo "</td>";
         echo "</tr>";
         
         echo '<tr class="tab_bg_1">';
         echo '<td>'.$LANG['plugin_formcreator']["question"][11].' :</td>';
         echo '<td><input type="text" name="position" 
                           value="'.$this->fields['position'].'" size="3" /></td>';
         
         
         echo "<td>".$LANG['joblist'][6]."&nbsp;:</td>";
         echo "<td>";
         echo "<textarea name='content' cols='55' rows='6'>";
         echo $this->fields["content"];
         echo "</textarea>";
         echo "</td>";
         echo '</tr>';
        
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
   
   static function getSelectSection($formID,
                                   $selectName='plugin_formcreator_sections_id',
                                   $selected='') {
      
      $section = new self;
      $listSection = $section->find("plugin_formcreator_forms_id = '$formID'");  
      
      echo '<select name="'.$selectName.'">';
      
      foreach($listSection as $section_id => $values) {
         if($selected == $section_id) {
            echo '<option value="'.$section_id.'" selected="selected">'.$values['name'].'</option>';
         } else {
            echo '<option value="'.$section_id.'">'.$values['name'].'</option>';
         }
      } 
      
      echo '</select>';
   }
   
   static function getSectionName($section_ID) {
      
      $section = new self;
      $listSection = $section->find("id = '$section_ID'"); 
      
      foreach($listSection as $section) {
         $sectionName = $section['name'];
      }
      
      return $sectionName;      
   }
}
?>