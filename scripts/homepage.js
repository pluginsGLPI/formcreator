Ext.Ajax.on('requestcomplete', function(ajax, xhr, option){
      //delay the execution (ajax requestcomplete event fired before dom loading)
      if (xhr.status == 200) {
         if (option.url.indexOf('common.tabs.php') > 0 && (option.params.indexOf("Central$1") > 0 || option.params.indexOf("-1") > 0)) {
            if (location.pathname.indexOf('front/central.php') > 0) {
               Ext.Ajax.request({
                  url: "../plugins/formcreator/ajax/homepage_forms.php",
                  success: function(data) {
                     Ext.select('.tab_cadre_central td:nth-child(2) .central > tbody > tr > td:first-child')
                        .insertHtml('afterBegin', data.responseText);
                  }
               });
            }
         }
      }
});
