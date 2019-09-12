<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorGlpiselectField extends PluginFormcreatorDropdownField
{
   public function isPrerequisites() {
      return true;
   }

   public function getDesignSpecializationField() {
      $rand = mt_rand();

      $label = '<label for="dropdown_glpi_objects'.$rand.'" id="label_dropdown_values">';
      $label .= _n('GLPI object', 'GLPI objects', 1, 'formcreator');
      $label .= '</label>';

      $optgroup = [
         __("Assets") => [
            Computer::class         => Computer::getTypeName(2),
            Monitor::class          => Monitor::getTypeName(2),
            Software::class         => Software::getTypeName(2),
            Networkequipment::class => Networkequipment::getTypeName(2),
            Peripheral::class       => Peripheral::getTypeName(2),
            Printer::class          => Printer::getTypeName(2),
            Cartridgeitem::class    => Cartridgeitem::getTypeName(2),
            Consumableitem::class   => Consumableitem::getTypeName(2),
            Phone::class            => Phone::getTypeName(2),
            Line::class             => Line::getTypeName(2)],
         __("Assistance") => [
            Ticket::class           => Ticket::getTypeName(2),
            Problem::class          => Problem::getTypeName(2),
            TicketRecurrent::class  => TicketRecurrent::getTypeName(2)],
         __("Management") => [
            Budget::class           => Budget::getTypeName(2),
            Supplier::class         => Supplier::getTypeName(2),
            Contact::class          => Contact::getTypeName(2),
            Contract::class         => Contract::getTypeName(2),
            Document::class         => Document::getTypeName(2),
            Project::class          => Project::getTypeName(2)],
         __("Tools") => [
            Reminder::class         => __("Notes"),
            RSSFeed::class          => __("RSS feed")],
         __("Administration") => [
            User::class             => User::getTypeName(2),
            Group::class            => Group::getTypeName(2),
            Entity::class           => Entity::getTypeName(2),
            Profile::class          => Profile::getTypeName(2)],
      ];
      array_unshift($optgroup, '---');
      $field = Dropdown::showFromArray('glpi_objects', $optgroup, [
         'value'     => $this->question->fields['values'],
         'rand'      => $rand,
         'on_change' => 'plugin_formcreator_changeGlpiObjectItemType();',
         'display'   => false,
      ]);

      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values'.$rand.'">';
      $additions .= __('Default values');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td id="dropdown_default_value_field">';
      $additions .= '</td>';
      $additions .= '<td></td>';
      $additions .= '<td></td>';
      $additions .= '</tr>';
      $additions .= Html::scriptBlock("plugin_formcreator_changeGlpiObjectItemType($rand);");
      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => true,
         'may_be_required' => true,
      ];
   }

   public static function getName() {
      return _n('GLPI object', 'GLPI objects', 1, 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['glpi_objects'])) {
         if (empty($input['glpi_objects'])) {
            Session::addMessageAfterRedirect(
                  __('The field value is required:', 'formcreator') . ' ' . $input['name'],
                  false,
                  ERROR);
            return [];
         }
         $input['values']         = $input['glpi_objects'];
         $this->value = isset($input['dropdown_default_value']) ? $input['dropdown_default_value'] : '';
      }
      return $input;
   }

   public function isValid() {
      // If the field is required it can't be empty (0 is a valid value for entity)
      $itemtype = $this->question->fields['values'];
      $item = new $itemtype();
      if ($this->isRequired() && $item->isNewID($this->value)) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public static function canRequire() {
      return true;
   }

   public function equals($value) {
      $value = html_entity_decode($value);
      $itemtype = $this->question->fields['values'];
      $item = new $itemtype();
      if ($item->isNewId($this->value)) {
         return ($value === '');
      }
      if (!$item->getFromDB($this->value)) {
         throw new PluginFormcreatorComparisonException('Item not found for comparison');
      }
      return $item->getField($item->getNameField()) == $value;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      $value = html_entity_decode($value);
      $itemtype = $this->question->fields['values'];
      $item = new $itemtype();
      if (!$item->getFromDB($this->value)) {
         throw new PluginFormcreatorComparisonException('Item not found for comparison');
      }
      return $item->getField($item->getNameField()) > $value;
   }

   public function lessThan($value) {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function isAnonymousFormCompatible() {
      return false;
   }

   /**
    * Check for object properties placeholder to commpute.
    * The expected format is ##answer_X.search_option_english_label##
    *
    * We use search option to be able to access data that may be outside
    * the given object in a generic way (e.g. email adresses for user,
    * this is data that is not stored in the user table. The searchOption
    * will give us the details on how to retrieve it).
    *
    * We also have a direct link between each searchOptions and their
    * labels that also us to use the label in the placeholder.
    * The user only need to look at his search menu to find the available
    * fields.
    *
    * Since we look for a searchOption by its name, it is not impossible
    * to find duplicates (they will usually by in differents groups in the
    * search dropdown).
    * For now we will use the first result.
    * An improvement would be to allow the user to specify the group in
    * the placeholder :
    * ##answer_X.search_option_group.search_option_english_label##
    * If not specified, search_option_group would be the default "common"
    * group.
    *
    * @param PluginFormCreatorQuestion $question
    * @param PluginFormCreatorAnswer   $answer
    * @param string                    $content
    *
    * @return string
    */
   public function parseObjectProperties(
      $answer,
      $content
   ) {
      global $TRANSLATE;

      // Get ID from question
      // $questionID = $question->fields['id'];
      $questionID = $this->getQuestionId();

      // We need english locale to search searchOptions by name
      $oldLocale = $TRANSLATE->getLocale();
      $TRANSLATE->setLocale("en_GB");

      // Load target item from DB
      // $itemtype = $question->getField('values');
      $itemtype = $this->question->fields['values'];
      $item = new $itemtype;
      $item->getFromDB($answer);

      // Search for placeholders
      $matches = [];
      $regex = "/##answer_$questionID\.(?<property>[a-zA-Z0-9_.]+)##/";
      preg_match_all($regex, $content, $matches);

      // For each placeholder found
      foreach ($matches["property"] as $property) {
         $placeholder = "##answer_$questionID.$property##";
         // Convert Property_Name to Property Name
         $property = str_replace("_", " ", $property);
         $searchOption = $item->getSearchOptionByField("name", $property);

         // Execute search
         $data = Search::prepareDatasForSearch(get_class($item), [
            'criteria' => [
               [
                  'field'      => $searchOption['id'],
                  'searchtype' => "contains",
                  'value'      => "",
               ],
               [
                  'field'      => 2,
                  'searchtype' => "equals",
                  'value'      => $answer,
               ]
            ]
         ]);
         Search::constructSQL($data);
         Search::constructData($data);

         // Handle search result, there may be multiple values
         $propertyValue = "";
         foreach ($data['data']['rows'] as $row) {
            $targetKey = get_class($item) . "_" . $searchOption['id'];
            // Add each result
            for ($i=0; $i < $row[$targetKey]['count']; $i++) {
               $propertyValue .=$row[$targetKey][$i]['name'];
               if ($i+1 < $row[$targetKey]['count']) {
                  $propertyValue .= ", ";
               }
            }
         }

         // Replace placeholder in content
         $content = str_replace(
            $placeholder,
            Toolbox::addslashes_deep($propertyValue),
            $content
         );
      }
      // Put the old locales on succes or if an expection was thrown
      $TRANSLATE->setLocale($oldLocale);
      return $content;
   }
}
