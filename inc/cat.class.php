<?php

class PluginFormcreatorCat extends CommonDBTM {

   static function canCreate() {
      return Session::haveRight('config', 'w');
   }

   static function canView() {
      return Session::haveRight('config', 'r');
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $LANG;

         switch($item->getType()) {
            case 'PluginFormcreatorForm': 
               $cat = new self;
               $cat->showAddCat($item);
            break;
         }
         
      return true;
   }
      
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      return $LANG['plugin_formcreator']["headings"][7];
   }

   function showAddCat($form) {
      global $LANG, $CFG_GLPI;
      
      echo "<div id='viewaddcat'></div>\n";
      
      echo "<script type='text/javascript' >\n";
      echo "function viewAddCat () {\n";
      $params = array('type'       => __CLASS__,
                      'parenttype' => 'PluginFormcreatorForm',
                      'plugin_formcreator_forms_id'    => $form->fields['id'],
                      'id'         => -1);
      Ajax::updateItemJsCode("viewaddcat",
                             $CFG_GLPI["root_doc"]."/plugins/formcreator/ajax/viewaddobject.php", 
                             $params);
      echo "};";
      echo "</script>\n";

      echo "<div class='center'>".
           "<a href='javascript:viewAddCat();'>";
      echo $LANG['plugin_formcreator']["cat"][0]."</a></div><br/>\n";
      
      self::getListCat($form->fields['id']);
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
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/cat.form.php'>";
      
      echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='".$params['plugin_formcreator_forms_id']."' />";
      
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["cat"][1]."</th>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["cat"][2]."</th>";
         echo "</tr>";
         
		 echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['plugin_formcreator']["cat"][1]."&nbsp;:</td>";
         echo '<td><input type="text" name="name" 
               value="'.$this->fields['name'].'" size="54"/></td>';
         echo "</td>";
         echo "<td>".$LANG['plugin_formcreator']["cat"][2]."&nbsp;:</td>";
         echo "<td>";
         echo '<input type="text" name="position" value="'.$this->fields['position'].'" size="3"/>';
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

   function prepareInputForAdd($input) {
      global $CFG_GLPI, $LANG;
      
      if (empty($input['name'])) {
         
         Session::addMessageAfterRedirect($LANG['plugin_formcreator']["error_form"][5], false, ERROR);
         return false;
      }
      
      return $input;
   }
   
   static function getListCat($formID) {
      global $LANG, $CFG_GLPI;
      
      $cat = new self;
      $listCat = $cat->find(" 1 ORDER BY position");
      
      if(!empty($listCat)) {
         echo '<div class="center">';
         echo '<table class="tab_cadrehov" border="0" >';
            echo '<th width="20">';
               echo 'ID';
            echo '</th>';
            echo '<th>';
               echo $LANG['plugin_formcreator']["cat"][1];
            echo '</th>';
			echo '<th>';
               echo $LANG['plugin_formcreator']["cat"][2];
            echo '</th>';
         
         foreach($listCat as $cat_id => $values) {
            echo '<tr>';
               echo '<td class="center">';
                  echo $cat_id;
               echo '</td>';
               echo '<td>';
                  echo '<a id="cat'.$cat_id.'">'.$values['name'].'</a>';
               echo '</td>';
			   echo '<td>';
                  echo '<center>'.$values['position'].'</center>';
               echo '</td>';
            echo '</tr>';

         }
      
         echo '</table>';
         echo '</div>';
         
         foreach($listCat as $cat_id => $values) {
            Ajax::updateItemOnEvent('cat'.$cat_id,
                                    'editCat',
                                    $CFG_GLPI["root_doc"].
                                    '/plugins/formcreator/ajax/vieweditobject.php',
                                    array('id' => $cat_id, 'type' => __CLASS__),
                                    array('click'));
         }
         
         echo '<br /><div id="editCat"></div>';

      }
   }
   
   static function getListing() {
   
      $cat = new self;
      $listCat = $cat->find(" 1 ORDER BY position");
      
      if(!empty($listCat)) {
         foreach($listCat as $cat_id => $values) {
			$tab[$values['id']] = $values['name'];
         }
		 return $tab;
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
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/cat.form.php'>";
	  
      echo "<input type='hidden' name='id' 
            value='".$this->fields['id']."' />";
                     
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["cat"][1]."</th>";
            echo "<th colspan='2'>".$LANG['plugin_formcreator']["cat"][2]."</th>";
         echo "</tr>";
         
		 echo "<tr class='tab_bg_1'>";
         echo "<td>".$LANG['plugin_formcreator']["cat"][1]."&nbsp;:</td>";
         echo '<td><input type="text" name="name" 
               value="'.$this->fields['name'].'" size="54"/></td>';
         echo "</td>";
         echo "<td>".$LANG['plugin_formcreator']["cat"][2]."&nbsp;:</td>";
         echo "<td>";
         echo '<input type="text" name="position" value="'.$this->fields['position'].'" size="3"/>';
         echo "</td>";
         echo "</tr>";
		 
         echo "<tr>";
         echo "<td class='center' colspan='2'>";
            echo "<input class='submit' type='submit' value='";
			echo __('Update');
			echo "' name='update'>";
         echo "</td>";
		 echo "<td class='center' colspan='2'>";
			echo "<input class='submit' type='submit' value='";
			echo __('Purge');
			echo "' name='delete'>";
		 echo "</td>";
         echo "</tr>";
         
      echo "</table>";
      echo "</div>";
      
      Html::closeForm();
   }
   
   static function getSelectCat($formID, $selected='', $selectName='cat') {
      
      $cat = new self;
      $listCat = $cat->find();  
      
      echo '<select name="'.$selectName.'">';
      
      foreach($listCat as $cat_id => $values) {
         if($selected == $cat_id) {
            echo '<option value="'.$cat_id.'" selected="selected">'.$values['name'].'</option>';
         } else {
            echo '<option value="'.$cat_id.'">'.$values['name'].'</option>';
         }
      }
      echo '</select>';
   }
   
}
