<?php

class PluginFormcreatorFormlist extends CommonGLPI
{

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0)
   {
      return _n('Form', 'Forms', $nb, 'formcreator');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu = parent::getMenuContent();
      $image = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/check.png"
                  title="' . __('Forms waiting for validation', 'formcreator') . '"
                  alt="' . __('Forms waiting for validation', 'formcreator') . '">';

      $menu['links']['search'] = PluginFormcreatorFormList::getSearchURL(false);
      if (PluginFormcreatorForm::canCreate()) {
         $menu['links']['add'] = PluginFormcreatorForm::getFormURL(false);
      }
      $menu['links']['config'] = PluginFormcreatorForm::getSearchURL(false);
      $menu['links'][$image]   = PluginFormcreatorFormanswer::getSearchURL(false);

      return $menu;
   }
   
   /**
    * returns the forms in the category subtree
    * @param integer $categoryId category id of the subtree to search into  
    */
   public static function getHtmlFormListForCategory($categoryId) {
      // Find all categories in the subtree
      $categories = getSonsOf(PluginFormcreatorCategory::getTable(), 'plugin_formcreator_categories_id');
      $categoryIdList = implode(', ', array_keys($categories));
      
      // Find forms in the subtree
      $form = new PluginFormcreatorForm();
      $forms = $form->find("`plugin_formcreator_categories_id` IN ($categoryIdList)");
      $html = '';
      foreach ($forms as $formId => $item) {
         $html .= '<tr><td>' . '' . '</tr></td>';
      }
      
   }
}
