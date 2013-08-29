<?php

class PluginFormcreatorTitle extends CommonDBTM {

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
               $title = new self;
               $title->showAddTitle($item);
            break;
         }
         
      return true;
   }
      
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      return $LANG['plugin_formcreator']["headings"][8];
   }

   function showAddTitle($form) {
      global $LANG, $CFG_GLPI;
      
      echo "<div id='viewaddtitle'></div>\n";
      
      echo "<script type='text/javascript' >\n";
      echo "function viewAddTitle () {\n";
      $params = array('type'       => __CLASS__,
                      'parenttype' => 'PluginFormcreatorForm',
                      'plugin_formcreator_forms_id'    => $form->fields['id'],
                      'id'         => -1);
      Ajax::updateItemJsCode("viewaddtitle",
                             $CFG_GLPI["root_doc"]."/plugins/formcreator/ajax/viewaddobject.php", 
                             $params);
      echo "};";
      echo "</script>\n";

      echo "<div class='center'>".
           "<a href='javascript:viewAddTitle();'>";
      echo $LANG['plugin_formcreator']["title"][0]."</a></div><br/>\n";
      
      self::getListTitle($form->fields['id']);
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
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/title.form.php'>";
      
      echo "<input type='hidden' name='plugin_formcreator_forms_id' 
            value='".$params['plugin_formcreator_forms_id']."' />";
      
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='1'>".$LANG['plugin_formcreator']["title"][0]."</th>";
            echo "<th colspan='1'>";
			echo __('Select Language');
			echo "</th>";
         echo "</tr>";
         
		 echo "<tr class='tab_bg_1'>";
         echo '<td>'.$LANG['plugin_formcreator']["title"][1].' : <textarea name="name" rows="10" cols="100">'.$this->fields['name'].'</textarea></td>';
         echo "</td>";
         echo "<td>";
		 if ($this->fields["language"])
			Dropdown::showLanguages("language", array('value' => $this->fields["language"]));
		 else
			Dropdown::showLanguages("language", array('value' => $_SESSION['glpilanguage']));
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
   
   static function getListTitle($formID) {
      global $LANG, $CFG_GLPI;
      $title = new self;
      $listTitle = $title->find();
      
      if(!empty($listTitle)) {
         echo '<div class="center">';
         echo '<table class="tab_cadrehov" border="0" >';
            echo '<th width="20">';
               echo 'ID';
            echo '</th>';
			echo '<th>';
               echo $LANG['plugin_formcreator']["title"][2];
            echo '</th>';
         
         foreach($listTitle as $title_id => $values) {
            echo '<tr>';
               echo '<td class="center">';
                  echo '<a id="id'.$title_id.'">'.$title_id.'</a>';
               echo '</td>';
			   echo '<td>';
                  echo '<a id="lang'.$title_id.'">'.$values['language'].'</a>';
               echo '</td>';
            echo '</tr>';
         }
      
         echo '</table>';
         echo '</div>';
         
         foreach($listTitle as $title_id => $values) {
            Ajax::updateItemOnEvent('lang'.$title_id,
                                    'editTitle',
                                    $CFG_GLPI["root_doc"].
                                    '/plugins/formcreator/ajax/vieweditobject.php',
                                    array('id' => $title_id, 'type' => __CLASS__),
                                    array('click'));
         }
         
         echo '<br /><div id="editTitle"></div>';

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
      action='".$CFG_GLPI["root_doc"]."/plugins/formcreator/front/title.form.php'>";
	  
      echo "<input type='hidden' name='id' 
            value='".$this->fields['id']."' />";
                     
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='1'>".$LANG['plugin_formcreator']["title"][1]."</th>";
            echo "<th colspan='1'>".$LANG['plugin_formcreator']["title"][2]."</th>";
         echo "</tr>";
         
		 echo "<tr class='tab_bg_1'>";
         echo '<td>'.$LANG['plugin_formcreator']["title"][1].' : <textarea name="name" rows="10" cols="100">'.$this->fields['name'].'</textarea></td>';
         echo "<td>";
         Dropdown::showLanguages("language", array('value' => $this->fields["language"]));
         echo "</td>";
         echo "</tr>";
		 
		 echo "<tr class='tab_bg_1'>";
         echo '<td class="center" colspan="2">'.$LANG['plugin_formcreator']["title"][3].'</td>';
         echo "</tr>";
		 
		 echo "<tr class='tab_bg_1'>";
         echo '<td colspan="2">'.self::bbCode($this->fields['name']).'</td>';
         echo "</tr>";
		 
         echo "<tr>";
         echo "<td class='center' colspan='1'>";
            echo "<input class='submit' type='submit' value='";
			echo __('Update');
			echo "' name='update'>";
         echo "</td>";
		 echo "<td class='center' colspan='1'>";
			echo "<input class='submit' type='submit' value='";
			echo __('Purge');
			echo "' name='delete'>";
		 echo "</td>";
         echo "</tr>";
         
      echo "</table>";
      echo "</div>";
      
      Html::closeForm();
   }
   
   static function getSelectTitle($language) {
	  $text = '';
      $title = new self;
      $requete = $title->find("language='".$language."'");  
      foreach ($requete as $value) {
		$text = $value["name"];
	  }
      return self::bbCode($text);
   }
   
   static function bbCode($t)
	// remplace les balises BBCode par des balises HTML
	{
		// barre horizontale
		$t=str_replace("[hr]", "<hr width=\"100%\" size=\"1\" />", $t);
		
		// saut de ligne
		$t=str_replace("[br]", "<br/>", $t);

		// gras
		$t=str_replace("[b]", "<strong>", $t);
		$t=str_replace("[/b]", "</strong>", $t);

		// italique
		$t=str_replace("[i]", "<em>", $t);
		$t=str_replace("[/i]", "</em>", $t);
		
		// italique
		$t=str_replace("[tab]", "&nbsp&nbsp&nbsp&nbsp&nbsp", $t);

		// soulignement
		$t=str_replace("[u]", "<u>", $t);
		$t=str_replace("[/u]", "</u>", $t);

		// alignement centré
		$t=str_replace("[center]", "<div style=\"text-align: center\">", $t);
		$t=str_replace("[/center]", "</div>", $t);

		// alignement à droite
		$t=str_replace("[right]", "<div style=\"text-align: right\">", $t);
		$t=str_replace("[/right]", "</div>", $t);

		// alignement justifié
		$t=str_replace("[justify]", "<div style=\"text-align: justify\">", $t);
		$t=str_replace("[/justify]", "</div>", $t);

		// taille des caractères
		$t = preg_replace('/\[size=([0-9]{1,2})\]/', '<span style="font-size:$1px">', $t);
		$t=str_replace("[/size]", "</span>", $t);

		// couleur
		$t = preg_replace('/\[color=([a-z]{1,10})\]/', '<span style="color:$1">', $t);
		$t=str_replace("[/color]", "</span>", $t);

		// lien avec target blank
		$t = preg_replace('/\[url=(.+)\](.+?)\[\/url\]/', '<a href="$1" target="_blank">$2</a>', $t);

		//lien normal
		$t = preg_replace('/\[url\](.+?)\[\/url\]/', '<a href="$1">$1</a>', $t);

		//image
		$t = preg_replace('/\[img\](.+?)\[\/img\]/', '<img src="$1" alt="" border="0" />', $t);

		//image 2
		$t = preg_replace('/\[img=(.+)\]/', '<img src="$1" alt="" border="0">', $t);

		//mail 1
		$t = preg_replace('/\[mail=(.+)\](.+?)\[\/mail\]/', '<a href="mailto:$1">$2</a>', $t);

		//mail 2
		$t = preg_replace('/\[mail\](.+?)\[\/mail\]/', '<a href="mailto:$1">$1</a>', $t);

		return $t;
	}
}
