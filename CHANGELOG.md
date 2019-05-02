<a name="2.8.2"></a>
## [2.8.2](https://github.com-btry/pluginsglpi/formcreator/compare/v2.8.1...v2.8.2) (2019-05-02)


### Bug Fixes

* **dropdownfield:** upgraded fields from 2.5 may crash ([8233b75](https://github.com-btry/pluginsglpi/formcreator/commit/8233b75))
* **filefield:** uploaded files lost ([1cec1e0](https://github.com-btry/pluginsglpi/formcreator/commit/1cec1e0))
* **form:** redirect to formlist after filling a form ([51fe9ae](https://github.com-btry/pluginsglpi/formcreator/commit/51fe9ae))
* **issue:** warnings with GLPI 9.3 ([04791f4](https://github.com-btry/pluginsglpi/formcreator/commit/04791f4))
* **question:** quote escaping in import ([ed4b021](https://github.com-btry/pluginsglpi/formcreator/commit/ed4b021))
* **serviceCatalog:** fix left menu for some languages ([f1bc390](https://github.com-btry/pluginsglpi/formcreator/commit/f1bc390))



<a name="2.8.1"></a>
## [2.8.1](https://github.com/pluginsglpi/formcreator/compare/v2.8.0...v2.8.1) (2019-04-08)


### Bug Fixes

* **filefield:** fix bad value in generated targets ([d3aeb0d](https://github.com/pluginsglpi/formcreator/commit/d3aeb0d))
* **form_validator:** import valodators from JSON ([b6ea017](https://github.com/pluginsglpi/formcreator/commit/b6ea017))
* **glpiselect:** show user firstname and lastname ([575dbf8](https://github.com/pluginsglpi/formcreator/commit/575dbf8))
* **issue:** mis ordered columns in resync ([cf9aea1](https://github.com/pluginsglpi/formcreator/commit/cf9aea1))
* **issue:** resynchronization error ([65cadb3](https://github.com/pluginsglpi/formcreator/commit/65cadb3))
* **question_condition:** inverted import condition ([1299b77](https://github.com/pluginsglpi/formcreator/commit/1299b77))
* **wizard:** inconsistencies in counters ([ff95440](https://github.com/pluginsglpi/formcreator/commit/ff95440))



<a name="2.8.0"></a>
# [2.8.0](https://github.com/pluginsglpi/formcreator/compare/v2.7.0...v2.8.0) (2019-03-06)


### Bug Fixes

* **checkboxesfield:** handle case when one option is selcted ([5cee1a5](https://github.com/pluginsglpi/formcreator/commit/5cee1a5))
* **dropdownfield:** bad entity restriction ([e9c27dd](https://github.com/pluginsglpi/formcreator/commit/e9c27dd))
* **dropdownfield:** bad subtree handling for ITIL category ([a62e764](https://github.com/pluginsglpi/formcreator/commit/a62e764))
* **form:** fix anonymous file upload ([a13ec8b](https://github.com/pluginsglpi/formcreator/commit/a13ec8b))
* **form:** import of entity and category ([2e3beea](https://github.com/pluginsglpi/formcreator/commit/2e3beea))
* **formanswer:** use of non-eistent field for count ([fb30d55](https://github.com/pluginsglpi/formcreator/commit/fb30d55))
* **glpiobject,dropdown:** formanswer must display name, not the ID ([db24ae9](https://github.com/pluginsglpi/formcreator/commit/db24ae9))
* **install:** remvoe rename of the plugin from upgtrade to 2.7 ([6e03e21](https://github.com/pluginsglpi/formcreator/commit/6e03e21))
* **install:** rename of the plugin in 2.8, not 2.7 ([e176d3c](https://github.com/pluginsglpi/formcreator/commit/e176d3c))
* **issue:** rebuild of issues table ([eec8012](https://github.com/pluginsglpi/formcreator/commit/eec8012))
* **question,section:** escaping bug on duplication ([971339f](https://github.com/pluginsglpi/formcreator/commit/971339f))
* **questionrange,questionregex:** bad var names ([978a116](https://github.com/pluginsglpi/formcreator/commit/978a116))
* **section:** escape questions to duplciate ([9786afa](https://github.com/pluginsglpi/formcreator/commit/9786afa))
* **targetchange:** add users from question of type actors ([fcb357b](https://github.com/pluginsglpi/formcreator/commit/fcb357b))
* **targetchange:** category not assigned ([7f840df](https://github.com/pluginsglpi/formcreator/commit/7f840df))
* **wizard:** compatibility accross versions od font awesome ([2462ca4](https://github.com/pluginsglpi/formcreator/commit/2462ca4))
* rename the plugin ([2f5c27f](https://github.com/pluginsglpi/formcreator/commit/2f5c27f)), closes [#1264](https://github.com/pluginsglpi/formcreator/issues/1264)


### Features

* **glpiselect:** add Project in the supported list ([eae0a3b](https://github.com/pluginsglpi/formcreator/commit/eae0a3b))
* **issue:** add qtip for ticket types ([4edcd06](https://github.com/pluginsglpi/formcreator/commit/4edcd06))



<a name="2.7.0"></a>
# [2.7.0](https://github.com/pluginsglpi/formcreator/compare/v2.7.0-beta.2...v2.7.0) (2019-02-12)


### Bug Fixes

* **formanswer:** avoid loop of purge permission check ([93a7f84](https://github.com/pluginsglpi/formcreator/commit/93a7f84))
* compatibility with GLPI 9.4 ([1225c02](https://github.com/pluginsglpi/formcreator/commit/1225c02))
* **filefield:** add error message if file must have an upload ([c800495](https://github.com/pluginsglpi/formcreator/commit/c800495))
* **form:** escape single quote when duplicating a form ([9d735d8](https://github.com/pluginsglpi/formcreator/commit/9d735d8))
* **form_profile:** more resilient export / import ([0303120](https://github.com/pluginsglpi/formcreator/commit/0303120))
* **formanswer:** abusive scaping on section name ([0a74aba](https://github.com/pluginsglpi/formcreator/commit/0a74aba))
* **formanswer:** generation of full form template ([8b99bd1](https://github.com/pluginsglpi/formcreator/commit/8b99bd1))
* **formaswer:** remove is_deleted column ([8f4111f](https://github.com/pluginsglpi/formcreator/commit/8f4111f))
* **glpiselectfield:** missing entries in entity select ([746326c](https://github.com/pluginsglpi/formcreator/commit/746326c))
* **issue:** compatibility with search engine of GLPI 9.4 ([65a48fe](https://github.com/pluginsglpi/formcreator/commit/65a48fe))
* **target:** escape single quotes ([9f641e3](https://github.com/pluginsglpi/formcreator/commit/9f641e3))
* **targetbase:** rename of a target was ignored ([4f0c7c8](https://github.com/pluginsglpi/formcreator/commit/4f0c7c8))
* **targetchange:** copy pasted import code ([7f64792](https://github.com/pluginsglpi/formcreator/commit/7f64792))
* **targetticket:** email field as source of actors ([002778d](https://github.com/pluginsglpi/formcreator/commit/002778d))
* **targetticket:** single quote not escaped ([dac1f25](https://github.com/pluginsglpi/formcreator/commit/dac1f25))
* **targetticket,targetchange:** escape single quotes ([5d5f22b](https://github.com/pluginsglpi/formcreator/commit/5d5f22b))
* **wizard:** invisible logout icon ([4ab1299](https://github.com/pluginsglpi/formcreator/commit/4ab1299))
* default value of integer and float fields ([3d90e6b](https://github.com/pluginsglpi/formcreator/commit/3d90e6b))
* improve quote escaping ([b8497fa](https://github.com/pluginsglpi/formcreator/commit/b8497fa))
* quote issue in javascript code ([a91cc11](https://github.com/pluginsglpi/formcreator/commit/a91cc11))
* resolve several other quote escaping problems ([7e306f5](https://github.com/pluginsglpi/formcreator/commit/7e306f5))



<a name="2.7.0-beta.3"></a>
# [2.7.0-beta.3](https://github.com/pluginsglpi/formcreator/compare/v2.7.0-beta.2...v2.7.0-beta.3) (2019-01-16)


### Bug Fixes

* compatibility with GLPI 9.4 ([9b9922c](https://github.com/pluginsglpi/formcreator/commit/9b9922c))



<a name="2.7.0-beta.2"></a>
# [2.7.0-beta.2](https://github.com/pluginsglpi/formcreator/compare/v2.7.0-beta.1...v2.7.0-beta.2) (2019-01-16)


### Bug Fixes

* **descriptionfield:** show / hide did not work ([04f1695](https://github.com/pluginsglpi/formcreator/commit/04f1695))
* **form:** access rights checking ([e05e20b](https://github.com/pluginsglpi/formcreator/commit/e05e20b))
* **formanswer:** empty list of answers ([2478b2c](https://github.com/pluginsglpi/formcreator/commit/2478b2c))
* **formanswer:** missing right check ([bdac689](https://github.com/pluginsglpi/formcreator/commit/bdac689))
* **install:** create tables before filling them ([#1234](https://github.com/pluginsglpi/formcreator/issues/1234)) ([c08e299](https://github.com/pluginsglpi/formcreator/commit/c08e299))
* **install:** update relation ([09c0101](https://github.com/pluginsglpi/formcreator/commit/09c0101))
* **issue:** fix filtered searches of issues ([9bda871](https://github.com/pluginsglpi/formcreator/commit/9bda871))
* **targetTicket:** generation of ticket ([8355480](https://github.com/pluginsglpi/formcreator/commit/8355480))
* **textfield:** abuse escaping ([167c43f](https://github.com/pluginsglpi/formcreator/commit/167c43f))
* **urgencyfield:** default value ([1849e61](https://github.com/pluginsglpi/formcreator/commit/1849e61))


### Features

* **targetticket:** compatibility with GLPI 9.4 ([6afe5fe](https://github.com/pluginsglpi/formcreator/commit/6afe5fe))



<a name="2.7.0-beta.1"></a>
# [2.7.0-beta.1](https://github.com/pluginsglpi/formcreator/compare/v2.6.4...v2.7.0-beta.1) (2018-12-05)


### Bug Fixes

* **actor:** fix broken select tag ([9d38a45](https://github.com/pluginsglpi/formcreator/commit/9d38a45))
* **actorfield:** default value not correctly handled ([a210e9a](https://github.com/pluginsglpi/formcreator/commit/a210e9a))
* **actors:** fix broken select tag ([82509e8](https://github.com/pluginsglpi/formcreator/commit/82509e8))
* **actors:** rendering for answers view, and use JSON  in DB ([3f0c9b7](https://github.com/pluginsglpi/formcreator/commit/3f0c9b7))
* **checkboxesfield:** single quote rendering ([fecb8a8](https://github.com/pluginsglpi/formcreator/commit/fecb8a8))
* **core:** clean doc name on create issue ([095ce60](https://github.com/pluginsglpi/formcreator/commit/095ce60))
* **date:** format dates for target generation ([ed32016](https://github.com/pluginsglpi/formcreator/commit/ed32016)), closes [#1050](https://github.com/pluginsglpi/formcreator/issues/1050)
* **datetimefield:** fix name of the type and validity check ([accae14](https://github.com/pluginsglpi/formcreator/commit/accae14))
* **dropdown:** change to ticket category ([22809ce](https://github.com/pluginsglpi/formcreator/commit/22809ce))
* **dropdownfield:** restrict by entity when needed ([a63c04f](https://github.com/pluginsglpi/formcreator/commit/a63c04f))
* **field:** prevent use of tag field if the plugin Tag is not available ([453bc95](https://github.com/pluginsglpi/formcreator/commit/453bc95))
* **field:** remove unused method ([5a1bfe7](https://github.com/pluginsglpi/formcreator/commit/5a1bfe7))
* **fields:** fields not hidden correctly ([0c75a69](https://github.com/pluginsglpi/formcreator/commit/0c75a69))
* **filefield:** check all files, not only one ([3a26922](https://github.com/pluginsglpi/formcreator/commit/3a26922))
* **filefield:** fix validity of the field when  required ([0a460dd](https://github.com/pluginsglpi/formcreator/commit/0a460dd))
* **form:** bad quote in FORM HTML tag ([3883c1c](https://github.com/pluginsglpi/formcreator/commit/3883c1c))
* **form:** display and reload entered values ([3f4342d](https://github.com/pluginsglpi/formcreator/commit/3f4342d))
* **form:** duplicate may fail ([5c6607c](https://github.com/pluginsglpi/formcreator/commit/5c6607c))
* **form:** duplicate may fail ([1251ea1](https://github.com/pluginsglpi/formcreator/commit/1251ea1))
* **form:** duplicate with quote ([a3d9d0d](https://github.com/pluginsglpi/formcreator/commit/a3d9d0d))
* **form:** export broken due to typo ([d1f93ce](https://github.com/pluginsglpi/formcreator/commit/d1f93ce))
* **form:** php warning ([d0a56e5](https://github.com/pluginsglpi/formcreator/commit/d0a56e5))
* **form:** remove html entities in db ([468ee6b](https://github.com/pluginsglpi/formcreator/commit/468ee6b))
* **form:** search engine and accented chars ([0583a5f](https://github.com/pluginsglpi/formcreator/commit/0583a5f))
* **form_answer:** deprecated calls ([f1ba71b](https://github.com/pluginsglpi/formcreator/commit/f1ba71b))
* **form_answer:** refuse and accept form answers ([8dec5d2](https://github.com/pluginsglpi/formcreator/commit/8dec5d2))
* **formanswer:** fix call to saveForm ([17bd8c5](https://github.com/pluginsglpi/formcreator/commit/17bd8c5))
* **formanswer:** show/hide questions in various cases ([87cc394](https://github.com/pluginsglpi/formcreator/commit/87cc394))
* **formanwer:** various bugs ([9649c52](https://github.com/pluginsglpi/formcreator/commit/9649c52))
* **install:** ad specific values only for itilcategory ([75a815a](https://github.com/pluginsglpi/formcreator/commit/75a815a))
* **install:** avoid warnings ([b171ef3](https://github.com/pluginsglpi/formcreator/commit/b171ef3))
* **install:** bad logic for upgrade steps ([c324e1c](https://github.com/pluginsglpi/formcreator/commit/c324e1c))
* **install:** bad sql for upgrade ([73b4515](https://github.com/pluginsglpi/formcreator/commit/73b4515))
* **install:** check for composer autoload ([c3c2612](https://github.com/pluginsglpi/formcreator/commit/c3c2612))
* **install:** delete tables when the plugin is being uninstalled ([0741de1](https://github.com/pluginsglpi/formcreator/commit/0741de1))
* **install:** harmonize upgrade methods ([b1666b8](https://github.com/pluginsglpi/formcreator/commit/b1666b8))
* **install:** remove useless field ([56a4b5f](https://github.com/pluginsglpi/formcreator/commit/56a4b5f))
* **install:** typo for validator id upgrade ([c9874da](https://github.com/pluginsglpi/formcreator/commit/c9874da))
* **install:** upgrade problem in issues table" ([32d940e](https://github.com/pluginsglpi/formcreator/commit/32d940e))
* **integer:** fix validity checks ([0584604](https://github.com/pluginsglpi/formcreator/commit/0584604))
* **isntal:** fix upgrade to 2.5.0 ([3d6917c](https://github.com/pluginsglpi/formcreator/commit/3d6917c))
* **issue:** avoid truncate of comment ([5e2d7e2](https://github.com/pluginsglpi/formcreator/commit/5e2d7e2))
* **ldapfield:** missing post value when field fails to populate ([4969e64](https://github.com/pluginsglpi/formcreator/commit/4969e64))
* **question:** limit assets dropdowns to my assets ([3cf510a](https://github.com/pluginsglpi/formcreator/commit/3cf510a))
* **question:** move up/down broken ([f65887f](https://github.com/pluginsglpi/formcreator/commit/f65887f))
* **QuestionCondition:** inconsistency in selectors ([ce7444f](https://github.com/pluginsglpi/formcreator/commit/ce7444f))
* **questionparameter:** if the parameter is missing in the db, use default values ([3f10030](https://github.com/pluginsglpi/formcreator/commit/3f10030))
* **radios:** fix trim value ([fadaefe](https://github.com/pluginsglpi/formcreator/commit/fadaefe))
* **tagfield:** fatal error if comparison with a tag field when the tag plugin is not available ([c7fcf8a](https://github.com/pluginsglpi/formcreator/commit/c7fcf8a))
* **tagfield:** fatal error if the plugin Tag is not available ([94905b5](https://github.com/pluginsglpi/formcreator/commit/94905b5))
* **target:** actors not inserted on tarrgets ([220f4ae](https://github.com/pluginsglpi/formcreator/commit/220f4ae))
* **target:** content generation ([993576d](https://github.com/pluginsglpi/formcreator/commit/993576d))
* **target:** deduplicate actors ([d5d4e0c](https://github.com/pluginsglpi/formcreator/commit/d5d4e0c)), closes [#1089](https://github.com/pluginsglpi/formcreator/issues/1089)
* **target:** inconsistency in multiple file upload ([4f4c24c](https://github.com/pluginsglpi/formcreator/commit/4f4c24c))
* **target:** prevent imploding a non-array ([b1b8560](https://github.com/pluginsglpi/formcreator/commit/b1b8560))
* **targetbase:** deduplication of group raises a warning ([34a9a3e](https://github.com/pluginsglpi/formcreator/commit/34a9a3e))
* **targetbase:** fix double quote renderiing in targets ([5711ef0](https://github.com/pluginsglpi/formcreator/commit/5711ef0))
* **targetchange:** apply fix [#267](https://github.com/pluginsglpi/formcreator/issues/267) to target changes ([d186ef3](https://github.com/pluginsglpi/formcreator/commit/d186ef3))
* **targetchange:** changes  does not supports rich text ([653fd6a](https://github.com/pluginsglpi/formcreator/commit/653fd6a)), closes [#1139](https://github.com/pluginsglpi/formcreator/issues/1139)
* **targetchange:** changes don't support template ([08236c7](https://github.com/pluginsglpi/formcreator/commit/08236c7))
* **targetchange:** duplication leaves default actors ([a227341](https://github.com/pluginsglpi/formcreator/commit/a227341))
* **targetchange:** duplication leaves default actors ([dd9e8de](https://github.com/pluginsglpi/formcreator/commit/dd9e8de))
* **targetchange:** entity from a question ([e199085](https://github.com/pluginsglpi/formcreator/commit/e199085))
* **targetchange:** fix creation if relation between change and form answer ([8d8810f](https://github.com/pluginsglpi/formcreator/commit/8d8810f))
* **targetchange:** harmonize column name and type with glpi change itemtype ([1dd58b9](https://github.com/pluginsglpi/formcreator/commit/1dd58b9))
* **targetchange:** rendering with rich text ([a86f6da](https://github.com/pluginsglpi/formcreator/commit/a86f6da))
* **targetchange:** rich text does not exists for changes ([bc8fffd](https://github.com/pluginsglpi/formcreator/commit/bc8fffd))
* remove call to abusive encoding ([49a1fb6](https://github.com/pluginsglpi/formcreator/commit/49a1fb6))
* **targetchange:** time to resolve not populated when required ([f087ad0](https://github.com/pluginsglpi/formcreator/commit/f087ad0))
* **targetchange:** title edition fails ([f3701fb](https://github.com/pluginsglpi/formcreator/commit/f3701fb))
* **targetticket:** fix HTML ([5cac2b8](https://github.com/pluginsglpi/formcreator/commit/5cac2b8))
* **targetticket:** harmonize comumns name and type wih glpi ticket itemtype ([9dc1da5](https://github.com/pluginsglpi/formcreator/commit/9dc1da5))
* **textarea:** rendering without rich text mode ([90fcf8c](https://github.com/pluginsglpi/formcreator/commit/90fcf8c))
* **textareafield:** problem with backslashes rendering ([43fdbe1](https://github.com/pluginsglpi/formcreator/commit/43fdbe1))
* **textareafield:** render HTML when viewing an answer ([8b79d01](https://github.com/pluginsglpi/formcreator/commit/8b79d01))
* **ticket:** redirect to service catalog when viewing ticket ([0bb64c5](https://github.com/pluginsglpi/formcreator/commit/0bb64c5))
* **wizard:** warnings ([a4b9899](https://github.com/pluginsglpi/formcreator/commit/a4b9899)), closes [#1076](https://github.com/pluginsglpi/formcreator/issues/1076)


### Features

* **core:** add options to display ITIL Category question ([f20acbf](https://github.com/pluginsglpi/formcreator/commit/f20acbf))
* **core:** add requester on validation form ([d7b6dc8](https://github.com/pluginsglpi/formcreator/commit/d7b6dc8))
* **core:** add search option to get assign group / tech to ticket ([cb2d9c1](https://github.com/pluginsglpi/formcreator/commit/cb2d9c1))
* **core:** add status label on list ([83247c2](https://github.com/pluginsglpi/formcreator/commit/83247c2))
* **dropdown:** choose a root ticket category for display ([9a28a61](https://github.com/pluginsglpi/formcreator/commit/9a28a61))
* **field:** hostname field ([72a643a](https://github.com/pluginsglpi/formcreator/commit/72a643a))
* handle rich text mode for GLPI 9.4 ([91cc6ce](https://github.com/pluginsglpi/formcreator/commit/91cc6ce))
* **file:** multiple file upload for a single file field ([30e4057](https://github.com/pluginsglpi/formcreator/commit/30e4057))
* **form:** forbid change of access right if incompatible questions found ([7c9733d](https://github.com/pluginsglpi/formcreator/commit/7c9733d))
* **form_answer:** add search option ([ecaaa3f](https://github.com/pluginsglpi/formcreator/commit/ecaaa3f)), closes [#1220](https://github.com/pluginsglpi/formcreator/issues/1220)
* **install:** add force instal or upgrade modes ([bfc83e8](https://github.com/pluginsglpi/formcreator/commit/bfc83e8))
* **install:** Use InnoDB as DB table engine ([0385c20](https://github.com/pluginsglpi/formcreator/commit/0385c20))
* **question:** forbid use of some qustion  types with public forms ([ac557e3](https://github.com/pluginsglpi/formcreator/commit/ac557e3))



<a name="2.6.5"></a>
## [2.6.5](https://github.com/pluginsglpi/formcreator/compare/2.6.3...2.6.5) (2018-11-06)


### Bug Fixes

* **actorfield:** compatibility with GLPI 9.3 ([#1034](https://github.com/pluginsglpi/formcreator/issues/1034)) ([3b2051f](https://github.com/pluginsglpi/formcreator/commit/3b2051f))
* **actors:** fix broken select tag ([82509e8](https://github.com/pluginsglpi/formcreator/commit/82509e8))
* **actors:** fix edit of an existing question of type actors ([f1606af](https://github.com/pluginsglpi/formcreator/commit/f1606af))
* **actors:** use first / last name in generataed tickets ([1455708](https://github.com/pluginsglpi/formcreator/commit/1455708)), closes [#1016](https://github.com/pluginsglpi/formcreator/issues/1016)
* **build:** check consistency of manifest XML file ([fb06543](https://github.com/pluginsglpi/formcreator/commit/fb06543))
* **checkboxedfield:** single quote rendering ([888b13f](https://github.com/pluginsglpi/formcreator/commit/888b13f))
* **date:** format dates for target generation ([10c70fc](https://github.com/pluginsglpi/formcreator/commit/10c70fc)), closes [#1050](https://github.com/pluginsglpi/formcreator/issues/1050)
* **description:** cannot apply hide/show condition on descriptions ([8693a6a](https://github.com/pluginsglpi/formcreator/commit/8693a6a))
* **filefield:** SQL single quote escaping ([e0b9bd6](https://github.com/pluginsglpi/formcreator/commit/e0b9bd6))
* **form:** access to form not properly checked ([#1047](https://github.com/pluginsglpi/formcreator/issues/1047)) ([1a40790](https://github.com/pluginsglpi/formcreator/commit/1a40790))
* **form:** check access to form only by entity ([c0973cb](https://github.com/pluginsglpi/formcreator/commit/c0973cb))
* **form:** duplicate may fail ([a29f806](https://github.com/pluginsglpi/formcreator/commit/a29f806))
* **form:** duplicate may fail ([b9b2547](https://github.com/pluginsglpi/formcreator/commit/b9b2547))
* **form:** duplicate with quote ([ec6460f](https://github.com/pluginsglpi/formcreator/commit/ec6460f))
* **form:** forbid purge of a form when there are answers ([f84b353](https://github.com/pluginsglpi/formcreator/commit/f84b353))
* **form:** import restrictions by profile was broken ([34ae3bf](https://github.com/pluginsglpi/formcreator/commit/34ae3bf)), closes [#722](https://github.com/pluginsglpi/formcreator/issues/722)
* **form_answer:** allow view of form if the user has update entity right ([7dad4cb](https://github.com/pluginsglpi/formcreator/commit/7dad4cb))
* **form_answer:** fix compatibility with GLPI 9.4 ([001a82f](https://github.com/pluginsglpi/formcreator/commit/001a82f))
* **form_answer:** fix fatal error ([c292981](https://github.com/pluginsglpi/formcreator/commit/c292981))
* **form_profile:** UUID was not generated ([44f231b](https://github.com/pluginsglpi/formcreator/commit/44f231b))
* **glpiselect:** compatibility with GLPI 9.3 ([a9aea5a](https://github.com/pluginsglpi/formcreator/commit/a9aea5a))
* **install:** bad logic for upgrade steps ([c324e1c](https://github.com/pluginsglpi/formcreator/commit/c324e1c))
* **issue:** avoid truncate of comment ([8a98b0d](https://github.com/pluginsglpi/formcreator/commit/8a98b0d))
* **issue:** more consistent status display for status of issues ([2802a78](https://github.com/pluginsglpi/formcreator/commit/2802a78))
* **issue:** php warnings in service catalog ([0754b5f](https://github.com/pluginsglpi/formcreator/commit/0754b5f))
* **plugin:** update compatibility ([57c607f](https://github.com/pluginsglpi/formcreator/commit/57c607f))
* **question:** description displayed in italic ([e572b43](https://github.com/pluginsglpi/formcreator/commit/e572b43))
* **question:** fix loss of description ([4b39371](https://github.com/pluginsglpi/formcreator/commit/4b39371))
* **question_condition:** fix creation of conditions ([135d6c8](https://github.com/pluginsglpi/formcreator/commit/135d6c8))
* **target:** actors not inserted on tarrgets ([18b5662](https://github.com/pluginsglpi/formcreator/commit/18b5662))
* **target:** continue keyword in switch ([576c891](https://github.com/pluginsglpi/formcreator/commit/576c891))
* **target:** loading of instance from DB ([1d314de](https://github.com/pluginsglpi/formcreator/commit/1d314de))
* **target:** unescaped quote ([6afa05b](https://github.com/pluginsglpi/formcreator/commit/6afa05b))
* **targetbase:** fix double quote renderiing in targets ([40811d8](https://github.com/pluginsglpi/formcreator/commit/40811d8))
* **targetchange:** apply fix [#267](https://github.com/pluginsglpi/formcreator/issues/267) to target changes ([3eafa29](https://github.com/pluginsglpi/formcreator/commit/3eafa29))
* **targetchange:** changes  does not supports rich text ([8d7bad0](https://github.com/pluginsglpi/formcreator/commit/8d7bad0)), closes [#1139](https://github.com/pluginsglpi/formcreator/issues/1139)
* **targetchange:** duplication leaves default actors ([854191d](https://github.com/pluginsglpi/formcreator/commit/854191d))
* **targetchange:** entity from a question ([40cc7eb](https://github.com/pluginsglpi/formcreator/commit/40cc7eb))
* **targetchange:** fix creation if relation between change and form answer ([8259899](https://github.com/pluginsglpi/formcreator/commit/8259899))
* compatibility with GLPI 9.4 ([6dfaae9](https://github.com/pluginsglpi/formcreator/commit/6dfaae9)), closes [#1022](https://github.com/pluginsglpi/formcreator/issues/1022)
* **targetchange:** rendering with rich text ([e842b0f](https://github.com/pluginsglpi/formcreator/commit/e842b0f))
* **targetchange:** rich text does not exists for changes ([e39028b](https://github.com/pluginsglpi/formcreator/commit/e39028b))
* **targetchange:** time to resolve not populated when required ([b1240d6](https://github.com/pluginsglpi/formcreator/commit/b1240d6))
* fix plugin manifest xml file ([7608920](https://github.com/pluginsglpi/formcreator/commit/7608920))
* **targetchange:** title edition fails ([32eb4db](https://github.com/pluginsglpi/formcreator/commit/32eb4db))
* **targetticket:** fix HTML ([00f81fc](https://github.com/pluginsglpi/formcreator/commit/00f81fc))
* **textarea:** rendering without rich text mode ([b41a9b2](https://github.com/pluginsglpi/formcreator/commit/b41a9b2))
* **textareafield:**  rendering for rich text ([8735189](https://github.com/pluginsglpi/formcreator/commit/8735189))
* **wizard:** warnings ([6a355f9](https://github.com/pluginsglpi/formcreator/commit/6a355f9)), closes [#1076](https://github.com/pluginsglpi/formcreator/issues/1076)


### Features

* **core:** add search option to get assign group / tech to ticket ([5f1eb35](https://github.com/pluginsglpi/formcreator/commit/5f1eb35))



<a name="2.6.4"></a>
## [2.6.4](https://github.com/pluginsGLPI/formcreator/compare/2.6.3...2.6.4) (2018-08-13)


### Bug Fixes

* **actorfield:** compatibility with GLPI 9.3 ([#1034](https://github.com/pluginsGLPI/formcreator/issues/1034)) ([3b2051f](https://github.com/pluginsGLPI/formcreator/commit/3b2051f))
* **actors:** fix edit of an existing question of type actors ([f1606af](https://github.com/pluginsGLPI/formcreator/commit/f1606af))
* **actors:** use first / last name in generataed tickets ([1455708](https://github.com/pluginsGLPI/formcreator/commit/1455708)), closes [#1016](https://github.com/pluginsGLPI/formcreator/issues/1016)
* **description:** cannot apply hide/show condition on descriptions ([8693a6a](https://github.com/pluginsGLPI/formcreator/commit/8693a6a))
* **filefield:** SQL single quote escaping ([e0b9bd6](https://github.com/pluginsGLPI/formcreator/commit/e0b9bd6))
* **form:** access to form not properly checked ([#1047](https://github.com/pluginsGLPI/formcreator/issues/1047)) ([1a40790](https://github.com/pluginsGLPI/formcreator/commit/1a40790))
* **form:** check access to form only by entity ([c0973cb](https://github.com/pluginsGLPI/formcreator/commit/c0973cb))
* **form:** forbid purge of a form when there are answers ([f84b353](https://github.com/pluginsGLPI/formcreator/commit/f84b353))
* **form:** import restrictions by profile was broken ([34ae3bf](https://github.com/pluginsGLPI/formcreator/commit/34ae3bf)), closes [#722](https://github.com/pluginsGLPI/formcreator/issues/722)
* **form_answer:** allow view of form if the user has update entity right ([7dad4cb](https://github.com/pluginsGLPI/formcreator/commit/7dad4cb))
* **form_answer:** fix compatibility with GLPI 9.4 ([001a82f](https://github.com/pluginsGLPI/formcreator/commit/001a82f))
* **form_answer:** fix fatal error ([c292981](https://github.com/pluginsGLPI/formcreator/commit/c292981))
* **form_profile:** UUID was not generated ([44f231b](https://github.com/pluginsGLPI/formcreator/commit/44f231b))
* compatibility with GLPI 9.4 ([6dfaae9](https://github.com/pluginsGLPI/formcreator/commit/6dfaae9)), closes [#1022](https://github.com/pluginsGLPI/formcreator/issues/1022)
* **issue:** more consistent status display for status of issues ([2802a78](https://github.com/pluginsGLPI/formcreator/commit/2802a78))
* **issue:** php warnings in service catalog ([0754b5f](https://github.com/pluginsGLPI/formcreator/commit/0754b5f))
* **plugin:** update compatibility ([57c607f](https://github.com/pluginsGLPI/formcreator/commit/57c607f))
* **question:** description displayed in italic ([e572b43](https://github.com/pluginsGLPI/formcreator/commit/e572b43))
* **question:** fix loss of description ([4b39371](https://github.com/pluginsGLPI/formcreator/commit/4b39371))
* **question_condition:** fix creation of conditions ([135d6c8](https://github.com/pluginsGLPI/formcreator/commit/135d6c8))
* **target:** continue keyword in switch ([576c891](https://github.com/pluginsGLPI/formcreator/commit/576c891))
* **target:** loading of instance from DB ([1d314de](https://github.com/pluginsGLPI/formcreator/commit/1d314de))
* **target:** unescaped quote ([6afa05b](https://github.com/pluginsGLPI/formcreator/commit/6afa05b))



<a name="2.6.3"></a>
## [2.6.3](https://github.com/pluginsGLPI/formcreator/compare/2.6.2...2.6.3) (2018-04-30)


### Bug Fixes

* **condition:** escape quote in import ([b10134e](https://github.com/pluginsGLPI/formcreator/commit/b10134e))
* **condition:** fix show hide issues ([411b998](https://github.com/pluginsGLPI/formcreator/commit/411b998))
* **docs:** fix bad domain for locales ([47788ff](https://github.com/pluginsGLPI/formcreator/commit/47788ff))
* **field:** some fields output a duplicate HTML div ([c4d1a4c](https://github.com/pluginsGLPI/formcreator/commit/c4d1a4c))
* **file:** fix multiple file fields ([a9798d2](https://github.com/pluginsGLPI/formcreator/commit/a9798d2)), closes [#937](https://github.com/pluginsGLPI/formcreator/issues/937)
* **locales:** invalid domain ([7be6ef1](https://github.com/pluginsGLPI/formcreator/commit/7be6ef1)), closes [#872](https://github.com/pluginsGLPI/formcreator/issues/872)
* **question:** filtering out tag question type broken ([86b5a10](https://github.com/pluginsGLPI/formcreator/commit/86b5a10))
* **section:** fix creation of section with abusive escape ([6057cad](https://github.com/pluginsGLPI/formcreator/commit/6057cad)), closes [#940](https://github.com/pluginsGLPI/formcreator/issues/940) [#940](https://github.com/pluginsGLPI/formcreator/issues/940)
* **section:** single quote escaping issue ([b298505](https://github.com/pluginsGLPI/formcreator/commit/b298505)), closes [#940](https://github.com/pluginsGLPI/formcreator/issues/940)
* **target:** distinguish fields content ([326315c](https://github.com/pluginsGLPI/formcreator/commit/326315c))
* **target ticket:** backquote in ticket ticle ([1296925](https://github.com/pluginsGLPI/formcreator/commit/1296925)), closes [#951](https://github.com/pluginsGLPI/formcreator/issues/951)
* **target ticket:** observer groups from template added in assigned ones ([f09e685](https://github.com/pluginsGLPI/formcreator/commit/f09e685))
* **target ticket:** quote escaping for ticket title ([cd070ea](https://github.com/pluginsGLPI/formcreator/commit/cd070ea))
* **ticket:** use default values to set actors of tickets ([fa3f816](https://github.com/pluginsGLPI/formcreator/commit/fa3f816)), closes [#629](https://github.com/pluginsGLPI/formcreator/issues/629)
* **ui:** compatibility issue with internet explorer 11 ([fb2c711](https://github.com/pluginsGLPI/formcreator/commit/fb2c711)), closes [#936](https://github.com/pluginsGLPI/formcreator/issues/936)
* **ui:** responsive design of service catalog ([0f6e466](https://github.com/pluginsGLPI/formcreator/commit/0f6e466))



<a name="2.6.2"></a>
## [2.6.2](https://github.com/pluginsGLPI/formcreator/compare/2.6.1...2.6.2) (2018-02-12)


### Bug Fixes

* **condition:** fix multiple condition process when a question is unanswered ([6bce6e7](https://github.com/pluginsGLPI/formcreator/commit/6bce6e7))
* **condition:** questions conditions may lead to wring  result in complex cases ([42cb852](https://github.com/pluginsGLPI/formcreator/commit/42cb852)), closes [#880](https://github.com/pluginsGLPI/formcreator/issues/880)
* **field:** fix quote escaping for file field ([0280acf](https://github.com/pluginsGLPI/formcreator/commit/0280acf)), closes [#832](https://github.com/pluginsGLPI/formcreator/issues/832)
* **field:** inability to save properties of a tag question ([faf304b](https://github.com/pluginsGLPI/formcreator/commit/faf304b))
* **form_answer:** fix search option ([1c8f38f](https://github.com/pluginsGLPI/formcreator/commit/1c8f38f)), closes [#602](https://github.com/pluginsGLPI/formcreator/issues/602)
* **notification:** pending validation email for groups ([54c2a2e](https://github.com/pluginsGLPI/formcreator/commit/54c2a2e)), closes [#871](https://github.com/pluginsGLPI/formcreator/issues/871)
* **question:** fields hidden under condition not rendered in  tickets ([9d87dd7](https://github.com/pluginsGLPI/formcreator/commit/9d87dd7)), closes [#880](https://github.com/pluginsGLPI/formcreator/issues/880)
* **target:** answer rendering issues ([6d73872](https://github.com/pluginsGLPI/formcreator/commit/6d73872)), closes [#877](https://github.com/pluginsGLPI/formcreator/issues/877) [#817](https://github.com/pluginsGLPI/formcreator/issues/817)
* **target:** remove abusive encoding in target names ([a0dca23](https://github.com/pluginsGLPI/formcreator/commit/a0dca23))



<a name="2.6.1"></a>
## [2.6.1](https://github.com/pluginsGLPI/formcreator/compare/2.6.0...2.6.1) (2018-01-02)


### Bug Fixes

* avoid duplicated form having the uuid of the source one ([464757e](https://github.com/pluginsGLPI/formcreator/commit/464757e))
* form duplication issue when source form contains access restriction ([ec40d9f](https://github.com/pluginsGLPI/formcreator/commit/ec40d9f))
* misconceptions in duplication process ([06c2430](https://github.com/pluginsGLPI/formcreator/commit/06c2430))
* **locales:** fix missing locales, update them ([33cbe5e](https://github.com/pluginsGLPI/formcreator/commit/33cbe5e))
* rich description encoding in ticket ([#775](https://github.com/pluginsGLPI/formcreator/issues/775)) ([f739c54](https://github.com/pluginsGLPI/formcreator/commit/f739c54))
* **answer:** HTML entity decode for older textarea answers ([3612c3c](https://github.com/pluginsGLPI/formcreator/commit/3612c3c))
* **condition:** fix inconsistency when checking question conditions ([a820e55](https://github.com/pluginsGLPI/formcreator/commit/a820e55)), closes [#829](https://github.com/pluginsGLPI/formcreator/issues/829)
* **field:** avoid html entitization of accented chars ([a973f7b](https://github.com/pluginsGLPI/formcreator/commit/a973f7b))
* **form:** duplicate target changes when duplicating form ([7f78de9](https://github.com/pluginsGLPI/formcreator/commit/7f78de9))
* **form:** fix escaping and logic issues in duplication ([236effd](https://github.com/pluginsGLPI/formcreator/commit/236effd))
* **form:** repair massive acions ([7221644](https://github.com/pluginsGLPI/formcreator/commit/7221644))
* **form:** update target settings depending on questions ([7acbc11](https://github.com/pluginsGLPI/formcreator/commit/7acbc11))
* **form_answer:** restrict display of form answers to requesters and valdators ([8909e4e](https://github.com/pluginsGLPI/formcreator/commit/8909e4e)), closes [#869](https://github.com/pluginsGLPI/formcreator/issues/869)
* **install:** detect version 2.6 without schema version, see [#794](https://github.com/pluginsGLPI/formcreator/issues/794) ([decaafe](https://github.com/pluginsGLPI/formcreator/commit/decaafe))
* **install:** fix inconsistencies in install process" ([99eb790](https://github.com/pluginsGLPI/formcreator/commit/99eb790))
* **install:** fresh 2.6.0 install inconsistent ([903a13a](https://github.com/pluginsGLPI/formcreator/commit/903a13a))
* **install:** fresh installation does not saves current schema version ([8eadd7d](https://github.com/pluginsGLPI/formcreator/commit/8eadd7d)), closes [#794](https://github.com/pluginsGLPI/formcreator/issues/794)
* **install:** inconsistency in fresh 2.6.0 install ([e41a86d](https://github.com/pluginsGLPI/formcreator/commit/e41a86d)), closes [#822](https://github.com/pluginsGLPI/formcreator/issues/822)
* **install:** restore lost JSON type creation ([40afda3](https://github.com/pluginsGLPI/formcreator/commit/40afda3))
* **install:** run issues synchronization after install ([2441d02](https://github.com/pluginsGLPI/formcreator/commit/2441d02))
* **issue:** bad search option ([bc4bec8](https://github.com/pluginsGLPI/formcreator/commit/bc4bec8))
* **issue:** issue not updated to refused status ([8b1e3b8](https://github.com/pluginsGLPI/formcreator/commit/8b1e3b8))
* **issue:** wrong  ticket disdplay ([5e33407](https://github.com/pluginsGLPI/formcreator/commit/5e33407)), closes [#859](https://github.com/pluginsGLPI/formcreator/issues/859)
* **locale:** bad domain for some locales ([1d9ff65](https://github.com/pluginsGLPI/formcreator/commit/1d9ff65))
* **locales:** add missing strings; update locales ([792a6c2](https://github.com/pluginsGLPI/formcreator/commit/792a6c2))
* **locales:** follow change of a localizable string from GLPI 9.1 ([75a1057](https://github.com/pluginsGLPI/formcreator/commit/75a1057))
* **locales:** harmonize and fix locales ([62076ed](https://github.com/pluginsGLPI/formcreator/commit/62076ed))
* **question:** fix duplicate code ([779a5c3](https://github.com/pluginsGLPI/formcreator/commit/779a5c3))
* **question:** fix escaping issues with regexes ([c807936](https://github.com/pluginsGLPI/formcreator/commit/c807936))
* **question:** fix typo breaking duplication ([e7d2b0e](https://github.com/pluginsGLPI/formcreator/commit/e7d2b0e))
* **question:** remove abusive encoding ([f183091](https://github.com/pluginsGLPI/formcreator/commit/f183091))
* **rule:** location affectation on ticket via business rule ([06d6461](https://github.com/pluginsGLPI/formcreator/commit/06d6461)), closes [#795](https://github.com/pluginsGLPI/formcreator/issues/795)
* **section:** delete a section displays an error ([1d1eb93](https://github.com/pluginsGLPI/formcreator/commit/1d1eb93))
* **selectfield:** workaround GLPI issue 3308 ([d086006](https://github.com/pluginsGLPI/formcreator/commit/d086006))
* **target:** do not mention the absence of an uploaded document in targets ([f1ac36b](https://github.com/pluginsGLPI/formcreator/commit/f1ac36b))
* **target:** fix HTML issues in generated tickets ([278c628](https://github.com/pluginsGLPI/formcreator/commit/278c628))
* **target:** fix typo preventing requester groups being added to  targets ([ececfe3](https://github.com/pluginsGLPI/formcreator/commit/ececfe3)), closes [#767](https://github.com/pluginsGLPI/formcreator/issues/767)
* **target:** fix warnings in  timeline when no fiel uploaded ([9c94128](https://github.com/pluginsGLPI/formcreator/commit/9c94128))
* **target:** rename a target overriden by a global var ([f5b14a9](https://github.com/pluginsGLPI/formcreator/commit/f5b14a9))
* **target-change:** nug handling the comment field of a target change ([5371da5](https://github.com/pluginsGLPI/formcreator/commit/5371da5))
* **targetchange:** fix reversed condition ([e2288bf](https://github.com/pluginsGLPI/formcreator/commit/e2288bf))
* **targetticket:** fix entity of generated ticket ([1ea5325](https://github.com/pluginsGLPI/formcreator/commit/1ea5325))
* **targetticket:** follow change in GLPI for due date ([efa5fcb](https://github.com/pluginsGLPI/formcreator/commit/efa5fcb))
* **targetticket,targetchange:** ticket and change rendering without rich text mode ([d723a47](https://github.com/pluginsGLPI/formcreator/commit/d723a47)), closes [#847](https://github.com/pluginsGLPI/formcreator/issues/847)
* **ui:** css ([c907214](https://github.com/pluginsGLPI/formcreator/commit/c907214))
* **ui:** dont force layout for service catalog ([617e8f1](https://github.com/pluginsGLPI/formcreator/commit/617e8f1))
* **ui:** pqselect enabled not loaded every time it is needed ([#768](https://github.com/pluginsGLPI/formcreator/issues/768)) ([22f3508](https://github.com/pluginsGLPI/formcreator/commit/22f3508))
* **ui:** tinymce may ot load ([86893f4](https://github.com/pluginsGLPI/formcreator/commit/86893f4))
* **ui:** too long localized string ([c83323d](https://github.com/pluginsGLPI/formcreator/commit/c83323d))
* **wizard:** bookmark was renamed into saved search i GLPI 9.2 ([02c2877](https://github.com/pluginsGLPI/formcreator/commit/02c2877)), closes [#799](https://github.com/pluginsGLPI/formcreator/issues/799)


### Features

* **file:** use enhanced file field ([988136a](https://github.com/pluginsGLPI/formcreator/commit/988136a))
* **install:** prepare upgrade code ([0c8c64f](https://github.com/pluginsGLPI/formcreator/commit/0c8c64f))



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
