<?php

class PluginFormcreatorHelpdesk {
   
   function getForm($formID) {
      
      $form = new PluginFormcreatorForm;
      $form = $form->find("id = '$formID'");
      
      return $form[$formID];
      
   }

   function getTarget($formID) {
      
      $target = new PluginFormcreatorTarget;
      $target = $target->find("plugin_formcreator_forms_id = '$formID'");
      
      return $target;
      
   }
   
   function getSection($formID) {
      
      $section = new PluginFormcreatorSection;
      $section = $section->find("plugin_formcreator_forms_id = '$formID'");
      
      return $section;
      
   }
   
   function getQuestionBySection($sectionID) {
      
      $question = new PluginFormcreatorQuestion;
      $question = $question->find("plugin_formcreator_sections_id = '$sectionID'", "position");
      
      return $question;
      
   } 
   
   function getQuestionBySectionTypeFile($sectionID) {
      
      $question = new PluginFormcreatorQuestion;
      $question = $question->find("plugin_formcreator_sections_id = '$sectionID' AND type ='5'");
      
      return $question;
      
   }  
         
   function getQuestionByForm($formID) {
      
      $question = new PluginFormcreatorQuestion;
      $question = $question->find("plugin_formcreator_forms_id = '$formID'");
      
      return $question;
      
   }  
     
   function getInputQuestion($id,$type,$data,$option) {
      global $LANG;
      
      $tab = PluginFormcreatorQuestion::_unserialize($data);
      
      $question_option = json_decode($option,true);
      $question_option_type = $question_option['type'];
      $question_option_regex = urldecode($question_option['value']);
      
      switch ($type) {

         case PluginFormcreatorQuestion::TEXT_FIELD: // Text
            switch ($question_option['type']) {
             case 1:
                 echo '<input type="text" name="question_'.$id.'" value="'.$tab['value'].'" size="40"/>';
                 break;
             
             case 2:
                 echo '<input type="text" name="question_'.$id.'" value="'.$tab['value'].'" size="40" onblur="verifTextNum(this);"/>';
                 break;
             
             case 3:
                 echo '<input type="text" name="question_'.$id.'" value="'.$tab['value'].'" size="40" onblur="verifText(this);"/>';
                 break;
             
             case 4:
                 echo '<input type="text" name="question_'.$id.'" value="'.$tab['value'].'" size="40" onblur="verifNum(this);"/>';
                 break;
             
             case 5:
                 echo '<input type="text" name="question_'.$id.'" value="'.$tab['value'].'" size="40" onblur="verifRegex(this,'. $question_option_regex .');"/>';
                 break;
             case 6:
                 echo '<input type="text" name="question_'.$id.'" value="'.$tab['value'].'" size="40" onblur="verifMail(this);"/>';
                 break;
            }
            break;
                     
         case PluginFormcreatorQuestion::SELECT_FIELD: // Select
            echo '<select name="question_'.$id.'">';
               foreach($tab['value'] as $value_id => $value) {
                  echo '<option value="'.$value.'">'.$value.'</option>';
               }
            echo '</select>';
            
            break;
            
         case PluginFormcreatorQuestion::CHECKBOX_FIELD: // Checkbox
               foreach($tab['value'] as $value_id => $value) {
                  echo '<input type="checkbox" name="question_'.$id.'[]" 
                                             value="'.$value.'"/>&nbsp;'.$value.'<br />';
               }
            
            break;
         case PluginFormcreatorQuestion::TEXTAREA_FIELD: // Textarea
            echo '<textarea name="question_'.$id.'" 
                              cols="50" rows="8">'.$tab['value'].'</textarea>';
            break;
            
            break;
         case PluginFormcreatorQuestion::UPLOAD_FIELD: // Upload
            echo '<input type="file" name="question_'.$id.'" />&nbsp;'.self::getMaxUploadSize();
            
            break;
            
         case PluginFormcreatorQuestion::VALIDATION_FIELD: // Validation
         
            echo '<table>';
            echo '<tr>';
            echo '<td>'.$LANG['validation'][21]."&nbsp;:</td><td>";
            User::dropdown(array('name'   => 'users_id_validate',
                                 'entity' => $_SESSION["glpiactive_entity"],
                                 'right'  => 'validate_ticket'));
            echo '</td></tr>';
            echo '<tr>';
            echo '<td>'.$LANG['validation'][5].'&nbsp;:</td>';
            echo '<td><textarea name="question_'.$id.'" 
                              cols="50" rows="8">'.$tab['value'].'</textarea></td></tr>';
            echo '</table>';
            
            break;
        
        case PluginFormcreatorQuestion::MULTIPLICATION_ITEM_FIELD: // 2 textfields sum
            echo '<script language="javascript">
            function multiplication(value1, value2, somme, value3) { somme.value = value1.value * value2.value; value3.value = value2.options[value2.selectedIndex].text; }
            </script>';
            echo '<input type="text" name="question1_'.$id.'" size="5" onchange="multiplication(question1_'.$id.', question2_'.$id.', somme_'.$id.', question3_'.$id.');"/>&nbsp;';
            echo '<select name="question2_'.$id.'" onchange="multiplication(question1_'.$id.', question2_'.$id.', somme_'.$id.', question3_'.$id.');">';
               foreach($tab['value'] as $value_id => $value) {
                  $typeMat = $tab["typeMat"][$value_id];
                  echo '<option value="'.$value.'">'.$typeMat.'</option>';
               }
            echo '</select>';
            echo '<input type=hidden name="question3_'.$id.'">';
            echo '&nbsp;<input type="text" name="somme_'.$id.'" size="5" readonly/>&#8364;';
            break;
      }
      
   }
   
   function getNameRegexType($type) {
      global $LANG;
      
      if($type != 1 && $type != 5) {
         $return = '&nbsp;&nbsp;<span style="font-size: 10px;">(&nbsp;';
         switch($type) {
            case 2: 
               $return.= $LANG['plugin_formcreator']["regex_type"][2];
               break;
            case 3: 
               $return.= $LANG['plugin_formcreator']["regex_type"][3];
               break;
            case 4: 
               $return.= $LANG['plugin_formcreator']["regex_type"][4];
               break;
            case 6: 
               $return.= $LANG['plugin_formcreator']["regex_type"][6];
               break;
         }
         $return.= '&nbsp;)</span>';
         return $return;
      }
   }
   
   public static function getMaxUploadSize() {
      global $LANG;
 
      $max_upload = (int)(ini_get('upload_max_filesize'));
      $max_post = (int)(ini_get('post_max_size'));
      $memory_limit = (int)(ini_get('memory_limit'));
 
      return $LANG['plugin_formcreator']["helpdesk"][2]
         ." : ".min($max_upload, $max_post, $memory_limit).$LANG['common'][82];
   }
   
   function addFilesTickets($id,$question_name,$entities_id) {
      global $LANG, $CFG_GLPI;

      $docadded = array();
      $doc      = new Document();
      $docitem  = new Document_Item();

      // if multiple files are uploaded
      $TMPFILE = array();
      if (is_array($_FILES[$question_name]['name'])) {
         $_FILES['filename'] = $_FILES[$question_name];
         foreach ($_FILES[$question_name]['name'] as $key => $filename) {
            if (!empty($filename)) {
               $TMPFILE[$key]['filename']['name']     = $filename;
               $TMPFILE[$key]['filename']['type']     = $_FILES['filename']['type'][$key];
               $TMPFILE[$key]['filename']['tmp_name'] = $_FILES['filename']['tmp_name'][$key];
               $TMPFILE[$key]['filename']['error']    = $_FILES['filename']['error'][$key];
               $TMPFILE[$key]['filename']['size']     = $_FILES['filename']['size'][$key];
            }
         }
      } else {
         $TMPFILE = array( $_FILES );
      }

      foreach ($TMPFILE as $_FILES) {
         if (isset($_FILES[$question_name])
             && count($_FILES[$question_name]) > 0
             && $_FILES[$question_name]["size"] > 0) {
            
            $_FILES['filename'] = $_FILES[$question_name];
            
            // Check for duplicate
            if ($doc->getFromDBbyContent($entities_id,$_FILES['filename']['tmp_name'])) {
               $docID = $doc->fields["id"];

            } else {
               $input2         = array();
               $input2["name"] = addslashes($LANG['tracking'][24]." $id");

               $input2["tickets_id"]              = $id;
               $input2["entities_id"]             = $entities_id;
               $input2["documentcategories_id"]   = $CFG_GLPI["documentcategories_id_forticket"];
               $input2["_only_if_upload_succeed"] = 1;
               $input2["entities_id"]             = $entities_id;
               $docID = $doc->add($input2);
               
               if ($docID>0) {
                  if ($docitem->add(array('documents_id' => $docID,
                                          'itemtype'     => 'Ticket',
                                          'items_id'     => $id))) {
                     $docadded[] = stripslashes($doc->fields["name"]." - ".$doc->fields["filename"]);
                  }
               }
            }  
         }
      }
      unset($_FILES['filename']);
      return true;
   }
   
}

?>