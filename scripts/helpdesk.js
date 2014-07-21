Ext.onReady(function() {
   Ext.Ajax.request({
      url: "../plugins/formcreator/ajax/homepage_forms.php",
      success: function(data) {
         Ext.select('.tab_cadre_central .tab_cadrehov:has(a[href*=helpdesk.public.php?create_ticket=1])')
            .insertHtml('beforeBegin', data.responseText);
      }
   });
});
