<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
   Html::header($LANG['plugin_formcreator']['name'],
               $_SERVER['PHP_SELF'],
               "plugins",
               "formcreator",
               "form"
               );
} else {
   Html::helpHeader($LANG['plugin_formcreator']['name'], $_SERVER['PHP_SELF']);
}

function getFullForm($questions,$question,$content) {
   
   $out = '';
   
   foreach($questions as $question_id => $question_value) {
      
      if($question_value['type'] != 5 && $question_value['type'] != 6) {
         $question_name = "question_".$question_id;
         $answer_name = "answer_".$question_id;
         
         if(array_key_exists($question_name,$question)) {
            $out.= $question_value['name'].' : '.$question[$question_name]."\n\r";
         }
      }
   }

   return $out; 
}

echo "<div class='center'>"."\n\r";
   
   $helpdesk = new PluginFormcreatorHelpdesk;
   $formID = $_REQUEST['id'];
   
   $form = new PluginFormcreatorForm;
   $form->getFromDB($formID);

   $targets = $helpdesk->getTarget($formID);
   
   foreach($targets as $target_id => $target_value) {
      
      $ticket = array();
   
      $validation_exist = false;
      
      $ticket['entities_id']        = $form->fields['entities_id'];
      $ticket['urgency']            = $target_value['urgency'];
      $ticket['priority']           = $target_value['priority'];
      $ticket['itilcategories_id']  = $target_value['itilcategories_id'];
      
      $ticket['name']               = $target_value['name'];
      
      $questions = $helpdesk->getQuestionByForm($formID);
      
      foreach($questions as $question_id => $question_value) {
         
         $question_name = 'question_'.$question_id;
         
         if(isset($_REQUEST[$question_name])) {
            
            switch($question_value['type']) {
               
               case PluginFormcreatorQuestion::TEXT_FIELD:
                  
                  $question_option = json_decode($question_value['option'],true);
                  $question_option_type = $question_option['type'];
                  $question_option_value = urldecode($question_option['value']);
                  
                  if($question_option_type == 1) {
                     $question[$question_name] = $_REQUEST[$question_name];
                  } else {
                     
                     if(!preg_match("$question_option_value",$_REQUEST[$question_name])) {
                        Session::addMessageAfterRedirect(
                              $LANG['plugin_formcreator']["error_form"][1].'&laquo;'.
                              $question_value['name'].'&raquo;'.
                              $LANG['plugin_formcreator']["error_form"][2]
                              , false, ERROR);
                              Html::back();
                     } else {
                        $question[$question_name] = $_REQUEST[$question_name];
                     }
                     
                  }
                  break;
                  
               case PluginFormcreatorQuestion::TEXTAREA_FIELD:
                  $question[$question_name] = $_REQUEST[$question_name];
                  break;
                  
               case PluginFormcreatorQuestion::SELECT_FIELD:
                  $question[$question_name] = $_REQUEST[$question_name];
                  break;
               
               case PluginFormcreatorQuestion::CHECKBOX_FIELD:
                  
                  $reponse = '';
                  foreach($_REQUEST[$question_name] as $key => $value) {
                     $reponse.= $value.', ';
                  }
                  
                  $question[$question_name] = substr($reponse,0,-2);
                  break;
               
               case PluginFormcreatorQuestion::VALIDATION_FIELD:
                  
                  $validation_exist = true;
                  
                  $validationTab = array();
                  $validationTab['users_id_validate'] = $_REQUEST['users_id_validate'];
                  $validationTab['entities_id'] = $form->fields['entities_id'];
                  $validationTab['comment_submission'] = $_POST[$question_name];
                  $validationTab['user_id'] = Session::getLoginUserID();
                  break;
				  
			   case PluginFormcreatorQuestion::MULTIPLICATION_ITEM_FIELD:
                   
                   $question[$question_name] = $_REQUEST[$question_name1]. " - ".$_REQUEST[$question_name3];
                   break;
            }
            
         } else {
               $question[$question_name] = $LANG['plugin_formcreator']["helpdesk"][4];
         }
         
      }
      
      $ticket['content'] = $target_value['content'];
      
      foreach($questions as $question_id => $question_value) {
            $question_name = "question_".$question_id;
            $answer_name = "answer_".$question_id;
            
            if(array_key_exists($question_name,$question)) {
               $ticket['content'] = str_replace("##$question_name##",
                                                $question_value['name'],
                                                $ticket['content']);
                                             
               $ticket['content'] = str_replace("##$answer_name##",
                                                $question[$question_name],
                                                $ticket['content']);
            }
      }
      
      $ticket['content'] = str_replace('##full_form##',getFullForm($questions,$question,$ticket['content']),$ticket['content']);
      $ticket['content'] = PluginFormcreatorQuestion::protectData($ticket['content']);
      
      $user = new User;
      $user->getFromDB(Session::getLoginUserID());

      $ticket['users_id_recipient'] = $user->fields['id'];
      $ticket['users_id_lastupdater'] = $user->fields['id'];
      $ticket['type'] = 2;
      
      $track = new Ticket();
      $ticketID = $track->add($ticket);
      
      $validationTab['tickets_id'] = $ticketID;
      
      if($validation_exist) {
         $validation = new Ticketvalidation();
         $validation->add($validationTab);
      }
      
      $sections = new PluginFormcreatorSection;
      $sections = $sections->find("plugin_formcreator_targets_id = '$target_id'");
      
      foreach($sections as $section_id => $section_value) {
            
            $questions = $helpdesk->getQuestionBySectionTypeFile($section_id);
            
            foreach($questions as $question_id => $question_value) {
               $question_name = "question_".$question_id;

               if(array_key_exists($question_name,$_FILES)) {
                  
                  if($_FILES[$question_name]['error'] != 4) {
                     $helpdesk->addFilesTickets($ticketID,$question_name,$ticket['entities_id']);
                  }
                  
               }
               
            }
      }

   }
   unset ($_FILES);
   Session::addMessageAfterRedirect($LANG['plugin_formcreator']["helpdesk"][3],false,INFO);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/formcreator/front/formlist.php");

echo "</div>"."\n\r";

Html::footer();
?>
