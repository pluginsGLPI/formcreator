<?php
interface PluginFormcreatorQuestionParameterInterface {
   /**
    * Gets the HTML form part for the parameters
    * @param PluginFormcreatorForm $form a form used as context when displaying parameters
    * @param PluginFormcreatorQuestion $question question associated to the field, itself associated to the parameter
    * @return string HTML
    */
   public function getParameterForm(PluginFormcreatorForm $form, PluginFormcreatorQuestion $question);

   /**
    * Gets the Js selector containing the parameters to show or hide
    * @return string JS code
    */
   public function getJsShowHideSelector();

   /**
    * Gets the name of the parameter
    * @return string
    */
   public function getFieldName();

   /**
    * Gets the size of the parameter
    * Possible values are 0 for 2 table columns, or 1 for 4 table columns
    * @return integer
    */
   public function getParameterFormSize();

}