'use strict'
const Nightmare = require('nightmare')
const expect = require('chai').expect
const testSettings = require('../functionalTest.dist.js')

describe('Check the tab of forms appears on the homepage of helpdesk', function() {
   this.timeout('5s')

   const screenshotPath = require('path').dirname(__dirname) + '/logs';
   const login = 'glpi'
   const passwd = 'glpi'

   let nightmare = null
   beforeEach(() => {
      nightmare = new Nightmare({
         waitTimeout: 4000, // milliseconds
         loadTimeout: 2000, // in milliseconds
         show: testSettings.show
      })
   })

   describe('Helpdesk home page', () => {
      it('should show a new tab to get available forms', done => {
         nightmare
         .viewport(1280, 1024)
         .goto(testSettings.baseUrl)
         .wait('#boxlogin .submit')
         .type('input#login_name', login)
         .type('input#login_password', passwd)
         .click('#boxlogin > form input.submit')
         .wait('#footer')
         .html(testSettings.screenshot + '/error.html', 'HTMLOnly')
         .evaluate(() => {
            return document.querySelectorAll('#page > div > div > ul > li > a[title="Forms"]').length
         })
        .end()
        .then(result => {
            expect(result).to.eql(1)
            done()
         })
        .catch(error => {
            console.error('test failed:', error)
         })
      })
   })
})
