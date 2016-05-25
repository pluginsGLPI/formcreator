<?php
class dropdownField extends PluginFormcreatorField
{
   public function displayField($canEdit = true)
   {
      global $DB;

      if ($canEdit) {
         $rand     = mt_rand();
         $required = $this->fields['required'] ? ' required' : '';

         echo '<div class="form_field">';
         if(!empty($this->fields['values'])) {
            $values = array();
            if ($this->fields['show_empty']) $values[0] = Dropdown::EMPTY_VALUE;

            $obj = new $this->fields['values']();
            $obj->getEmpty();

            $where = '';
            $whereTab = array();
            if (isset($obj->fields['is_deleted'])) {
               $whereTab[] = '`is_deleted` = 0';
            }
            if (isset($obj->fields['is_active'])) {
               $whereTab[] = '`is_active` = 1';
            }
            $table = getTableForItemType($this->fields['values']);
            if (isset($obj->fields['entities_id'])) {
               $whereTab[] = getEntitiesRestrictRequest('', $table);
            }
            $where = implode(' AND ', $whereTab);

            $order = 'name';
            if (isset($obj->fields['completename'])) {
               $order = 'completename';
            }

            // specific way to retrieve data for users
            if ($obj instanceof User) {
               $query_user = "SELECT DISTINCT `glpi_users`.`id`,
                                     `glpi_users`.`name`
                              FROM `glpi_users`
                              INNER JOIN `glpi_profiles_users`
                                ON (`glpi_users`.`id` = `glpi_profiles_users`.`users_id`)
                              WHERE `glpi_users`.`is_active`  = 1
                                AND `glpi_users`.`is_deleted` = 0
                                AND ".getEntitiesRestrictRequest('', "glpi_profiles_users")."
                              ORDER BY `glpi_users`.`name`";
               $res_user = $DB->query($query_user);
               while ($data_user = $DB->fetch_assoc($res_user)) {
                  $result[$data_user['id']] = $data_user;
               }

            // Common way to retrieve data
            } else {
               $result = $obj->find($where, $order);
            }
            foreach ($result AS $id => $datas) {
               if ($this->fields['values'] == 'User') {
                  $values[$id] = getUserName($id);
               } else {
                  $values[$id] = $datas['name'];
               }
            }

            Dropdown::showFromArray('formcreator_field_' . $this->fields['id'], $values, array(
               'value'               => $this->getValue(),
               'comments'            => false,
               'rand'                => $rand,
            ));
         }
         echo '</div>' . PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("select2-selecting", function(e) {
                        formcreatorChangeValueOf (' . $this->fields['id']. ', e.val);
                     });
                  });
               </script>';
      } else {
         echo $this->getAnswer();
      }
   }

   public function getAnswer()
   {
      $value = $this->getValue();
      if ($this->fields['values'] == 'User') {
         return getUserName($value);
      } else {
         return Dropdown::getDropdownName(getTableForItemType($this->fields['values']), $value);
      }
   }

   public static function getName()
   {
      return _n('Dropdown', 'Dropdowns', 1);
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 1,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['dropdown'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
