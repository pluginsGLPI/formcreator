<?php

include ('../../../inc/includes.php');

//anonyme or not ?
Session::checkLoginUser();

if (Session::getLoginUserID()) {
    Html::header($LANG['plugin_formcreator']['name'], $_SERVER['PHP_SELF'], "plugins", "formcreator", "form"
    );
} else {
    //$_SESSION["glpilanguage"] = $CFG_GLPI['language'];
    Html::simpleHeader($LANG['plugin_formcreator']['name2'], array(__('Authentication') => "../../../index.php?co=1",
        __('FAQ') => "../../../front/helpdesk.faq.php",
        $LANG['plugin_formcreator']['name2'] => "./formlist.php"));
}

function getFullForm($questions, $question, $content) {

    $out = '';

    foreach ($questions as $question_id => $question_value) {

        if ($question_value['type'] != 5 && $question_value['type'] != 6) {
            $question_name = "question_" . $question_id;
            $answer_name = "answer_" . $question_id;

            if (array_key_exists($question_name, $question)) {
                $out.= $question_value['name'] . ' : ' . $question[$question_name] . "\n";
            }
        }
    }

    return $out;
}

function processIfs($string, $question) {  
	if (preg_match_all("/##IF([a-z\_0-9]*)[=]?(.*?)##/i",$string,$out)) {
		foreach ($out[1] as $key => $tag_infos) {
			$if_field = $tag_infos;
			preg_match("/([0-9]+)/i",$if_field,$matches);
			$question_name = "question_".$matches[1];
			//Get the field tag value (if one)
			$regex_if = "/##IF".$if_field."[=]?.*##(.*)##ENDIF".$if_field."##/Uis";
			//Get the else tag value (if one)
			$regex_else = "/##ELSE".$if_field."[=]?.*##(.*)##ENDELSE".$if_field."##/Uis";

			if (empty($out[2][$key]) && !strlen($out[2][$key]) ) { // No = : check if ot empty or not null
				if (isset($question[$question_name])
				&& $question[$question_name] != ''
				&& $question[$question_name] != '&nbsp;'
				&& !is_null($question[$question_name])) {
					$condition_ok = true;
				} else {
					$condition_ok = false;
				}
			} else { // check exact match
				if (isset($question[$question_name])
				&& Html::entity_decode_deep($question[$question_name]) == Html::entity_decode_deep($out[2][$key])) {
					$condition_ok = true;
				} else {
					$condition_ok = false;
				}
			}        
			
			// Force only one replacement to permit multiple use of the same condition
			if ($condition_ok) { // Do IF
				$string = preg_replace($regex_if, "\\1", $string,1);
				$string = preg_replace($regex_else, "",  $string,1);

			} else { // Do ELSE
				$string = preg_replace($regex_if, "", $string,1);
				$string = preg_replace($regex_else, "\\1",  $string,1);
			}
		}
	}
	return $string;
}

echo "<div class='center'>" . "\n\r";

$helpdesk = new PluginFormcreatorHelpdesk;
$formID = $_REQUEST['id'];

$form = new PluginFormcreatorForm;
$form->getFromDB($formID);
$tableau_beneficiary_name = "";
$targets = $helpdesk->getTarget($formID);

foreach ($targets as $target_id => $target_value) {

    $ticket = array();

    $validation_exist = false;
	$validationTab = array();
	$cpt_valid = 0;

    $ticket['entities_id'] = $form->fields['entities_id'];
    $ticket['urgency'] = $target_value['urgency'];
    $ticket['priority'] = $target_value['priority'];
    $ticket['itilcategories_id'] = $target_value['itilcategories_id'];

    $ticket['name'] = $target_value['name'];

    $questions = $helpdesk->getQuestionByForm($formID);

    foreach ($questions as $question_id => $question_value) {

        $question_name = 'question_' . $question_id;

        if ((isset($_REQUEST[$question_name])) || (isset($_REQUEST['question1_' . $question_id]))) {

            switch ($question_value['type']) {

                case PluginFormcreatorQuestion::TEXT_FIELD:

                    $question_option = json_decode($question_value['option'], true);
                    $question_option_type = $question_option['type'];
                    if ($question_option_type == 8)
                        $code_capex = $_REQUEST[$question_name];
                    $question_option_value = urldecode($question_option['value']);
                    $question[$question_name] = $_REQUEST[$question_name];
                    break;

                case PluginFormcreatorQuestion::TEXTAREA_FIELD:
                    $question[$question_name] = $_REQUEST[$question_name];
                    break;

                case PluginFormcreatorQuestion::SELECT_FIELD:
                    $question[$question_name] = $_REQUEST[$question_name];
                    break;

                case PluginFormcreatorQuestion::CHECKBOX_FIELD:

                    $reponse = '';
                    foreach ($_REQUEST[$question_name] as $key => $value) {
                        $reponse.= $value . ', ';
                    }

                    $question[$question_name] = substr($reponse, 0, -2);
                    break;

                case PluginFormcreatorQuestion::VALIDATION_FIELD:

                    $validation_exist = true;

                    $validationTab[$cpt_valid]['users_id_validate'] = $_REQUEST['users_id_validate_' . $question_id];
                    $validationTab[$cpt_valid]['entities_id'] = $form->fields['entities_id'];
                    $validationTab[$cpt_valid]['comment_submission'] = $_POST[$question_name];
                    $validationTab[$cpt_valid]['user_id'] = Session::getLoginUserID();
					$cpt_valid++;
                    break;

                case PluginFormcreatorQuestion::MULTIPLICATION_ITEM_FIELD:
                    $question[$question_name] = $_REQUEST["question1_" . $question_id] . " - " . $_REQUEST["question3_" . $question_id];
                    break;

                case PluginFormcreatorQuestion::DYNAMIC_FIELD:
                    $reponse = explode("&&", $_REQUEST[$question_name]);
                    $question[$question_name] = $reponse[0];
                    break;

                case PluginFormcreatorQuestion::DYNAMIC_SECTION:
                    $reponse = explode("&&", $_REQUEST[$question_name]);
                    $question[$question_name] = $reponse[0];
                    break;
					
				case PluginFormcreatorQuestion::ITEM:
                    $question[$question_name] = $_REQUEST[$question_name];
                    break;
            }
        } else {
            $question[$question_name] = $LANG['plugin_formcreator']["helpdesk"][4];
        }
    }

    $ticket['content'] = processIfs($target_value['content'], $question);

    foreach ($questions as $question_id => $question_value) {
        $question_name = "question_" . $question_id;
        $answer_name = "answer_" . $question_id;

        if (array_key_exists($question_name, $question)) {
            if (empty($question[$question_name])) {
                $ticket['content'] = str_replace("##$question_name##", " ", $ticket['content']);
                $ticket['content'] = str_replace("##$answer_name##", " ", $ticket['content']);
            } else {
                $ticket['content'] = str_replace("##$question_name##", $question_value['name'], $ticket['content']);
                $ticket['content'] = str_replace("##$answer_name##", $question[$question_name], $ticket['content']);
            }
            $ticket['name'] = str_replace("##$question_name##", $question_value['name'], $ticket['name']);
            $ticket['name'] = str_replace("##$answer_name##", $question[$question_name], $ticket['name']);
        }
    }
	
    $ticket['content'] = str_replace('##FULLFORM##', getFullForm($questions, $question, $ticket['content']), $ticket['content']);
    $ticket['content'] = PluginFormcreatorQuestion::protectData($ticket['content']);

	//modif yt pour prendre en compte valeurs prédéfinies du gabarit**********;

	$query = "SELECT `tickettemplates_id_incident`,`tickettemplates_id_demand` FROM `glpi_itilcategories` WHERE ID= ".$ticket['itilcategories_id'];
	$res=$DB->query($query);
	$tickettemplates_id_demand=$DB->result($res, 0, "tickettemplates_id_demand");
	$tickettemplates_id_incident=$DB->result($res, 0, "tickettemplates_id_incident");
    switch ($target_value['type'])
	{
		case "1":
			// formulaire par defaut pour les incidents de la catégorie;
			$template_id = $tickettemplates_id_incident;
			break;
			
		case "2":
			// formulaire par defaut pour les demandes de la catégorie;
			$template_id = $tickettemplates_id_demand;
			break;
			
		default:
			// sans précision le formulaire est indiqué par défaut;
			$template_id = "1";//formulaire par defaut;
			break;
	}
                
	$tt = new TicketTemplate();            
	$tt->getFromDBWithDatas($template_id, true);
	$ttp = new TicketTemplatePredefinedField();
	$predefined = $ttp->getPredefinedFields($template_id, true);

	if (isset($predefined['itemtype'])) {
		$ticket['itemtype'] = $predefined['itemtype'];
	}
	if (isset($predefined['items_id'])) {
		$ticket['items_id'] = $predefined['items_id'];
	}
	if (isset($predefined['_users_id_observer'])) {
		$ticket['_users_id_observer'] = $predefined['_users_id_observer'];
	}
	if (isset($predefined['_users_id_assign'])) {
		$ticket['_users_id_assign'] = $predefined['_users_id_assign'];
	}
	if (isset($predefined['_users_id_requester'])) {
		$ticket['_users_id_requester'] = $predefined['_users_id_requester'];
	}
	if (isset($predefined['_groups_id_requester'])) {
		$ticket['_groups_id_requester'] = $predefined['_groups_id_requester'];
	}
	if (isset($predefined['suppliers_id_assign'])) {
		$ticket['suppliers_id_assign'] = $predefined['suppliers_id_assign'];
	}
	if (isset($predefined['requesttypes_id'])) {
		$ticket['requesttypes_id'] = $predefined['requesttypes_id'];
	}

	// **********fin modif yt pour prendre en compte valeurs prédéfinies du gabarit**********;

    $user = new User;
	$user->getFromDB(Session::getLoginUserID());

	$ticket['users_id_recipient'] = $user->fields['id'];
	$ticket['users_id_lastupdater'] = $user->fields['id'];
	$ticket['type'] = $target_value['type'];
	
	$ticket['name'] = str_replace("'", "\'", $ticket['name']);

    $track = new Ticket();
    $ticketID = $track->add($ticket);

    if ($validation_exist) {
		for ($cpt_valid = 0 ; $cpt_valid < count($validationTab) ; $cpt_valid++) {
			$validationTab[$cpt_valid]['tickets_id'] = $ticketID;
			$validation = new Ticketvalidation();
			$validation->add($validationTab[$cpt_valid]);
		}
    }

    $sections = new PluginFormcreatorSection;
    $sections = $sections->find("plugin_formcreator_targets_id = '$target_id'");

    foreach ($sections as $section_id => $section_value) {

        $questions = $helpdesk->getQuestionBySectionTypeFile($section_id);

        foreach ($questions as $question_id => $question_value) {
            $question_name = "question_" . $question_id;

            if (array_key_exists($question_name, $_FILES)) {

                if ($_FILES[$question_name]['error'] != 4) {
                    $helpdesk->addFilesTickets($ticketID, $question_name, $ticket['entities_id']);
                }
            }
        }
    }
}
unset($_FILES);
if (Session::getLoginUserID())
    Session::addMessageAfterRedirect($LANG['plugin_formcreator']["helpdesk"][3], false, INFO);
else
    echo '<SCRIPT type="text/javascript">alert("' . $LANG['plugin_formcreator']["helpdesk"][3] . '");</SCRIPT>';
Html::redirect($CFG_GLPI["root_doc"] . "/plugins/formcreator/front/formlist.php");

echo "</div>" . "\n\r";

Html::footer();
?>