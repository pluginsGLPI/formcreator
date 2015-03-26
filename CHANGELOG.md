GLPI Formcreator 0.84-2.x ChangeLog
===================================

Version 0.84-2.1.4
------------------

> 2015-03-26

### Bugfixes:

* Don't display deleted forms
* Fix default value of integer fields

### Features:

* Add a new notification on form answered


Version 0.84-2.1.3
------------------

> 2015-02-13

### Bugfixes:

* Textarea fields where limited to 255 characters

### Features:

* The validator is not anymore the ticket creator
* Update answer size to accept long text
* Update lang files
* Add default message for form validation and show refused message to everyone.


Version 0.84-2.1.2
------------------

> 2014-12-18

### Bugfixes:

* Wrong value on LDAP field when no value selected
* Fix validator list when no validators selected
* Add test on required_validator field
* Remove strip_tags because of a bug in PHP 5.3
* Fix an error on date and datetime fields witch are hidden and must be… …
* Fix validator list per entities
* Fix #38 : Forms list not displayed in central view

### Features:

* Change validators display fonction in order to have ajax filter, empty choice and a list ordered by complete name
* Improve validation check


Version 0.84-2.1.1
------------------

> 2014-10-24

### Bugfixes:

* Fix #27 : Bad condition for adding validation_required on migration table
* Fix SQL error when upgrading from previous version
* Fix some issue with IE 7-8


### Features:

* Add new langages: Hungarian and Russian
* Change plugin edition access to all entities administrator


Version 0.84-2.1
----------------

> 2014-10-24

### Bugfixes:

* Fix IE 8 compatibility

### Features:

* Add validation process to form
* Add GLPI Object list field


Version 0.84-2.0
----------------

> 2014-09-09

### Features:

* Source code completly refactor
* Change form display
* Added fields lists supplied by LDAP
* Added fields lists of objects from GLPI (Users, Computers, profiles, etc.)
* Targeting forms by profiles
* Public Forms available without connection to GLPI
* Use tickets templates for more flexibility and features
* Previewing forms directly from the configuration
* Multiple Controls on the answers to questions (eg E-mail + mandatory, Number + greater than X + less than Y, etc.)
* New types of fields:
    * Date
    * Date & Time
    * Description
    * GLPI dropdowns
    * LDAP list
    * List of objects from GLPI
    * E-mail
    * Integers
    * Floating numbers
    * Hidden Fields
    * List of radio buttons
    * Multiple choice lists
