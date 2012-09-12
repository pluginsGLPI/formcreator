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

$helpdesk = new PluginFormcreatorHelpdesk;
$formID = $_GET['form'];

echo "<div class='center'>"."\n\r";

$form = $helpdesk->getForm($formID);

echo "<p class='h1'>".$form['name']."</p>";
echo "<p class='h6'>".$form['content']."</p><br />";

$verifQuestion = $helpdesk->getQuestionByForm($formID);

if(!empty($verifQuestion)) {
   
echo '<form action="'.$CFG_GLPI["root_doc"].'/plugins/formcreator/front/helpdesk.form.php" 
         enctype="multipart/form-data" name="form_ticket" method="post">';

echo '<input type="hidden" name="id" value="'.$form['id'].'" />';
         
$sections = $helpdesk->getSection($formID);

foreach($sections as $section_id => $section_value) {
   
   $questions = $helpdesk->getQuestionBySection($section_id);

   if(!empty($questions)) {
      
      echo "<div class='spaced' id='tabsbody'>";
      echo"<table class='tab_cadre_fixe fix_tab_height'>";
         echo "<tr>";
            echo "<th colspan='3'>".$section_value['name']."</th>";
         echo "</tr>";
         
         if(!empty($section_value['content'])) {
            echo "<tr>";
               echo "<td colspan='3' class='center'>".nl2br($section_value['content']).'</td>';
            echo "</tr>";
         }
         
         foreach($questions as $question_id => $question_value) {
            echo "<tr>";
               echo "<td>".$question_value['name']."</td>";
               echo "<td>";
               $helpdesk->getInputQuestion($question_id,
                                           $question_value['type'],
                                           $question_value['data'],
                                           $question_value['option']);
                                           
               $question_option = json_decode($question_value['option'],true);
               $question_option_type = $question_option['type'];
               
               if($question_value['type'] == 1) {
                  echo $helpdesk->getNameRegexType($question_option['type']);
               }
               
               echo "</td>";
               echo "<td>".$question_value['content']."</td>";
            echo "</tr>";
         }
         
      echo "</table>";     
      echo "</div>";
      
   }
}

echo '<input type="submit" name="add" value="'.$LANG['buttons'][8].'" class="submit"/>';
Html::closeForm();;

} else {
   echo $LANG['plugin_formcreator']["target"][7];
}

echo "</div>"."\n\r";

Html::footer();
?>
