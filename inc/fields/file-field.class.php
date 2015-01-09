<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class fileField implements Field
{
   public static function show($field, $datas, $edit = true)
   {
      if($field['required'])  $required = ' required';
      else $required = '';

      if (!$edit) {
         echo '<div class="form-group" id="form-group-field' . $field['id'] . '">';
         echo '<label>' . $field['name'] . '</label>';
         $doc = new Document();
         if($doc->getFromDB($datas['formcreator_field_' . $field['id']])) {
            echo $doc->getDownloadLink();
         }
         echo '</div>' . PHP_EOL;
         return;
      }

      echo '<div class="form-group' . $required . '" id="form-group-field' . $field['id'] . '">';
         echo '<label>';
         echo  $field['name'];
         if($field['required'])  echo ' <span class="red">*</span>';
         echo '<br /><small>(' . __('File size:', 'formcreator') . ' ' . Document::getMaxUploadSize() . ')</small>';
         echo '</label>';


         echo '<input type="hidden" class="form-control"
                  name="formcreator_field_' . $field['id'] . '" value="" />' . PHP_EOL;

         echo '<input type="file" class="form-control"
                  name="formcreator_field_' . $field['id'] . '"
                  id="formcreator_field_' . $field['id'] . '"' . $required . ' />';
         echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "");</script>';

         echo '<div class="help-block">' . html_entity_decode($field['description']) . '</div>';
      echo '</div>' . PHP_EOL;
   }

   public static function displayValue($value, $values)
   {
      return '';
   }

   public static function isValid($field, $value, $datas)
   {
      // If the field are hidden, don't test it
      if (($field['show_type'] == 'hide') && isset($datas['formcreator_field_' . $field['show_field']])) {
         $hidden = true;

         switch ($field['show_condition']) {
            case 'notequal':
               if ($field['show_value'] != $datas['formcreator_field_' . $field['show_field']])
                  $hidden = false;
               break;
            case 'lower':
               if ($field['show_value'] < $datas['formcreator_field_' . $field['show_field']])
                  $hidden = false;
               break;
            case 'greater':
               if ($field['show_value'] > $datas['formcreator_field_' . $field['show_field']])
                  $hidden = false;
               break;

            default:
               if ($field['show_value'] == $datas['formcreator_field_' . $field['show_field']])
                  $hidden = false;
               break;
         }

         if ($hidden) return true;
      }

      // Not required or not empty
      if($field['required'] && (empty($_FILES['formcreator_field_' . $field['id']]['tmp_name'])
                                 || !is_file($_FILES['formcreator_field_' . $field['id']]['tmp_name']))) {
         Session::addMessageAfterRedirect(__('A required file is missing:', 'formcreator') . ' ' . $field['name'], false, ERROR);
         return false;

      // All is OK
      } else {
         return true;
      }
   }

   public static function getName()
   {
      return __('File');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['file'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
