<a name="2.6.1"></a>
## [2.6.1](https://github.com-btry/pluginsGLPI/formcreator/compare/2.6.0...2.6.1) (2018-01-02)


### Bug Fixes

* avoid duplicated form having the uuid of the source one ([464757e](https://github.com-btry/pluginsGLPI/formcreator/commit/464757e))
* form duplication issue when source form contains access restriction ([ec40d9f](https://github.com-btry/pluginsGLPI/formcreator/commit/ec40d9f))
* misconceptions in duplication process ([06c2430](https://github.com-btry/pluginsGLPI/formcreator/commit/06c2430))
* **locales:** fix missing locales, update them ([33cbe5e](https://github.com-btry/pluginsGLPI/formcreator/commit/33cbe5e))
* rich description encoding in ticket ([#775](https://github.com-btry/pluginsGLPI/formcreator/issues/775)) ([f739c54](https://github.com-btry/pluginsGLPI/formcreator/commit/f739c54))
* **answer:** HTML entity decode for older textarea answers ([3612c3c](https://github.com-btry/pluginsGLPI/formcreator/commit/3612c3c))
* **condition:** fix inconsistency when checking question conditions ([a820e55](https://github.com-btry/pluginsGLPI/formcreator/commit/a820e55)), closes [#829](https://github.com-btry/pluginsGLPI/formcreator/issues/829)
* **field:** avoid html entitization of accented chars ([a973f7b](https://github.com-btry/pluginsGLPI/formcreator/commit/a973f7b))
* **form:** duplicate target changes when duplicating form ([7f78de9](https://github.com-btry/pluginsGLPI/formcreator/commit/7f78de9))
* **form:** fix escaping and logic issues in duplication ([236effd](https://github.com-btry/pluginsGLPI/formcreator/commit/236effd))
* **form:** repair massive acions ([7221644](https://github.com-btry/pluginsGLPI/formcreator/commit/7221644))
* **form:** update target settings depending on questions ([7acbc11](https://github.com-btry/pluginsGLPI/formcreator/commit/7acbc11))
* **form_answer:** restrict display of form answers to requesters and valdators ([8909e4e](https://github.com-btry/pluginsGLPI/formcreator/commit/8909e4e)), closes [#869](https://github.com-btry/pluginsGLPI/formcreator/issues/869)
* **install:** detect version 2.6 without schema version, see [#794](https://github.com-btry/pluginsGLPI/formcreator/issues/794) ([decaafe](https://github.com-btry/pluginsGLPI/formcreator/commit/decaafe))
* **install:** fix inconsistencies in install process" ([99eb790](https://github.com-btry/pluginsGLPI/formcreator/commit/99eb790))
* **install:** fresh 2.6.0 install inconsistent ([903a13a](https://github.com-btry/pluginsGLPI/formcreator/commit/903a13a))
* **install:** fresh installation does not saves current schema version ([8eadd7d](https://github.com-btry/pluginsGLPI/formcreator/commit/8eadd7d)), closes [#794](https://github.com-btry/pluginsGLPI/formcreator/issues/794)
* **install:** inconsistency in fresh 2.6.0 install ([e41a86d](https://github.com-btry/pluginsGLPI/formcreator/commit/e41a86d)), closes [#822](https://github.com-btry/pluginsGLPI/formcreator/issues/822)
* **install:** restore lost JSON type creation ([40afda3](https://github.com-btry/pluginsGLPI/formcreator/commit/40afda3))
* **install:** run issues synchronization after install ([2441d02](https://github.com-btry/pluginsGLPI/formcreator/commit/2441d02))
* **issue:** bad search option ([bc4bec8](https://github.com-btry/pluginsGLPI/formcreator/commit/bc4bec8))
* **issue:** issue not updated to refused status ([8b1e3b8](https://github.com-btry/pluginsGLPI/formcreator/commit/8b1e3b8))
* **issue:** wrong  ticket disdplay ([5e33407](https://github.com-btry/pluginsGLPI/formcreator/commit/5e33407)), closes [#859](https://github.com-btry/pluginsGLPI/formcreator/issues/859)
* **locale:** bad domain for some locales ([1d9ff65](https://github.com-btry/pluginsGLPI/formcreator/commit/1d9ff65))
* **locales:** add missing strings; update locales ([792a6c2](https://github.com-btry/pluginsGLPI/formcreator/commit/792a6c2))
* **locales:** follow change of a localizable string from GLPI 9.1 ([75a1057](https://github.com-btry/pluginsGLPI/formcreator/commit/75a1057))
* **locales:** harmonize and fix locales ([62076ed](https://github.com-btry/pluginsGLPI/formcreator/commit/62076ed))
* **question:** fix duplicate code ([779a5c3](https://github.com-btry/pluginsGLPI/formcreator/commit/779a5c3))
* **question:** fix escaping issues with regexes ([c807936](https://github.com-btry/pluginsGLPI/formcreator/commit/c807936))
* **question:** fix typo breaking duplication ([e7d2b0e](https://github.com-btry/pluginsGLPI/formcreator/commit/e7d2b0e))
* **question:** remove abusive encoding ([f183091](https://github.com-btry/pluginsGLPI/formcreator/commit/f183091))
* **rule:** location affectation on ticket via business rule ([06d6461](https://github.com-btry/pluginsGLPI/formcreator/commit/06d6461)), closes [#795](https://github.com-btry/pluginsGLPI/formcreator/issues/795)
* **section:** delete a section displays an error ([1d1eb93](https://github.com-btry/pluginsGLPI/formcreator/commit/1d1eb93))
* **selectfield:** workaround GLPI issue 3308 ([d086006](https://github.com-btry/pluginsGLPI/formcreator/commit/d086006))
* **target:** do not mention the absence of an uploaded document in targets ([f1ac36b](https://github.com-btry/pluginsGLPI/formcreator/commit/f1ac36b))
* **target:** fix HTML issues in generated tickets ([278c628](https://github.com-btry/pluginsGLPI/formcreator/commit/278c628))
* **target:** fix typo preventing requester groups being added to  targets ([ececfe3](https://github.com-btry/pluginsGLPI/formcreator/commit/ececfe3)), closes [#767](https://github.com-btry/pluginsGLPI/formcreator/issues/767)
* **target:** fix warnings in  timeline when no fiel uploaded ([9c94128](https://github.com-btry/pluginsGLPI/formcreator/commit/9c94128))
* **target:** rename a target overriden by a global var ([f5b14a9](https://github.com-btry/pluginsGLPI/formcreator/commit/f5b14a9))
* **target-change:** nug handling the comment field of a target change ([5371da5](https://github.com-btry/pluginsGLPI/formcreator/commit/5371da5))
* **targetchange:** fix reversed condition ([e2288bf](https://github.com-btry/pluginsGLPI/formcreator/commit/e2288bf))
* **targetticket:** fix entity of generated ticket ([1ea5325](https://github.com-btry/pluginsGLPI/formcreator/commit/1ea5325))
* **targetticket:** follow change in GLPI for due date ([efa5fcb](https://github.com-btry/pluginsGLPI/formcreator/commit/efa5fcb))
* **targetticket,targetchange:** ticket and change rendering without rich text mode ([d723a47](https://github.com-btry/pluginsGLPI/formcreator/commit/d723a47)), closes [#847](https://github.com-btry/pluginsGLPI/formcreator/issues/847)
* **ui:** css ([c907214](https://github.com-btry/pluginsGLPI/formcreator/commit/c907214))
* **ui:** dont force layout for service catalog ([617e8f1](https://github.com-btry/pluginsGLPI/formcreator/commit/617e8f1))
* **ui:** pqselect enabled not loaded every time it is needed ([#768](https://github.com-btry/pluginsGLPI/formcreator/issues/768)) ([22f3508](https://github.com-btry/pluginsGLPI/formcreator/commit/22f3508))
* **ui:** tinymce may ot load ([86893f4](https://github.com-btry/pluginsGLPI/formcreator/commit/86893f4))
* **ui:** too long localized string ([c83323d](https://github.com-btry/pluginsGLPI/formcreator/commit/c83323d))
* **wizard:** bookmark was renamed into saved search i GLPI 9.2 ([02c2877](https://github.com-btry/pluginsGLPI/formcreator/commit/02c2877)), closes [#799](https://github.com-btry/pluginsGLPI/formcreator/issues/799)


### Features

* **file:** use enhanced file field ([988136a](https://github.com-btry/pluginsGLPI/formcreator/commit/988136a))
* **install:** prepare upgrade code ([7a93405](https://github.com-btry/pluginsGLPI/formcreator/commit/7a93405))



GLPI Formcreator ChangeLog
===============================

Version 2.6.0
-------------
## Bugfixes
* fix CSS preventing access to entity selection in service catalog (simplified interface)
* fix error if plugin Tag not available but used in a form to display
* various JS fixes

Version 2.6.0 Release Candidate 1
---------------------------------

## Bugfixes
* limit displayed columns on form answers tab of a form (#686)
* fix bulleted lists for IE
* fix bad display of a dropdown
* fix loss of input when validating requester's answers and form is incomplete 
* fix ticket categories displayed in helpdesk when they should not
* fix rejected entity dropdown answer if choosing root entity
* fix newlines lost in textareas
* fix rich text rendering of a textarea
* fix broken multiselect field
* fix inconsistent foreign key in the schema and code
* fix move up / down questions
* association of a document to all generated tickets

## Features
* update slinky JS library
* many code cleanup and refactor
* simplify string escaping code
* give more power to form designers when using regex (#701)
* limit display of ticket categories by tree depth and type criteria
* location of tickets can be set from a question


Version 2.5.2
-------------

## Bugfixes
* blank or nearly blank view of form when displaying it for print
* output name instead of ID for some fields when generating target tickets
* fix use of a non existent class Ticket_Supplier
* fix search options of form answers
* fix loss of forn answers in screen when reject then accept a form
* fix regression of not displayed tab for forms on helpdesk
* fix bulleted and numbered lists in generated ticket followup
* fix a bad SQL query

## Features:
* many code cleanup 
* simplify string escaping code in some places


Version 2.5.1
-------------

## Bugfixes:
* restore compatibility with PHP 5.4


Version 2.5.0
-------------

## Features:
* forms can target tickets and changes
* complex question conditions with multiple criterias
* set ticket category and urgency from a form's field
* show list of answers of a form
* print answers of a form

## Bugfixes:
* single quotes upgrade issues 
* LDAP field


Version 2.4.2
-------------

## Bugfixes:
* Fix compatibility issue with GLPI 0.90 and actors field
* Fix empty observer actor with the actors field
* Fix dropdown when typing an actor
* Fix actors field list may contain fields from other forms than the one being edited in destination gicket
* Add error message if actors field does not validate

Version 2.4.1
-------------

## Bugfixes:
* better performance in the service catalog when the database contains hundreds of thousands of tickets
* easy configuration of JSON document types for import feature
* form duplication
* upgrade from older versions
* encoding problems with non latin characters
* many other bugs

Version 2.4.0
-------------

## Bugfixes:
* character escaping issues
* customization of notifications
* validation from unauthorized validators
* disable useless history entries
* several bugs related to validation

## Features:
* Service catalog for simplified interface
* JSON import / export between instances of GLPI (or backup)
* New field types : actor and urgency


Version 0.90-1.4-beta2
----------------------
* form categories support parent / child relationship
* new presentation of forms to requesters  
* optional replacement of the simplified interface with a service catalog
* natural language search engine
* sort forms alphabetically or by popularity
 
Version 0.90-1.3.4
------------------

## Bugfixes:

* multiple issues with validators
* sql strict mode compatibility
* performance improvements
* order of dropdowns
* display forms without languages
* deletion of forms is now possible

## Features:

* Integration with tag plugin
* more options for entity computing



Version 0.90-1.3.3
------------------

### Bugfixes:

* Fix destination formating
* Remove notification "form accepted" when no validation needed
* Fix a security issue : Forms could always be shown as public forms
* Fix Formcreator menu was displayed too times due to a bug in GLPI

### Features:

* Improve public form display
* Refactor GLPI object list questions display to ignore GLPI limitations on authentification
* Add groups to validators



Version 0.90-1.3.2
------------------

### Bugfixes:

* Fix a blocking bug that hide the plugin menu from previous release


Version 0.90-1.3.1
------------------

### Bugfixes:

* Fix broken links to "My Forms".
* Fix Status search and display on form list.
* Fix "+" link on GLPI dropdown questions administration (wasn't update on type changes).
* Hide the "default value" field when no object is select (on GLPI object questions administration).
* Fix anonymous forms access (no CSS, no access to dropdowns and objects).
* Fix CSS display error on dropdowns.
* Fix "Due date calculated from the ticket" value dipslay.
* Fix HTML tags which were encoded in ticket desciption if Rich text editor is activated


Version 0.90-1.3.0
------------------

### Bugfixes:

* Fix a translation bug introduce in 0.90-1.2.5 that include the impossibility to save or update forms destinations.
* Fix validation link in notifications (now set with Configuration value instead of fixed value)
* Fix notification on ticket creation for destination with only one requester.
* Improve right management on menu.
* Fix form appear on home page even I select "no" in Direct access on homepage field.
* The validation link into formcreator's notification is now dynamic and take care of GLPI's URL defined in setup.
* Fix a bug introduced by GLPI 0.90 on vertical split view. It was impossible to scroll down for long forms.

### Features:

* Forms categories are now optional.
* Add link between formanswer and generated tickets + Add document, notes and history tabs
* Add an IP address field type.


Version 0.90-1.2.5
------------------

### Bugfixes:

* Nombre de "Destinations" limitées
* Question de type LDAP impossible à créer
* Erreur de suppression d'une section
* Affichage des réponses des "Zone de texte" avec mise en forme dans la liste des réponse/validations de formulaires
* Problème d'affichage des champs "Affichage du champ"
* Problème d'affichage des listes déroulantes dans l'édition des questions
* Problème mise en forme texte enrichi dans ticket GLPI 0.85.4 et formcreator

### Features:

* Сategories of forms feature
* Add compatibility with GLPI 0.90.x



Version 0.85-1.2.4
------------------

> 2015-03-26

### Bugfixes:

* Fix due date selected value in form target configuration
* Fix severals issues on encoding, quotes and languages
* Fix multi-select field display for validators
* Fix a bug on ticket creation for form which don't need validation
* Send "Form validation accepted" notification only if form need to be validated


### Features:

* Redirect to login if not logged (from notifaction link)
* Don't chek entity right on answer validation
* Optimize init of plugin and load js/css only when needed


Version 0.85-1.2.3
------------------

> 2015-03-26

### Bugfixes:

* Fix validation of empty and not required number fields

### Features:

* Add migration for special chars
* Add a new notification on form answered
* Add ChangeLog file


Version 0.85-1.2.2
------------------

> 2015-03-20

###Bugfixes:

* Fix display form list in home page with the "simplified interface"
* Fix errors with special chars with PHP 5.3

###Features:

* Change display of validators dropdown in form configuration in order to improve selection on large list of validators.


Version 0.85-1.2
------------------

> 2015-02-27

###Bugfixes:

* Vérification du champs catégorie à la création d'un formulaire
* PHP Warning lors de l'ajout d'un formulaire
* Antislashes in answers are broken
* HTML descriptions no longer parsed
* Failed form validation add slashes in fields

###Features:

* Add the possibility to select target ticket actors
* Add the ability to define the Due date
* Add validation comment as first ticket followup
* Add the ability to clone a form
* Add feature to disable email notification to requester enhancement feature


Version 0.85-1.1
------------------

> 2015-02-13

###Bugfixes:

* Cannot add a question
* targetticket, lien vers le formulaire parent
* erreur js en administration d'une question
* fonction updateConditions : log dans php_error.log
* Affichage du champ non fonctionnel (et non sauvegardé)
* crash on glpi object
* Valideur du formulaire devient demandeur du ticket target
* link between questions only now work with radio button
* redirect (from notification) not working
* error missing \_user\_id_requester on ticket creation
* link for create forms (after breadcrumb) is available for non-admins users
* Validation sending (ajax get) : request uri too long
* Show field condition issue
* Forms list not displayed in central view
* List LDAP value --- Valeur liste LDAP
* PHP warnings (related to validation feature ?)
* Change links by buttons in formcreator configuration
* PHP Parse error: syntax error, unexpected T\_CONSTANT\_ENCAPSED_STRING in /var/www/glpi/plugins/formcreator/inc/targetticket.class.php on line 87

###Features:

* administration, emplacement objet glpi
* Formulaire accepté : Accepté ne s'affiche pas
* item forms in global menu must be added at the end of it
* Add WYSIWYG editor for textarea fields feature


Version 0.85-1.0
------------------

> 2014-12-18

###Features:

* Port Formcreator 0.84-2.1 to GLPI 0.85. See [Formcreator 0.84 ChangeLog](https://github.com/TECLIB/formcreator/blob/0.84-bugfixes/CHANGELOG.md)
