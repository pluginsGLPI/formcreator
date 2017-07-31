casper.test.begin("Central page shows Formcreator's tab", {
   test: function(test) {
      casper.start('http://localhost:8088/', function() {
         this.waitForSelector('#boxlogin .submit');
      }).then(function() {
         casper.viewport(1280, 1024);
         test.assertHttpStatus(200);
         casper.fillSelectors('#boxlogin form', {
            'input#login_name' : 'glpi',
            'input#login_password' : 'glpi'
         }, true);
      }).then(function() {
         this.waitForSelector('#footer');
      }).then(function() {
         test.assertUrlMatch('/front/central.php', 'login succeeded');
         test.assertExists('#page > div > div > ul > li > a[title="Forms"]');
      }).run(function() {
         test.done();
      });
   }
});