'use strict'

const fs = require('fs')
var settings

if (!fs.existsSync(__dirname + '/functionalTest.json')) {
   settings = {
      "baseUrl": "http://localhost:8088"
   }
} else {
   settings = require(__dirname + '/functionalTest.json')
}
settings.screenshot = __dirname + '/logs'

console.log(settings)
module.exports = settings
