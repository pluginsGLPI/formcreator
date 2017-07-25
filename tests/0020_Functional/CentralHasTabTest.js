casper.test.begin("Central page shows Formcreator's tab", {
   test: function(test) {
      casper.start('http://localhost:8088/', function() {
      }).then(function() {
         casper.wait(5000);
      }).then(function(response) {
         require('utils').dump(response);
         casper.viewport(1280, 1024);
         test.assertHttpStatus(200);
         casper.fillSelectors('#boxlogin form', {
            'input#login_name' : 'glpi',
            'input#login_password' : 'glpi'
         }, true);
      }).then(function() {
         casper.capture('01.png');
         test.assertUrlMatch('/front/central.php', 'login succeeded');
         test.assertExists('#page > div > div > ul > li > a[title="Forms"]');
      }).run(function() {
         test.done();
      });
   }
});
