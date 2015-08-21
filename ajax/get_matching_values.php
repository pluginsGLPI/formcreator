<?php
include ('../../../inc/includes.php');

// If we don't have the ID of the question, we can't continue
if (empty($_POST['field_id']) || empty($_POST['field_value'])) {
   header('HTTP/1.1 400 Bad Request');
   echo __("Invalid parameters", 'formcreator');
   exit();
}

// === INITIALIZE THE QUESTION AND ITS SEARCH OPTIONS ===
// Get the question
$question = new PluginFormcreatorQuestion();
$question->getFromDB((int) $_POST['field_id']);

// Get class for User questions
if ('user' == $question->fields['fieldtype']) {
   $class = 'User';

// Get class for GLPI objects questions
} elseif ('glpiselect' == $question->fields['fieldtype']
      && (!empty($question->fields['values']))) {
   $class = $question->fields['values'];

// Can't get a class for other questions types
} else {
   header('HTTP/1.1 400 Bad Request');
   echo __("Invalid object", 'formcreator');
   exit();
}

// Get search options
$obj           = new $class();
$table         = getTableForItemType($class);
$searchOptions = $obj->getSearchOptions();
$objforeignkey = $obj->getForeignKeyField();
array_shift($searchOptions);
$obj->getFromDB($_POST['field_value']);

// === GET MATCHING VALUES ===
$response = array();

$query  = "SELECT `targets_id`, `fields`
            FROM `glpi_plugin_formcreator_matching_questions`
            WHERE `questions_id` = " . (int) $_POST['field_id'];
$result = $GLOBALS['DB']->query($query);
while ($line = $GLOBALS['DB']->fetch_assoc($result)) {

   $target = new PluginFormcreatorQuestion();
   $target->getFromDB($line['targets_id']);
   
   // Many to one connections
   if (isset($searchOptions[$line['fields']]['joinparams'])
         && isset($searchOptions[$line['fields']]['joinparams']['jointype'])
         && ('child' == $searchOptions[$line['fields']]['joinparams']['jointype'])) {
      
         if (isset($searchOptions[$line['fields']]['joinparams']['linkfield'])) {
            $fieldname = $searchOptions[$line['fields']]['joinparams']['linkfield'];
         } else {
            $fieldname = $searchOptions[$line['fields']]['field'];
         }
            
         $itemtype = getItemTypeForTable($searchOptions[$line['fields']]['table']);
         $itemobj = new $itemtype();
         $found = $itemobj->find('users_id_recipient = ' . $obj->fields['id']);

        if ('count' == $searchOptions[$line['fields']]['field']) {
            $value = count($found);
         } else {
            if (0 == count($found)) {
               $value = '';
            } else {
               if (in_array($target->fields['fieldtype'] , array('multiselect', 'checkboxes'))) {
                  $value = array();
                  foreach ($found as $object) {
                     $value[] = $object->fields[$searchOptions[$line['fields']]['field']];
                  }
               } else {
                  $first = array_shift($found);
                  $value = $first->fields[$searchOptions[$line['fields']]['field']];
               }
            }
         }
       
   // One to One with special conditions
   } elseif (isset($searchOptions[$line['fields']]['joinparams'])
         && isset($searchOptions[$line['fields']]['joinparams']['condition'])) {
      
      $str = $searchOptions[$line['fields']]['joinparams']['condition'];
      $substr = substr($str, strpos($str, '`') + 1);
      $field = trim(substr($substr, 0, strrpos($substr, '`')));
      $value = trim(substr($str, strrpos($str, '=') + 1));
      
      if ($obj->fields[$field] != $value) {
         $value = 0;
      } else {
         if ('dropdown' == $target->fields['fieldtype']) {
            $value = $obj->fields[$searchOptions[$line['fields']]['linkfield']];
         } else {
            $itemtype = getItemTypeForTable($searchOptions[$line['fields']]['table']);
            $itemtype::getFromDB($obj->fields[$searchOptions[$line['fields']]['linkfield']]);
            $value = $itemtype->fields[$searchOptions[$line['fields']]['field']];
         }
      }
      
   // Many to Many connections
   } elseif (isset($searchOptions[$line['fields']]['joinparams'])
         && isset($searchOptions[$line['fields']]['joinparams']['beforejoin'])) {
      
      $itemtype = getItemTypeForTable($searchOptions[$line['fields']]['table']);
         
      $target_table     = getTableForItemType($itemtype);
      $target_linkfield = getForeignKeyFieldForItemType($itemtype);
      $link_table       = $searchOptions[$line['fields']]['joinparams']['beforejoin']['table'];
      
      $sql2 = 'SELECT DISTINCT tab_target.name
              FROM `' . $target_table . '` tab_target
              LEFT JOIN `' . $link_table . '` tab_link ON tab_link.' . $target_linkfield . ' = tab_target.id
              LEFT JOIN `' . $table . '` tab_obj ON tab_link.' . $objforeignkey . ' = tab_obj.id 
              WHERE tab_obj.id = ' . $obj->getID() . ';';
      $result2 = $GLOBALS['DB']->query($sql2);
      
      $count = $GLOBALS['DB']->numrows($result2);
          
      // If Search option field request number of object, return number of rows
      if ('count' == $searchOptions[$line['fields']]['field']) {
          $value = $count;
          
       // Else return values
       } else {
          if (0 == $count) {
             $value = '';
          } else {
             if (in_array($target->fields['fieldtype'] , array('multiselect', 'checkboxes'))) {
                $value = array();
                while ($row = $GLOBALS['DB']->fetch_assoc($result2)) {
                   $value[] = $row['name'];
                }
             } else {
                $row = $GLOBALS['DB']->fetch_assoc($result2);
                $value = $row['name'];
             }
          }
       }
      
   // Inner fields or One to One connection without conditions
   } else {
      switch($searchOptions[$line['fields']]['datatype']) {
         case 'bool':
            $value = Dropdown::getYesNo($obj->fields[$searchOptions[$line['fields']]['field']]);
            break;
         case 'dropdown':
            if ('dropdown' == $target->fields['fieldtype']) {
               if ($table == $searchOptions[$line['fields']]['table']) {
                  $value = $obj->fields[$searchOptions[$line['fields']]['field']];
               } else {
                  $itemtype = getItemTypeForTable($searchOptions[$line['fields']]['table']);
                  $fkfield = $itemtype::getForeignKeyField();
                  $value = $obj->fields[$fkfield];
               }
            } else {
               $value = Dropdown::getDropdownName($obj->fields[$searchOptions[$line['fields']]['table']], $obj->fields[$searchOptions[$line['fields']]['field']]);
            }
            break;
         case 'specific' :
            $value = $obj->getValueToDisplay($line['fields'], $obj->fields[$searchOptions[$line['fields']]['field']]);
            break;
         default:
            if ($table == $searchOptions[$line['fields']]['table']) {
               $value = $obj->fields[$searchOptions[$line['fields']]['field']];
            } else {
               $itemtype = getItemTypeForTable($searchOptions[$line['fields']]['table']);
               $fkfield = $itemtype::getForeignKeyField();
               $fieldid = $obj->fields[$fkfield];
               $subobj = new $itemtype();
               $subobj->getFromDB($fieldid);
               $value = $subobj->fields[$searchOptions[$line['fields']]['field']];
            }
      }
   }

   $response['formcreator_field_' . $line['targets_id']] = array(
      'value' => $value,
      'type'  => $target->fields['fieldtype'],
   );
}

header('HTTP/1.1 200 OK');
header('Content-Type: application/json');

echo json_encode($response);