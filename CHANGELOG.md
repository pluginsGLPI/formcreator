<a name="v2.10.0"></a>
## [v2.10.0](https://github.com/pluginsglpi/formcreator/compare/v2.9.1..v2.10.0) (2020-01-22)


### Bug Fixes

*  avoid caps in filenames ([918a88da](https://github.com/pluginsglpi/formcreator/commit/918a88da))
* **build:** invert order of versions in changelog ([ff604eef](https://github.com/pluginsglpi/formcreator/commit/ff604eef))
* **condition:** export broken ([9b7a6923](https://github.com/pluginsglpi/formcreator/commit/9b7a6923))
* **condition:** permit update of conditionnable items without specifying conditions agaiin ([dc183425](https://github.com/pluginsglpi/formcreator/commit/dc183425))
* **condition:** use of constants ([19d1e71d](https://github.com/pluginsglpi/formcreator/commit/19d1e71d))
* **filefield:** php warning when editing the question ([9d8eb554](https://github.com/pluginsglpi/formcreator/commit/9d8eb554))
* **filefield:** show download links when field is read only ([dc6905f8](https://github.com/pluginsglpi/formcreator/commit/dc6905f8))
* **form:** my last form (validator) were not sorted ([6c2b0be9](https://github.com/pluginsglpi/formcreator/commit/6c2b0be9))
* **form:** single quotes around a table name ([04650950](https://github.com/pluginsglpi/formcreator/commit/04650950)), closes [#1606](https://github.com/pluginsglpi/formcreator/issues/1606)
* **fotrm:** some icons may be not displayed ([69786c67](https://github.com/pluginsglpi/formcreator/commit/69786c67))
* **install:** quote escaping when uprgading to 2.9.0 ([678e5864](https://github.com/pluginsglpi/formcreator/commit/678e5864))
* **multiselectfield:** visible JS ([bb77c0e8](https://github.com/pluginsglpi/formcreator/commit/bb77c0e8))
* **question:** SQL errors when deleting a question ([f3f4899e](https://github.com/pluginsglpi/formcreator/commit/f3f4899e))
* **question:** inoperant buttons to move questions ([3a191ebd](https://github.com/pluginsglpi/formcreator/commit/3a191ebd))
* **question:** javascript code was displayed ([a7be9314](https://github.com/pluginsglpi/formcreator/commit/a7be9314))
* **question:** space betwen icon and name ([4019a4d2](https://github.com/pluginsglpi/formcreator/commit/4019a4d2))
* **question:** update JS selectors for question edit ([0969ad1b](https://github.com/pluginsglpi/formcreator/commit/0969ad1b))
* **question:** update of parameters broken ([14188596](https://github.com/pluginsglpi/formcreator/commit/14188596))
* **radiosfield:** better overlap prevention ([fad550de](https://github.com/pluginsglpi/formcreator/commit/fad550de))
* **radiosfield:** overlapped long labels ([6528a167](https://github.com/pluginsglpi/formcreator/commit/6528a167))
* **robo:** prevent exception when computong log with commit without body ([e5aa246a](https://github.com/pluginsglpi/formcreator/commit/e5aa246a))
* **section:** conditions not duplicated ([78a3d9c5](https://github.com/pluginsglpi/formcreator/commit/78a3d9c5))
* **selectfield:** comparison with empty string ([bcb929e3](https://github.com/pluginsglpi/formcreator/commit/bcb929e3))
* **targetchange:** bad url when delete an actor ([62e0de19](https://github.com/pluginsglpi/formcreator/commit/62e0de19)), closes [#1607](https://github.com/pluginsglpi/formcreator/issues/1607)
* **targetticket,targetchange:** inverted show / hide for urgenty settings ([9be5e3aa](https://github.com/pluginsglpi/formcreator/commit/9be5e3aa))
* **targetticket,targetchange:** update constants for due date resolution in JS code ([4ddb6e8f](https://github.com/pluginsglpi/formcreator/commit/4ddb6e8f))
* **textarea:** workaround GLPI bug in 9.4.5 ([8de43588](https://github.com/pluginsglpi/formcreator/commit/8de43588)), closes [#1613](https://github.com/pluginsglpi/formcreator/issues/1613)


### Features

* **form:** condition on submit button ([73af77db](https://github.com/pluginsglpi/formcreator/commit/73af77db))
* **targetticket:** set type from question ([d23759aa](https://github.com/pluginsglpi/formcreator/commit/d23759aa))



<a name="v2.9.1"></a>
## [v2.9.1](https://github.com/pluginsglpi/formcreator/compare/v2.9.0..v2.9.1) (2020-01-13)


### Bug Fixes

*  useless escaping ([812c76d3](https://github.com/pluginsglpi/formcreator/commit/812c76d3))
* **condition:** inability to add a rows to conditions (#1598) ([bb1f2e4d](https://github.com/pluginsglpi/formcreator/commit/bb1f2e4d))
* **form:** bad call to count validators ([e6f4bc8e](https://github.com/pluginsglpi/formcreator/commit/e6f4bc8e))
* **form:** purge message if answers exist ([fa0c1dd0](https://github.com/pluginsglpi/formcreator/commit/fa0c1dd0))
* **form:** show error if failure in import of a sub item ([8dfab243](https://github.com/pluginsglpi/formcreator/commit/8dfab243))
* **form:** typo in var name ([582d37c8](https://github.com/pluginsglpi/formcreator/commit/582d37c8))
* **formanswer:** print icon ([2d871a38](https://github.com/pluginsglpi/formcreator/commit/2d871a38))
* **formanswer:** viewing answers causes a fatal error ([5453a927](https://github.com/pluginsglpi/formcreator/commit/5453a927))
* **question:** quote escaping when importing questions ([bb0d6d25](https://github.com/pluginsglpi/formcreator/commit/bb0d6d25))
* **target_actor,form_validator:** use statement for exception class ([ddbc5b23](https://github.com/pluginsglpi/formcreator/commit/ddbc5b23))
* **targetticket:** determine requester when answer is valdiated ([19b8232a](https://github.com/pluginsglpi/formcreator/commit/19b8232a)), closes [#50](https://github.com/pluginsglpi/formcreator/issues/50)
* **targetticket,targetchange:** loss of the target name when duplicating ([897c564f](https://github.com/pluginsglpi/formcreator/commit/897c564f))
* **targetticket,targetchange:** obsolete mandatory mark ([1a29a666](https://github.com/pluginsglpi/formcreator/commit/1a29a666))



<a name="v2.9.0"></a>
## [v2.9.0](https://github.com/pluginsglpi/formcreator/compare/v2.9.0-beta.1..v2.9.0) (2019-12-17)


### Bug Fixes

*  compatibility with GLPI 9.5 ([f818178d](https://github.com/pluginsglpi/formcreator/commit/f818178d))
*  duplicate menu entry ([4075b1ec](https://github.com/pluginsglpi/formcreator/commit/4075b1ec))
*  duplicate menu entry ([c4d47920](https://github.com/pluginsglpi/formcreator/commit/c4d47920))
*  extended service catalog ([8d5879bd](https://github.com/pluginsglpi/formcreator/commit/8d5879bd))
*  fa data prerequisite check ([74c83bb5](https://github.com/pluginsglpi/formcreator/commit/74c83bb5))
*  loading resources for anonymous forms ([58b71417](https://github.com/pluginsglpi/formcreator/commit/58b71417)), closes [#1535](https://github.com/pluginsglpi/formcreator/issues/1535)
*  local problem in menu ([861a7363](https://github.com/pluginsglpi/formcreator/commit/861a7363)), closes [#1516](https://github.com/pluginsglpi/formcreator/issues/1516)
*  not loaded resource for anonymous form ([bea27a1b](https://github.com/pluginsglpi/formcreator/commit/bea27a1b)), closes [#1536](https://github.com/pluginsglpi/formcreator/issues/1536)
*  unexpected redirection while editing a ticket as post-only + service catalog ([63f3ceec](https://github.com/pluginsglpi/formcreator/commit/63f3ceec))
*  unexpected redirection while editing a ticket as post-only + service catalog ([266d9d31](https://github.com/pluginsglpi/formcreator/commit/266d9d31))
* **actorsfield:** dropdown does not show all items ([95a3a512](https://github.com/pluginsglpi/formcreator/commit/95a3a512))
* **category:** bad sub categories count ([40f8071e](https://github.com/pluginsglpi/formcreator/commit/40f8071e))
* **category:** bad sub categories count ([ef68bb60](https://github.com/pluginsglpi/formcreator/commit/ef68bb60))
* **category:** translation support ([495c8dc5](https://github.com/pluginsglpi/formcreator/commit/495c8dc5))
* **condition:** misordered display on edit ([2e33592c](https://github.com/pluginsglpi/formcreator/commit/2e33592c))
* **dropdown:** show ID in items ([dc402d93](https://github.com/pluginsglpi/formcreator/commit/dc402d93))
* **dropdownfield:** root and limit miscomputations ([9e8cc738](https://github.com/pluginsglpi/formcreator/commit/9e8cc738))
* **dropdownfield:** unwanted single quote escaping when rendering target ticket ([c149cd47](https://github.com/pluginsglpi/formcreator/commit/c149cd47))
* **faq:** errors in FAQ list ([380e9ca5](https://github.com/pluginsglpi/formcreator/commit/380e9ca5))
* **field:** width of textareas in fields settings ([1e2c07b5](https://github.com/pluginsglpi/formcreator/commit/1e2c07b5))
* **floatfield,integerfield,textfield:** fix escaping in regex ([ca0eb4ee](https://github.com/pluginsglpi/formcreator/commit/ca0eb4ee))
* **floatfield,integerfield,textfield:** fix escaping in regex ([4a122353](https://github.com/pluginsglpi/formcreator/commit/4a122353))
* **form:** anonymous forms don't load JS ([9d3ae4e0](https://github.com/pluginsglpi/formcreator/commit/9d3ae4e0))
* **form:** deny access if form not enabled ([5d4489d8](https://github.com/pluginsglpi/formcreator/commit/5d4489d8))
* **form:** deny access if form not enabled ([0290a979](https://github.com/pluginsglpi/formcreator/commit/0290a979))
* **form:** don't access deleted forms ([6169412d](https://github.com/pluginsglpi/formcreator/commit/6169412d))
* **form:** duplicate question conditions ([552fe398](https://github.com/pluginsglpi/formcreator/commit/552fe398))
* **form:** import of form category with single quote ([39f98da0](https://github.com/pluginsglpi/formcreator/commit/39f98da0))
* **form:** obey redirect setting after creation ([314ff9cb](https://github.com/pluginsglpi/formcreator/commit/314ff9cb))
* **form:** tile height shall not be fixed ([bfb273fe](https://github.com/pluginsglpi/formcreator/commit/bfb273fe))
* **form:** undefined var used ([1b70a259](https://github.com/pluginsglpi/formcreator/commit/1b70a259))
* **form:** uninitialized var ([542f6866](https://github.com/pluginsglpi/formcreator/commit/542f6866))
* **formanswer:** always check for ticket validation ([24faaf7b](https://github.com/pluginsglpi/formcreator/commit/24faaf7b))
* **formanswer:** bad key for form ID ([58dc3d39](https://github.com/pluginsglpi/formcreator/commit/58dc3d39))
* **formanswer:** bad sql ([b7a78b44](https://github.com/pluginsglpi/formcreator/commit/b7a78b44))
* **formanswer:** bad sql ([023a60e3](https://github.com/pluginsglpi/formcreator/commit/023a60e3))
* **formanswer:** better restrict list of formanswers ([b918f211](https://github.com/pluginsglpi/formcreator/commit/b918f211))
* **formanswer:** canViewItem with group ([4c226003](https://github.com/pluginsglpi/formcreator/commit/4c226003))
* **formanswer:** canViewItem with group ([e770b08c](https://github.com/pluginsglpi/formcreator/commit/e770b08c))
* **formanswer:** missing icon for form valisation status ([2f8b5338](https://github.com/pluginsglpi/formcreator/commit/2f8b5338))
* **formanswer:** more permissive READ access to formanswers ([e2eda19f](https://github.com/pluginsglpi/formcreator/commit/e2eda19f))
* **formanswer:** restore lost method ([dddf8300](https://github.com/pluginsglpi/formcreator/commit/dddf8300))
* **instal:** useless columns in schema of fresh install ([8f54c952](https://github.com/pluginsglpi/formcreator/commit/8f54c952))
* **install:** database schema inconsistencies between instal and upgrade ([46ac7ada](https://github.com/pluginsglpi/formcreator/commit/46ac7ada))
* **install:** inconsistency between install and upgrade ([3c8b0b28](https://github.com/pluginsglpi/formcreator/commit/3c8b0b28))
* **install:** loss of condition on upgrade ([b9de3fd2](https://github.com/pluginsglpi/formcreator/commit/b9de3fd2))
* **install:** remove deletes answers when dropping is_deleted ([3ed1515f](https://github.com/pluginsglpi/formcreator/commit/3ed1515f)), closes [#1513](https://github.com/pluginsglpi/formcreator/issues/1513)
* **install:** reorder key changes on conditions table ([701abeec](https://github.com/pluginsglpi/formcreator/commit/701abeec))
* **install:** reorder key changes on conditions table ([82838a2c](https://github.com/pluginsglpi/formcreator/commit/82838a2c))
* **install:** schema mismatch betwheen install and upgrade ([fd99d933](https://github.com/pluginsglpi/formcreator/commit/fd99d933))
* **install:** upgrade to 2.7 misses range for select and textarea ([61e49d6f](https://github.com/pluginsglpi/formcreator/commit/61e49d6f))
* **integerfield,floadfield:** avoid integrity checks in parseAnswerValue ([3cbaf611](https://github.com/pluginsglpi/formcreator/commit/3cbaf611))
* **issue:** missing status for all statuses ([41cdc487](https://github.com/pluginsglpi/formcreator/commit/41cdc487))
* **issue:** size of text content for issue ([a72ce860](https://github.com/pluginsglpi/formcreator/commit/a72ce860))
* **locales:** drop unwanted file ([a7429ebc](https://github.com/pluginsglpi/formcreator/commit/a7429ebc))
* **locales:** plural problem ([d3e06ae9](https://github.com/pluginsglpi/formcreator/commit/d3e06ae9))
* **question:** handle cascaded show/hide conditions ([76718c14](https://github.com/pluginsglpi/formcreator/commit/76718c14))
* **question:** locale issue cause tests to fail ([51148a34](https://github.com/pluginsglpi/formcreator/commit/51148a34))
* **question_condition:** better performance ([0fc6aea5](https://github.com/pluginsglpi/formcreator/commit/0fc6aea5))
* **question_condition:** better performance ([8a542580](https://github.com/pluginsglpi/formcreator/commit/8a542580))
* **qusetion:** remove strict comparison ([fe256826](https://github.com/pluginsglpi/formcreator/commit/fe256826))
* **selectfield:** select field cannot support range ([5138ac16](https://github.com/pluginsglpi/formcreator/commit/5138ac16))
* **tagfield:** show in saved answers the tag names ([19a6c2b6](https://github.com/pluginsglpi/formcreator/commit/19a6c2b6))
* **tags:** bad tag filter when selecting tags for target ticket ([299ba2c6](https://github.com/pluginsglpi/formcreator/commit/299ba2c6))
* **target:** bad constants ([78c12a81](https://github.com/pluginsglpi/formcreator/commit/78c12a81))
* **target_actor,change_actor:** fix duplciation ([772fecdb](https://github.com/pluginsglpi/formcreator/commit/772fecdb))
* **targetticket:** fix tags handling ([47db2d8b](https://github.com/pluginsglpi/formcreator/commit/47db2d8b))
* **targetticket:** missing JS code, typo ([072258f0](https://github.com/pluginsglpi/formcreator/commit/072258f0))
* **targetticket,targetchange:** fix not rendered fields ([fd25d4ef](https://github.com/pluginsglpi/formcreator/commit/fd25d4ef))
* **targetticket,targetchange:** remove HTML code tag ([e7cabe7d](https://github.com/pluginsglpi/formcreator/commit/e7cabe7d))
* **targetticket,targetchange:** remove more code tags ([a32b0565](https://github.com/pluginsglpi/formcreator/commit/a32b0565))
* **targetticket,targetchange:** return value of save() method ([cbc22499](https://github.com/pluginsglpi/formcreator/commit/cbc22499))
* **textfield,actorsfield:** missing default value on edit ([d9327ac1](https://github.com/pluginsglpi/formcreator/commit/d9327ac1))
* **wizard:** fix inconsistencies in counters ([ee0b9873](https://github.com/pluginsglpi/formcreator/commit/ee0b9873))
* **wizard:** form categories may show when they are empty ([37edabf4](https://github.com/pluginsglpi/formcreator/commit/37edabf4))
* **wizard:** inconsistency between helpesk and service catalog ([a41bbe44](https://github.com/pluginsglpi/formcreator/commit/a41bbe44))


### Features

*  compatibility with GLPI 9.5 ([135ec44f](https://github.com/pluginsglpi/formcreator/commit/135ec44f))
* **form:** auto select validator if only one avaialble ([79ad2f9f](https://github.com/pluginsglpi/formcreator/commit/79ad2f9f))
* **ldapfield:** comparisons support ([9c553237](https://github.com/pluginsglpi/formcreator/commit/9c553237)), closes [#1454](https://github.com/pluginsglpi/formcreator/issues/1454)
* **question:** bring a question to 1st position ([9ed109e7](https://github.com/pluginsglpi/formcreator/commit/9ed109e7))
* **question:** use font awesome ([bf7b2742](https://github.com/pluginsglpi/formcreator/commit/bf7b2742))
* **section:** show conditions ([0d416501](https://github.com/pluginsglpi/formcreator/commit/0d416501))
* **timefield:** time field ([e4a430e4](https://github.com/pluginsglpi/formcreator/commit/e4a430e4))



<a name="v2.9.0-beta.1"></a>
## [v2.9.0-beta.1](https://github.com/pluginsglpi/formcreator/compare/v2.8.6..v2.9.0-beta.1) (2019-10-02)


### Bug Fixes

*  bad class name ([2535f4a4](https://github.com/pluginsglpi/formcreator/commit/2535f4a4))
*  fix refactor bugs, update unit tests ([a0704def](https://github.com/pluginsglpi/formcreator/commit/a0704def))
*  security when loading a class ([0e15eac0](https://github.com/pluginsglpi/formcreator/commit/0e15eac0))
* **category:** bad conversion from raw SQL to quiery builder ([9cbbfe78](https://github.com/pluginsglpi/formcreator/commit/9cbbfe78))
* **dropdownfield:** typo in method call ([dd30b306](https://github.com/pluginsglpi/formcreator/commit/dd30b306))
* **dropdownfield:** various errors ([2134e8e9](https://github.com/pluginsglpi/formcreator/commit/2134e8e9))
* **dropdownfield:** wrong var in join ([e4fdff39](https://github.com/pluginsglpi/formcreator/commit/e4fdff39))
* **dropdownfield:** wrong var in where ([cce35fa0](https://github.com/pluginsglpi/formcreator/commit/cce35fa0))
* **field:** default value for most fields ([f05f2de3](https://github.com/pluginsglpi/formcreator/commit/f05f2de3))
* **form:** SQL errors when counting available forms ([66a03378](https://github.com/pluginsglpi/formcreator/commit/66a03378))
* **form:** anonymous forms don't load JS ([0510cc71](https://github.com/pluginsglpi/formcreator/commit/0510cc71))
* **form:** bad classname ([6ef88de9](https://github.com/pluginsglpi/formcreator/commit/6ef88de9))
* **form:** bad method name ([af42d419](https://github.com/pluginsglpi/formcreator/commit/af42d419))
* **form:** broken add target form ([c591a483](https://github.com/pluginsglpi/formcreator/commit/c591a483))
* **form:** duplicated form needs a different name ([404232b4](https://github.com/pluginsglpi/formcreator/commit/404232b4))
* **form:** get form from question ID ([2d28fe3d](https://github.com/pluginsglpi/formcreator/commit/2d28fe3d))
* **form:** have default values for color and icon ([c2e360e9](https://github.com/pluginsglpi/formcreator/commit/c2e360e9))
* **form:** import of form category with single quote ([ba82bd1e](https://github.com/pluginsglpi/formcreator/commit/ba82bd1e))
* **form:** import of forms in non existing entity ([00ae3b14](https://github.com/pluginsglpi/formcreator/commit/00ae3b14))
* **form:** menu name to access form from assistance menu ([abd9860b](https://github.com/pluginsglpi/formcreator/commit/abd9860b))
* **form:** requests for helpdesk home forms ([983c9cac](https://github.com/pluginsglpi/formcreator/commit/983c9cac))
* **form:** validator setting broken ([e431d5df](https://github.com/pluginsglpi/formcreator/commit/e431d5df))
* **form_profile:** broken restrictions settings ([cd26e033](https://github.com/pluginsglpi/formcreator/commit/cd26e033))
* **form_profile:** undeclared vars ([a6c07f37](https://github.com/pluginsglpi/formcreator/commit/a6c07f37))
* **formanswer:** better restrict list of formanswers ([3a0cc92e](https://github.com/pluginsglpi/formcreator/commit/3a0cc92e))
* **formanswer:** fix code refactor ([70bc39dd](https://github.com/pluginsglpi/formcreator/commit/70bc39dd))
* **formanswer:** more permissive READ access to formanswers ([e4d4c24c](https://github.com/pluginsglpi/formcreator/commit/e4d4c24c))
* **formanswer:** use of undefined variables ([363366f7](https://github.com/pluginsglpi/formcreator/commit/363366f7))
* **install:** add new colomns for upgrade ([9c5d50f2](https://github.com/pluginsglpi/formcreator/commit/9c5d50f2))
* **install:** bad associate rule default on upgrade ([cd4007b3](https://github.com/pluginsglpi/formcreator/commit/cd4007b3))
* **install:** default value for a column ([5eae51c9](https://github.com/pluginsglpi/formcreator/commit/5eae51c9))
* **install:** inconsistency between install and upgrade ([78d4eb32](https://github.com/pluginsglpi/formcreator/commit/78d4eb32))
* **install:** move upgrade to 2.9 ([b9ad9a35](https://github.com/pluginsglpi/formcreator/commit/b9ad9a35))
* **install:** update matrix of version upgrade ([bd9f74a3](https://github.com/pluginsglpi/formcreator/commit/bd9f74a3))
* **install:** upgrade to 2.7 misses range for select and textarea ([c5ee7d18](https://github.com/pluginsglpi/formcreator/commit/c5ee7d18))
* **install:** wrong version to add item association feature in DB ([3d1afa33](https://github.com/pluginsglpi/formcreator/commit/3d1afa33))
* **integerfield,floadfield:** avoid integrity checks in parseAnswerValue ([621f0368](https://github.com/pluginsglpi/formcreator/commit/621f0368))
* **issue:** missing status for all statuses ([8d97354d](https://github.com/pluginsglpi/formcreator/commit/8d97354d))
* **linker:** inverted arguments ([05927924](https://github.com/pluginsglpi/formcreator/commit/05927924))
* **question:** handle cascaded show/hide conditions ([de58991e](https://github.com/pluginsglpi/formcreator/commit/de58991e))
* **question:** misplaced field ([8d98ac80](https://github.com/pluginsglpi/formcreator/commit/8d98ac80))
* **question_condition:** fix non static method ([f7df70fa](https://github.com/pluginsglpi/formcreator/commit/f7df70fa))
* **selectfield:** duplicate declaration of method ([45d0e34a](https://github.com/pluginsglpi/formcreator/commit/45d0e34a))
* **selectfield:** select field cannot support range ([eca4175e](https://github.com/pluginsglpi/formcreator/commit/eca4175e))
* **tags:** bad tag filter when selecting tags for target ticket ([7c3f451c](https://github.com/pluginsglpi/formcreator/commit/7c3f451c))
* **target:** malformed query ([4e01b385](https://github.com/pluginsglpi/formcreator/commit/4e01b385))
* **target_actor:** export / import issue ([a88df902](https://github.com/pluginsglpi/formcreator/commit/a88df902))
* **targetchange,targetticket:** PluginFormcreatorTarget itemtype removed ([dedb4129](https://github.com/pluginsglpi/formcreator/commit/dedb4129))
* **targetticket:** associate item to ticket ([001976fc](https://github.com/pluginsglpi/formcreator/commit/001976fc))
* **targetticket:** fix tags handling ([eca75f2f](https://github.com/pluginsglpi/formcreator/commit/eca75f2f))
* **targetticket:** js error for associated element settings ([3548783e](https://github.com/pluginsglpi/formcreator/commit/3548783e))
* **targetticket:** missing method ([5844fdd9](https://github.com/pluginsglpi/formcreator/commit/5844fdd9))
* **targetticket,targetchange:** editing actors ([87d7bc5d](https://github.com/pluginsglpi/formcreator/commit/87d7bc5d))
* **targetticket,targetchange:** errors while dropping raw queries ([216265da](https://github.com/pluginsglpi/formcreator/commit/216265da))
* **targetticket,targetchange:** fix array index ([5ab24606](https://github.com/pluginsglpi/formcreator/commit/5ab24606))
* **targetticket,targetchange:** fix display of actors ([93597c04](https://github.com/pluginsglpi/formcreator/commit/93597c04))
* **targetticket,targetchange:** fix misuse of constants ([04e6df06](https://github.com/pluginsglpi/formcreator/commit/04e6df06))
* **targetticket,targetchange:** fix not rendered fields ([7f67076f](https://github.com/pluginsglpi/formcreator/commit/7f67076f))
* **targetticket,targetchange:** fix(targetticket,targetchange): remove HTML code tag ([9ef4fc31](https://github.com/pluginsglpi/formcreator/commit/9ef4fc31))
* **targetticket,targetchange:** return value of save() method ([fa4f7854](https://github.com/pluginsglpi/formcreator/commit/fa4f7854))
* **targetticket,targetchange:** set name of target ([7b90ef4e](https://github.com/pluginsglpi/formcreator/commit/7b90ef4e))
* **targetticket,targetchange:** unable to select email questions for actors from questions ([b466d341](https://github.com/pluginsglpi/formcreator/commit/b466d341))
* **textarea,text:** question designer adjustments ([84e6b959](https://github.com/pluginsglpi/formcreator/commit/84e6b959))
* **textareafield:** paste images reachs limit of field in DB ([84e9c685](https://github.com/pluginsglpi/formcreator/commit/84e9c685))
* **textfield:** wrong method signature ([96838072](https://github.com/pluginsglpi/formcreator/commit/96838072))
* **wizard:** form categories may show when they are empty ([d3182ac4](https://github.com/pluginsglpi/formcreator/commit/d3182ac4))
* **wizard:** inconsistency between helpesk and service catalog ([9a00c270](https://github.com/pluginsglpi/formcreator/commit/9a00c270))
* **wizard:** various errors displaying forms in service catalog ([309e7e49](https://github.com/pluginsglpi/formcreator/commit/309e7e49))


### Features

* **dropdownfield:** root and depth settings for all CommonTreeDropdown ([2150e645](https://github.com/pluginsglpi/formcreator/commit/2150e645))
* **form:** customizable icon from Fonte Awesome ([8ba9c785](https://github.com/pluginsglpi/formcreator/commit/8ba9c785))
* **form:** customize color of icon ([3c340f3b](https://github.com/pluginsglpi/formcreator/commit/3c340f3b))
* **form:** customize icon ([7635f0e3](https://github.com/pluginsglpi/formcreator/commit/7635f0e3))
* **form:** set background color of tile ([d6e40a2b](https://github.com/pluginsglpi/formcreator/commit/d6e40a2b))
* **ldapfield:** comparisons support ([1cb94ade](https://github.com/pluginsglpi/formcreator/commit/1cb94ade)), closes [#1454](https://github.com/pluginsglpi/formcreator/issues/1454)
* **target:** remove target itemtype ([41bc1257](https://github.com/pluginsglpi/formcreator/commit/41bc1257))
* **targetticket:** associate assets to tickets ([c9e3d1e4](https://github.com/pluginsglpi/formcreator/commit/c9e3d1e4))
* **targetticket:** ticket type ([fa432f78](https://github.com/pluginsglpi/formcreator/commit/fa432f78))
* **wizard:** separate faqs and forms ([a08541fc](https://github.com/pluginsglpi/formcreator/commit/a08541fc))


<a name="2.8.6"></a>
## [2.8.6](https://github.com/pluginsglpi/formcreator/compare/v2.8.5...v2.8.6) (2019-11-07)


### Bug Fixes

* **form:** deny access if form not enabled ([0290a97](https://github.com/pluginsglpi/formcreator/commit/0290a97))
* **form:** don't access deleted forms ([6169412](https://github.com/pluginsglpi/formcreator/commit/6169412))
* **formanswer:** bad sql ([023a60e](https://github.com/pluginsglpi/formcreator/commit/023a60e))
* loading resources for anonymous forms ([58b7141](https://github.com/pluginsglpi/formcreator/commit/58b7141)), closes [#1535](https://github.com/pluginsglpi/formcreator/issues/1535)
* **formanswer:** canViewItem with group ([4c22600](https://github.com/pluginsglpi/formcreator/commit/4c22600))
* unexpected redirection while editing a ticket as post-only + service catalog ([63f3cee](https://github.com/pluginsglpi/formcreator/commit/63f3cee)), closes [#1557](https://github.com/pluginsglpi/formcreator/issues/1557)
* **question_condition:** better performance ([0fc6aea](https://github.com/pluginsglpi/formcreator/commit/0fc6aea))
* **targetticket,targetchange:** return value of save() method ([cbc2249](https://github.com/pluginsglpi/formcreator/commit/cbc2249))



<a name="2.8.5"></a>
## [2.8.5](https://github.com/pluginsglpi/formcreator/compare/v2.8.4...v2.8.5) (2019-09-02)


### Bug Fixes

* duplicate menu entry ([4075b1e](https://github.com/pluginsglpi/formcreator/commit/4075b1e))



<a name="2.8.4"></a>
## [2.8.4](https://github.com/pluginsglpi/formcreator/compare/v2.8.3...v2.8.4) (2019-08-21)


### Bug Fixes

* **dropdownfield:** restrict itemtypes assignables to ticket ([98a76f2](https://github.com/pluginsglpi/formcreator/commit/98a76f2))
* **dropdownfield:** unwanted single quote escaping when rendering target ticket ([c149cd4](https://github.com/pluginsglpi/formcreator/commit/c149cd4))
* **form:** anonymous forms don't load JS ([9d3ae4e](https://github.com/pluginsglpi/formcreator/commit/9d3ae4e))
* **form:** duplicate question conditions ([552fe39](https://github.com/pluginsglpi/formcreator/commit/552fe39))
* **form:** import of form category with single quote ([39f98da](https://github.com/pluginsglpi/formcreator/commit/39f98da))
* **formanswer:** better restrict list of formanswers ([b918f21](https://github.com/pluginsglpi/formcreator/commit/b918f21))
* **formanswer:** more permissive READ access to formanswers ([e2eda19](https://github.com/pluginsglpi/formcreator/commit/e2eda19))
* **glpiobject:** make items more easily searchable ([0fd617b](https://github.com/pluginsglpi/formcreator/commit/0fd617b))
* **instal:** useless columns in schema of fresh install ([8f54c95](https://github.com/pluginsglpi/formcreator/commit/8f54c95))
* **install:** database schema inconsistencies between instal and upgrade ([46ac7ad](https://github.com/pluginsglpi/formcreator/commit/46ac7ad))
* **install:** inconsistency between install and upgrade ([3c8b0b2](https://github.com/pluginsglpi/formcreator/commit/3c8b0b2))
* **install:** move columns in somez tables ([2cecff7](https://github.com/pluginsglpi/formcreator/commit/2cecff7))
* **install:** possible upgrade issue ([d50c7f6](https://github.com/pluginsglpi/formcreator/commit/d50c7f6))
* **install:** upgrade to 2.7 misses range for select and textarea ([61e49d6](https://github.com/pluginsglpi/formcreator/commit/61e49d6))
* **integerfield,floadfield:** avoid integrity checks in parseAnswerValue ([3cbaf61](https://github.com/pluginsglpi/formcreator/commit/3cbaf61))
* **issue:** missing status for all statuses ([41cdc48](https://github.com/pluginsglpi/formcreator/commit/41cdc48))
* **locales:** drop unwanted file ([a7429eb](https://github.com/pluginsglpi/formcreator/commit/a7429eb))
* **question:** handle cascaded show/hide conditions ([76718c1](https://github.com/pluginsglpi/formcreator/commit/76718c1))
* **selectfield:** select field cannot support range ([5138ac1](https://github.com/pluginsglpi/formcreator/commit/5138ac1))
* **tagfield:** show in saved answers the tag names ([19a6c2b](https://github.com/pluginsglpi/formcreator/commit/19a6c2b))
* **tags:** bad tag filter when selecting tags for target ticket ([299ba2c](https://github.com/pluginsglpi/formcreator/commit/299ba2c))
* **target_actor,change_actor:** fix duplciation ([772fecd](https://github.com/pluginsglpi/formcreator/commit/772fecd))
* **targetticket:** fix tags handling ([47db2d8](https://github.com/pluginsglpi/formcreator/commit/47db2d8))
* **targetticket,targetchange:** fix not rendered fields ([fd25d4e](https://github.com/pluginsglpi/formcreator/commit/fd25d4e))
* **targetticket,targetchange:** remove HTML code tag ([e7cabe7](https://github.com/pluginsglpi/formcreator/commit/e7cabe7))
* **targetticket,targetchange:** remove more code tags ([a32b056](https://github.com/pluginsglpi/formcreator/commit/a32b056))
* **wizard:** form categories may show when they are empty ([37edabf](https://github.com/pluginsglpi/formcreator/commit/37edabf))
* **wizard:** inconsistency between helpesk and service catalog ([a41bbe4](https://github.com/pluginsglpi/formcreator/commit/a41bbe4))


### Features

* **ldapfield:** comparisons support ([9c55323](https://github.com/pluginsglpi/formcreator/commit/9c55323)), closes [#1454](https://github.com/pluginsglpi/formcreator/issues/1454)



<a name="2.8.3"></a>
## [2.8.3](https://github.com/pluginsglpi/formcreator/compare/v2.8.2...v2.8.3) (2019-06-13)


### Bug Fixes

* **filefield:** file upload mai fail ([c69a5d0](https://github.com/pluginsglpi/formcreator/commit/c69a5d0))
* **form:** import of forms in non existing entity ([8446e47](https://github.com/pluginsglpi/formcreator/commit/8446e47))
* **form:** missing log tab ([3ee8400](https://github.com/pluginsglpi/formcreator/commit/3ee8400))
* **form_validator:** possible call to non existing method ([7c85532](https://github.com/pluginsglpi/formcreator/commit/7c85532))
* **install:** upgrade from 2.5.x to 2.8 alters target ticket name ([f4a21e7](https://github.com/pluginsglpi/formcreator/commit/f4a21e7))
* **question_condition:** unable to use some comparisons ([fad48aa](https://github.com/pluginsglpi/formcreator/commit/fad48aa))
* **targetticket:** set default document category ([a5dc10d](https://github.com/pluginsglpi/formcreator/commit/a5dc10d))
* **targetticket,targetchange:** useless escaping ([529c592](https://github.com/pluginsglpi/formcreator/commit/529c592))


### Features

* **dropdown:** show serial and inventory number when available ([bb92244](https://github.com/pluginsglpi/formcreator/commit/bb92244))


<a name="2.8.2"></a>
## [2.8.2](https://github.com/pluginsglpi/formcreator/compare/v2.8.1...v2.8.2) (2019-05-02)


### Bug Fixes

* **dropdownfield:** upgraded fields from 2.5 may crash ([8233b75](https://github.com/pluginsglpi/formcreator/commit/8233b75))
* **filefield:** uploaded files lost ([1cec1e0](https://github.com/pluginsglpi/formcreator/commit/1cec1e0))
* **form:** redirect to formlist after filling a form ([51fe9ae](https://github.com/pluginsglpi/formcreator/commit/51fe9ae))
* **issue:** warnings with GLPI 9.3 ([04791f4](https://github.com/pluginsglpi/formcreator/commit/04791f4))
* **question:** quote escaping in import ([ed4b021](https://github.com/pluginsglpi/formcreator/commit/ed4b021))
* **serviceCatalog:** fix left menu for some languages ([f1bc390](https://github.com/pluginsglpi/formcreator/commit/f1bc390))



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
