<?php
/**
 */

/**
 * A parameter for a type of question.
 */
abstract class PluginFormcreatorQuestionParameter
extends CommonDBChild
implements PluginFormcreatorQuestionParameterInterface, PluginFormcreatorExportableInterface
{

   // From CommonDBRelation
   static public $itemtype       = PluginFormcreatorQuestion::class;
   static public $items_id       = 'plugin_formcreator_questions_id';

   static public $disableAutoEntityForwarding   = true;

   /** @var string $fieldName the name of the field representing the parameter when editing the question */
   protected $fieldName = null;

   protected $label = null;

   protected $field;

   /** @var $domId string the first part of the DOM Id representing the parameter */
   protected $domId = '';

   /**
    * @param PluginFormcreatorFieldInterface $field Field
    * @param array $options
    *                - fieldName: name of the HTML input tag
    *                - label    : label for the parameter
    */
   public function __construct(PluginFormcreatorFieldInterface $field, array $options) {
      $fieldType = $field->getFieldTypeName();
      $fieldName = $options['fieldName'];
      $this->domId = $this->domId . "_{$fieldType}_{$fieldName}";
      $this->field = $field;
      $this->label = $options['label'];
      $this->fieldName = $options['fieldName'];
   }

   public function prepareInputforAdd($input) {
      $input = parent::prepareInputForAdd($input);
      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   /**
    * Export in an array all the data of the current instanciated condition
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $parameter = $this->fields;
      unset($parameter['id'],
            $parameter[PluginFormcreatorQuestion::getForeignKeyField()]);

      if ($remove_uuid) {
         $parameter['uuid'] = '';
      }

      return $parameter;
   }

   public static function import(PluginFormcreatorImportLinker $importLinker, $questions_id = 0, $fieldName = '', $parameter = []) {
      $parameter['plugin_formcreator_questions_id'] = $questions_id;

      // get a built instance of the parameter
      $question = new PluginFormcreatorquestion();
      $question->getFromDB($questions_id);
      $fieldType = 'PluginFormcreator' . ucfirst($question->fields['fieldtype']) . 'Field';
      $field = new $fieldType($question->fields);
      $parameters = $field->getUsedParameters();
      $item = $parameters[$fieldName];
      $found = $item->getFromDBByCrit([
         'plugin_formcreator_questions_id' => $questions_id,
         'fieldname' => $fieldName,
      ]);
      if (!$item->convertUuids($parameter)) {
         $importLinker->postponeImport($parameter['uuid'], $item->getType(), $parameter, $questions_id);
         return false;
      }

      if ($found) {
         $parameter['id'] = $item->getID();
         $item->update($parameter);
      } else {
         $item->add($parameter);
      }
      $importLinker->addImportedObject($item->fields['uuid'], $item);
   }

   /**
    * Covnerts IDs of related objects into their UUID
    * @param array $parameter
    */
   protected function convertIds(&$parameter) {}

   /**
    * Converts uuids of linked objects into ID
    * @param array $parameter
    * @return boolean true if success, or false otherwise
    */
   protected function convertUuids(&$parameter) {
      return true;
   }

   /**
    * Duplicates a parameter
    * @param PluginFormcreatorQuestion $newQuestion question which will contain the new parameter
    * @param array $tab_questions map old question ID => new question ID
    * @return PluginFormcreatorQuestionParameter new isntance (not saved in DB)
    */
   public function duplicate(PluginFormcreatorQuestion $newQuestion, array $tab_questions) {
      $parameter = new static($this->field, ['fieldName' => $this->fieldName, 'label' => $this->label]);
      $row = $this->fields;
      unset($row['id']);

      // Update the question ID linked to the parameter with the old/new question ID map
      $questionKey = PluginFormcreatorQuestion::getForeignKeyField();
      $row[$questionKey] = $tab_questions[$this->fields[$questionKey]];

      // return  the new instance, not saved yet in DB
      $parameter->fields = $row;
      return $parameter;
   }
}