<?php

include ('../../../inc/includes.php');

//anonyme or not ?
Session::checkLoginUser();

//onload
$helpdesk = new PluginFormcreatorHelpdesk;
$formID = $_GET['form'];
$verifQuestion = $helpdesk->getQuestionByForm($formID);

if (!empty($verifQuestion)) {

    $cache = "\"";
    $affiche = "\"";
    foreach ($verifQuestion as $question_id => $question_value) {
        $x = $y =0;
        if ($question_value['type'] == 8) {
            $tab = PluginFormcreatorQuestion::_unserialize($question_value['data']);
            foreach ($tab['value'] as $value_id => $value) {
					if ($x != 0) {
							$cache .= $helpdesk->creationTabDyna($tab['question'][$value_id]);
							if (strlen($cache)>2)
								$cache .= ":";
					} else {
						if (isset($tab['question'][$value_id])) {
							$affiche .= $helpdesk->creationTabDyna($tab['question'][$value_id]);
							if (strlen($affiche)>2)
								$affiche .= ":";
						}
						$x = 1;
					}
            }
        }
        else if ($question_value['type'] == 9) {
            $tab = PluginFormcreatorQuestion::_unserialize($question_value['data']);
            foreach ($tab['value'] as $value_id => $value) {
				if ($y != 0) {
						$cache .= $helpdesk->creationTabDyna($tab['section'][$value_id]);
						if (strlen($cache)>2)
							$cache .= ":";
				} else {
					if (isset($tab['section'][$value_id])) {
						$affiche .= $helpdesk->creationTabDyna($tab['section'][$value_id]);
						if (strlen($affiche)>2){
							$affiche .= ":";
						}
					}
					$y = 1;
				}
            }
        }
    }
}
$cache  = str_replace ("::", ":", $cache);
$cache  = str_replace ("::", ":", $cache);
$affiche  = str_replace ("::", ":", $affiche);
$affiche  = str_replace ("::", ":", $affiche);

while ($cache{strlen($cache)-1} == ":")
        $cache = substr($cache, 0, -1);

while ($affiche{strlen($affiche)-1} == ":")
        $affiche = substr($affiche, 0, -1);
		
while ($cache[0] == ":")
        $cache = substr($cache, 1, 0);

while ($affiche[0] == ":")
        $affiche = substr($affiche, 1, 0);


$cache .= "\"";
$affiche .= "\"";

if (Session::getLoginUserID()) {
   Html::header($LANG['plugin_formcreator']['name'],
               $_SERVER['PHP_SELF'],
               "plugins",
               "formcreator",
               "form",
               "chargement($cache, $affiche)"
               );
} else {
        Html::simpleHeader($LANG['plugin_formcreator']['name2'],array(__('Authentication') => "../../../index.php?co=1",
                                                   __('FAQ')  => "../../../front/helpdesk.faq.php",
                                                   $LANG['plugin_formcreator']['name2'] => "./formlist.php"),
                                                   "chargement($cache, $affiche)");
}

echo "<div class='center'>" . "\n\r";

$form = $helpdesk->getForm($formID);

echo "<p class='h1'>" . $form['name'] . "</p>";
echo "<p class='h6'>" . $form['content'] . "</p><br />";

if (!empty($verifQuestion)) {
    
    echo '<form action="' . $CFG_GLPI["root_doc"] . '/plugins/formcreator/front/helpdesk.form.php" 
         enctype="multipart/form-data" id="form_ticket" name="form_ticket" method="post">';

    echo '<input type="hidden" name="id" value="' . $form['id'] . '" />';

    $sections = $helpdesk->getSection($formID);
    
    $listequestion = "'";
	$boolMultiplication = 0;
    foreach ($sections as $section_id => $section_value) {

        $questions = $helpdesk->getQuestionBySection($section_id);
        
        if (!empty($questions)) {

            echo "<div class='spaced' id='sec_".$section_id."'>";
            echo"<table class='tab_cadre_fixe fix_tab_height'>";
            echo "<tr>";
            echo "<th colspan='5'>" . $section_value['name'] . "</th>";
            echo "</tr>";

            if (!empty($section_value['content'])) {
                echo "<tr>";
                echo "<td colspan='4' class='center'>" . nl2br($section_value['content']) . '</td>';
                echo "</tr>";
            }

            foreach ($questions as $question_id => $question_value) {
                echo "<tr id='" . $question_id . "'>";
                if ($question_value['type'] != "10") // empeche de mettre le nom de la question sur le formulaire pour liste déraoulante utlisateur
                    echo "<td>" . stripslashes($question_value['name']) . "</td>";
                echo "<td>";
                $helpdesk->getInputQuestion($question_id, $question_value['type'], $question_value['data'], $question_value['option']);

                $question_option = json_decode($question_value['option'], true);
				
				//si la question est du type champ texte
                if ($question_value['type'] == 1) {
                    echo $helpdesk->getNameRegexType($question_option['type']);
					//si il y a un controle sur le champ
                    if ($question_option['type'] != 1) {
                        //remplissage de la liste pour effectuer la vérification si le champ est non caché et obligatoire à la fois
                        $question_option = json_decode($question_value['option'], true);
                        $question_option_value = urldecode($question_option['value']);
                        $listequestion .= "sec_".$section_id."::".$question_id."::".$question_option_value."::".$question_value['name']."&&";
                    }
                }
                if (($question_value['type'] == "7") || ($question_value['type'] == "11")) //initialisation d'une variable pour savoir s'il y a un champ de multiplication de champ pour implémenter un champ total somme
                    $boolMultiplication = 1;
				if ($question_value['type'] == "8" || $question_value['type'] == "9" || $question_value['type'] == "4") { // section dynamique et question dynamique obligatoire
					$tab = PluginFormcreatorQuestion::_unserialize($question_value['data']);
					if ((isset($tab["obli"])) && ($tab["obli"] == "1")) {
						$listequestion .= "sec_".$section_id."::".$question_id."::".$question_value['name']."&&";
					}
				}
                $chaine = $question_value['content'];
                //remplacement lien url en BBCODE
                //$chaine = preg_replace("#\[url\]((ht|f)tp://)([^\r\n\t<\"]*?)\[/url\]#sie", "'<a href=\"\\1' . str_replace(' ', '%20', '\\3') . '\">\\1\\3</a>'", $chaine);
                $chaine = preg_replace_callback(
                	"#\[url\]((ht|f)tp://)([^\r\n\t<\"]*?)\[/url\]#si", 
                	function ($s) { return '<a href="' . $s[1] . str_replace(' ', '%20', $s[3]) . '">' . $s[1].$s[3] . '</a>'; }, 
                	$chaine
        		);
                $chaine = preg_replace("/\[url=(.+?)\](.+?)\[\/url\]/", "<a href=\"$1\">$2</a>", $chaine);
                //remplacement gras en BBCODE
                $chaine = str_replace("[b]", "<b>", $chaine);
                $chaine = str_replace("[/b]", "</b>", $chaine);
                //remplacement italique en BBCODE
                $chaine = str_replace("[i]", "<em>", $chaine);
                $chaine = str_replace("[/i]", "</em>", $chaine);
                //remplacement souligne en BBCODE
                $chaine = str_replace("[u]", "<u>", $chaine);
                $chaine = str_replace("[/u]", "</u>", $chaine);
                
                echo "</td>";
                echo "<td>" . $chaine . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
    }
    if ($boolMultiplication == 1) {
        echo '<input type="hidden" name="liste_champ_somme" id="liste_champ_somme" value=""/>';
        echo $LANG['plugin_formcreator']["information"][1].'<input type="text" name="somme_total_achat" id="somme_total_achat" value="0" readonly="readonly"/>&nbsp;&nbsp;<br/><br/>';
    }
    if (strlen($listequestion) > 1)
        $listequestion = substr($listequestion, 0, -2);
    $listequestion .= "'";
	
	
    echo '<input type="button" onClick="verif('.$listequestion.');" name="add" value="' . __('Add') . '" class="submit"/>';
    Html::closeForm();
} else {
    echo $LANG['plugin_formcreator']["target"][7];
}

echo "</div>" . "\n\r";

Html::footer();
?>