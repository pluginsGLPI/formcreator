<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class textField implements Field
{
	public static function show($field, $datas)
   {
      $value = (!empty($datas['formcreator_field_' . $field['id']]))
               ? $datas['formcreator_field_' . $field['id']]
               : $field['default_values'];

      if($field['required'])  $required = ' required';
      else $required = '';

      $hide = ($field['show_type'] == 'hide') ? ' style="display: none"' : '';
      echo '<div class="form-group' . $required . '" id="form-group-field' . $field['id'] . '"' . $hide . '>';
      echo '<label>';
      echo  $field['name'];
      if($field['required'])  echo ' <span class="red">*</span>';
      echo '</label>';

      $value = htmlentities(stripslashes(strip_tags(html_entity_decode($value))));
      echo '<input type="text" class="form-control"
               name="formcreator_field_' . $field['id'] . '"
               id="formcreator_field_' . $field['id'] . '"
               value="' . $value . '" />';

      echo '<div class="help-block">' . html_entity_decode($field['description']) . '</div>';

      switch ($field['show_condition']) {
         case 'notequal':
            $condition = '!=';
            break;
         case 'lower':
            $condition = '<';
            break;
         case 'greater':
            $condition = '>';
            break;

         default:
            $condition = '==';
            break;
      }

      if ($field['show_type'] == 'hide') {
         echo '<script type="text/javascript">
                  document.getElementsByName("formcreator_field_' . $field['show_field'] . '")[0].addEventListener("change", function(){showFormGroup' . $field['id'] . '()});
                  function showFormGroup' . $field['id'] . '() {
                     var field_value = document.getElementsByName("formcreator_field_' . $field['show_field'] . '")[0].value;

                     if(field_value ' . $condition . ' "' . $field['show_value'] . '") {
                        document.getElementById("form-group-field' . $field['id'] . '").style.display = "block";
                     } else {
                        document.getElementById("form-group-field' . $field['id'] . '").style.display = "none";
                     }
                  }
                  showFormGroup' . $field['id'] . '();
               </script>';
      }

      echo '</div>' . PHP_EOL;
	}

   public static function displayValue($value, $values)
   {
      return $value;
   }

	public static function isValid($field, $value, $datas)
   {
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
      } else {
         $hidden = false;
      }

      // Not required or not empty
      if($field['required'] && empty($value) && !$hidden) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $field['name'], false, ERROR);
         return false;

      // Min range not set or text length longer than min length
      } elseif(!empty($field['range_min']) && strlen($value) < $field['range_min']) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too short (minimum %d characters):', 'formcreator'), $field['range_min']) . ' ' . $field['name'], false, ERROR);
         return false;

      // Max range not set or text length shorter than max length
      } elseif(!empty($field['range_max']) && strlen($value) > $field['range_max']) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too long (maximum %d characters):', 'formcreator'), $field['range_max']) . ' ' . $field['name'], false, ERROR);
         return false;

      // Specific format not set or well match
      } elseif(!empty($field['regex']) && !preg_match($field['regex'], $value)) {
         Session::addMessageAfterRedirect(__('Specific format does not match:', 'formcreator') . ' ' . $field['name'], false, ERROR);
         return false;

      // All is OK
		} else {
			return true;
		}
	}

   public static function getName()
   {
      return __('Text', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 1,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['text'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
