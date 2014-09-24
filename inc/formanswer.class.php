<?php
class PluginFormcreatorFormanswer extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorForm";
   static public $items_id = "plugin_formcreator_forms_id";

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate()
   {
      return true;
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView()
   {
      return true;
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0)
   {
      return _n('Form answer', 'Form answers', $nb, 'formcreator');
   }

   /**
    * Define search options for forms
    *
    * @return Array Array of fields to show in search engine and options for each fields
    */
   public function getSearchOptions()
   {
      $tab = array(
         '1' => array(
            'table'         => $this->getTable(),
            'field'         => 'status',
            'name'          => _n('Status', 'Statuses', 1),
            'searchtype'    => array('equals', 'notequals'),
            'datatype'      => 'specific',
            'massiveaction' => false,
         ),
         '2' => array(
            'table'         => $this->getTable(),
            'field'         => 'id',
            'name'          => __('ID'),
            'searchtype'    => 'contains',
            'datatype'      => 'itemlink',
            'massiveaction' => false,
         ),
         '3' => array(
            'table'         => getTableForItemType('PluginFormcreatorForm'),
            'field'         => 'name',
            'name'          => PluginFormcreatorForm::getTypeName(1),
            'datatype'      => 'itemlink',
            'massiveaction' => false,

         ),
         '4' => array(
            'table'         => getTableForItemType('User'),
            'field'         => 'name',
            'name'          => __('Requester', 'formcreator'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
            'linkfield'     => 'requester_id',

         ),
         '5' => array(
            'table'         => getTableForItemType('User'),
            'field'         => 'name',
            'name'          => __('Validator', 'formcreator'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
            'linkfield'     => 'validator_id',

         ),
         '6' => array(
            'table'         => $this->getTable(),
            'field'         => 'request_date',
            'name'          => __('Creation date'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
            'linkfield'     => 'validator_id',
         ),
      );
      return $tab;
   }

   // /**
   //  * Define default search request
   //  *
   //  * @return Array Array of search options : [field, searchtype, contains, sort, order]
   //  */
   // public static function getDefaultSearchRequest()
   // {
   //    $search = array('field'      => array(0 => 30),
   //                    'searchtype' => array(0 => 'equals'),
   //                    'contains'   => array(0 => 30),
   //                    'sort'       => 2,
   //                    'order'      => 'ASC');
   //    return $search;
   // }

   /**
    * Define how to display search field for a specific type
    *
    * @since version 0.84
    *
    * @param String $field           Name of the field as define in $this->getSearchOptions()
    * @param String $name            Name attribute for the field to be posted (default '')
    * @param Array  $values          Array of all values to display in search engine (default '')
    * @param Array  $options         Options (optional)
    *
    * @return String                 Html string to be displayed for the form field
    **/
   public static function getSpecificValueToSelect($field, $name='', $values='', array $options=array())
   {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      $options['display'] = false;

      switch ($field) {
         case 'status' :
            $output  = '<select name="' . $name . '">';
            $output .=  '<option value="waiting" '
                           . (($values[$field] == 'waiting') ? ' selected ' : '') . '>'
                        . __('waiting', 'formcreator')
                        . '</option>';
            $output .=  '<option value="accepted" '
                           . (($values[$field] == 'accepted') ? ' selected ' : '') . '>'
                        . __('accepted', 'formcreator')
                        . '</option>';
            $output .=  '<option value="refused" '
                           . (($values[$field] == 'refused') ? ' selected ' : '') . '>'
                        . __('refused', 'formcreator')
                        . '</option>';
            $output .=  '</select>';

            return $output;
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   /**
    * Define how to display a specific value in search result table
    *
    * @param  String $field   Name of the field as define in $this->getSearchOptions()
    * @param  Mixed  $values  The value as it is stored in DB
    * @param  Array  $options Options (optional)
    * @return Mixed           Value to be displayed
    */
   public static function getSpecificValueToDisplay($field, $values, array $options=array())
   {
      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'status' :
            $output = '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/' . $values[$field] . '.png"
                         alt="' . __($values[$field], 'formcreator') . '" title="' . __($values[$field], 'formcreator') . '" />';
            return $output;
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForAdd($input)
   {

      return $input;
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForUpdate($input)
   {

      return $input;
   }

   public function saveAnswers($datas)
   {
      $accepted = false;

      $form = new PluginFormcreatorForm();
      $form->getFromDB($datas['formcreator_form']);

      // Does the form need to be validate ?
      if ($form->fields['validation_required']) {
         $status = 'waiting';
      } else {
         $status = 'accepted';
      }

      $obj        = new self();
      $formanswer = $obj->add(array(
         'entities_id'                 => isset($_SESSION['glpiactive_entity'])
                                             ? $_SESSION['glpiactive_entity']
                                             : $form->fields['entities_id'],
         'is_recursive'                => $form->fields['is_recursive'],
         'plugin_formcreator_forms_id' => $datas['formcreator_form'],
         'requester_id'                => isset($_SESSION['glpiID'])
                                             ? $_SESSION['glpiID']
                                             : 0,
         'validator_id'                => isset($datas['formcreator_validator'])
                                             ? $datas['formcreator_validator']
                                             : 0,
         'status'                      => $status,
         'request_date'                => date('Y-m-d H:i:s'),
      ));

      $query = "SELECT q.`id`
                FROM glpi_plugin_formcreator_questions q
                LEFT JOIN glpi_plugin_formcreator_sections s ON s.`id` = q.`plugin_formcreator_sections_id`
                WHERE s.`plugin_formcreator_forms_id` = {$datas['formcreator_form']}";
      $result = $GLOBALS['DB']->query($query);

      while ($question = $GLOBALS['DB']->fetch_array($result)) {
         $answer = new PluginFormcreatorAnswer();
         $answer->add(array(
            'plugin_formcreator_formanwers_id' => $formanswer,
            'plugin_formcreator_question_id'   => $question['id'],
            'answer'                           => isset($datas['formcreator_field_' . $question['id']])
                                                   ? is_array($datas['formcreator_field_' . $question['id']])
                                                      ? implode(',', $datas['formcreator_field_' . $question['id']])
                                                      : $datas['formcreator_field_' . $question['id']]
                                                   : '',
         ));
      }

      // If form is accepted, generate targets
      if ($status == 'accepted') {
         $_SESSION['formcreator_documents'] = array();

         // Save files as Documents
         foreach ($_FILES as $question_name => $file) {
            if (isset($file['tmp_name']) && is_file($file['tmp_name'])) {
               $doc         = new Document();
               $question_id = trim(strrchr($question_name, '_'), '_');
               $question    = new PluginFormcreatorQuestion();
               $question->getFromDB($question_id);

               $file_datas                 = array();
               $file_datas["name"]         = $this->fields['name'] . ' - ' . $question->fields['name'];
               $file_datas["entities_id"]  = (isset($_SESSION['glpiactive_entity']))
                                             ? $_SESSION['glpiactive_entity']
                                             : $this->fields['entities_id'];
               $file_datas["is_recursive"] = $this->fields['is_recursive'];
               Document::uploadDocument($file_datas, $file);

               if ($docID = $doc->add($file_datas)) {
                  $_SESSION['formcreator_documents'][] = $docID;
                  $table = getTableForItemType('Document');
                  $query = "UPDATE $table SET filename = '" . addslashes($file['name']) . "' WHERE id = " . $docID;
                  $GLOBALS['DB']->query($query);
               }
            }
         }

         // Get all targets
         $target_class    = new PluginFormcreatorTarget();
         $founded_targets = $target_class->find('plugin_formcreator_forms_id = ' . $this->getID());

         foreach($founded_targets as $target) {
            $obj = new $target['itemtype'];
            $obj->getFromDB($target['items_id']);
            $obj->save($this, $datas);
         }

         Session::addMessageAfterRedirect(__('The form have been successfully saved!', 'formcreator'), true, INFO);
         unset($_SESSION['formcreator_documents']);
      }
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   public static function install(Migration $migration)
   {
      $obj   = new self();
      $table = $obj->getTable();

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create questions table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `entities_id` int(11) NOT NULL DEFAULT '0',
                     `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                     `plugin_formcreator_forms_id` int(11) NOT NULL,
                     `requester_id` int(11) NULL,
                     `validator_id` int(11) NULL,
                     `request_date` datetime NOT NULL,
                     `status` enum('waiting', 'refused', 'accepted') NOT NULL DEFAULT 'waiting'
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
      }

      return true;
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   public static function uninstall()
   {
      $obj = new self();
      $GLOBALS['DB']->query('DROP TABLE IF EXISTS `' . $obj->getTable() . '`');

      // Delete logs of the plugin
      $GLOBALS['DB']->query('DELETE FROM `glpi_logs` WHERE itemtype = "' . __CLASS__ . '"');

      return true;
   }
}
