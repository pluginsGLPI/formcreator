<a name="2.13.7"></a>
## [2.13.7](https://github.com/pluginsGLPI/formcreator/compare/2.13.6..2.13.7) (2023-07-19)


### Bug Fixes

*  Adding READ right for display reservations menu tab ([03e6281e](https://github.com/pluginsGLPI/formcreator/commit/03e6281e))
*  bad lcoale in en_US ([db9986f1](https://github.com/pluginsGLPI/formcreator/commit/db9986f1))
*  resize dashboard to match GLPI's core (#3306) ([9272cda3](https://github.com/pluginsGLPI/formcreator/commit/9272cda3))
*  show FAQ without tabs for self-service ([c5499ad4](https://github.com/pluginsGLPI/formcreator/commit/c5499ad4))
* **TargetChange:** use RichText instead of plaintext ([8845b888](https://github.com/pluginsGLPI/formcreator/commit/8845b888))
* **checkboxesfield,radiosfield,selectfield:** add missing error messages ([66585193](https://github.com/pluginsGLPI/formcreator/commit/66585193))
* **datefield, datetimefield:** comparison against empty string ([be4831c7](https://github.com/pluginsGLPI/formcreator/commit/be4831c7))
* **dropdownfield:** SQL error for GLPI objects / tickets and some specific rights ([2539e366](https://github.com/pluginsGLPI/formcreator/commit/2539e366))
* **dropdownfield:** handle specific case with Entity itemtype ([bd25e7d1](https://github.com/pluginsGLPI/formcreator/commit/bd25e7d1))
* **dropdownfield:** missing entity restriction setting ([54543cb3](https://github.com/pluginsGLPI/formcreator/commit/54543cb3))
* **dropdownfield:** prevent language switching and log error ([49f8fc07](https://github.com/pluginsGLPI/formcreator/commit/49f8fc07))
* **fieldsfield:** restore mandatory field as read only ([52a9fc2b](https://github.com/pluginsGLPI/formcreator/commit/52a9fc2b))
* **form,category:** obey show count on tabs parameter ([f4ebf9e5](https://github.com/pluginsGLPI/formcreator/commit/f4ebf9e5))
* **form_language:** obey show counter in tab setting ([9dfc3b8d](https://github.com/pluginsGLPI/formcreator/commit/9dfc3b8d))
* **formanswer:** php warning ([ce078990](https://github.com/pluginsGLPI/formcreator/commit/ce078990))
* **formanswer:** prevent silent rejection of answers ([d630302d](https://github.com/pluginsGLPI/formcreator/commit/d630302d))
* **formanswer:** redirect to login if session expired ([eb0acb65](https://github.com/pluginsGLPI/formcreator/commit/eb0acb65))
* **glpiselectfield:** fix namespace (#3287) ([613e0fad](https://github.com/pluginsGLPI/formcreator/commit/613e0fad))
* **install:** missing row in sql query, causing PHP warning ([0c47776a](https://github.com/pluginsGLPI/formcreator/commit/0c47776a))
* **issue:** php warnings when anonymisation enabled ([f6f01d7d](https://github.com/pluginsGLPI/formcreator/commit/f6f01d7d))
* **issue:** prevent fatal error in tooltip ([3419affc](https://github.com/pluginsGLPI/formcreator/commit/3419affc))
* **question,section:** duplicate a question or section must duplicate inner conditions ([22597832](https://github.com/pluginsGLPI/formcreator/commit/22597832))
* **section:** cannot rename section twice ([7bbb9b81](https://github.com/pluginsGLPI/formcreator/commit/7bbb9b81))
* **section:** condition rule loss after duplicate / import ([883a1227](https://github.com/pluginsGLPI/formcreator/commit/883a1227))
* **section:** duplicate form may lead to bad question id in condition ([a6f9c41c](https://github.com/pluginsGLPI/formcreator/commit/a6f9c41c))
* **section:** rename section impacts display of inner questions ([c4277d8c](https://github.com/pluginsGLPI/formcreator/commit/c4277d8c))
* **selectfield,multiselectfield:** fix possible encoding problem ([8aaec8ac](https://github.com/pluginsGLPI/formcreator/commit/8aaec8ac))
* **tag:** tag was always deleted ([6201d61d](https://github.com/pluginsGLPI/formcreator/commit/6201d61d))
* **targetchange,targetproblem:** folow method call signature for fields plugin ([016696ab](https://github.com/pluginsGLPI/formcreator/commit/016696ab))
* **textfield:** Unescaped HTML when displaying a form answer ([6ce71f95](https://github.com/pluginsGLPI/formcreator/commit/6ce71f95))
* **textfield:** exception while displaying counters ([0a857d7f](https://github.com/pluginsGLPI/formcreator/commit/0a857d7f))
* **textfield:** target ticket title need html encoding ([1b71d652](https://github.com/pluginsGLPI/formcreator/commit/1b71d652))



<a name="2.13.6"></a>
## [2.13.6](https://github.com/pluginsGLPI/formcreator/compare/2.13.5..2.13.6) (2023-05-26)


### Bug Fixes

*  SQL error when inconsistency in DB ([1af78720](https://github.com/pluginsGLPI/formcreator/commit/1af78720)), closes [#3242](https://github.com/pluginsGLPI/formcreator/issues/3242)
*  confirrmation strings ([8ced5744](https://github.com/pluginsGLPI/formcreator/commit/8ced5744))
*  translate field label in error messages ([a4bf10a6](https://github.com/pluginsGLPI/formcreator/commit/a4bf10a6))
* **DropdownField:** fix array key value format ([7729fe20](https://github.com/pluginsGLPI/formcreator/commit/7729fe20))
* **FormAnswer:** redirect to FormAnswer or to list if needed ([44e4ab6d](https://github.com/pluginsGLPI/formcreator/commit/44e4ab6d))
* **category:** SQL statement cause MariaDB crash ([fb94035a](https://github.com/pluginsGLPI/formcreator/commit/fb94035a))
* **checkboxesfield,multiselectfield:** php warning ([342c39e9](https://github.com/pluginsGLPI/formcreator/commit/342c39e9))
* **condition:** conditions don't work when not sanitized ([f2b0fad5](https://github.com/pluginsGLPI/formcreator/commit/f2b0fad5))
* **faq:** visibility (#3118) ([252ef204](https://github.com/pluginsGLPI/formcreator/commit/252ef204))
* **filefield:** rebuild uploads for answer edition ([4f1cdf6e](https://github.com/pluginsGLPI/formcreator/commit/4f1cdf6e))
* **form:** double post broken ([07b8a1a1](https://github.com/pluginsGLPI/formcreator/commit/07b8a1a1))
* **form_language:** inverted arguments ([261e5361](https://github.com/pluginsGLPI/formcreator/commit/261e5361))
* **form_language:** restrict languages to those known by GLPI ([f8dc0803](https://github.com/pluginsGLPI/formcreator/commit/f8dc0803))
* **formanswer:** catch and report exception to end user ([9dd9777f](https://github.com/pluginsGLPI/formcreator/commit/9dd9777f))
* **formanswer:** too many escaping ([e518b7de](https://github.com/pluginsGLPI/formcreator/commit/e518b7de)), closes [#3271](https://github.com/pluginsGLPI/formcreator/issues/3271)
* **formanswer:** translate question label ([61722daf](https://github.com/pluginsGLPI/formcreator/commit/61722daf))
* **glpiselectfield:** max_tree_depth_not_saved ([462ea695](https://github.com/pluginsGLPI/formcreator/commit/462ea695))
* **install:** prevent fatal error in migration ([306c2c3f](https://github.com/pluginsGLPI/formcreator/commit/306c2c3f))
* **item_targetticket:** export of question based composition ([dc8c931a](https://github.com/pluginsGLPI/formcreator/commit/dc8c931a))
* **radiosfield, selectfield:** allow unset default value ([020bd45b](https://github.com/pluginsGLPI/formcreator/commit/020bd45b))
* **radiosfield, selectfield:** check default value before save ([d4a2ecb4](https://github.com/pluginsGLPI/formcreator/commit/d4a2ecb4))
* **section:** condition rule loss after duplicate / import ([7bfe6ca0](https://github.com/pluginsGLPI/formcreator/commit/7bfe6ca0))
* **selectfield:** show contact field ([11c86b7c](https://github.com/pluginsGLPI/formcreator/commit/11c86b7c))
* **selectfield,radiosfield:** abusive escaping ([db01a561](https://github.com/pluginsGLPI/formcreator/commit/db01a561))
* **targetchange,targetproblem:** several fields must use rich text ([cf24aa19](https://github.com/pluginsGLPI/formcreator/commit/cf24aa19))
* **targetticket:** request source ([c72901c7](https://github.com/pluginsGLPI/formcreator/commit/c72901c7))
* **targetticket:** request source may be unexpected value ([2fd6cf54](https://github.com/pluginsGLPI/formcreator/commit/2fd6cf54))
* **targetticket,targetchange,targetproblem:** missing translation of target content ([902efa78](https://github.com/pluginsGLPI/formcreator/commit/902efa78))
* **textfield:** Unescaped HTML when displaying a form answer ([d4763859](https://github.com/pluginsGLPI/formcreator/commit/d4763859))
* **translation:** delete rich editor's ghost toolbar ([ba97c842](https://github.com/pluginsGLPI/formcreator/commit/ba97c842))
* **translation:** dialog width ([0b46dec3](https://github.com/pluginsGLPI/formcreator/commit/0b46dec3))
* **translation:** typo in localizable tring ([3bb2f3d4](https://github.com/pluginsGLPI/formcreator/commit/3bb2f3d4))


### Features

*  reminders ([6ed85cd3](https://github.com/pluginsGLPI/formcreator/commit/6ed85cd3))
* **abstractitiltarget:** duplicate ([3c093012](https://github.com/pluginsGLPI/formcreator/commit/3c093012))
* **category:** show category comment in service catalog ([09727147](https://github.com/pluginsGLPI/formcreator/commit/09727147)), closes [#394](https://github.com/pluginsGLPI/formcreator/issues/394)
* **clean_tickets_command:** Take merged tickets into account ([768cd467](https://github.com/pluginsGLPI/formcreator/commit/768cd467))
* **form:** edit recursion with massive actions ([7c918d3b](https://github.com/pluginsGLPI/formcreator/commit/7c918d3b))
* **formaccesstype:** massive action ([53a4285a](https://github.com/pluginsGLPI/formcreator/commit/53a4285a))
* **glpiselectfield:** PDU in assets section ([bc6a790d](https://github.com/pluginsGLPI/formcreator/commit/bc6a790d))
* **glpiselectfield:** refactor entity_restriction ([b3fb0804](https://github.com/pluginsGLPI/formcreator/commit/b3fb0804))
* **install:** stronger upgrade for unsigned columns ([030f93e3](https://github.com/pluginsGLPI/formcreator/commit/030f93e3))
* **notificationtargetformanswer:** label tags ([f1dc000c](https://github.com/pluginsGLPI/formcreator/commit/f1dc000c)), closes [#1023](https://github.com/pluginsGLPI/formcreator/issues/1023)
* **section:** update condition count after section edition ([8c04048f](https://github.com/pluginsGLPI/formcreator/commit/8c04048f))


<a name="2.13.5"></a>
## [2.13.5](https://github.com/pluginsGLPI/formcreator/compare/2.13.4..2.13.5) (2023-03-24)


### Bug Fixes

*  add missing domain for public forms translation (#3162) ([970f183c6](https://github.com/pluginsGLPI/formcreator/commit/970f183c6))
*  duplicate key when updating a profile ([1bd6a2ab6](https://github.com/pluginsGLPI/formcreator/commit/1bd6a2ab6))
*  remote glpi prefix for commands ([651444a27](https://github.com/pluginsGLPI/formcreator/commit/651444a27))
* **abstractitiltarget:** set priority from urgency and impact (#3178) ([1269edd51](https://github.com/pluginsGLPI/formcreator/commit/1269edd51))
* **checkboxes:** better display ([f8fe93a63](https://github.com/pluginsGLPI/formcreator/commit/f8fe93a63))
* **checkboxes:** padding between items ([a62f879ce](https://github.com/pluginsGLPI/formcreator/commit/a62f879ce))
* **condition:** infinite loop detection ([172d5e8eb](https://github.com/pluginsGLPI/formcreator/commit/172d5e8eb))
* **dropdownfield:** prevent ambiguous column name ([b54523219](https://github.com/pluginsGLPI/formcreator/commit/b54523219))
* **form:** remove obsolete translations on update ([3cc58ac7d](https://github.com/pluginsGLPI/formcreator/commit/3cc58ac7d))
* **form:** rename form answer properties tab ([a3395179d](https://github.com/pluginsGLPI/formcreator/commit/a3395179d))
* **form_language:** avoid persistent rich editor toolbar when closing modal ([11a8808b5](https://github.com/pluginsGLPI/formcreator/commit/11a8808b5))
* **form_language:** display problems when translating ([93073e656](https://github.com/pluginsGLPI/formcreator/commit/93073e656))
* **form_language:** filter out obsolete translations ([b38555c5e](https://github.com/pluginsGLPI/formcreator/commit/b38555c5e))
* **formanswer:** access restriction ([a9451d982](https://github.com/pluginsGLPI/formcreator/commit/a9451d982))
* **install:** distinguish error messages for sanity check ([b798bf264](https://github.com/pluginsGLPI/formcreator/commit/b798bf264))
* **notifications:** missing lang tags ([3cad18562](https://github.com/pluginsGLPI/formcreator/commit/3cad18562))
* **question:** missing conditions count after update ([ea185beb8](https://github.com/pluginsGLPI/formcreator/commit/ea185beb8))
* **question:** updating a question returns sanitized label ([936ccd475](https://github.com/pluginsGLPI/formcreator/commit/936ccd475))
* **radios:** update escaping of valies ([c940e1764](https://github.com/pluginsGLPI/formcreator/commit/c940e1764))
* **radiosfield:** better display ([fe6c2e8d0](https://github.com/pluginsGLPI/formcreator/commit/fe6c2e8d0))
* **restrictedformcriteria:** bad key when generating error message ([6cabca1fe](https://github.com/pluginsGLPI/formcreator/commit/6cabca1fe))
* **targetchange,targetproblem:** harmonize implemetnation with targetticket ([1ba402de0](https://github.com/pluginsGLPI/formcreator/commit/1ba402de0))
* **targetchange,targetproblem:** missed code refactor ([e24d2fc13](https://github.com/pluginsGLPI/formcreator/commit/e24d2fc13))
* **targetticket:** wrong property label ([fd3d30973](https://github.com/pluginsGLPI/formcreator/commit/fd3d30973))
* **textareafield:** target ticket shows HTML when image uploaded ([56fc8d54d](https://github.com/pluginsGLPI/formcreator/commit/56fc8d54d))
* **translation:** avoid rn when using formatted rich (html) text ([24113a353](https://github.com/pluginsGLPI/formcreator/commit/24113a353))


### Features

*  Link documents to form ([690f58d13](https://github.com/pluginsGLPI/formcreator/commit/690f58d13))
* **form_language:** add items count in tab ([90a579680](https://github.com/pluginsGLPI/formcreator/commit/90a579680))
* **issue:** more anonymising options (#3160) ([075896bb6](https://github.com/pluginsGLPI/formcreator/commit/075896bb6))


<a name="2.13.4"></a>
## [2.13.4](https://github.com/pluginsGLPI/formcreator/compare/2.13.3..2.13.4) (2023-01-24)


### Bug Fixes

*  handle undefined setting for service catalog homepage ([411ae3597](https://github.com/pluginsGLPI/formcreator/commit/411ae3597))
*  typo in french locale ([f61ded17a](https://github.com/pluginsGLPI/formcreator/commit/f61ded17a))
* **abstractitiltarget:** multiple tag questions set but not displayed in designer ([90f2a95d8](https://github.com/pluginsGLPI/formcreator/commit/90f2a95d8))
* **checkboxesfield,multiselectfield:** default value not displayed ([8f36ab726](https://github.com/pluginsGLPI/formcreator/commit/8f36ab726))
* **composite:** ignore link to non existing ticket ([8502d4b16](https://github.com/pluginsGLPI/formcreator/commit/8502d4b16))
* **condition:** allow longer texts ([eecdf8a2a](https://github.com/pluginsGLPI/formcreator/commit/eecdf8a2a))
* **condition:** display of tested question shows wrong item ([5d34da8b4](https://github.com/pluginsGLPI/formcreator/commit/5d34da8b4))
* **condition:** width of question dropdown ([ce0389efd](https://github.com/pluginsGLPI/formcreator/commit/ce0389efd))
* **dropdownfield:** empty SQL IN statement when restricted tickets rights ([5c5244a85](https://github.com/pluginsGLPI/formcreator/commit/5c5244a85))
* **form:** image upload handling in header field ([5dc66a5ef](https://github.com/pluginsGLPI/formcreator/commit/5dc66a5ef))
* **formanswer:** default search filter hides legit access ([2dc9f8e3f](https://github.com/pluginsGLPI/formcreator/commit/2dc9f8e3f))
* **formanswer:** malformed search option ([5339b7912](https://github.com/pluginsGLPI/formcreator/commit/5339b7912))
* **formanswer:** missing newline between sections of fullform tag ([61122bc93](https://github.com/pluginsGLPI/formcreator/commit/61122bc93))
* **formanswer:** temporary disable debug mode ([e9e8da484](https://github.com/pluginsGLPI/formcreator/commit/e9e8da484))
* **formanswer, textfield, textareafield:** escaping ([3e0666d4d](https://github.com/pluginsGLPI/formcreator/commit/3e0666d4d))
* **glpiselectfield:** cannot set empty value by default for entity question ([fe2130bbe](https://github.com/pluginsGLPI/formcreator/commit/fe2130bbe))
* **glpiselectfield:** restore entity restriction for users ([e525b3a82](https://github.com/pluginsGLPI/formcreator/commit/e525b3a82))
* **helpdesk:** better handling of users that can't see tickets ([a93f03126](https://github.com/pluginsGLPI/formcreator/commit/a93f03126))
* **install:** add empty schema for new version ([817a9ec7e](https://github.com/pluginsGLPI/formcreator/commit/817a9ec7e))
* **install:** resync not needed in upgrade to 2.13.4 ([d66a12017](https://github.com/pluginsGLPI/formcreator/commit/d66a12017))
* **install:** typo in method name ([eac5d77ac](https://github.com/pluginsGLPI/formcreator/commit/eac5d77ac))
* **issue:** follow entity change on ticket transfer ([434bd3572](https://github.com/pluginsGLPI/formcreator/commit/434bd3572))
* **issues:** Tooltip consistency with core ([c45d21550](https://github.com/pluginsGLPI/formcreator/commit/c45d21550))
* **question:** subtype plural and appliance in bad group ([1f780370a](https://github.com/pluginsGLPI/formcreator/commit/1f780370a))
* **tagfield:** php warning ([cc4b673a8](https://github.com/pluginsGLPI/formcreator/commit/cc4b673a8))
* **targetticket:** allow more itemtypes to associated elements (#3155) ([cee504c24](https://github.com/pluginsGLPI/formcreator/commit/cee504c24))
* **textfield:** useless HTML entity encode ([c3d03b51e](https://github.com/pluginsGLPI/formcreator/commit/c3d03b51e))


### Features

*  drop support for GLPI 10.1 ([a99a8bcb2](https://github.com/pluginsGLPI/formcreator/commit/a99a8bcb2))
* **dropdownfield:** always show ticket id ([0190adac9](https://github.com/pluginsGLPI/formcreator/commit/0190adac9))
* **issue:** access tickets from service catalog ([a6b4f19d0](https://github.com/pluginsGLPI/formcreator/commit/a6b4f19d0))
* **question:** add support for database sub itemtype ([45126012d](https://github.com/pluginsGLPI/formcreator/commit/45126012d))
* **wizard:** selectable home page in service catalog ([95103fe54](https://github.com/pluginsGLPI/formcreator/commit/95103fe54))


<a name="2.13.3"></a>
## [2.13.3](https://github.com/pluginsGLPI/formcreator/compare/2.13.2..2.13.3) (2022-11-24)


### Bug Fixes

* **abstractitiltarget:** copy may generate unwanted ouput to navigator ([8792ed3dc](https://github.com/pluginsGLPI/formcreator/commit/8792ed3dc))
* **abstracttarget:** support for sla and ola from question ([e4c6ffeb6](https://github.com/pluginsGLPI/formcreator/commit/e4c6ffeb6))
* **category:** do not access page if the plugin is not active ([a959839c7](https://github.com/pluginsGLPI/formcreator/commit/a959839c7))
* **category:** don't activate plugin to access categories ([4cd4f600e](https://github.com/pluginsGLPI/formcreator/commit/4cd4f600e))
* **checkboxesfield:** back to BR ([c8908f265](https://github.com/pluginsGLPI/formcreator/commit/c8908f265))
* **checkboxesfield:** back to BR ([56d1e7e94](https://github.com/pluginsGLPI/formcreator/commit/56d1e7e94))
* **checkboxesfield, radiosfield:** checkboxes and radios backslashes (#3050) ([47da0ea0a](https://github.com/pluginsGLPI/formcreator/commit/47da0ea0a))
* **common:** captcha check ([b2b7efc89](https://github.com/pluginsGLPI/formcreator/commit/b2b7efc89))
* **dashboard:** fix dashboard height ([712bdc8ad](https://github.com/pluginsGLPI/formcreator/commit/712bdc8ad))
* **datefield:** change event and comparison ([9da947783](https://github.com/pluginsGLPI/formcreator/commit/9da947783))
* **filefield:** do not assume index of files ([a02a9c7ce](https://github.com/pluginsGLPI/formcreator/commit/a02a9c7ce))
* **form:** delete question does not reset preview tab ([ad87ddc87](https://github.com/pluginsGLPI/formcreator/commit/ad87ddc87))
* **form:** prevent SQL error ([17aa94309](https://github.com/pluginsGLPI/formcreator/commit/17aa94309))
* **form:** prevent sending two csrf tokens ([c04c71bab](https://github.com/pluginsGLPI/formcreator/commit/c04c71bab))
* **form:** tab name must obey 'show count' setting ([b89232eb3](https://github.com/pluginsGLPI/formcreator/commit/b89232eb3))
* **form_language:** call to undefined method ([137a66047](https://github.com/pluginsGLPI/formcreator/commit/137a66047))
* **formanswer:** page switching loose filter ([14d3ed7ac](https://github.com/pluginsGLPI/formcreator/commit/14d3ed7ac))
* **install:** bad command in error message ([f357d9ca4](https://github.com/pluginsGLPI/formcreator/commit/f357d9ca4))
* **install:** handle possible null while changing fields ([0a847af4c](https://github.com/pluginsGLPI/formcreator/commit/0a847af4c))
* **issue:** access to saved searches from service catalog ([b7481825a](https://github.com/pluginsGLPI/formcreator/commit/b7481825a))
* **issue:** default joint for issue ([631888e47](https://github.com/pluginsGLPI/formcreator/commit/631888e47))
* **issue:** show save button for followup edit ([810c854f1](https://github.com/pluginsGLPI/formcreator/commit/810c854f1))
* **issue:** sync issue fails when a ticket has several validators ([3f51fbdd9](https://github.com/pluginsGLPI/formcreator/commit/3f51fbdd9))
* **issue:** useless criteria nesting ([369fdb57b](https://github.com/pluginsGLPI/formcreator/commit/369fdb57b))
* **selectfield:** too many unescaping ([706b70faa](https://github.com/pluginsGLPI/formcreator/commit/706b70faa))
* **targetticket:** set request source if no rule specified ([2e04680eb](https://github.com/pluginsGLPI/formcreator/commit/2e04680eb))
* **textareadifield:** error when deduplicating uploads ([666d81395](https://github.com/pluginsGLPI/formcreator/commit/666d81395))
* **wizard:** consistent breadcrumb on several pages ([6639cda03](https://github.com/pluginsGLPI/formcreator/commit/6639cda03))


### Features

*  handle a new case in fix tool: litteral > sign (#3048) ([275c3506e](https://github.com/pluginsGLPI/formcreator/commit/275c3506e))
* **install:** empty SQL schema ([aacccfd7f](https://github.com/pluginsGLPI/formcreator/commit/aacccfd7f))
* **question:** new hooks for other plugins interaction (#3093) ([f9a23b646](https://github.com/pluginsGLPI/formcreator/commit/f9a23b646))



<a name="2.13.2"></a>
## [2.13.2](https://github.com/pluginsGLPI/formcreator/compare/2.13.1..2.13.2) (2022-10-17)


### Bug Fixes

*  just reencode br ([cce2e7e1c](https://github.com/pluginsGLPI/formcreator/commit/cce2e7e1c))
*  show KB items without category ([91f4deb75](https://github.com/pluginsGLPI/formcreator/commit/91f4deb75))
* **abstractitiltarget:** email addresses were ignored ([4c28a09b8](https://github.com/pluginsGLPI/formcreator/commit/4c28a09b8))
* **docs:** mix of single and singular/plural locales ([dc8f38cc3](https://github.com/pluginsGLPI/formcreator/commit/dc8f38cc3))
* **dropdownfield:** tree depth not restored in design dialog ([af4096bba](https://github.com/pluginsGLPI/formcreator/commit/af4096bba))
* **entityfilter:** bad namespace for 2 classes ([75d759940](https://github.com/pluginsGLPI/formcreator/commit/75d759940))
* **fields:** add default value to prevent SQL error (#2965) ([19f039569](https://github.com/pluginsGLPI/formcreator/commit/19f039569))
* **form:** re-enable submit on validation failure ([e39f6184c](https://github.com/pluginsGLPI/formcreator/commit/e39f6184c))
* **form:** risk of selecting the wrong form in DOM ([bb31fd163](https://github.com/pluginsGLPI/formcreator/commit/bb31fd163))
* **form:** submit once ([b00844208](https://github.com/pluginsGLPI/formcreator/commit/b00844208))
* **form:** unescape form name ([5b802658a](https://github.com/pluginsGLPI/formcreator/commit/5b802658a))
* **formanswer:** PHP 8.1 compatbility, error message if invalid JSON detected ([8ff7ff91a](https://github.com/pluginsGLPI/formcreator/commit/8ff7ff91a))
* **formanswer:** PHP 8.1 compatibility: null passed instead of string ([297fb2713](https://github.com/pluginsGLPI/formcreator/commit/297fb2713))
* **formanswer:** redirect after submission of targetless form ([4d60239d1](https://github.com/pluginsGLPI/formcreator/commit/4d60239d1))
* **requesttypefield:** warning if comparing against empty value ([dca5afb82](https://github.com/pluginsGLPI/formcreator/commit/dca5afb82))
* **section:** label for conditions in designer ([01e570319](https://github.com/pluginsGLPI/formcreator/commit/01e570319))
* **ticket satisfaction:** missing satisfaction field ([6f252bf91](https://github.com/pluginsGLPI/formcreator/commit/6f252bf91))
* **wizard:** FAQ list (#3031) ([bb0732ca7](https://github.com/pluginsGLPI/formcreator/commit/bb0732ca7))


### Features

*  tool to repair escaping problem in some tickets ([68db0ffda](https://github.com/pluginsGLPI/formcreator/commit/68db0ffda))
* **form:** submit forms once ([abed86101](https://github.com/pluginsGLPI/formcreator/commit/abed86101))
* **formanswer:** notification with URL to generated objets ([fa6a360f0](https://github.com/pluginsGLPI/formcreator/commit/fa6a360f0))
* **formanswer:** restore toasts when craeting targets ([f43df3ebb](https://github.com/pluginsGLPI/formcreator/commit/f43df3ebb))
* **glpiselectfield:** support change objects ([e1514b00e](https://github.com/pluginsGLPI/formcreator/commit/e1514b00e))
* **install:** show the DB diff when upgrade runs from CLI (#2994) ([4abb099a6](https://github.com/pluginsGLPI/formcreator/commit/4abb099a6))



## [2.13.1](https://github.com/pluginsGLPI/formcreator/compare/2.13.0..2.13.1) (2022-09-19)


### Bug Fixes

*  inverted existence test on ticket update ([2acc5cd4](https://github.com/pluginsGLPI/formcreator/commit/2acc5cd4))
*  log more errors, and update obsolete error logging ([ae28ed6d](https://github.com/pluginsGLPI/formcreator/commit/ae28ed6d))
*  restore page redirections existing in v2.12 ([582f926c](https://github.com/pluginsGLPI/formcreator/commit/582f926c))
*  update obsolete error logging ([da8929e0](https://github.com/pluginsGLPI/formcreator/commit/da8929e0))
* **abstractitiltarget:** glpi 10.0.3 will require a data with a valid value ([5f385bb8](https://github.com/pluginsGLPI/formcreator/commit/5f385bb8))
* **actorfield:** default value not saved ([c3baebbe](https://github.com/pluginsGLPI/formcreator/commit/c3baebbe))
* **actorfield:** php warning ([6d3e98d1](https://github.com/pluginsGLPI/formcreator/commit/6d3e98d1))
* **checkboxesfield:** replace div with p in checkbowes answers ([9ef95343](https://github.com/pluginsGLPI/formcreator/commit/9ef95343))
* **composite:** php warning breaks JSON if a ticket is not generated ([2108983c](https://github.com/pluginsGLPI/formcreator/commit/2108983c))
* **descriptionfield:** bad form rendering ([87a74058](https://github.com/pluginsGLPI/formcreator/commit/87a74058))
* **filefield:** php error when switching field type to file ([a03c7a0a](https://github.com/pluginsGLPI/formcreator/commit/a03c7a0a))
* **form:** javascript ([f05bc697](https://github.com/pluginsGLPI/formcreator/commit/f05bc697))
* **form:** list on self service homepage ([ba6d4a58](https://github.com/pluginsGLPI/formcreator/commit/ba6d4a58))
* **form:** undefined var ([169d2c8e](https://github.com/pluginsGLPI/formcreator/commit/169d2c8e))
* **form:** url to form answer lists may be invalid ([6cd29e6d](https://github.com/pluginsGLPI/formcreator/commit/6cd29e6d))
* **install:** avoid alter table fail ([4dadea8a](https://github.com/pluginsGLPI/formcreator/commit/4dadea8a))
* **install:** missing method in upgrade to 2.13.1 ([7e9cdcd5](https://github.com/pluginsGLPI/formcreator/commit/7e9cdcd5))
* **issue:** issue not deleted when tichet goes to trash bin ([c977b1ca](https://github.com/pluginsGLPI/formcreator/commit/c977b1ca))
* **issue:** purge issue when deleting associated ticket ([76444ecc](https://github.com/pluginsGLPI/formcreator/commit/76444ecc))
* **issue:** recreate when restore ticket ([2656e284](https://github.com/pluginsGLPI/formcreator/commit/2656e284))
* **item_targetticket:** uuid to ID conversion ([e9f326c0](https://github.com/pluginsGLPI/formcreator/commit/e9f326c0))
* **section:** name encoding in designer and rendered form" ([491dcb69](https://github.com/pluginsGLPI/formcreator/commit/491dcb69))
* **targetticket:** bad constant name ([48dda4f3](https://github.com/pluginsGLPI/formcreator/commit/48dda4f3))
* **targetticket:** table structure inconsistency ([ff56f3f1](https://github.com/pluginsGLPI/formcreator/commit/ff56f3f1))
* **targetticket:** table structure inconsistency ([892a83c3](https://github.com/pluginsGLPI/formcreator/commit/892a83c3))
* **targetticket,targetchange:** tags from queestion or specific tags not saved ([ec08d95e](https://github.com/pluginsGLPI/formcreator/commit/ec08d95e))


### Features

*  prepare compatibility with PHP 8.2 (#2966) ([4bb7f3c3](https://github.com/pluginsGLPI/formcreator/commit/4bb7f3c3))
* **formanswer,issue:** show title in navigation header ([1878e4b0](https://github.com/pluginsGLPI/formcreator/commit/1878e4b0))
* **kb:** preselect see all categorie ([1b669d4f](https://github.com/pluginsGLPI/formcreator/commit/1b669d4f))



<a name="2.13.0"></a>
## [2.13.0](https://github.com/pluginsGLPI/formcreator/compare/2.13.0-rc.2..2.13.0) (2022-08-18)


### Bug Fixes

*  cannot delete a ticket from service catalog ([acec9bb8](https://github.com/pluginsGLPI/formcreator/commit/acec9bb8))
* **abstractitiltarget:** alternative email lost if no requester user ([78fd8450](https://github.com/pluginsGLPI/formcreator/commit/78fd8450))
* **abstracttarget:** uuid should not be updated ([b1e492d3](https://github.com/pluginsGLPI/formcreator/commit/b1e492d3))
* **checkboxesfield:** avoid HTML br tag ([c3a60bbb](https://github.com/pluginsGLPI/formcreator/commit/c3a60bbb))
* **condition:** compatibility with Advanced forms validation ([6685b943](https://github.com/pluginsGLPI/formcreator/commit/6685b943))
* **descriptinfield:** conversion to target requires escaping ([b79cfa95](https://github.com/pluginsGLPI/formcreator/commit/b79cfa95))
* **filefield:** mandatory check may cause exception ([3f711a54](https://github.com/pluginsGLPI/formcreator/commit/3f711a54))
* **form:** PHP warning ([844ef96c](https://github.com/pluginsGLPI/formcreator/commit/844ef96c))
* **form:** bad URL when using advanced form validation plugin ([adb9fba5](https://github.com/pluginsGLPI/formcreator/commit/adb9fba5))
* **formanswer:** grid style updated for current version of gridstack ([85b6a686](https://github.com/pluginsGLPI/formcreator/commit/85b6a686))
* **formanswer:** select inherited class if needed ([955dc969](https://github.com/pluginsGLPI/formcreator/commit/955dc969))
* **formanswer:** update gridstack css ([70deaa06](https://github.com/pluginsGLPI/formcreator/commit/70deaa06))
* **glpiselectfield:** missing entity restrict ([40c9ab73](https://github.com/pluginsGLPI/formcreator/commit/40c9ab73))
* **install:** prevent useless warnings ([001d12f5](https://github.com/pluginsGLPI/formcreator/commit/001d12f5))
* **install:** use modern settings for tables ([f04e4181](https://github.com/pluginsGLPI/formcreator/commit/f04e4181))
* **issue:** remove duplicate item in status dropdown ([27f9f313](https://github.com/pluginsGLPI/formcreator/commit/27f9f313))
* **ldapselectfield:** log LDAP error instead of showing it to user ([e170dc6f](https://github.com/pluginsGLPI/formcreator/commit/e170dc6f))
* **ldapselectfield:** no translation for items ([d170c79c](https://github.com/pluginsGLPI/formcreator/commit/d170c79c))
* **targetticket:** prevent exception in inconsistent target ticket ([ba6ed88e](https://github.com/pluginsGLPI/formcreator/commit/ba6ed88e))
* **textarea:** on change event broken ([9fb70edb](https://github.com/pluginsGLPI/formcreator/commit/9fb70edb))
* **textarea:** rn chars added between lines ([66571b80](https://github.com/pluginsGLPI/formcreator/commit/66571b80))
* **textarea, entityconfig:** embedded image question description (#2901) ([0d78db1a](https://github.com/pluginsGLPI/formcreator/commit/0d78db1a))
* **textareafield:** embedded image upload broken ([d58075cd](https://github.com/pluginsGLPI/formcreator/commit/d58075cd))
* **textareafield:** missing escape before compare ([ba78e935](https://github.com/pluginsGLPI/formcreator/commit/ba78e935))


### Features

* **formanswer:** order formanswers by date desc ([7fdeda51](https://github.com/pluginsGLPI/formcreator/commit/7fdeda51))
* **ldapselectfield:** lazy loading ([bffcb5b7](https://github.com/pluginsGLPI/formcreator/commit/bffcb5b7))



<a name="2.13.0-rc.2"></a>
## [2.13.0-rc.2](https://github.com/pluginsGLPI/formcreator/compare/2.13.0-rc.1..2.13.0-rc.2) (2022-07-20)


### Bug Fixes

*  php warning ([9304443c](https://github.com/pluginsGLPI/formcreator/commit/9304443c))
* **abstractitiltarget:** use_notification is not a bool ([fc7d8a2f](https://github.com/pluginsGLPI/formcreator/commit/fc7d8a2f))
* **changelog:** bad anchor ([be417f55](https://github.com/pluginsGLPI/formcreator/commit/be417f55))
* **fieldsfield:** broken rendering of question ([e6bb7fbc](https://github.com/pluginsGLPI/formcreator/commit/e6bb7fbc))
* **fieldsfield:** typo in string ([ad9bdfb1](https://github.com/pluginsGLPI/formcreator/commit/ad9bdfb1))
* **form:** able to submit when button disabled ([bf3ebefe](https://github.com/pluginsGLPI/formcreator/commit/bf3ebefe))
* **form:** form property showed twice ([c5e00541](https://github.com/pluginsGLPI/formcreator/commit/c5e00541))
* **form:** unable to upload files from public_form ([6276402b](https://github.com/pluginsGLPI/formcreator/commit/6276402b))
* **formanswer:** handle null value when parsing tags ([63bb428c](https://github.com/pluginsGLPI/formcreator/commit/63bb428c))
* **formanswer:** typo in comparison operator ([77415730](https://github.com/pluginsGLPI/formcreator/commit/77415730)), closes [#2844](https://github.com/pluginsGLPI/formcreator/issues/2844)
* **glpiselectfield:** regex comparison ([64e28bbd](https://github.com/pluginsGLPI/formcreator/commit/64e28bbd))
* **install:** bad argument fordefault values ([6f7fbc84](https://github.com/pluginsGLPI/formcreator/commit/6f7fbc84))
* **install:** convert all FK to unsigned ([4ba5ed89](https://github.com/pluginsGLPI/formcreator/commit/4ba5ed89))
* **install:** disable db check prior upgrade ([504727b5](https://github.com/pluginsGLPI/formcreator/commit/504727b5))
* **install:** fix possible schema errors coming from version older than 2.5.0 ([c9338cd6](https://github.com/pluginsGLPI/formcreator/commit/c9338cd6))
* **install:** give more instructions to support the upgrade process ([104f8fd0](https://github.com/pluginsGLPI/formcreator/commit/104f8fd0))
* **install:** handle invalid values before changing columns, add possibly missing index ([f5369f6d](https://github.com/pluginsGLPI/formcreator/commit/f5369f6d))
* **install:** less picky checks when upgrading from 2.13.0 ([7420cdce](https://github.com/pluginsGLPI/formcreator/commit/7420cdce))
* **install:** move command out of localizable string ([859443f4](https://github.com/pluginsGLPI/formcreator/commit/859443f4))
* **install:** move error messages, find schema file with unstable  versions ([cc886985](https://github.com/pluginsGLPI/formcreator/commit/cc886985))
* **install:** pick the right schema ([d9cf90a4](https://github.com/pluginsGLPI/formcreator/commit/d9cf90a4))
* **install:** post install db check ([722158db](https://github.com/pluginsGLPI/formcreator/commit/722158db))
* **install:** preveit failure if tables contains some NULL values ([e87b6f57](https://github.com/pluginsGLPI/formcreator/commit/e87b6f57))
* **install:** prevent output in ajax response ([1d19d7c9](https://github.com/pluginsGLPI/formcreator/commit/1d19d7c9))
* **install:** try to prevent SQL error in migration ([831f273d](https://github.com/pluginsGLPI/formcreator/commit/831f273d))
* **install:** workaround alter table failure ([91baefb9](https://github.com/pluginsGLPI/formcreator/commit/91baefb9))
* **intall:** do not process non-existing tables ([d5b5fd89](https://github.com/pluginsGLPI/formcreator/commit/d5b5fd89))
* **issue:** Show pending / accepted forms search results ([58ea0662](https://github.com/pluginsGLPI/formcreator/commit/58ea0662))
* **issue:** accepted status not searchable ([2df02ae4](https://github.com/pluginsGLPI/formcreator/commit/2df02ae4))
* **issue:** allow null in name column ([bb32843d](https://github.com/pluginsGLPI/formcreator/commit/bb32843d))
* **issue:** vonsistency with seaerch for accepted issues ([64876111](https://github.com/pluginsGLPI/formcreator/commit/64876111))
* **section:** untranslated sring ([fb6800cc](https://github.com/pluginsGLPI/formcreator/commit/fb6800cc))
* **target_actor:** import requires specific input format ([3a8d3eae](https://github.com/pluginsGLPI/formcreator/commit/3a8d3eae))
* **targetticket:** prevent setting a type from an incompatible question ([f5de8bb6](https://github.com/pluginsGLPI/formcreator/commit/f5de8bb6))
* **textarea:** encoding problem when picture is embedded ([525cfd5b](https://github.com/pluginsGLPI/formcreator/commit/525cfd5b))
* **wizard:** rename forms menu for simplified interface ([07d27926](https://github.com/pluginsGLPI/formcreator/commit/07d27926))


### Features

*  enable advanced validation ([6a24d7bd](https://github.com/pluginsGLPI/formcreator/commit/6a24d7bd))
*  update dependencies ([3b9c5d8e](https://github.com/pluginsGLPI/formcreator/commit/3b9c5d8e))
*  update js dependencies ([7dba5f2a](https://github.com/pluginsGLPI/formcreator/commit/7dba5f2a))
* **fields:** manage new fields type plugin ([a2b58191](https://github.com/pluginsGLPI/formcreator/commit/a2b58191))
* **formanswer:** do not valdiate if requester is validator ([613e6e30](https://github.com/pluginsGLPI/formcreator/commit/613e6e30))
* **install:** add schema of all older versions ([814729be](https://github.com/pluginsGLPI/formcreator/commit/814729be))
* **install:** attempt to fix old inconsistencies ([a94c928c](https://github.com/pluginsGLPI/formcreator/commit/a94c928c))
* **install:** check schema prior to upgrade ([60f4bf75](https://github.com/pluginsGLPI/formcreator/commit/60f4bf75))
* **install:** find old plugin version ([5cbcd2d7](https://github.com/pluginsGLPI/formcreator/commit/5cbcd2d7))
* **install:** method to get the empty SQL file ([ef5fd200](https://github.com/pluginsGLPI/formcreator/commit/ef5fd200))
* **install:** migrate tables to dynamic ([54c40c8d](https://github.com/pluginsGLPI/formcreator/commit/54c40c8d))
* **install:** report successful integrity check ([f2316602](https://github.com/pluginsGLPI/formcreator/commit/f2316602))
* **issue:** allow search on techs, not only myself as tech ([5a462977](https://github.com/pluginsGLPI/formcreator/commit/5a462977))
* **issue:** limit tech users list ([0feb7d16](https://github.com/pluginsGLPI/formcreator/commit/0feb7d16))
* **question:** allow gaps in rows ([20c212e8](https://github.com/pluginsGLPI/formcreator/commit/20c212e8))
* **targetticket,targetcvhange,targetproblem:** update actors array ([4d92bc59](https://github.com/pluginsGLPI/formcreator/commit/4d92bc59))


<a name="2.13.0-rc.1"></a>
## [2.13.0-rc.1](https://github.com/pluginsGLPI/formcreator/compare/2.13.0-beta.2..2.13.0-rc.1) (2022-06-07)


### Bug Fixes

*  missing menu entry in simplified interface ([5a07a70c](https://github.com/pluginsGLPI/formcreator/commit/5a07a70c))
* **abstractitiltarget:** due date constants ([eda3c116](https://github.com/pluginsGLPI/formcreator/commit/eda3c116))
* **abstractitiltarget:** failed to assign group frop object ([aa92a2f8](https://github.com/pluginsGLPI/formcreator/commit/aa92a2f8))
* **abstractitiltarget:** mess with actors dropdown ([baf9c042](https://github.com/pluginsGLPI/formcreator/commit/baf9c042))
* **abstractitiltarget:** php warning when processing file questions without answer ([63e54162](https://github.com/pluginsGLPI/formcreator/commit/63e54162))
* **abstractitiltarget:** unable to choose a question for location ([6ad92e9a](https://github.com/pluginsGLPI/formcreator/commit/6ad92e9a))
* **bastractitiltarget:** reapply a324ed9f0635e033cbabaeddb8e238147d0c5024 ([c4dac1c0](https://github.com/pluginsGLPI/formcreator/commit/c4dac1c0))
* **dropdownfield,glpiselectfield:** build parameters even when none in DB ([57b7596b](https://github.com/pluginsGLPI/formcreator/commit/57b7596b))
* **fieldsfield:** remove redefined methond identical to parent ([728acf60](https://github.com/pluginsGLPI/formcreator/commit/728acf60))
* **form:** search criteria when using keywords ([23ab7a35](https://github.com/pluginsGLPI/formcreator/commit/23ab7a35))
* **form:** search forms to approce ([5335d0ae](https://github.com/pluginsGLPI/formcreator/commit/5335d0ae))
* **form:** show validator dropdown before submit button ([3ec88a41](https://github.com/pluginsGLPI/formcreator/commit/3ec88a41))
* **form:** where criteria outside where clause ([4cdb8d8a](https://github.com/pluginsGLPI/formcreator/commit/4cdb8d8a))
* **formanswer:** quote escaping problem ([1b56e540](https://github.com/pluginsGLPI/formcreator/commit/1b56e540))
* **instal:** add column to explicit position, sync issues only on version condition ([0bcba9be](https://github.com/pluginsGLPI/formcreator/commit/0bcba9be))
* **issue:** pass object isntead of ID ([6605ced2](https://github.com/pluginsGLPI/formcreator/commit/6605ced2))
* **issue:** redirect ticket to issue only in service catalog ([6826ea86](https://github.com/pluginsGLPI/formcreator/commit/6826ea86))
* **questionregex:** compatibility with PHP 8.1 ([b64386f9](https://github.com/pluginsGLPI/formcreator/commit/b64386f9))
* **requesttypefield:** typo ([5b9e8f56](https://github.com/pluginsGLPI/formcreator/commit/5b9e8f56))
* **selectfield:** trim values ([4f59294a](https://github.com/pluginsGLPI/formcreator/commit/4f59294a))
* **target_actor:** drop unused var causing PHP warning ([7b223001](https://github.com/pluginsGLPI/formcreator/commit/7b223001))
* **targetticket:** unable to set type ([2e186345](https://github.com/pluginsGLPI/formcreator/commit/2e186345))
* **textareafield:** remove hack for textarea in previous versions ([203acd10](https://github.com/pluginsGLPI/formcreator/commit/203acd10))
* **urgencyfield,requesttypefield:** php error if answers does not exists ([42178663](https://github.com/pluginsGLPI/formcreator/commit/42178663))
* **wizard:** FAQ page may show forms ([d90b0b93](https://github.com/pluginsGLPI/formcreator/commit/d90b0b93))
* **wizard:** Info message on emlty FAQ result ([d268caea](https://github.com/pluginsGLPI/formcreator/commit/d268caea))
* **wizard:** adjustable width for ticket footer ([80033ada](https://github.com/pluginsGLPI/formcreator/commit/80033ada))
* **wizard:** card content overflow ([8b22b271](https://github.com/pluginsGLPI/formcreator/commit/8b22b271))
* **wizard:** hide save button ([48d98870](https://github.com/pluginsGLPI/formcreator/commit/48d98870))
* **wizard:** margin problem on separated FAQ page ([03828b4c](https://github.com/pluginsGLPI/formcreator/commit/03828b4c))
* **wizard:** more space between radio and iconic label ([e2cdbe76](https://github.com/pluginsGLPI/formcreator/commit/e2cdbe76))
* **wizard:** show only one information message ([74a55a70](https://github.com/pluginsGLPI/formcreator/commit/74a55a70))
* **wizard:** show plugin menus in service catalog ([77c8f62c](https://github.com/pluginsGLPI/formcreator/commit/77c8f62c))
* **wizard:** show search input only if config allows it ([b807974a](https://github.com/pluginsGLPI/formcreator/commit/b807974a))
* **wizard:** simplified catalog: only timeline for ticket ([7a24970c](https://github.com/pluginsGLPI/formcreator/commit/7a24970c))
* **wizard:** swapped form and faq icons ([42a45579](https://github.com/pluginsGLPI/formcreator/commit/42a45579))
* **wizard:** tooltip and ellipsis on long category name ([77036a9d](https://github.com/pluginsGLPI/formcreator/commit/77036a9d))
* **wizard:** various fixes in KB only search ([41cbfd22](https://github.com/pluginsGLPI/formcreator/commit/41cbfd22))
* **wizard:** vertical overlap of categories ([5412ed99](https://github.com/pluginsGLPI/formcreator/commit/5412ed99))


### Features

*  do not modify the menu with simplified interface ([f7ccc2b8](https://github.com/pluginsGLPI/formcreator/commit/f7ccc2b8))
* **install:** run sync issues only once when if several upgrade steps require it ([c7f329ca](https://github.com/pluginsGLPI/formcreator/commit/c7f329ca))



<a name="2.13.0-beta.2"></a>
## [2.13.0-beta.2](https://github.com/pluginsGLPI/formcreator/compare/2.13.0-beta.1..2.13.0-beta.2) (2022-05-17)


### Bug Fixes

*  do not overwrite dashboards from other plugins ([d0f49e19](https://github.com/pluginsGLPI/formcreator/commit/d0f49e19))
*  remove tech group search option from service catalog ([c473076f](https://github.com/pluginsGLPI/formcreator/commit/c473076f))
* **condition:** condition loss if itil target edited ([aaae0344](https://github.com/pluginsGLPI/formcreator/commit/aaae0344))
* **condition:** fail to import conditions ([cb4dceb3](https://github.com/pluginsGLPI/formcreator/commit/cb4dceb3))
* **conditionnabletrait:** php warning ([bdfabf8e](https://github.com/pluginsGLPI/formcreator/commit/bdfabf8e))
* **form:** Form with restricted acces: redirect to login form if not logged in ([fe1eadd6](https://github.com/pluginsGLPI/formcreator/commit/fe1eadd6))
* **form:** allow redirect to login for private forms as well ([bdae85a0](https://github.com/pluginsGLPI/formcreator/commit/bdae85a0))
* **form:** design css broken ([d8c76692](https://github.com/pluginsGLPI/formcreator/commit/d8c76692))
* **formanswer:** bad parenthesis nest and bad oject used ([70b77d32](https://github.com/pluginsGLPI/formcreator/commit/70b77d32))
* **glpiselectfield:** generic objects support lost in TWIG conversion ([d18852c0](https://github.com/pluginsGLPI/formcreator/commit/d18852c0))
* **glpiselectfield,dropdownfield:** comparisons methods for conditions ([f41dafce](https://github.com/pluginsGLPI/formcreator/commit/f41dafce))
* **install:** index overflow (#2775) ([cec857b8](https://github.com/pluginsGLPI/formcreator/commit/cec857b8))
* **issue:** restrict tech group search option to assignable groups ([230b33ef](https://github.com/pluginsGLPI/formcreator/commit/230b33ef))
* **targetticket:** associated elements from question ([0a62b976](https://github.com/pluginsGLPI/formcreator/commit/0a62b976))
* **targetticket:** select questions outside form ([c38e1d9c](https://github.com/pluginsGLPI/formcreator/commit/c38e1d9c))
* **wizard:** bad label when searching KB items ([1fc81bc8](https://github.com/pluginsGLPI/formcreator/commit/1fc81bc8))
* **wizard:** fix KB only browsing ([de78b9ed](https://github.com/pluginsGLPI/formcreator/commit/de78b9ed))


### Features

* **dashboard:** enable label ([6309aa37](https://github.com/pluginsGLPI/formcreator/commit/6309aa37))
* **dashboard:** harmonize with GLPI color ([529395a4](https://github.com/pluginsGLPI/formcreator/commit/529395a4))
* **dashboard:** shorter card labels ([3e29a467](https://github.com/pluginsGLPI/formcreator/commit/3e29a467))
* **form:** better target list presentation ([343e5261](https://github.com/pluginsGLPI/formcreator/commit/343e5261))
* **form:** default language for new form unset ([a57e4a61](https://github.com/pluginsGLPI/formcreator/commit/a57e4a61))
* **formanswer:** hook before generating targets ([8542adc4](https://github.com/pluginsGLPI/formcreator/commit/8542adc4))
* **issue:** add option to hide search Issue if needed ([b37e1c02](https://github.com/pluginsGLPI/formcreator/commit/b37e1c02))
* **tile:** radius and margin ([b5ce782f](https://github.com/pluginsGLPI/formcreator/commit/b5ce782f))
* **tile:** title of tiles in bold ([425c71d4](https://github.com/pluginsGLPI/formcreator/commit/425c71d4))
* **ui:** rework category list ([2bbbe735](https://github.com/pluginsGLPI/formcreator/commit/2bbbe735))
* **wizard:** new tile design, optional ([aeb040c6](https://github.com/pluginsGLPI/formcreator/commit/aeb040c6))
* **wizard:** replace sort label by icon ([f7320a5c](https://github.com/pluginsGLPI/formcreator/commit/f7320a5c))



<a name="2.13.0-beta.1"></a>
## [HEAD](https://github.com/pluginsGLPI/formcreator/compare/2.13.0-alpha.4..2.13.0-beta.1) (2022-04-26)


### Bug Fixes

*  build css command ([4b29c0f3](https://github.com/pluginsGLPI/formcreator/commit/4b29c0f3))
*  change assistance request menu icon ([180a29ec](https://github.com/pluginsGLPI/formcreator/commit/180a29ec))
*  fix typo in plugin.xml ([1f11bc02](https://github.com/pluginsGLPI/formcreator/commit/1f11bc02))
*  restore compatibility with plugin News (alerts) ([d421971f](https://github.com/pluginsGLPI/formcreator/commit/d421971f))
* **abstacttarget:** plugin fields dropdowns not saved on ITIL object ([2050c61e](https://github.com/pluginsGLPI/formcreator/commit/2050c61e))
* **abstractitiltarget:** json_decode deprecated null support ([a2761ce6](https://github.com/pluginsGLPI/formcreator/commit/a2761ce6))
* **entityconfig:** bad ID when editing config ([7ee43b8c](https://github.com/pluginsGLPI/formcreator/commit/7ee43b8c))
* **fieldsfield:** instanciation error when fields plugin disabled ([404111bd](https://github.com/pluginsGLPI/formcreator/commit/404111bd))
* **filefield:** null passed to json_decode deprecated ([6f56484e](https://github.com/pluginsGLPI/formcreator/commit/6f56484e))
* **form:** anonymous form with textarea fields ([a1e71718](https://github.com/pluginsGLPI/formcreator/commit/a1e71718))
* **form:** check hook before using it ([d0d4beff](https://github.com/pluginsGLPI/formcreator/commit/d0d4beff))
* **formanswer:** bad id in list of uploaded files ([b5a39cf0](https://github.com/pluginsGLPI/formcreator/commit/b5a39cf0))
* **formlist:** prevent php deprecation ([6bc86419](https://github.com/pluginsGLPI/formcreator/commit/6bc86419))
* **install:** bad FK in query ([6515c843](https://github.com/pluginsGLPI/formcreator/commit/6515c843))
* **install:** explicit default value, just in case ([f27ddbc2](https://github.com/pluginsGLPI/formcreator/commit/f27ddbc2))
* **issue:** bad test ([8cb1bc3b](https://github.com/pluginsGLPI/formcreator/commit/8cb1bc3b))
* **issue:** performance problem in sync issue query ([c81d71ad](https://github.com/pluginsGLPI/formcreator/commit/c81d71ad))
* **issue:** redirections when browsing tickets on helpdesk ([c4f30322](https://github.com/pluginsGLPI/formcreator/commit/c4f30322))
* **ldapselectfield:** do not translate items ([0ede18d8](https://github.com/pluginsGLPI/formcreator/commit/0ede18d8))
* **ldapselectfield:** dynamic field update ([2c4ffb86](https://github.com/pluginsGLPI/formcreator/commit/2c4ffb86))
* **ldapselectfield:** use of uninitialized var ([376e2104](https://github.com/pluginsGLPI/formcreator/commit/376e2104))
* **question,section:** rich text features in modals ([7c8d7f7a](https://github.com/pluginsGLPI/formcreator/commit/7c8d7f7a))
* **targetticket:** composite ticket settings ([b194a602](https://github.com/pluginsGLPI/formcreator/commit/b194a602))
* **targetticket:** prevent array_merge with null value ([a4dc685e](https://github.com/pluginsGLPI/formcreator/commit/a4dc685e))


### Features

*  prevent using formcreator's dashboard as default ([26f2b17b](https://github.com/pluginsGLPI/formcreator/commit/26f2b17b))
*  redo dashboard with summary ([fd3ac826](https://github.com/pluginsGLPI/formcreator/commit/fd3ac826))
* **issue:** drop obsolete code for legacy counters ([802851ff](https://github.com/pluginsGLPI/formcreator/commit/802851ff))
* **ldapselectfield:** remove ldap connection test when saving question ([536edbf5](https://github.com/pluginsGLPI/formcreator/commit/536edbf5))
* **targetticket:** support location from template ([4a142bd9](https://github.com/pluginsGLPI/formcreator/commit/4a142bd9))
* **wizard:** replace home pictogram with text and move below category tree ([bd79fc53](https://github.com/pluginsGLPI/formcreator/commit/bd79fc53))
* **wizard:** show rss menu entry only if rss is avaiable ([352ea544](https://github.com/pluginsGLPI/formcreator/commit/352ea544))



<a name="2.13.0-alpha.4"></a>
## [2.13.0-alpha.4](https://github.com/pluginsGLPI/formcreator/compare/2.13.0-alpha.3..2.13.0-alpha.4) (2022-04-01)


### Bug Fixes

*  an other attempt ([fb267cf5](https://github.com/pluginsGLPI/formcreator/commit/fb267cf5))
*  avoid use of undefined var (unit tests) ([a4365ef6](https://github.com/pluginsGLPI/formcreator/commit/a4365ef6))
*  description positionning in question design ([a9352fdb](https://github.com/pluginsGLPI/formcreator/commit/a9352fdb))
*  link to license ([afc274b0](https://github.com/pluginsGLPI/formcreator/commit/afc274b0))
*  missing twig template file ([eeb768bd](https://github.com/pluginsGLPI/formcreator/commit/eeb768bd))
*  php warnings ([d3915e6b](https://github.com/pluginsGLPI/formcreator/commit/d3915e6b))
* **abstracttarget:** HTML tags rendered in targets ([d844b400](https://github.com/pluginsGLPI/formcreator/commit/d844b400)), closes [#2568](https://github.com/pluginsGLPI/formcreator/issues/2568)
* **actorfield:** edition problem when switching question type from dromdown / object ([0c83da08](https://github.com/pluginsGLPI/formcreator/commit/0c83da08))
* **common:** typo ([2c5e647f](https://github.com/pluginsGLPI/formcreator/commit/2c5e647f))
* **condition:** add condition on not-yet-created item ([257a41e4](https://github.com/pluginsGLPI/formcreator/commit/257a41e4))
* **condition:** implementation check may fail ([1e89a226](https://github.com/pluginsGLPI/formcreator/commit/1e89a226))
* **descriptionfield:** picture rendering in user form ([d86df5ca](https://github.com/pluginsGLPI/formcreator/commit/d86df5ca))
* **dropdownfield,glpiselectfield:** shiw item ID only on user preference ([bffbcbf5](https://github.com/pluginsGLPI/formcreator/commit/bffbcbf5))
* **dscriptionfield:** rendering shows HTML tags ([1a5fe980](https://github.com/pluginsGLPI/formcreator/commit/1a5fe980))
* **entityconfig:** enhance lazy creation of entity config ([7297624b](https://github.com/pluginsGLPI/formcreator/commit/7297624b))
* **entityconfig:** load entity properly to get configuration ([a99f5412](https://github.com/pluginsGLPI/formcreator/commit/a99f5412))
* **entityconfig:** use entity foreign key ([d22f176d](https://github.com/pluginsGLPI/formcreator/commit/d22f176d))
* **entityconfig:** use foreign key ([2271e141](https://github.com/pluginsGLPI/formcreator/commit/2271e141))
* **filefield:** missing class use statement ([5bd752d9](https://github.com/pluginsGLPI/formcreator/commit/5bd752d9))
* **form:** bad var to speecify the translation domain ([eb189256](https://github.com/pluginsGLPI/formcreator/commit/eb189256))
* **form:** count forms must take into account visibility ([af49feba](https://github.com/pluginsGLPI/formcreator/commit/af49feba))
* **form:** don't set default name to new targets ([4fe174f6](https://github.com/pluginsGLPI/formcreator/commit/4fe174f6))
* **form:** dot at end of error message ([a2c94654](https://github.com/pluginsGLPI/formcreator/commit/a2c94654))
* **form:** lightbulb always gray in darker theme ([2be8508b](https://github.com/pluginsGLPI/formcreator/commit/2be8508b))
* **form:** malformed SQL when searching for forms ([4b5ac477](https://github.com/pluginsGLPI/formcreator/commit/4b5ac477))
* **form:** re-add missing method ! ([eada255e](https://github.com/pluginsGLPI/formcreator/commit/eada255e))
* **form:** redirect anon user to success page ([56ad7b0e](https://github.com/pluginsGLPI/formcreator/commit/56ad7b0e))
* **form:** show a warning when trying to unset form answer name ([7872379a](https://github.com/pluginsGLPI/formcreator/commit/7872379a))
* **form:** translation editor update for GLPI 10 ([99a5a959](https://github.com/pluginsGLPI/formcreator/commit/99a5a959))
* **form:** typo in css ([9c88ec0d](https://github.com/pluginsGLPI/formcreator/commit/9c88ec0d))
* **form:** values handling with twig ([ecceb93c](https://github.com/pluginsGLPI/formcreator/commit/ecceb93c))
* **form_language:** add dialog titles, code celanup ([fabcdc1f](https://github.com/pluginsGLPI/formcreator/commit/fabcdc1f))
* **form_language:** do not show entity assignment ([eb856f0f](https://github.com/pluginsGLPI/formcreator/commit/eb856f0f))
* **form_language:** do not submit form when adding a translation ([6d74fb85](https://github.com/pluginsGLPI/formcreator/commit/6d74fb85))
* **form_language:** positionning and header ([97124c47](https://github.com/pluginsGLPI/formcreator/commit/97124c47))
* **form_language:** untranslated string ([c6743348](https://github.com/pluginsGLPI/formcreator/commit/c6743348))
* **form_translation:** edit translation dialog does not hide ([bb5a8cdb](https://github.com/pluginsGLPI/formcreator/commit/bb5a8cdb))
* **formanswer:** fatal error if Tag plugin is disabled ([aed10dca](https://github.com/pluginsGLPI/formcreator/commit/aed10dca))
* **formanswer:** tolbar button should show list of forms to validate only ([18bb917c](https://github.com/pluginsGLPI/formcreator/commit/18bb917c))
* **formanswer:** use same HTML escaping as GLPI 10 does ([35f9b6e9](https://github.com/pluginsGLPI/formcreator/commit/35f9b6e9))
* **formanswer,issue:** breadcrumb ([9a55cbd2](https://github.com/pluginsGLPI/formcreator/commit/9a55cbd2))
* **glpiselectfield:** bad WHERE criteria with entities ([ac36272d](https://github.com/pluginsGLPI/formcreator/commit/ac36272d))
* **install:** delegate to GLPI the conversion of most foreign keys ([c9486b7f](https://github.com/pluginsGLPI/formcreator/commit/c9486b7f))
* **install:** drop migration of target problem: the table is not created yet ([fc0dffc3](https://github.com/pluginsGLPI/formcreator/commit/fc0dffc3))
* **install:** prevent harmful upgrade replay ([477b5f1e](https://github.com/pluginsGLPI/formcreator/commit/477b5f1e))
* **install:** repair possible inconsistent root entity config ([490af18c](https://github.com/pluginsGLPI/formcreator/commit/490af18c))
* **install:** resequencing entityconfigs ([c5bd66d2](https://github.com/pluginsGLPI/formcreator/commit/c5bd66d2))
* **install:** typo in SQL requests ([e517e5b3](https://github.com/pluginsGLPI/formcreator/commit/e517e5b3))
* **issue:** ambiguous column in SQL query ([9e867808](https://github.com/pluginsGLPI/formcreator/commit/9e867808))
* **issue:** null not alowed fir requesters_id ([0cfea01f](https://github.com/pluginsGLPI/formcreator/commit/0cfea01f))
* **issue:** remove redundant condition ([093c3555](https://github.com/pluginsGLPI/formcreator/commit/093c3555))
* **issue:** requester replaced by author on ticket update ([7bc09ea1](https://github.com/pluginsGLPI/formcreator/commit/7bc09ea1))
* **issue:** restrict list of issues ([75276f12](https://github.com/pluginsGLPI/formcreator/commit/75276f12))
* **ldapfield:** raw condition expression must be replaced by array ([7620249c](https://github.com/pluginsGLPI/formcreator/commit/7620249c))
* **question:** wrong small title ([e13bb3ed](https://github.com/pluginsGLPI/formcreator/commit/e13bb3ed))
* **section:** duplicating section shows inner questions twice ([cfdf9405](https://github.com/pluginsGLPI/formcreator/commit/cfdf9405))
* **target_actor:** bad  error message ([e4c4ebb9](https://github.com/pluginsGLPI/formcreator/commit/e4c4ebb9))
* **targetticket:** bad class for constants ([475d4aee](https://github.com/pluginsGLPI/formcreator/commit/475d4aee))
* **targetticket:** possible SQL error ([44be6de1](https://github.com/pluginsGLPI/formcreator/commit/44be6de1))
* **targetticket:** prevent SQL error ([067902fb](https://github.com/pluginsGLPI/formcreator/commit/067902fb))
* **targetticket,targetchange,targetproblem:** restore back to form ([cf9c7b26](https://github.com/pluginsGLPI/formcreator/commit/cf9c7b26))
* **targetticket,targetchange,targetproblem:** use regular anchor to edit a target ([d3fd221e](https://github.com/pluginsGLPI/formcreator/commit/d3fd221e))
* **targetticket,targethange,targetproblem:** add missing file ([ca23dd92](https://github.com/pluginsGLPI/formcreator/commit/ca23dd92))
* **targetticket,targethange,targetproblem:** add missing file ([85609c4c](https://github.com/pluginsGLPI/formcreator/commit/85609c4c))
* **textareafield:** html escaping problem ([63eac853](https://github.com/pluginsGLPI/formcreator/commit/63eac853))
* **textareafield:** rich text and width in some configuration fields ([7de8b01b](https://github.com/pluginsGLPI/formcreator/commit/7de8b01b))
* **textareafield:** update escaping / line ending conversion to match GLPI 10 ([5b6bc51a](https://github.com/pluginsGLPI/formcreator/commit/5b6bc51a))


### Features

*  convert foreign keys to unsigned integers ([4af002a8](https://github.com/pluginsGLPI/formcreator/commit/4af002a8))
*  use twig to edit questions ([338d5fe9](https://github.com/pluginsGLPI/formcreator/commit/338d5fe9))
* **abstracttarget:** prepare to support non Itil targets ([0d0834d4](https://github.com/pluginsGLPI/formcreator/commit/0d0834d4))
* **core:** manage fields plugin ([7149aa07](https://github.com/pluginsGLPI/formcreator/commit/7149aa07))
* **entityconfig:** distinguish ID and entity foreign key ([542b414f](https://github.com/pluginsGLPI/formcreator/commit/542b414f))
* **filefield:** allow access to document from self service ([152ba276](https://github.com/pluginsGLPI/formcreator/commit/152ba276))
* **form:** header hook ([f8ff8a8f](https://github.com/pluginsGLPI/formcreator/commit/f8ff8a8f))
* **form:** icon dropdown list included in the project ([1902cb20](https://github.com/pluginsGLPI/formcreator/commit/1902cb20))
* **form:** rename anonymous form to public form ([538aebd2](https://github.com/pluginsGLPI/formcreator/commit/538aebd2))
* **form:** show all forms or only default forms on virtual root category (#2644) ([45e43e3b](https://github.com/pluginsGLPI/formcreator/commit/45e43e3b))
* **form:** single click to toggle default form flag ([0130897c](https://github.com/pluginsGLPI/formcreator/commit/0130897c))
* **install:** do not install the plugin on marketplace deployment ([88e92041](https://github.com/pluginsGLPI/formcreator/commit/88e92041))
* **targetticket:** better template support for request source ([61cf134f](https://github.com/pluginsGLPI/formcreator/commit/61cf134f))
* **targetticket,targetchange,targetproblem:** add documents to targets before creating them ([39a6cd78](https://github.com/pluginsGLPI/formcreator/commit/39a6cd78))



<a name="2.13.6-alpha.3"></a>
## [2.13.6-alpha.3](https://github.com/pluginsglpi/formcreator/compare/2.13.0-alpha.2..2.13.6-alpha.3) (2022-01-31)


### Bug Fixes

*  add missing templates ([36b80180](https://github.com/pluginsglpi/formcreator/commit/36b80180))
*  add missing templates ([c133b3dc](https://github.com/pluginsglpi/formcreator/commit/c133b3dc))
*  bad class names ([3929bd52](https://github.com/pluginsglpi/formcreator/commit/3929bd52))
*  deduplicate a constant ([c5708445](https://github.com/pluginsglpi/formcreator/commit/c5708445))
*  left botrder ([edfacd96](https://github.com/pluginsglpi/formcreator/commit/edfacd96))
*  margin in ticket's timeline ([368450bd](https://github.com/pluginsglpi/formcreator/commit/368450bd))
*  remove dead code ([7197679d](https://github.com/pluginsglpi/formcreator/commit/7197679d))
*  restore useful file ([cf619ca6](https://github.com/pluginsglpi/formcreator/commit/cf619ca6))
*  ribbon in modals ([a33386fc](https://github.com/pluginsglpi/formcreator/commit/a33386fc))
* **abstracttarget:** bad field name ([9e0c3de3](https://github.com/pluginsglpi/formcreator/commit/9e0c3de3))
* **abstracttarget:** bad field name ([c8b9ae4b](https://github.com/pluginsglpi/formcreator/commit/c8b9ae4b))
* **abstracttarget:** email actors deduplication ([4d64a072](https://github.com/pluginsglpi/formcreator/commit/4d64a072))
* **actorfield:** avoid empty choice ([5743b78e](https://github.com/pluginsglpi/formcreator/commit/5743b78e))
* **actorfield:** padding before items ([c7615250](https://github.com/pluginsglpi/formcreator/commit/c7615250))
* **condition:** better integrity check ([b93a46c0](https://github.com/pluginsglpi/formcreator/commit/b93a46c0))
* **condition:** enhance skip checks flag implementation ([2ca80e14](https://github.com/pluginsglpi/formcreator/commit/2ca80e14))
* **condition:** make condition work with question (not using twig yet) ([6a76c43b](https://github.com/pluginsglpi/formcreator/commit/6a76c43b))
* **condition:** some display failures ([c456a5d2](https://github.com/pluginsglpi/formcreator/commit/c456a5d2))
* **form:** add margin between form header and sections list ([105a918d](https://github.com/pluginsglpi/formcreator/commit/105a918d))
* **form:** data consistency check ([4def6775](https://github.com/pluginsglpi/formcreator/commit/4def6775))
* **form:** display user form spacing problem ([80d47d0a](https://github.com/pluginsglpi/formcreator/commit/80d47d0a))
* **form:** problem displaying a anonymous compatible question ([962bb337](https://github.com/pluginsglpi/formcreator/commit/962bb337))
* **form:** saved input problem ([c37f0a09](https://github.com/pluginsglpi/formcreator/commit/c37f0a09))
* **form:** typo ([65a818cb](https://github.com/pluginsglpi/formcreator/commit/65a818cb))
* **form:** typo ([7414ff7e](https://github.com/pluginsglpi/formcreator/commit/7414ff7e))
* **form:** typo ([a47b1ca7](https://github.com/pluginsglpi/formcreator/commit/a47b1ca7))
* **form:** typo in localized string ([521f5d6b](https://github.com/pluginsglpi/formcreator/commit/521f5d6b))
* **form:** visible by default ([bc2ebfd6](https://github.com/pluginsglpi/formcreator/commit/bc2ebfd6))
* **form_profile:** php warning ([0feade42](https://github.com/pluginsglpi/formcreator/commit/0feade42))
* **form_validator:** factorize, fix several code inconsistencies ([df636174](https://github.com/pluginsglpi/formcreator/commit/df636174))
* **formanswer:** breadcrumb ([1b13ee89](https://github.com/pluginsglpi/formcreator/commit/1b13ee89))
* **formanswer:** ignore deleted tickets when finding minimal status ([a3c529d6](https://github.com/pluginsglpi/formcreator/commit/a3c529d6))
* **formlanguage:** chained translation broken ([a7d559cf](https://github.com/pluginsglpi/formcreator/commit/a7d559cf))
* **glpiselectfield:** entity restriction not applied on users ([e8a3d1b4](https://github.com/pluginsglpi/formcreator/commit/e8a3d1b4))
* **glpiselectfield:** itemtype data migrated in dedicated column ([d667d690](https://github.com/pluginsglpi/formcreator/commit/d667d690))
* **glpiselectfield:** search itemtype in wrong locatin ([9fd2befe](https://github.com/pluginsglpi/formcreator/commit/9fd2befe))
* **hostnamefield:** value not read from answers ([dc7ad7b9](https://github.com/pluginsglpi/formcreator/commit/dc7ad7b9))
* **instal:** tables must be utf8mb4 ([5e36515c](https://github.com/pluginsglpi/formcreator/commit/5e36515c))
* **install:** force update of the table before updating it with syncIssues ([f4c95ed1](https://github.com/pluginsglpi/formcreator/commit/f4c95ed1))
* **install:** make install more silent ([e857e2fb](https://github.com/pluginsglpi/formcreator/commit/e857e2fb))
* **issue:** do not alter validator user on ticket update ([4d8ae9d3](https://github.com/pluginsglpi/formcreator/commit/4d8ae9d3))
* **issue:** prevent creation if the ticket is linked to a form answer ([13638cdb](https://github.com/pluginsglpi/formcreator/commit/13638cdb))
* **issue:** prevent php warings with incorrect url ([972dc400](https://github.com/pluginsglpi/formcreator/commit/972dc400))
* **issue:** show issue even when service catalog is disabled ([131bfe53](https://github.com/pluginsglpi/formcreator/commit/131bfe53))
* **issue:** show ticket in service catalog ([e83c7793](https://github.com/pluginsglpi/formcreator/commit/e83c7793))
* **question:** don't show mandatory toggle when not supported ([b5e15b7d](https://github.com/pluginsglpi/formcreator/commit/b5e15b7d))
* **question:** empty name section unavailable ([356a4e18](https://github.com/pluginsglpi/formcreator/commit/356a4e18))
* **question:** missing arg for template, breaking conditions rendering ([9c0ce396](https://github.com/pluginsglpi/formcreator/commit/9c0ce396))
* **question:** no twig here for now ([cfeab139](https://github.com/pluginsglpi/formcreator/commit/cfeab139))
* **question:** show error message on deletion failure ([0f5b49f0](https://github.com/pluginsglpi/formcreator/commit/0f5b49f0))
* **question:** uniformuze question and section JS code ([4e997e59](https://github.com/pluginsglpi/formcreator/commit/4e997e59))
* **question:** vertical margin between condition mode and list of conditions ([2d961c76](https://github.com/pluginsglpi/formcreator/commit/2d961c76))
* **question,section:** set icon ([c3b17174](https://github.com/pluginsglpi/formcreator/commit/c3b17174))
* **radiosfield:** changing question type from glpi select fails ([e35cfea6](https://github.com/pluginsglpi/formcreator/commit/e35cfea6))
* **section:** order lost after section deletion ([ac495734](https://github.com/pluginsglpi/formcreator/commit/ac495734))
* **section,question:** add and edit don't get teh value of rich text areas ([7c7e4a9a](https://github.com/pluginsglpi/formcreator/commit/7c7e4a9a))
* **section,question:** modals malfunctions ([cf22eb02](https://github.com/pluginsglpi/formcreator/commit/cf22eb02))
* **target_actor:** export/import ([8dfbeb15](https://github.com/pluginsglpi/formcreator/commit/8dfbeb15))
* **target_actor:** prevent inconsistent adds ([9f75b258](https://github.com/pluginsglpi/formcreator/commit/9f75b258))
* **target_actor:** unable to add some actors ([16c35f34](https://github.com/pluginsglpi/formcreator/commit/16c35f34))
* **target_actor:** use ajax to add and delete items ([f7efae09](https://github.com/pluginsglpi/formcreator/commit/f7efae09))
* **targetchange:** add missing template ([6f532fe1](https://github.com/pluginsglpi/formcreator/commit/6f532fe1))
* **targetproblem:** update interface ([1ee9f3da](https://github.com/pluginsglpi/formcreator/commit/1ee9f3da))
* **targetticket:** do not force request type ([dedef1d5](https://github.com/pluginsglpi/formcreator/commit/dedef1d5))
* **targetticket:** loss of associated element ([82112fba](https://github.com/pluginsglpi/formcreator/commit/82112fba))
* **textareafield:** call to non existing class ([a333da83](https://github.com/pluginsglpi/formcreator/commit/a333da83))
* **textareafield:** deprecated call ([2c3702d9](https://github.com/pluginsglpi/formcreator/commit/2c3702d9))


### Features

*  drop layout : no longer exists in GLPI 10 ([b4f7089b](https://github.com/pluginsglpi/formcreator/commit/b4f7089b))
*  drop support of GLPI 9.5 ([372a917e](https://github.com/pluginsglpi/formcreator/commit/372a917e))
*  mini dashboard ([fa60d270](https://github.com/pluginsglpi/formcreator/commit/fa60d270))
* **category:** use home icon instead of 'see all' ([e75e6ab6](https://github.com/pluginsglpi/formcreator/commit/e75e6ab6))
* **entityconfig:** show option for dashboard ([5ebc2db3](https://github.com/pluginsglpi/formcreator/commit/5ebc2db3))
* **entityconfig:** show option for dashboard ([347083d3](https://github.com/pluginsglpi/formcreator/commit/347083d3))
* **form:** automatically create a section when creating a form ([e5577ed1](https://github.com/pluginsglpi/formcreator/commit/e5577ed1))
* **formanswer:** priority to waiting and processing status ([4fbdfa44](https://github.com/pluginsglpi/formcreator/commit/4fbdfa44))
* **install:** drop upgrade support from very old versions ([214cfc82](https://github.com/pluginsglpi/formcreator/commit/214cfc82))
* **issue,formanswer:** compose status from ticket ([fa89fd70](https://github.com/pluginsglpi/formcreator/commit/fa89fd70))
* **wizard:** do not show forms or FAQ on page load ([dcbcdf94](https://github.com/pluginsglpi/formcreator/commit/dcbcdf94))


<a name="2.13.0-alpha.2"></a>
## [2.13.0-alpha.2](https://github.com/pluginsglpi/formcreator/compare/2.13.0-alpha.1..2.13.0-alpha.2) (2021-12-14)


### Bug Fixes

*  category tree broken ([f60043fb](https://github.com/pluginsglpi/formcreator/commit/f60043fb))
*  do not redefine menu if standard interface ([37001d25](https://github.com/pluginsglpi/formcreator/commit/37001d25))
*  ineffective code to define menu ([1ab4a065](https://github.com/pluginsglpi/formcreator/commit/1ab4a065))
*  malformed menu item for KB ([7641ba6b](https://github.com/pluginsglpi/formcreator/commit/7641ba6b))
*  re-add menu items for service catalog ([f24d4e00](https://github.com/pluginsglpi/formcreator/commit/f24d4e00))
*  redirection to wrong ticket in some cases ([a2c3898a](https://github.com/pluginsglpi/formcreator/commit/a2c3898a))
*  remove useless config link in plugin ([58df4079](https://github.com/pluginsglpi/formcreator/commit/58df4079))
* **form:** PHP warning when displaying form's historical ([893efdab](https://github.com/pluginsglpi/formcreator/commit/893efdab))
* **form:** duplication error ([0ff45961](https://github.com/pluginsglpi/formcreator/commit/0ff45961)), closes [#2448](https://github.com/pluginsglpi/formcreator/issues/2448)
* **form:** import linker reset ([1003dc45](https://github.com/pluginsglpi/formcreator/commit/1003dc45))
* **form:** use download icon for import ([7c9e88ec](https://github.com/pluginsglpi/formcreator/commit/7c9e88ec))
* **formanswer:** loss of answers upon valdiation ([21a29ba3](https://github.com/pluginsglpi/formcreator/commit/21a29ba3))
* **formlist:** remove useless links in menu ([e85e04bd](https://github.com/pluginsglpi/formcreator/commit/e85e04bd))
* **fromanwser:** status display enhancements (#2508) ([e87cbdbc](https://github.com/pluginsglpi/formcreator/commit/e87cbdbc))
* **issue:** call to deprecated method ([db56d3e5](https://github.com/pluginsglpi/formcreator/commit/db56d3e5))
* **issue:** redirection when multiple tickets ([64eb8a50](https://github.com/pluginsglpi/formcreator/commit/64eb8a50))
* **issue:** use of RichText class ([707a9b91](https://github.com/pluginsglpi/formcreator/commit/707a9b91))
* **ldapfield:** non latin char escaping ([c4473de3](https://github.com/pluginsglpi/formcreator/commit/c4473de3))
* **ldapselect:** drop support for PHP 7.3 as GLPI supports 7.4 and later ([52e6cad2](https://github.com/pluginsglpi/formcreator/commit/52e6cad2))
* **ldapselect:** drop support for PHP 7.3 as GLPI supports 7.4 and later ([c98e3430](https://github.com/pluginsglpi/formcreator/commit/c98e3430))
* **ldapselect:** drop support for PHP 7.3 as GLPI supports 7.4 and later ([7033c3a4](https://github.com/pluginsglpi/formcreator/commit/7033c3a4))
* **linker:** inverted arguments in method call ([c477ae91](https://github.com/pluginsglpi/formcreator/commit/c477ae91))
* **question:** show error toast when editing a question fails ([d90c3f99](https://github.com/pluginsglpi/formcreator/commit/d90c3f99))


### Features

*  Improve browser tabs names ([58a9b047](https://github.com/pluginsglpi/formcreator/commit/58a9b047))
*  adapt to new GLPI's autoload ([894df7cc](https://github.com/pluginsglpi/formcreator/commit/894df7cc))
*  adapt to new GLPI's autoload ([534856df](https://github.com/pluginsglpi/formcreator/commit/534856df))
*  build css if missing ([9d6aec77](https://github.com/pluginsglpi/formcreator/commit/9d6aec77))
* **answer:** copmatibility with API ([f4dd4a31](https://github.com/pluginsglpi/formcreator/commit/f4dd4a31))
* **category:** show parent label in back pseudo-item ([893b2ccc](https://github.com/pluginsglpi/formcreator/commit/893b2ccc))
* **form:** compliance with GLPI 10; replace form categories by KB categories ([c7c60592](https://github.com/pluginsglpi/formcreator/commit/c7c60592))
* **issue:** access to admins ([33ee3eaa](https://github.com/pluginsglpi/formcreator/commit/33ee3eaa))
* **targetproblem:** target problem ([e9af4130](https://github.com/pluginsglpi/formcreator/commit/e9af4130))
* **targetproblem:** target problem ([4ae2a92e](https://github.com/pluginsglpi/formcreator/commit/4ae2a92e))



<a name="2.13.0-alpha.1"></a>
## [2.13.0-alpha.1](https://github.com/pluginsglpi/formcreator/compare/v2.12.3..2.13.0-alpha.1) (2021-11-23)


### Bug Fixes

*  colors for counters ([d172843c](https://github.com/pluginsglpi/formcreator/commit/d172843c))
*  remove compiled css ([51573194](https://github.com/pluginsglpi/formcreator/commit/51573194))
*  responsive UI ([44d061c7](https://github.com/pluginsglpi/formcreator/commit/44d061c7))
*  strict JS code triggers warnings ([8262bd3c](https://github.com/pluginsglpi/formcreator/commit/8262bd3c))
*  text inputs CSS ([cd7a232c](https://github.com/pluginsglpi/formcreator/commit/cd7a232c))
*  timestamps in DB ([3897820c](https://github.com/pluginsglpi/formcreator/commit/3897820c))
*  various UI fixes ([cabdd8a5](https://github.com/pluginsglpi/formcreator/commit/cabdd8a5))
* **abstracttarget:** add button appearance ([f82a277d](https://github.com/pluginsglpi/formcreator/commit/f82a277d))
* **docs:** remove again useless files ([56f49410](https://github.com/pluginsglpi/formcreator/commit/56f49410))
* **docs:** remove useless files from repo ([75f81c33](https://github.com/pluginsglpi/formcreator/commit/75f81c33))
* **form:** store itemtype in DOM ([68ad8969](https://github.com/pluginsglpi/formcreator/commit/68ad8969))
* **form:** text inputs without bootstrap CSS ([989505e7](https://github.com/pluginsglpi/formcreator/commit/989505e7))
* **form:** text inputs without bootstrap CSS ([08e9aa2f](https://github.com/pluginsglpi/formcreator/commit/08e9aa2f))
* **form:** use font awesome ([15c66f80](https://github.com/pluginsglpi/formcreator/commit/15c66f80))
* **form_validator:** manage deletion of all items for a level ([41320baf](https://github.com/pluginsglpi/formcreator/commit/41320baf))
* **form_validator:** manage deletion of all items for a level ([dc3dcf4b](https://github.com/pluginsglpi/formcreator/commit/dc3dcf4b))
* **form_validator:** possible blank page ([43e4a091](https://github.com/pluginsglpi/formcreator/commit/43e4a091))
* **formanswer:** handle answers when valdiating ([a5a6a6c7](https://github.com/pluginsglpi/formcreator/commit/a5a6a6c7))
* **formanswer:** load answers when validating a formanswer ([99844c97](https://github.com/pluginsglpi/formcreator/commit/99844c97))
* **formanswer:** no longer need to call showfields on display ([c01ae3d7](https://github.com/pluginsglpi/formcreator/commit/c01ae3d7))
* **install:** port DATETIME to TIMESTAMP upgrade bug from glpi ([619f647c](https://github.com/pluginsglpi/formcreator/commit/619f647c))
* **issue:** enable qtip for formanswer ([915bf058](https://github.com/pluginsglpi/formcreator/commit/915bf058))
* **issue:** include 1st level validator groups of current user ([2dd31f28](https://github.com/pluginsglpi/formcreator/commit/2dd31f28))
* **issue:** possible SQL error whe naccessing issues from helpdesk ([5a772ae2](https://github.com/pluginsglpi/formcreator/commit/5a772ae2))
* **issue:** restrict user dropdowns to current user in service catalog ([cedfe559](https://github.com/pluginsglpi/formcreator/commit/cedfe559))
* **question:** make import resilient against missing parameters ([5604c92d](https://github.com/pluginsglpi/formcreator/commit/5604c92d))
* **question:** refactor requirement for input ([3934caa9](https://github.com/pluginsglpi/formcreator/commit/3934caa9))
* **question:** text input CSS ([d3bddf17](https://github.com/pluginsglpi/formcreator/commit/d3bddf17))
* **question:** text input CSS ([0c0a932c](https://github.com/pluginsglpi/formcreator/commit/0c0a932c))
* **targetticket:** SQL exception, refactor ([2af9d34d](https://github.com/pluginsglpi/formcreator/commit/2af9d34d))
* **targetticket,targetchange:** adding tags was broken ([bf34506c](https://github.com/pluginsglpi/formcreator/commit/bf34506c))
* **targetticket,targetchange:** deletion broken ([f251dced](https://github.com/pluginsglpi/formcreator/commit/f251dced))


### Features

*  big update ([b60fbe47](https://github.com/pluginsglpi/formcreator/commit/b60fbe47))
* **dropdownfield,glpiselectfield:** choose if subtree root is selectable ([164e7524](https://github.com/pluginsglpi/formcreator/commit/164e7524))
* **form_validator:** enhance presentation ([2cb3f4ef](https://github.com/pluginsglpi/formcreator/commit/2cb3f4ef))
* **form_valodator:** multiple validation level ([75106b61](https://github.com/pluginsglpi/formcreator/commit/75106b61))
* **glpiselectfield:** databases plugin support ([1a42e8ad](https://github.com/pluginsglpi/formcreator/commit/1a42e8ad))
* **install:** drop internal CLI instal script ([96a26317](https://github.com/pluginsglpi/formcreator/commit/96a26317))
* **install:** upgrade from 2.11.3 to 2.12 ([d115c3d8](https://github.com/pluginsglpi/formcreator/commit/d115c3d8))
* **issue:** validation_percent ([bd9b865c](https://github.com/pluginsglpi/formcreator/commit/bd9b865c))



<a name="v2.12.3"></a>
## [v2.12.3](https://github.com/pluginsglpi/formcreator/compare/v2.12.2..v2.12.3) (2021-11-05)


### Bug Fixes

*  color of counters lost ([5834f96f](https://github.com/pluginsglpi/formcreator/commit/5834f96f))
*  css minify via GLPI's CLI tool broken ([5fdedb38](https://github.com/pluginsglpi/formcreator/commit/5fdedb38))
*  menu unreadable in service catalog ([2790dac8](https://github.com/pluginsglpi/formcreator/commit/2790dac8))
* **abstracttarget:** conflicting dropdowns ([57b775fb](https://github.com/pluginsglpi/formcreator/commit/57b775fb))
* **answer:** missing default value in schema ([195ef446](https://github.com/pluginsglpi/formcreator/commit/195ef446))
* **category:** completename rendered instead of short name ([82f50ccb](https://github.com/pluginsglpi/formcreator/commit/82f50ccb)), closes [#2424](https://github.com/pluginsglpi/formcreator/issues/2424)
* **condition:** ensure form object is loaded ([d7c3c2c9](https://github.com/pluginsglpi/formcreator/commit/d7c3c2c9))
* **descriptinfield:** list rendering ([0e4421da](https://github.com/pluginsglpi/formcreator/commit/0e4421da))
* **dropdownfield:** group restriction inaccurate ([ab83f34d](https://github.com/pluginsglpi/formcreator/commit/ab83f34d))
* **dropdownfield:** itil category is entity assignable ([26d8352a](https://github.com/pluginsglpi/formcreator/commit/26d8352a))
* **form:** allow admins to testforms ([e3462d5b](https://github.com/pluginsglpi/formcreator/commit/e3462d5b))
* **form:** allow condition evaluation on disabled forms for admins ([65c46bd7](https://github.com/pluginsglpi/formcreator/commit/65c46bd7))
* **form:** export of entity / category name ([e14a585f](https://github.com/pluginsglpi/formcreator/commit/e14a585f))
* **form:** handling duplication failure when no form to rename ([351a36d3](https://github.com/pluginsglpi/formcreator/commit/351a36d3))
* **form:** invaldiate opcache ([630e5239](https://github.com/pluginsglpi/formcreator/commit/630e5239))
* **form_language:** vertical view does not works ([1b180d27](https://github.com/pluginsglpi/formcreator/commit/1b180d27))
* **formanswer:** unloadded objects when validating ([b4891be4](https://github.com/pluginsglpi/formcreator/commit/b4891be4))
* **glpiselectfield:** tree settings of entity question ([48053f55](https://github.com/pluginsglpi/formcreator/commit/48053f55))
* **glpiselectfield, dropdownfield:** filter by helpdesk visibility only in simplified interface ([1cb5f346](https://github.com/pluginsglpi/formcreator/commit/1cb5f346))
* **install:** populate issues table on upgrade ([76550d21](https://github.com/pluginsglpi/formcreator/commit/76550d21))
* **issue:** SQL escaping problem with text fields ([d589745e](https://github.com/pluginsglpi/formcreator/commit/d589745e))
* **issue:** answers with multiple tickets ([c77be76f](https://github.com/pluginsglpi/formcreator/commit/c77be76f))
* **locales:** wrong language used in service catalog ([0327d520](https://github.com/pluginsglpi/formcreator/commit/0327d520))


### Features

* **glpiselectfield:** restrict tickets in simplified interface ([8a901f48](https://github.com/pluginsglpi/formcreator/commit/8a901f48))
* **targetticket:** link to a ticket from a question ([a563d11e](https://github.com/pluginsglpi/formcreator/commit/a563d11e))


## [2.12.2](https://github.com/pluginsglpi/formcreator/compare/v2.12.1...v2.12.2) (2021-09-14)


### Bug Fixes

* **entityconfig:** hamonize wtUI with GLPI ([99ef6e6](https://github.com/pluginsglpi/formcreator/commit/99ef6e6d339c9714aa40c7fb2428a70214424c3a))
* **field:** check access rights before updating fields visibility ([ef3fc66](https://github.com/pluginsglpi/formcreator/commit/ef3fc6623cfb2b9a1ebc7d6889179d895f2a0852))
* **form_validator:** php errors in import process ([7ae01dc](https://github.com/pluginsglpi/formcreator/commit/7ae01dc90d414bfc5af44fa77dd2b5c56fc15210))
* **ipfield, hiddenfield:** do not generate HTML input if edition disabled ([0776ef2](https://github.com/pluginsglpi/formcreator/commit/0776ef2c3342fb5881790c48239904017559788c))
* **targetticket:** associated items from question ([2cd2bd6](https://github.com/pluginsglpi/formcreator/commit/2cd2bd62e8767b8eba63f0a617053a0fed6ea44d))
* check right before export ([af04e78](https://github.com/pluginsglpi/formcreator/commit/af04e78ccbb0de4471a567b2f4ca9b419ac3ef31))
* duplicate JS function ([5386f65](https://github.com/pluginsglpi/formcreator/commit/5386f650e6bf5cd178908f56ca4b1414315ed9ea))
* **fields:** preveint fatal error when inconsistency found in DB ([65c461a](https://github.com/pluginsglpi/formcreator/commit/65c461a10316cc02d46f1ec95e2c10ea78bb07e1))
* **ldapfield:** organize code and prevent obsolete function call ([514e751](https://github.com/pluginsglpi/formcreator/commit/514e751c92ce3b7e9c73642e74eb3e3ad7ecde99))
* **ldapfield:** undefined var makes LDAP querying fail ([2dddc30](https://github.com/pluginsglpi/formcreator/commit/2dddc301f7a137ceb03c4602b89f8b5236ea6a55))
* **question:** save images in description as inline base64 ([21b94f5](https://github.com/pluginsglpi/formcreator/commit/21b94f5b762633c4e3288667fdcbe10ab3453e1b))
* **targetticket:** remove useless use statement ([906ebeb](https://github.com/pluginsglpi/formcreator/commit/906ebeb41059456a838a77f93e514169ad257468))
* **targetticket:** type not set ([6d4c3af](https://github.com/pluginsglpi/formcreator/commit/6d4c3af65e1c1ed336e1d35befff5ae9b321eea0))
* **targetticket, targetchange:** embedded image handling ([44a65a0](https://github.com/pluginsglpi/formcreator/commit/44a65a0218d9ac472b3a77f3a4de69b0609e9c1b))
* **targetticket,targetchange:** avoid adding same actor several times ([1f82f3b](https://github.com/pluginsglpi/formcreator/commit/1f82f3b191e52f4bb3000c1c16b93806e83dcd68))
* **textfield,txtareafield:** defaultr value not translated ([15bb281](https://github.com/pluginsglpi/formcreator/commit/15bb281e611ffc5f202ee5d45b06510ae83c8dd4))
* **urgencyfield:** obey empty_value_setting ([91f14eb](https://github.com/pluginsglpi/formcreator/commit/91f14ebcb01b3da4519e5e8c55e42a371cc21df3))
* LDAP error handler ([6130581](https://github.com/pluginsglpi/formcreator/commit/6130581309c080d9c8e9f3b99ad8761e770fc6df))



<a name="v2.12.1"></a>
## [v2.12.1](https://github.com/pluginsglpi/formcreator/compare/v2.12.0...v2.12.1) (2021-08-16)


### Bug Fixes

* **category:** bad load event ([b573f74](https://github.com/pluginsglpi/formcreator/commit/b573f74b9eaeb27b4b53030a8a35fb946db955af))
* **descriptionfield:** increase tex limit ([c57cb14](https://github.com/pluginsglpi/formcreator/commit/c57cb148a2d31be30ab15cdc82be145b292a1a56))
* **entityconfig:** default values and constant values of KB separation ([31ccd9b](https://github.com/pluginsglpi/formcreator/commit/31ccd9b0f60d5fc0e4d7d93b75ba181ffcba1259))
* **entityconfig:** do not allow edition of config if not enough right ([067600a](https://github.com/pluginsglpi/formcreator/commit/067600a0ef45f7703daa6b90c60ddb7de69533bc))
* **entityconfig:** do not show Formcreator tab if no right ([452f682](https://github.com/pluginsglpi/formcreator/commit/452f6825d431acf148583cbc7987a48b286f0903))
* **entityconfig:** HTML should be clenaed for safety, not dropped ([39b569f](https://github.com/pluginsglpi/formcreator/commit/39b569f8babb544eed935b154329e3ed2d2201b1))
* **entityconfig:** tinymce not always loaded ([f2cd143](https://github.com/pluginsglpi/formcreator/commit/f2cd1439b55699eb265da0b72d1451d24fc843a7))
* **form:** add target form shall use the theme's color palette ([4774f1d](https://github.com/pluginsglpi/formcreator/commit/4774f1ddd37cefe8e694c215eb8749173ee69acf))
* **form:** forms not translated on central tab ([f7a6ec8](https://github.com/pluginsglpi/formcreator/commit/f7a6ec85fdaea9717317450c7cc1fb0fa6a4d30a))
* **form:** untranslated description ([e97d3fb](https://github.com/pluginsglpi/formcreator/commit/e97d3fb11655a1bb37987013b92b4da3d5c6249d))
* **form:** use GLPI's color theme when showing list of targets ([ee8a6e2](https://github.com/pluginsglpi/formcreator/commit/ee8a6e239d00cead87e1589d7394afdb51f4ce3c))
* **formanswer:** deletion of answers when accepting answers without editing them ([317c4da](https://github.com/pluginsglpi/formcreator/commit/317c4dac91a4581d7f6a655403039042bc69ef45))
* **glpiselectfield:** entity restriction not saved ([6869eed](https://github.com/pluginsglpi/formcreator/commit/6869eed831610092c6f7d8d65b3c9b9004a83765))
* **glpiselectfield,dropdownfield:** entity restriction show / hide issues ([72972f9](https://github.com/pluginsglpi/formcreator/commit/72972f961783476fdbc5e80bf639f26fa9aa0c0e))
* **issue:** missed column rename in redirection handling ([01d0816](https://github.com/pluginsglpi/formcreator/commit/01d0816b9e44ead7891cb815ea765ac087db0ed5))
* **issue:** redirection error ([aeb297b](https://github.com/pluginsglpi/formcreator/commit/aeb297bd8c2a33cca6fa3288d9859dff18921dd0))
* **question:** check regex condition before save or update ([57914ac](https://github.com/pluginsglpi/formcreator/commit/57914ac4ff3971cab16f4130eaca4988302b4bb7))
* **question,section:** backslash in the name appears after editing an existing item ([1db01f5](https://github.com/pluginsglpi/formcreator/commit/1db01f52a4bca09c40e0ae38bcaff320f4afc54c))
* **targetticket,targetchange:** cannot use dropdown type questions to set entity, category ([de0a303](https://github.com/pluginsglpi/formcreator/commit/de0a303c92febdcb7f56a660d24b81a75afad973))
* **targetticket,targetchange:** current user may be automatically added to requesters ([8fa2a3f](https://github.com/pluginsglpi/formcreator/commit/8fa2a3f6bcf075113d376ecb0734b855c83601c0))
* **translation:** backslashes in translated text ([cf7589f](https://github.com/pluginsglpi/formcreator/commit/cf7589ffad273cb917e130bf724f743fc33b6c81))
* **translation:** bad arguments when clearing cache ([623728a](https://github.com/pluginsglpi/formcreator/commit/623728a3c2976ddb8f425e6e025033243681da11))
* typo causing a fatal error ([fa8dc9b](https://github.com/pluginsglpi/formcreator/commit/fa8dc9bf96bdebbea618eb7098e62083aab2b7c2))
* **radiosfield:** bad regex condition check ([221ae34](https://github.com/pluginsglpi/formcreator/commit/221ae34156fc345785587d92e5f4c0825299dab8))
* **targetticket,targetchange:** long text truncated by GLPI ([9143172](https://github.com/pluginsglpi/formcreator/commit/9143172f8f64f9f58099cfb3aabcb9ad383e414f))
* **targetticket,targetchange:** target title not translated ([fee9f4f](https://github.com/pluginsglpi/formcreator/commit/fee9f4f17f85aac94be2f94b50076cdc1f236387))
* **targetticket,targetchange:** title is a string, not a rich text ([3b6171a](https://github.com/pluginsglpi/formcreator/commit/3b6171ae52dc0c8433c3bac52dcf78a8333769ef))
* redirect from  ticket to formanswer if several target tickets ([dbfbb10](https://github.com/pluginsglpi/formcreator/commit/dbfbb10d8acdfc9949ed47731a4eb61bf7e1e86c))



<a name="v2.12.0"></a>
## [v2.12.0](https://github.com/pluginsglpi/formcreator/compare/v2.12.0-beta.1..v2.12.0) (2021-07-09)


### Bug Fixes

*  compatibility with next version of GLPI ([08f07cf9](https://github.com/pluginsglpi/formcreator/commit/08f07cf9))
*  rename scripts file ([5899fd35](https://github.com/pluginsglpi/formcreator/commit/5899fd35))
* **actorsfield:** prevent error when computing tooltip ([2d1b85d9](https://github.com/pluginsglpi/formcreator/commit/2d1b85d9))
* **checkboxes:** avoid error when computing tooltip ([f054bbcd](https://github.com/pluginsglpi/formcreator/commit/f054bbcd))
* **composite:** avoid error if ticket does not exists ([67a4092f](https://github.com/pluginsglpi/formcreator/commit/67a4092f))
* **composite:** fix PHP warning ([6cc01b9b](https://github.com/pluginsglpi/formcreator/commit/6cc01b9b))
* **docs:** bug report template must specify GLPI and plugins versions ([2bdb173a](https://github.com/pluginsglpi/formcreator/commit/2bdb173a))
* **dropdownfield:** fix parameters build for dropdowns ([75c09678](https://github.com/pluginsglpi/formcreator/commit/75c09678))
* **dropdownfield:** handling tree restriction params ([ca23e501](https://github.com/pluginsglpi/formcreator/commit/ca23e501))
* **form:** default value for language ([5005a279](https://github.com/pluginsglpi/formcreator/commit/5005a279))
* **form:** form title not translated in service catalog ([a61cbf65](https://github.com/pluginsglpi/formcreator/commit/a61cbf65))
* **form:** language column too short ([3f56044b](https://github.com/pluginsglpi/formcreator/commit/3f56044b)), closes [#2285](https://github.com/pluginsglpi/formcreator/issues/2285)
* **form:** performance fix ([81cf0065](https://github.com/pluginsglpi/formcreator/commit/81cf0065))
* **formanswer:** bad validator right check for groups ([91561830](https://github.com/pluginsglpi/formcreator/commit/91561830))
* **glpiselectfield:** rendering the itemtype ([6d244c6c](https://github.com/pluginsglpi/formcreator/commit/6d244c6c))
* **issue:** bad key when finding sub item of an assistance request ([6392cdf3](https://github.com/pluginsglpi/formcreator/commit/6392cdf3))
* **issue:** normalize columns ([f7931150](https://github.com/pluginsglpi/formcreator/commit/f7931150))
* **issue:** search options 14 ant 15 ([dd9d2608](https://github.com/pluginsglpi/formcreator/commit/dd9d2608))
* **issue:** update status when adding a validation ([2e1ae1a9](https://github.com/pluginsglpi/formcreator/commit/2e1ae1a9))
* **section,question:** workaround GLPI bug ([8d837f34](https://github.com/pluginsglpi/formcreator/commit/8d837f34))
* **targetchange,targetticket:** DB schema ([29a7c1df](https://github.com/pluginsglpi/formcreator/commit/29a7c1df))
* **targetticket:** associate items to tickets ([14a991b9](https://github.com/pluginsglpi/formcreator/commit/14a991b9))
* **targetticket,targetchange:** file dispatch accross several targets broken ([753b423d](https://github.com/pluginsglpi/formcreator/commit/753b423d))
* **targetticket,targetchange:** missing import of template settings ([791b1a20](https://github.com/pluginsglpi/formcreator/commit/791b1a20))


### Features

* **targetchange:** change template support ([0ea4079e](https://github.com/pluginsglpi/formcreator/commit/0ea4079e))
* **targetticket:** actor type: "Form author's manager" ([acefca84](https://github.com/pluginsglpi/formcreator/commit/acefca84))

<a name="v2.12.0-beta.1"></a>
## [v2.12.0-beta.1](https://github.com/pluginsglpi/formcreator/compare/v2.11.4..v2.12.0-beta.1) (2021-06-14)


### Bug Fixes

*  add and refactor search options ([81f4a448](https://github.com/pluginsglpi/formcreator/commit/81f4a448))
*  change the placeholder of the search input ([8cc14d4e](https://github.com/pluginsglpi/formcreator/commit/8cc14d4e))
*  drop obsolete GLPI bug workaround ([5dbfb775](https://github.com/pluginsglpi/formcreator/commit/5dbfb775))
*  fatal error when not filling a date ([70896c96](https://github.com/pluginsglpi/formcreator/commit/70896c96))
*  inappropriate css loading ([bb48bd8b](https://github.com/pluginsglpi/formcreator/commit/bb48bd8b))
*  long text may be truncated ([b534f7e9](https://github.com/pluginsglpi/formcreator/commit/b534f7e9))
*  path detection to load JS ([06a10e05](https://github.com/pluginsglpi/formcreator/commit/06a10e05))
*  reset obsoleted tabs ([75d67687](https://github.com/pluginsglpi/formcreator/commit/75d67687))
*  responsive UI ([9f177131](https://github.com/pluginsglpi/formcreator/commit/9f177131))
*  several field have useless slash escaping ([14cceffe](https://github.com/pluginsglpi/formcreator/commit/14cceffe))
*  show menu when width is low ([6571bd57](https://github.com/pluginsglpi/formcreator/commit/6571bd57))
*  timestamps in DB ([e8649b9e](https://github.com/pluginsglpi/formcreator/commit/e8649b9e))
* **actorfield:** answer not displayed when shwoing saved data ([c1e3f91d](https://github.com/pluginsglpi/formcreator/commit/c1e3f91d))
* **actorsfield:** missed function rename ([9ed3a50d](https://github.com/pluginsglpi/formcreator/commit/9ed3a50d))
* **checkboxesfield:** use correct translation input type ([50a79820](https://github.com/pluginsglpi/formcreator/commit/50a79820))
* **condition:** avoid HTML entities in dropdown ([4bdbdb85](https://github.com/pluginsglpi/formcreator/commit/4bdbdb85))
* **condition:** loss of condition on submit button ([42d5fedd](https://github.com/pluginsglpi/formcreator/commit/42d5fedd))
* **docs:** remove again useless files ([f57d04fc](https://github.com/pluginsglpi/formcreator/commit/f57d04fc))
* **docs:** remove useless files from repo ([259da94b](https://github.com/pluginsglpi/formcreator/commit/259da94b))
* **dropdownfield:** add security token for GLPI 9.5.3 ([d6adbbff](https://github.com/pluginsglpi/formcreator/commit/d6adbbff))
* **dropdownfield:** disable recursivity ([98f87ab3](https://github.com/pluginsglpi/formcreator/commit/98f87ab3))
* **dropdownfield:** entity restriction relative to the form, not the user ([358b78a0](https://github.com/pluginsglpi/formcreator/commit/358b78a0)), closes [#2047](https://github.com/pluginsglpi/formcreator/issues/2047)
* **dropdownfield:** fix SQL error when translations are enabled ([c55dc491](https://github.com/pluginsglpi/formcreator/commit/c55dc491))
* **dropdownfield:** wrong IDOR token construct ([d7152e61](https://github.com/pluginsglpi/formcreator/commit/d7152e61))
* **entityconfig:** bad constant value ([b887b204](https://github.com/pluginsglpi/formcreator/commit/b887b204))
* **filefield:** mandatory fails when file is uploaded ([08e297b2](https://github.com/pluginsglpi/formcreator/commit/08e297b2))
* **form:** add label to validator inputs ([34269120](https://github.com/pluginsglpi/formcreator/commit/34269120))
* **form:** add spacing between questions ([68df69f9](https://github.com/pluginsglpi/formcreator/commit/68df69f9))
* **form:** create dir for translations ([d8b49484](https://github.com/pluginsglpi/formcreator/commit/d8b49484))
* **form:** error message when anonymous form submitted ([f48f010d](https://github.com/pluginsglpi/formcreator/commit/f48f010d))
* **form:** loss of icon when editing a form ([ba1ac340](https://github.com/pluginsglpi/formcreator/commit/ba1ac340))
* **form:** reimplement submit button conditions ([363141e6](https://github.com/pluginsglpi/formcreator/commit/363141e6))
* **form:** sort not applied on 1st display ([3ef23095](https://github.com/pluginsglpi/formcreator/commit/3ef23095))
* **form:** typo in class name ([3fcf5bd5](https://github.com/pluginsglpi/formcreator/commit/3fcf5bd5))
* **form:** version check on import ([41e0108b](https://github.com/pluginsglpi/formcreator/commit/41e0108b))
* **form_language:** bad array construct ([83088b9a](https://github.com/pluginsglpi/formcreator/commit/83088b9a))
* **form_language:** limit items with langaues for the form only ([d103bb49](https://github.com/pluginsglpi/formcreator/commit/d103bb49))
* **form_translation:** form serilization problem with rich text ([c34c0c28](https://github.com/pluginsglpi/formcreator/commit/c34c0c28))
* **form_translation:** refresh tab after update of a string ([3dfca832](https://github.com/pluginsglpi/formcreator/commit/3dfca832))
* **formanswer:** cacptcha check ([c5044cf1](https://github.com/pluginsglpi/formcreator/commit/c5044cf1))
* **formanswer:** load answers when validating a formanswer ([8277822d](https://github.com/pluginsglpi/formcreator/commit/8277822d))
* **formanswer:** no longer need to call showfields on display ([17d85f93](https://github.com/pluginsglpi/formcreator/commit/17d85f93))
* **formanswer:** status displayed twice, useless ([41a46c64](https://github.com/pluginsglpi/formcreator/commit/41a46c64))
* **formanswers:** execute show conditions when displaying formanswer ([3e508a10](https://github.com/pluginsglpi/formcreator/commit/3e508a10))
* **glpiselectfield:** comparisons need to properly find the itemtype ([57578ec2](https://github.com/pluginsglpi/formcreator/commit/57578ec2))
* **install:** broken upgrade of target_actors ([d4441623](https://github.com/pluginsglpi/formcreator/commit/d4441623))
* **install:** consistency between datetime and timestamp type in DB ([270ee38a](https://github.com/pluginsglpi/formcreator/commit/270ee38a))
* **install:** php error in upgrade ([8477345d](https://github.com/pluginsglpi/formcreator/commit/8477345d))
* **install:** port DATETIME to TIMESTAMP upgrade bug from glpi ([c8405774](https://github.com/pluginsglpi/formcreator/commit/c8405774))
* **install:** prevent ON UPDATE statement in table description ([ab32142e](https://github.com/pluginsglpi/formcreator/commit/ab32142e))
* **issue:** access to tickets ([38376d94](https://github.com/pluginsglpi/formcreator/commit/38376d94))
* **issue:** enable qtip for formanswer ([968c2f9f](https://github.com/pluginsglpi/formcreator/commit/968c2f9f))
* **issue:** include 1st level validator groups of current user ([3d86a3f4](https://github.com/pluginsglpi/formcreator/commit/3d86a3f4))
* **issue:** loss of issue on automatic action ([f6a33adb](https://github.com/pluginsglpi/formcreator/commit/f6a33adb))
* **issue:** php warning when running mailcollector ([03869be2](https://github.com/pluginsglpi/formcreator/commit/03869be2))
* **issue:** possible SQL error whe naccessing issues from helpdesk ([535aa824](https://github.com/pluginsglpi/formcreator/commit/535aa824))
* **issue:** restrict user dropdowns to current user in service catalog ([3ad80e25](https://github.com/pluginsglpi/formcreator/commit/3ad80e25))
* **issue:** update handling of url in emai: notifications ([77d3a329](https://github.com/pluginsglpi/formcreator/commit/77d3a329))
* **issue:** update modificaitoin date when a followup is added to ticket ([0d6597ad](https://github.com/pluginsglpi/formcreator/commit/0d6597ad))
* **ldapfield:** PHP warning when editing the question ([09e3a3a9](https://github.com/pluginsglpi/formcreator/commit/09e3a3a9)), closes [#2116](https://github.com/pluginsglpi/formcreator/issues/2116)
* **ldapfield:** only last page of LDAP results rendered ([17ac4615](https://github.com/pluginsglpi/formcreator/commit/17ac4615))
* **notificationtargetformanswer:** tags not fully rendered ([e0b3ba7d](https://github.com/pluginsglpi/formcreator/commit/e0b3ba7d))
* **question:** better error handling ([cf8f56fc](https://github.com/pluginsglpi/formcreator/commit/cf8f56fc))
* **question:** handle long label display ([e1301b8b](https://github.com/pluginsglpi/formcreator/commit/e1301b8b))
* **question:** make import resilient against missing parameters ([6a2e2aba](https://github.com/pluginsglpi/formcreator/commit/6a2e2aba))
* **question:** prevent bad request ([ea66d631](https://github.com/pluginsglpi/formcreator/commit/ea66d631))
* **question:** reduce spacing in edition tools ([f0e9f139](https://github.com/pluginsglpi/formcreator/commit/f0e9f139))
* **questionrange:** bad search option indexing ([f73aa118](https://github.com/pluginsglpi/formcreator/commit/f73aa118))
* **section:** fail to import condition settings ([6f2e76fa](https://github.com/pluginsglpi/formcreator/commit/6f2e76fa))
* **section:** handle long label in design mode ([5398c4f1](https://github.com/pluginsglpi/formcreator/commit/5398c4f1))
* **section:** improve again UI ([faa53a28](https://github.com/pluginsglpi/formcreator/commit/faa53a28))
* **section:** third iteration of improvements ([4a68dbcb](https://github.com/pluginsglpi/formcreator/commit/4a68dbcb))
* **section:** various visual fixes ([e8dbaf53](https://github.com/pluginsglpi/formcreator/commit/e8dbaf53))
* **selectfield:** validity check different from radios field ([c59c16ae](https://github.com/pluginsglpi/formcreator/commit/c59c16ae))
* **targetchange:** may return null instead of string ([c5c05c30](https://github.com/pluginsglpi/formcreator/commit/c5c05c30))
* **targetticket:** date 'now' from a template ([47317884](https://github.com/pluginsglpi/formcreator/commit/47317884))
* **targetticket:** dropdowns for SLA/OLA ([7cd80230](https://github.com/pluginsglpi/formcreator/commit/7cd80230))
* **targetticket,targetchange:** adding tags was broken ([bdd0ac97](https://github.com/pluginsglpi/formcreator/commit/bdd0ac97))
* **targetticket,targetchange:** bad return value in setTargetEntity ([eff7ade4](https://github.com/pluginsglpi/formcreator/commit/eff7ade4))
* **targetticket,targetchange:** error when displaying tag from question settings ([b1968084](https://github.com/pluginsglpi/formcreator/commit/b1968084))
* **textareafield:** wrong translatable string returned ([add03993](https://github.com/pluginsglpi/formcreator/commit/add03993))
* **translation:** dialog to add a language to translate ([43c9cca8](https://github.com/pluginsglpi/formcreator/commit/43c9cca8))
* **wizard:** don't show tabs for KB item in service catalog ([514905d2](https://github.com/pluginsglpi/formcreator/commit/514905d2))
* **wizard:** reduce spacing between search bar and results ([1068d6a3](https://github.com/pluginsglpi/formcreator/commit/1068d6a3))
* **wizard:** show FAQ items only if have right ([8ae7fe2c](https://github.com/pluginsglpi/formcreator/commit/8ae7fe2c))
* **wizard:** use constant ([586a6bf9](https://github.com/pluginsglpi/formcreator/commit/586a6bf9))
* **wizard:** var declaration mandatory ([1d457ebb](https://github.com/pluginsglpi/formcreator/commit/1d457ebb))
* **wizard:** wrong menu highlighted when browsing FAQ ([9778b300](https://github.com/pluginsglpi/formcreator/commit/9778b300))


### Features

*  configurable visibiliy of search input ([f0e79c4b](https://github.com/pluginsglpi/formcreator/commit/f0e79c4b))
*  fields with arbitrary values are displayed translated ([935168af](https://github.com/pluginsglpi/formcreator/commit/935168af))
*  header on service catalog ([52a5e655](https://github.com/pluginsglpi/formcreator/commit/52a5e655))
*  translatable forms ([5b750a19](https://github.com/pluginsglpi/formcreator/commit/5b750a19))
*  translate answers for targets and notifications ([9b30339b](https://github.com/pluginsglpi/formcreator/commit/9b30339b))
* **condition:** add condition to show or hide the item ([c932ff71](https://github.com/pluginsglpi/formcreator/commit/c932ff71))
* **dropdownfield:** allow regex comparison ([cbbff5c3](https://github.com/pluginsglpi/formcreator/commit/cbbff5c3))
* **dropdownfield:** integrate splitcate (1.3.0) ([1ce6d49a](https://github.com/pluginsglpi/formcreator/commit/1ce6d49a))
* **dropdownfield,glpiselectfield:** allow customization of base entity ([e974fb1d](https://github.com/pluginsglpi/formcreator/commit/e974fb1d))
* **dropdownfield,glpiselectfield:** choose if subtree root is selectable ([33775ddc](https://github.com/pluginsglpi/formcreator/commit/33775ddc))
* **form:** enable / disable form with single click ([4ebbac6c](https://github.com/pluginsglpi/formcreator/commit/4ebbac6c))
* **form_language:** export / import ([1c741964](https://github.com/pluginsglpi/formcreator/commit/1c741964))
* **formanswer:** update anwers when validating ([571e9cec](https://github.com/pluginsglpi/formcreator/commit/571e9cec))
* **formanswer,issue:** convert datetimes to timestamps ([6c5525d5](https://github.com/pluginsglpi/formcreator/commit/6c5525d5))
* **glpiselect:** add tree settings to entity" ([fe2867d4](https://github.com/pluginsglpi/formcreator/commit/fe2867d4))
* **glpiselectfield:** databases plugin support ([b18d4ce7](https://github.com/pluginsglpi/formcreator/commit/b18d4ce7))
* **glpiselectfield:** hook to allow plugins to declare their itemtypes ([91926b29](https://github.com/pluginsglpi/formcreator/commit/91926b29))
* **glpiselectfield:** support for Generic Object plugin ([dbc5ae48](https://github.com/pluginsglpi/formcreator/commit/dbc5ae48))
* **install:** drop internal CLI instal script ([62f601f6](https://github.com/pluginsglpi/formcreator/commit/62f601f6))
* **install:** upgrade from 2.11.3 to 2.12 ([2e855157](https://github.com/pluginsglpi/formcreator/commit/2e855157))
* **issue:** change status conversion matrix ([cd16915b](https://github.com/pluginsglpi/formcreator/commit/cd16915b))
* **questionparameter:** make translatable ([8de2948c](https://github.com/pluginsglpi/formcreator/commit/8de2948c))
* **translation:** delete translation when translation is being deleted ([0ee9257f](https://github.com/pluginsglpi/formcreator/commit/0ee9257f))
* **translation:** limit massive actions ([03365766](https://github.com/pluginsglpi/formcreator/commit/03365766))
* **translation:** make translations UI faster to use ([b765943c](https://github.com/pluginsglpi/formcreator/commit/b765943c))
* **translation:** use modal ([89b62215](https://github.com/pluginsglpi/formcreator/commit/89b62215))



<a name="v2.11.4"></a>
## [2.11.4](https://github.com/pluginsglpi/formcreator/compare/v2.11.3..v2.11.4) (2021-05-27)


### Bug Fixes

* **dropdownfield,glpiselectfield:** entity recursivity regression ([a7e08a69](https://github.com/pluginsglpi/formcreator/commit/a7e08a69))
* **form:** compatibility with themes ([43ae9986](https://github.com/pluginsglpi/formcreator/commit/43ae9986))
* **ldapselectfield:** compatibility with PHP 8 ([ca09db9a](https://github.com/pluginsglpi/formcreator/commit/ca09db9a))
* **selectfield:** regex comparison ([b211bd39](https://github.com/pluginsglpi/formcreator/commit/b211bd39))
* **selectfield,cheeckboxesfield:** too much escaping ([ee54f8b4](https://github.com/pluginsglpi/formcreator/commit/ee54f8b4))


### Features

* **glpiselectfield:** hook to allow plugins to declare their itemtypes ([3274d3c0](https://github.com/pluginsglpi/formcreator/commit/3274d3c0))



<a name="v2.11.3"></a>
## [2.11.3](https://github.com/pluginsglpi/formcreator/compare/v2.11.2...v2.11.3) (2021-04-30)


### Bug Fixes

* **condition:** avoid HTML entities in dropdown ([451dfbd](https://github.com/pluginsglpi/formcreator/commit/451dfbdbac718d77f40b61fc06cc22de92c56286))
* **dropdownfield:** disable recursivity ([daa7fb0](https://github.com/pluginsglpi/formcreator/commit/daa7fb0213b4012e7651d424f2ec53307c74e0b8))
* **fields:** prevent empty expression evaluation ([f9fabb5](https://github.com/pluginsglpi/formcreator/commit/f9fabb5e2b25368a4649d0aa777e4b2457d23542)), closes [#2195](https://github.com/pluginsglpi/formcreator/issues/2195)
* **form:** bad sql expression for right check ([2e9b693](https://github.com/pluginsglpi/formcreator/commit/2e9b69317b3a01ae012832418d8574687c290e82))
* **form:** bad SQL to find validator groups ([42d0665](https://github.com/pluginsglpi/formcreator/commit/42d06654ccdb86c8e5d9481e961be4e81eb94b3a))
* **form:** compatibility with dark theme ([4c465f5](https://github.com/pluginsglpi/formcreator/commit/4c465f559ee84af0c034fc8081d6c69796e91c09))
* **form:** sort not applied on 1st display ([cfe5347](https://github.com/pluginsglpi/formcreator/commit/cfe534749ea5239d61bbeaa827d1f17213ad1048))
* **form:** version check on import ([849db8e](https://github.com/pluginsglpi/formcreator/commit/849db8e3f35fb9358b47e67b73294483e2e626c7))
* **formanswer:** load answers when validating a formanswer ([9ea1460](https://github.com/pluginsglpi/formcreator/commit/9ea14608d0e8df9f7138e3c8f41ad73a4db10e73))
* **formanswer:** no longer need to call showfields on display ([9cea337](https://github.com/pluginsglpi/formcreator/commit/9cea3377d2aec6835120689ea540e585aa9d99bd))
* **formanswers:** execute show conditions when displaying formanswer ([edd1247](https://github.com/pluginsglpi/formcreator/commit/edd1247cecc02a663cf831c85db9fd6c487e7d41))
* **issue:** enable qtip for formanswer ([72d89b0](https://github.com/pluginsglpi/formcreator/commit/72d89b01b73870b3cc3ba72bf73a07239bedc3a5))
* **issue:** include 1st level validator groups of current user ([f9addab](https://github.com/pluginsglpi/formcreator/commit/f9addabd3a355eaaae068b9ba19f384acb99d9e0))
* **issue:** php warning when running mailcollector ([c4bc865](https://github.com/pluginsglpi/formcreator/commit/c4bc8657e9685df44ea0827074f94b8196c47251))
* **issue:** restrict user dropdowns to current user in service catalog ([9891c89](https://github.com/pluginsglpi/formcreator/commit/9891c897c00796b3dda6bf67fa7acc091c9084f3))
* **issue:** update modificaitoin date when a followup is added to ticket ([4d5ed7f](https://github.com/pluginsglpi/formcreator/commit/4d5ed7fd2811e0c8dda2152bec0f11a6fe902e40))
* **ldapfield:** only last page of LDAP results rendered ([ea4ddfc](https://github.com/pluginsglpi/formcreator/commit/ea4ddfc59c829c397e4dae433c5e1dd6836713ac))
* **question:** make import resilient against missing parameters ([1594e6f](https://github.com/pluginsglpi/formcreator/commit/1594e6f27b85cc5c8c8f582c59f25b7b3d8b3108))
* **section:** ensure unique order for duplicate ([9db4229](https://github.com/pluginsglpi/formcreator/commit/9db4229a99eb401141185287c0e366e95e18d382))
* **targetticket:** date 'now' from a template ([f40ec4d](https://github.com/pluginsglpi/formcreator/commit/f40ec4d0a79dba18ce4a66d17b0db06e11b8f37b))
* apply translation on kb list ([fb0f1a6](https://github.com/pluginsglpi/formcreator/commit/fb0f1a6defa4bc307d75000ee7bb7cacd68f500e))
* prevent inconsistent timestamps in DB ([4b66eb8](https://github.com/pluginsglpi/formcreator/commit/4b66eb8adb2dedec74704d978b7c98cdc1e8e0e0))
* responsive UI ([33d8ee4](https://github.com/pluginsglpi/formcreator/commit/33d8ee41d55af25607fa723e5526212dd90904f2))
* show menu when width is low ([9ec53fa](https://github.com/pluginsglpi/formcreator/commit/9ec53fa181cd8d45c694a5dc91cacdcb33fa5301))
* **targetticket,targetchange:** bad return value in setTargetEntity ([e0ddd2d](https://github.com/pluginsglpi/formcreator/commit/e0ddd2d379d1e38c902518b6a060fa4458036887))
* **targetticket,targetchange:** error when displaying tag from question settings ([3c1ed6a](https://github.com/pluginsglpi/formcreator/commit/3c1ed6aea2c80be3c618f7e9d7a011911aaa2219))
* **wizard:** don't show tabs for KB item in service catalog ([ea3afe5](https://github.com/pluginsglpi/formcreator/commit/ea3afe573d4684d7ce3cabacc38dc69e71d73865))
* **wizard:** responsiveness for mobile devices ([e2508f3](https://github.com/pluginsglpi/formcreator/commit/e2508f37c1c9211e7986ab3fac657314a2b43600))
* **wizard:** show FAQ items only if have right ([8d2cdf1](https://github.com/pluginsglpi/formcreator/commit/8d2cdf196536f5894fba6bfa3247d21d345b8797))
* **wizard:** wrong menu highlighted when browsing FAQ ([cfe2ac0](https://github.com/pluginsglpi/formcreator/commit/cfe2ac05de9fe67c2af76e4d825ab7aab4052ae0))


### Features

* **form:** enable / disable form with single click ([e7bd38e](https://github.com/pluginsglpi/formcreator/commit/e7bd38ee8757502f5ca36380b2e6b6d4cfae5360))
* **glpiselectfield:** databases plugin support ([e245ba5](https://github.com/pluginsglpi/formcreator/commit/e245ba5938ca12e942c4c9e28a645b7ea61eb7e0))
* **issue:** change status conversion matrix ([60ba8bf](https://github.com/pluginsglpi/formcreator/commit/60ba8bf493fbcde6481120c9c90775b61767da6f))



<a name="v2.11.2"></a>
## [2.11.2](https://github.com/pluginsglpi/formcreator/compare/v2.11.1...v2.11.2) (2021-02-25)


### Bug Fixes

* **actorfield:** answer not displayed when shwoing saved data ([003ddda](https://github.com/pluginsglpi/formcreator/commit/003ddda1bbe1bd4bdf8d4d1455e8835b171c2a6a))
* **form:** loss of icon when editing a form ([d340f79](https://github.com/pluginsglpi/formcreator/commit/d340f79bac0288ab6b301770a3941c0644d77bbd))
* **issue:** update handling of url in emai: notifications ([b99b19b](https://github.com/pluginsglpi/formcreator/commit/b99b19b4e8ec56602652c6c67c53af451f365e8f))
* fatal error when not filling a date ([940bfee](https://github.com/pluginsglpi/formcreator/commit/940bfee2ecf7a22a4fc5033d81c90536e0baeac8))
* **ldapfield:** PHP warning when editing the question ([db452c7](https://github.com/pluginsglpi/formcreator/commit/db452c7be5b18d41623b549c32cb45c8d0935efd)), closes [#2116](https://github.com/pluginsglpi/formcreator/issues/2116)
* **question:** better error handling ([051184a](https://github.com/pluginsglpi/formcreator/commit/051184ae8fdbab344ffd9e5d4cd48bf92d4de3dd))
* **question:** vertical alignment on display for requester ([eda6842](https://github.com/pluginsglpi/formcreator/commit/eda684241831a4d7077cd5e91bacfbf1bb4fe88e))
* **section:** fail to import condition settings ([7f712bd](https://github.com/pluginsglpi/formcreator/commit/7f712bd94a77bf871e2123981924510d08fbc44d))
* **selectfield:** validity check different from radios field ([46ce9b3](https://github.com/pluginsglpi/formcreator/commit/46ce9b3c5c3e0bb76c559d53cfd9af504109c384))
* **wizard:** reduce spacing between search bar and results ([36870e5](https://github.com/pluginsglpi/formcreator/commit/36870e51bfb15b4388f5e4fca0b27873460a8c30))


### Features

* **dropdownfield:** allow regex comparison ([9fd8c1a](https://github.com/pluginsglpi/formcreator/commit/9fd8c1aaf3638011f9da9ddae420d93a96f3405d))



### Features

* **dropdownfield:** allow regex comparison ([9fd8c1aa](https://github.com/pluginsglpi/formcreator/commit/9fd8c1aa))

## [2.11.1](https://github.com/pluginsglpi/formcreator/compare/v2.11.0...v2.11.1) (2021-02-03)


### Bug Fixes

* **dropdownfield:** add security token for GLPI 9.5.3 ([44b5244](https://github.com/pluginsglpi/formcreator/commit/44b5244d39bca5f0aafbaff409c6d98af6f6974d))
* inappropriate css loading ([bbde619](https://github.com/pluginsglpi/formcreator/commit/bbde619559c94cf3147ef4b1dd9b16768c6b4e34))
* **dropdownfield:** fix SQL error when translations are enabled ([0272721](https://github.com/pluginsglpi/formcreator/commit/0272721d5a0e8e38f45a6c842a2b79c707bd18b0))
* **dropdownfield:** wrong IDOR token construct ([1689ecb](https://github.com/pluginsglpi/formcreator/commit/1689ecb4b73afe8c3dfea02b6641e5ab49c51ad3))
* **filefield:** mandatory fails when file is uploaded ([58c2dd1](https://github.com/pluginsglpi/formcreator/commit/58c2dd19fe8f1f0b3f619cb88774e5b4d382ae2e))
* **form:** error message when anonymous form submitted ([a9dd24b](https://github.com/pluginsglpi/formcreator/commit/a9dd24bbeddf712978be0cfa3b63efc93f29d3e1))
* **install:** broken upgrade of target_actors ([40db225](https://github.com/pluginsglpi/formcreator/commit/40db225929f9763cdd2cf8bb2399972d49418fc2))
* **notificationtargetformanswer:** tags not fully rendered ([9c2620f](https://github.com/pluginsglpi/formcreator/commit/9c2620fc555859268be55e8c05c23aed091829ca))
* **question:** prevent bad request ([ac9f693](https://github.com/pluginsglpi/formcreator/commit/ac9f693a14add919412cc7a76c11c25348fa788d))
* **targetticket:** dropdowns for SLA/OLA ([9e5bd85](https://github.com/pluginsglpi/formcreator/commit/9e5bd8563381334edfad02fd265cab1b24ac2a88))



# [2.11.0](https://github.com/pluginsglpi/formcreator/compare/v2.11.0-beta.1...v2.11.0) (2021-01-26)


### Bug Fixes

* **actorsfield:** missed function rename ([8d26857](https://github.com/pluginsglpi/formcreator/commit/8d2685720b6750b1492cad1857633b1c1539a1bc))
* **condition:** loss of condition on submit button ([bc69358](https://github.com/pluginsglpi/formcreator/commit/bc69358ef6e1db0dfa95fce40b30480fa6a0de8f))
* **entityconfig:** bad constant value ([114f6d1](https://github.com/pluginsglpi/formcreator/commit/114f6d14564fe4d82b1b59d55adb85466369499b))
* **form:** add label to validator inputs ([73295e3](https://github.com/pluginsglpi/formcreator/commit/73295e311821c0dffd624475d635da81406481e7))
* **form:** add spacing between questions ([2946d74](https://github.com/pluginsglpi/formcreator/commit/2946d745acd6db82a302ec0197f99583d90b8bb9))
* **form:** reimplement submit button conditions ([9211926](https://github.com/pluginsglpi/formcreator/commit/92119263008cb253331f5d1fe6a404bb7d5525fb))
* **issue:** loss of issue on automatic action ([436035c](https://github.com/pluginsglpi/formcreator/commit/436035c893077764f04c084093107dccc4dd9d28))
* **question:** handle long label display ([7705e58](https://github.com/pluginsglpi/formcreator/commit/7705e589bdddf378954267578f6ed561843caa83))
* **question:** reduce spacing in edition tools ([bdd5240](https://github.com/pluginsglpi/formcreator/commit/bdd524090e944c3bbe61de738792275b0718222f))
* **section:** handle long label in design mode ([b86de8d](https://github.com/pluginsglpi/formcreator/commit/b86de8deab8f525c55f7ec2b8c1ca3ba1cb68778))
* **section:** improve again UI ([bd2dc96](https://github.com/pluginsglpi/formcreator/commit/bd2dc9672ccb8a71035a29937bb898811f734257))
* **section:** third iteration of improvements ([8716108](https://github.com/pluginsglpi/formcreator/commit/8716108157ed618251b379bc1cb7dbf2db547a03))
* path detection to load JS ([c2fa979](https://github.com/pluginsglpi/formcreator/commit/c2fa979ea110f4b1e931eb97d45de33cedc458e9))
* several field have useless slash escaping ([e61d6ff](https://github.com/pluginsglpi/formcreator/commit/e61d6fffd683205db9a517927ec8927c209c99dc))
* **section:** various visual fixes ([c9f9e3b](https://github.com/pluginsglpi/formcreator/commit/c9f9e3b64b17b5f144115b7d76848c32b428fa0e))
* **wizard:** use constant ([bb326eb](https://github.com/pluginsglpi/formcreator/commit/bb326ebcad59793ef53f978a9735b865226d2003))
* **wizard:** var declaration mandatory ([7645f63](https://github.com/pluginsglpi/formcreator/commit/7645f6338de184d4814b784dd628b799a15a2b9e))


### Features

* **condition:** add condition to show or hide the item ([2681b9c](https://github.com/pluginsglpi/formcreator/commit/2681b9c366e3e50ad7691c7bce0bb08ce329f45f))
* **dropdownfield:** integrate splitcate (1.3.0) ([784eae0](https://github.com/pluginsglpi/formcreator/commit/784eae02d72e7965a2dc7b064084f7763e1f2bd0))



<a name="2.11.0-beta.1"></a>
## [2.11.0-beta.1](https://github.com/pluginsglpi/formcreator/compare/v2.10.4..v2.11.0-beta.1) (2021-01-06)


### Bug Fixes

*  bad path for marketplace ([e8b38f0c](https://github.com/pluginsglpi/formcreator/commit/e8b38f0c))
*  bad path for marketplace, load tinymce in issue ([9b75f05f](https://github.com/pluginsglpi/formcreator/commit/9b75f05f))
*  class should not be accessed directly ([aeb59ebf](https://github.com/pluginsglpi/formcreator/commit/aeb59ebf))
*  class should not be accessed directly ([480a8fa7](https://github.com/pluginsglpi/formcreator/commit/480a8fa7))
*  fix ajax calls ([02013495](https://github.com/pluginsglpi/formcreator/commit/02013495))
*  keep backward compatibility with GLPI 9.4 ([834c73a2](https://github.com/pluginsglpi/formcreator/commit/834c73a2))
*  keep the user in the service catalog ([7f9451a8](https://github.com/pluginsglpi/formcreator/commit/7f9451a8))
*  marketplace compatibility ([08dc1a18](https://github.com/pluginsglpi/formcreator/commit/08dc1a18))
*  marketplace compatibility (again) ([ef5f0803](https://github.com/pluginsglpi/formcreator/commit/ef5f0803))
*  missing declaration for variable ([b765930d](https://github.com/pluginsglpi/formcreator/commit/b765930d))
*  missing declaration for variable ([071d39d0](https://github.com/pluginsglpi/formcreator/commit/071d39d0))
*  modal positionning ([c94df7c7](https://github.com/pluginsglpi/formcreator/commit/c94df7c7))
*  other bad redirections ([3830463b](https://github.com/pluginsglpi/formcreator/commit/3830463b))
*  path for marketplace compatibility ([1d8bcf60](https://github.com/pluginsglpi/formcreator/commit/1d8bcf60))
*  remove code left for debug ([81bc7b6b](https://github.com/pluginsglpi/formcreator/commit/81bc7b6b))
*  several missing cap in strings ([b8a37b6e](https://github.com/pluginsglpi/formcreator/commit/b8a37b6e))
*  syntax error in JS file ([84a3707e](https://github.com/pluginsglpi/formcreator/commit/84a3707e))
*  update hooks ([1ffb1ca1](https://github.com/pluginsglpi/formcreator/commit/1ffb1ca1))
*  useless escaping ([dca026d2](https://github.com/pluginsglpi/formcreator/commit/dca026d2))
*  various fix on fields ([a01d603c](https://github.com/pluginsglpi/formcreator/commit/a01d603c))
*  various fixes on duplicate / import ([0d7c4dd2](https://github.com/pluginsglpi/formcreator/commit/0d7c4dd2))
* **actorsfield:** compatibility with GLPI 9.5.3 ([7e3c6e74](https://github.com/pluginsglpi/formcreator/commit/7e3c6e74))
* **build:** invert order of versions in changelog ([9a8782e3](https://github.com/pluginsglpi/formcreator/commit/9a8782e3))
* **category:** entity restriction not applied ([fb2a1957](https://github.com/pluginsglpi/formcreator/commit/fb2a1957))
* **category:** use short name ([ad3d16d1](https://github.com/pluginsglpi/formcreator/commit/ad3d16d1))
* **central:** list of forms displayed twice ([056b419d](https://github.com/pluginsglpi/formcreator/commit/056b419d))
* **checkbowesfield:** migrate data to JSON ([2b431253](https://github.com/pluginsglpi/formcreator/commit/2b431253))
* **checkboxfield:** avoid unicode escaping ([3276b9a7](https://github.com/pluginsglpi/formcreator/commit/3276b9a7))
* **common:** better search for ticket validation ([e7bbdccb](https://github.com/pluginsglpi/formcreator/commit/e7bbdccb))
* **common:** getMax fails with PHP 7.4 ([51ebc459](https://github.com/pluginsglpi/formcreator/commit/51ebc459))
* **conditin:** export broken ([62b1d692](https://github.com/pluginsglpi/formcreator/commit/62b1d692))
* **condition:** broken UI when adding a conditionnable with conditions ([006b2860](https://github.com/pluginsglpi/formcreator/commit/006b2860))
* **condition:** catch comparison exception ([4bdaab4b](https://github.com/pluginsglpi/formcreator/commit/4bdaab4b))
* **condition:** export broken ([788d1f17](https://github.com/pluginsglpi/formcreator/commit/788d1f17))
* **condition:** inability to add a rows to conditions ([40129a69](https://github.com/pluginsglpi/formcreator/commit/40129a69))
* **condition:** missing FK when editing conditions ([c96f2f53](https://github.com/pluginsglpi/formcreator/commit/c96f2f53))
* **condition:** permit update of conditionnable items without specifying conditions agaiin ([b95bdae9](https://github.com/pluginsglpi/formcreator/commit/b95bdae9))
* **condition:** php warning if a wuestion does not exists ([9e6ae32b](https://github.com/pluginsglpi/formcreator/commit/9e6ae32b))
* **condition:** remove conditions when disabled ([b2655e54](https://github.com/pluginsglpi/formcreator/commit/b2655e54))
* **condition:** use of constants ([234b4e30](https://github.com/pluginsglpi/formcreator/commit/234b4e30))
* **confition:** hide garbage conditions ([4491e7a2](https://github.com/pluginsglpi/formcreator/commit/4491e7a2))
* **datefield:** undefined var when creating question ([b11bc4e5](https://github.com/pluginsglpi/formcreator/commit/b11bc4e5))
* **datefield,datetimefield:** avoid PHP warnings ([90f82596](https://github.com/pluginsglpi/formcreator/commit/90f82596))
* **datefield,datetimefield:** not rendered fields ([fe70b426](https://github.com/pluginsglpi/formcreator/commit/fe70b426))
* **description:** simple text may render HTML tags ([09c0b4bd](https://github.com/pluginsglpi/formcreator/commit/09c0b4bd))
* **dropdownfield:** SQL error : ambiguous column id ([2da15830](https://github.com/pluginsglpi/formcreator/commit/2da15830))
* **dropdownfield:** SQL error to find curent user's groups ([985a8b28](https://github.com/pluginsglpi/formcreator/commit/985a8b28))
* **dropdownfield:** bad entity restriction ([3a664f80](https://github.com/pluginsglpi/formcreator/commit/3a664f80))
* **dropdownfield:** compatibility with Document itemtype ([bc968b39](https://github.com/pluginsglpi/formcreator/commit/bc968b39))
* **dropdownfield:** compatibility with Tags plugin ([7752758a](https://github.com/pluginsglpi/formcreator/commit/7752758a))
* **dropdownfield:** empty dropdown ([2b8b7f12](https://github.com/pluginsglpi/formcreator/commit/2b8b7f12))
* **dropdownfield:** entity restriction relative to the form, not the user ([dc7dda94](https://github.com/pluginsglpi/formcreator/commit/dc7dda94)), closes [#2047](https://github.com/pluginsglpi/formcreator/issues/2047)
* * **dropdownfield:** label for change categories and request categories ([b534ad37](https://github.com/pluginsglpi/formcreator/commit/b534ad37))
* **dropdownfield:** not rendered ([a2633082](https://github.com/pluginsglpi/formcreator/commit/a2633082))
* **dropdownfield:** update classname ([67aec202](https://github.com/pluginsglpi/formcreator/commit/67aec202))
* **dropdownfield,glpiobjectfield:** sub type not dosplayed ([b4808b22](https://github.com/pluginsglpi/formcreator/commit/b4808b22))
* **dropdownfield,glpiselectfield:** empty value parameter not honored ([187daa50](https://github.com/pluginsglpi/formcreator/commit/187daa50))
* **dropdownfields:** handle empty value for entities dropdown ([3a5dab7c](https://github.com/pluginsglpi/formcreator/commit/3a5dab7c))
* **emailfield:** disable inherited parameters ([2ee1bd5a](https://github.com/pluginsglpi/formcreator/commit/2ee1bd5a))
* **exportable:** implement missing method ([4e867270](https://github.com/pluginsglpi/formcreator/commit/4e867270))
* **exportable:** implement missing method ([393dfea1](https://github.com/pluginsglpi/formcreator/commit/393dfea1))
* **exportable:** implement missing method ([6897cb17](https://github.com/pluginsglpi/formcreator/commit/6897cb17))
* **exportable:** unsolved merge conflict ([a868aff7](https://github.com/pluginsglpi/formcreator/commit/a868aff7))
* **field:** normalize class name ([a27f82eb](https://github.com/pluginsglpi/formcreator/commit/a27f82eb))
* **fieldinterface:** method signature mismatch ([076937b5](https://github.com/pluginsglpi/formcreator/commit/076937b5))
* **filefield:** broken mandatory check ([f449acaa](https://github.com/pluginsglpi/formcreator/commit/f449acaa))
* **filefield:** documentt upload with GLPI 9.5 ([2b48e824](https://github.com/pluginsglpi/formcreator/commit/2b48e824))
* **filefield:** php warning when editing the question ([74800699](https://github.com/pluginsglpi/formcreator/commit/74800699))
* **filefield:** show download links when field is read only ([3cd77dd2](https://github.com/pluginsglpi/formcreator/commit/3cd77dd2))
* **form:** add a first section requires refresh to be visible ([eef72ab5](https://github.com/pluginsglpi/formcreator/commit/eef72ab5))
* **form:** avoid useless HTTP requests and php warning ([7ab6fbcd](https://github.com/pluginsglpi/formcreator/commit/7ab6fbcd))
* **form:** bad call to count validators ([39822d0a](https://github.com/pluginsglpi/formcreator/commit/39822d0a))
* **form:** bad path to css ([a5fcf3c7](https://github.com/pluginsglpi/formcreator/commit/a5fcf3c7))
* **form:** bad session var type when using anonymous form ([9a9a07f4](https://github.com/pluginsglpi/formcreator/commit/9a9a07f4))
* **form:** bad sharing URL ([0e8ca72c](https://github.com/pluginsglpi/formcreator/commit/0e8ca72c))
* **form:** broken link to forms ([df0ec215](https://github.com/pluginsglpi/formcreator/commit/df0ec215))
* **form:** css inconsistencies ([e9aa87af](https://github.com/pluginsglpi/formcreator/commit/e9aa87af))
* **form:** doubling starcauses SQL error ([5f26fccb](https://github.com/pluginsglpi/formcreator/commit/5f26fccb))
* **form:** duplication exception (#1818) ([d057a55c](https://github.com/pluginsglpi/formcreator/commit/d057a55c))
* **form:** entity restrict problem ([88da1e7c](https://github.com/pluginsglpi/formcreator/commit/88da1e7c))
* **form:** error in displayed form URL ([4c4d97e2](https://github.com/pluginsglpi/formcreator/commit/4c4d97e2))
* **form:** forbid clone massive action in GLPI 9.5 ([4c418024](https://github.com/pluginsglpi/formcreator/commit/4c418024))
* **form:** hidden questions still consume 10 pixels height ([e063d0b4](https://github.com/pluginsglpi/formcreator/commit/e063d0b4))
* **form:** incorrect font on add target link ([e9372051](https://github.com/pluginsglpi/formcreator/commit/e9372051))
* **form:** list of forms on homepage ([841d459b](https://github.com/pluginsglpi/formcreator/commit/841d459b))
* **form:** make tab name same as title of its content ([b60c1249](https://github.com/pluginsglpi/formcreator/commit/b60c1249))
* **form:** missinb closing tag ([636d674a](https://github.com/pluginsglpi/formcreator/commit/636d674a))
* **form:** multiple selection of validators ([29d9fe86](https://github.com/pluginsglpi/formcreator/commit/29d9fe86))
* **form:** my last form (validator) were not sorted ([9ed9e871](https://github.com/pluginsglpi/formcreator/commit/9ed9e871))
* **form:** not well restored save answers after invalid submission ([9233628d](https://github.com/pluginsglpi/formcreator/commit/9233628d))
* **form:** prevent SQL errors, remove natural language search ([6c733a5b](https://github.com/pluginsglpi/formcreator/commit/6c733a5b))
* **form:** purge message if answers exist ([38f3094c](https://github.com/pluginsglpi/formcreator/commit/38f3094c))
* **form:** requesttype column no longer used ([f8630baa](https://github.com/pluginsglpi/formcreator/commit/f8630baa))
* **form:** restore padding ([2eb8c33e](https://github.com/pluginsglpi/formcreator/commit/2eb8c33e))
* **form:** show error if failure in import of a sub item ([387f1f23](https://github.com/pluginsglpi/formcreator/commit/387f1f23))
* **form:** show session messages on anonymous forms ([dada741f](https://github.com/pluginsglpi/formcreator/commit/dada741f))
* **form:** single quotes around a table name ([3668fb66](https://github.com/pluginsglpi/formcreator/commit/3668fb66)), closes [#1606](https://github.com/pluginsglpi/formcreator/issues/1606)
* **form:** typo in var name ([47ed6179](https://github.com/pluginsglpi/formcreator/commit/47ed6179))
* **form:** unused class usage ([f2a4b2c1](https://github.com/pluginsglpi/formcreator/commit/f2a4b2c1))
* **form:** unused variable ([1e8f417c](https://github.com/pluginsglpi/formcreator/commit/1e8f417c))
* **form:** users don't know that the lists are limited ([4f7cf5dc](https://github.com/pluginsglpi/formcreator/commit/4f7cf5dc))
* **form:** validators must show when more than 2 available ([3ee813f1](https://github.com/pluginsglpi/formcreator/commit/3ee813f1))
* **form:** version comparison for export / import ([0ad090d1](https://github.com/pluginsglpi/formcreator/commit/0ad090d1))
* **form,question:** duplicate fail on form without section ([818eab0a](https://github.com/pluginsglpi/formcreator/commit/818eab0a))
* **form_profile:** HTML form name mismatch ([93d0ae5b](https://github.com/pluginsglpi/formcreator/commit/93d0ae5b))
* **form_profile:** broken envelope icon ([bc0c9f9e](https://github.com/pluginsglpi/formcreator/commit/bc0c9f9e))
* **form_profile:** not rendered selection of profiles ([e70edcff](https://github.com/pluginsglpi/formcreator/commit/e70edcff))
* **formanswer:** display of status shall show a label ([250db60d](https://github.com/pluginsglpi/formcreator/commit/250db60d))
* **formanswer:** do not render section title if invisible ([1c696321](https://github.com/pluginsglpi/formcreator/commit/1c696321))
* **formanswer:** malformed SQL to delete answers ([e26a4eb6](https://github.com/pluginsglpi/formcreator/commit/e26a4eb6))
* **formanswer:** missing validation checks when user updates a refused form ([5f5cbbcd](https://github.com/pluginsglpi/formcreator/commit/5f5cbbcd))
* **formanswer:** possible loop bug ([25d1abfe](https://github.com/pluginsglpi/formcreator/commit/25d1abfe))
* **formanswer:** restore validation status ([cc8d981e](https://github.com/pluginsglpi/formcreator/commit/cc8d981e))
* **formanswer:** save update on refused form ([403d98ff](https://github.com/pluginsglpi/formcreator/commit/403d98ff))
* **formanswer:** use of  in static method ([fc0038f4](https://github.com/pluginsglpi/formcreator/commit/fc0038f4))
* **formanswer:** word wrap on display long lines with long words ([cbca8489](https://github.com/pluginsglpi/formcreator/commit/cbca8489))
* **fotrm:** some icons may be not displayed ([e94adaa1](https://github.com/pluginsglpi/formcreator/commit/e94adaa1))
* **gitignore:** change data folder config ([07289ed2](https://github.com/pluginsglpi/formcreator/commit/07289ed2))
* **glpiobjectfield:** show ID of items ([b8b8479f](https://github.com/pluginsglpi/formcreator/commit/b8b8479f))
* **glpiselect:** empty value is not empty string ([af5007d7](https://github.com/pluginsglpi/formcreator/commit/af5007d7))
* **glpiselectfield:** appliance plugin name is plural ([304ffafd](https://github.com/pluginsglpi/formcreator/commit/304ffafd))
* **glpiselectfield:** missing caps un classnames ([11e3e939](https://github.com/pluginsglpi/formcreator/commit/11e3e939))
* **glpiselectfield:** prevent use of the field with non existing itemtype ([4271a503](https://github.com/pluginsglpi/formcreator/commit/4271a503))
* **glpiselectfield:** restore palceholder DOM elements ([b64fc10c](https://github.com/pluginsglpi/formcreator/commit/b64fc10c))
* **glpiselectfield:** restrict to items associatable to tickets > > restriction on per item basis, like software ([da0aa810](https://github.com/pluginsglpi/formcreator/commit/da0aa810))
* **import:** cannot factorize deleteObsoleteItems ([1322fff1](https://github.com/pluginsglpi/formcreator/commit/1322fff1))
* **import:** don't handle immediately conditions on import ([f3c8c407](https://github.com/pluginsglpi/formcreator/commit/f3c8c407))
* **import:** more explicit error message ([17a7f5ef](https://github.com/pluginsglpi/formcreator/commit/17a7f5ef))
* **instal:** re-add 2.11 version for upgrade process ([dd483367](https://github.com/pluginsglpi/formcreator/commit/dd483367))
* **install:** malformed sql on upgrade ([3b1e8970](https://github.com/pluginsglpi/formcreator/commit/3b1e8970))
* **install:** missing column reorder on upgrade ([faaecf49](https://github.com/pluginsglpi/formcreator/commit/faaecf49))
* **install:** quote escaping when uprgading to 2.9.0 ([342fbb02](https://github.com/pluginsglpi/formcreator/commit/342fbb02))
* **install:** sql error in upgrade ([9c2bac0a](https://github.com/pluginsglpi/formcreator/commit/9c2bac0a))
* **install:** typo in SQL request ([c6cadb00](https://github.com/pluginsglpi/formcreator/commit/c6cadb00))
* **install:** upgrade may fail on single quote ([0c4959c3](https://github.com/pluginsglpi/formcreator/commit/0c4959c3))
* **install:** upgrade to 2.5.0 fails on categories ([7d23b359](https://github.com/pluginsglpi/formcreator/commit/7d23b359))
* **issue:** SQL error ([5578b87e](https://github.com/pluginsglpi/formcreator/commit/5578b87e))
* **issue:** adjust ticket status n automatic action ([8445363b](https://github.com/pluginsglpi/formcreator/commit/8445363b))
* **issue:** cancel ticket with simplified service catalog ([e718bac2](https://github.com/pluginsglpi/formcreator/commit/e718bac2))
* **issue:** distinguish requester and author ([b4af17f7](https://github.com/pluginsglpi/formcreator/commit/b4af17f7))
* **issue:** enhance error message when canceling an issue ([adabe361](https://github.com/pluginsglpi/formcreator/commit/adabe361))
* **issue:** fix navigation through items of a list ([63be3208](https://github.com/pluginsglpi/formcreator/commit/63be3208))
* **issue:** handle redirection to satisfaction survey from email ([fa17f523](https://github.com/pluginsglpi/formcreator/commit/fa17f523))
* **issue:** handle survey expiration ([0662f80f](https://github.com/pluginsglpi/formcreator/commit/0662f80f))
* **issue:** localization problem impacting picture ([2b33cd3e](https://github.com/pluginsglpi/formcreator/commit/2b33cd3e))
* **issue:** might have unwanted results ([b7cad38c](https://github.com/pluginsglpi/formcreator/commit/b7cad38c))
* **issue:** possible SQL error ([e567b53d](https://github.com/pluginsglpi/formcreator/commit/e567b53d))
* **issue:** properly set validation data on ticket restore ([050f5388](https://github.com/pluginsglpi/formcreator/commit/050f5388))
* **issue:** repopulate table on upgrade ([93e8a47a](https://github.com/pluginsglpi/formcreator/commit/93e8a47a))
* **issue:** restrictu requester expression in service catalog ([b576a501](https://github.com/pluginsglpi/formcreator/commit/b576a501))
* **issue:** self service is able to reopen a closed issue / ticket ([f90eafe1](https://github.com/pluginsglpi/formcreator/commit/f90eafe1))
* **issue:** show satisfaction for tickets on service catalog ([c8ce5e7e](https://github.com/pluginsglpi/formcreator/commit/c8ce5e7e))
* **issue:** simplify counters criterias ([7e7bf600](https://github.com/pluginsglpi/formcreator/commit/7e7bf600))
* **issue:** status conversion for ticket ([f7bd6c3b](https://github.com/pluginsglpi/formcreator/commit/f7bd6c3b))
* **issue:** support of ticket waiting for approval ([b5f0212b](https://github.com/pluginsglpi/formcreator/commit/b5f0212b))
* **issue:** syncissues drops most requesters ([c68628c1](https://github.com/pluginsglpi/formcreator/commit/c68628c1))
* **issue:** take ticket valdiation status and user into account ([937a4a5e](https://github.com/pluginsglpi/formcreator/commit/937a4a5e))
* **issue:** take ticket valdiation status into account ([5d9cb079](https://github.com/pluginsglpi/formcreator/commit/5d9cb079))
* **issue:** ticket status when approval request is used ([486e0331](https://github.com/pluginsglpi/formcreator/commit/486e0331))
* **issue:** update issue status on ticket validation update ([5db55f6b](https://github.com/pluginsglpi/formcreator/commit/5db55f6b))
* **issue:** update issue status on ticket validation update ([1a4986e6](https://github.com/pluginsglpi/formcreator/commit/1a4986e6))
* **issue:** validated ticket status ([912c008a](https://github.com/pluginsglpi/formcreator/commit/912c008a))
* **issue:** warning with GLPI 9.5 ([9ad7a3d3](https://github.com/pluginsglpi/formcreator/commit/9ad7a3d3))
* **linker:** may add several times a postponed item ([b37d784f](https://github.com/pluginsglpi/formcreator/commit/b37d784f))
* **linker:** prevent reuse of variable ([faf8a9c4](https://github.com/pluginsglpi/formcreator/commit/faf8a9c4))
* **locales:** bad string for french ([bad3c7dd](https://github.com/pluginsglpi/formcreator/commit/bad3c7dd))
* **locales:** en_US translated into an other language ([07806702](https://github.com/pluginsglpi/formcreator/commit/07806702))
* **locales:** english US contained korean translation ([cd3e91a1](https://github.com/pluginsglpi/formcreator/commit/cd3e91a1))
* **multiselectfield:** visible JS ([57ab7ded](https://github.com/pluginsglpi/formcreator/commit/57ab7ded))
* **question:** SQL errors when deleting a question ([6edaed6a](https://github.com/pluginsglpi/formcreator/commit/6edaed6a))
* **question:** bad pasing of user input ([5963666f](https://github.com/pluginsglpi/formcreator/commit/5963666f))
* **question:** broken JSON when duplicating questions ([7707875d](https://github.com/pluginsglpi/formcreator/commit/7707875d))
* **question:** conditions  count ([dab9280d](https://github.com/pluginsglpi/formcreator/commit/dab9280d))
* **question:** duplication may make several items having same position ([f332fc65](https://github.com/pluginsglpi/formcreator/commit/f332fc65))
* **question:** duplication of condition ([f2d04325](https://github.com/pluginsglpi/formcreator/commit/f2d04325))
* **question:** extra slash when requesting questions for the form ([b3a250f6](https://github.com/pluginsglpi/formcreator/commit/b3a250f6))
* **question:** javascript code was displayed ([4766010f](https://github.com/pluginsglpi/formcreator/commit/4766010f))
* **question:** remove unused var ([601f4387](https://github.com/pluginsglpi/formcreator/commit/601f4387))
* **question:** show / hode specific properties ([9dc71ca8](https://github.com/pluginsglpi/formcreator/commit/9dc71ca8))
* **question:** sql quote escaping ([b2443136](https://github.com/pluginsglpi/formcreator/commit/b2443136))
* **question:** sql quote escaping ([68fa87f1](https://github.com/pluginsglpi/formcreator/commit/68fa87f1))
* **question:** update parameters broken ([6b46b796](https://github.com/pluginsglpi/formcreator/commit/6b46b796))
* **question,section:** improve condition consistency checks ([a03e3d64](https://github.com/pluginsglpi/formcreator/commit/a03e3d64))
* **question,section:** more resilient order change handling ([d0a4c336](https://github.com/pluginsglpi/formcreator/commit/d0a4c336))
* **question,section:** style of conditions count ([655308d0](https://github.com/pluginsglpi/formcreator/commit/655308d0))
* **questionparameter:** bad data for add item ([fbd772b8](https://github.com/pluginsglpi/formcreator/commit/fbd772b8))
* **radiosfield:** bad rendering of buttons when printing ([b600e760](https://github.com/pluginsglpi/formcreator/commit/b600e760))
* **radiosfield:** better handling of long labels ([e39016a8](https://github.com/pluginsglpi/formcreator/commit/e39016a8))
* **radiosfield:** broken CSS ([68e7e0e3](https://github.com/pluginsglpi/formcreator/commit/68e7e0e3))
* **radiosfield:** prevent css to apply on plain html radios ([e7eb2bd6](https://github.com/pluginsglpi/formcreator/commit/e7eb2bd6))
* **requesttypefield:** typo braking the field edition ([f65e91a2](https://github.com/pluginsglpi/formcreator/commit/f65e91a2))
* **requesttypefield:** update interface ([c7a2537b](https://github.com/pluginsglpi/formcreator/commit/c7a2537b))
* **robo:** line formatting for changelog ([e57b33f0](https://github.com/pluginsglpi/formcreator/commit/e57b33f0))
* **robo:** prevent exception when computong log with commit without body ([876a9d0a](https://github.com/pluginsglpi/formcreator/commit/876a9d0a))
* **section:** conditions not duplicated ([e551e640](https://github.com/pluginsglpi/formcreator/commit/e551e640))
* **section:** duplication of conditions: bad itemtype ([dc1e972d](https://github.com/pluginsglpi/formcreator/commit/dc1e972d))
* **section:** no validation button when adding section ([a129a003](https://github.com/pluginsglpi/formcreator/commit/a129a003))
* **section:** order of sections not respected on import ([0e27cb3e](https://github.com/pluginsglpi/formcreator/commit/0e27cb3e))
* **section:** reorder if sections exist ([a1bde8b1](https://github.com/pluginsglpi/formcreator/commit/a1bde8b1))
* **section:** typo, update name of section may update name of an inner question ([d6cb0db9](https://github.com/pluginsglpi/formcreator/commit/d6cb0db9))
* **section:** unsupported use of QueryExpression ([ad22dfef](https://github.com/pluginsglpi/formcreator/commit/ad22dfef))
* **section,question:** actions on questions and sections misalignment ([e85c1c35](https://github.com/pluginsglpi/formcreator/commit/e85c1c35))
* **section,question:** don't force ordering on import ([fedf621d](https://github.com/pluginsglpi/formcreator/commit/fedf621d))
* **section,question:** move the page to the modal window ([0e78cf61](https://github.com/pluginsglpi/formcreator/commit/0e78cf61))
* **selectfield:** comparison with empty string ([89fb9dd3](https://github.com/pluginsglpi/formcreator/commit/89fb9dd3))
* **tagfield:** namespacing for HTML tools ([6fdeb60c](https://github.com/pluginsglpi/formcreator/commit/6fdeb60c))
* **target:** load all tag questions ([000003cc](https://github.com/pluginsglpi/formcreator/commit/000003cc))
* **target_actor:** bad label ([cbc73165](https://github.com/pluginsglpi/formcreator/commit/cbc73165))
* **target_actor:** better name for actor ([287f912e](https://github.com/pluginsglpi/formcreator/commit/287f912e))
* **target_actor:** duplication exception ([1b0523e4](https://github.com/pluginsglpi/formcreator/commit/1b0523e4))
* **target_actor:** duplication exception ([f7b27270](https://github.com/pluginsglpi/formcreator/commit/f7b27270))
* **target_actor:** tell the ID of missing actor ([7b4f5e3d](https://github.com/pluginsglpi/formcreator/commit/7b4f5e3d))
* **target_actor:** unable to add a specific person ([e6cbff3a](https://github.com/pluginsglpi/formcreator/commit/e6cbff3a))
* **target_actor:** update import / export ([4d33896e](https://github.com/pluginsglpi/formcreator/commit/4d33896e))
* **targetchange:** bad label ([1ecaeb6a](https://github.com/pluginsglpi/formcreator/commit/1ecaeb6a))
* **targetchange:** bad url when delete an actor ([265c8baa](https://github.com/pluginsglpi/formcreator/commit/265c8baa)), closes [#1607](https://github.com/pluginsglpi/formcreator/issues/1607)
* **targetchange:** do not geenrate HTML for simple text fields ([7333df85](https://github.com/pluginsglpi/formcreator/commit/7333df85))
* **targetchange:** prevent SQL escaping bug ([e7da8de9](https://github.com/pluginsglpi/formcreator/commit/e7da8de9))
* **targetchange:** value of checklist not rendered ([9c3cb6f4](https://github.com/pluginsglpi/formcreator/commit/9c3cb6f4))
* **targettichet,targetchange:** question tags not updated on duplication ([63927c78](https://github.com/pluginsglpi/formcreator/commit/63927c78))
* **targetticket:** change default for ticket type rule ([70656754](https://github.com/pluginsglpi/formcreator/commit/70656754))
* **targetticket:** determine requester when answer is valdiated ([14259f37](https://github.com/pluginsglpi/formcreator/commit/14259f37)), closes [#50](https://github.com/pluginsglpi/formcreator/issues/50)
* **targetticket:** empty dropdown for target ticket linking" ([8dae4e3e](https://github.com/pluginsglpi/formcreator/commit/8dae4e3e))
* **targetticket:** get category from template ([902cf130](https://github.com/pluginsglpi/formcreator/commit/902cf130))
* **targetticket:** remove fixed width ([e215154d](https://github.com/pluginsglpi/formcreator/commit/e215154d))
* **targetticket:** request type may be unset ([5caf1b01](https://github.com/pluginsglpi/formcreator/commit/5caf1b01))
* **targetticket:** undefined type after creation ([2e09c24c](https://github.com/pluginsglpi/formcreator/commit/2e09c24c))
* **targetticket,targetchange:** avoid possible PHP warning ([248d5c5d](https://github.com/pluginsglpi/formcreator/commit/248d5c5d))
* **targetticket,targetchange:** better title for condition edition ([b4b53407](https://github.com/pluginsglpi/formcreator/commit/b4b53407))
* **targetticket,targetchange:** covnert question ID into UUID for export of target settings ([5fa4707b](https://github.com/pluginsglpi/formcreator/commit/5fa4707b))
* **targetticket,targetchange:** duplicate actors run twice ([75ab2dd5](https://github.com/pluginsglpi/formcreator/commit/75ab2dd5))
* **targetticket,targetchange:** dynamic entity computation may fail ([2ee2616e](https://github.com/pluginsglpi/formcreator/commit/2ee2616e))
* **targetticket,targetchange:** escape text fields of targets ([e80a1cac](https://github.com/pluginsglpi/formcreator/commit/e80a1cac))
* **targetticket,targetchange:** export / import entity from question ([6ebbbacb](https://github.com/pluginsglpi/formcreator/commit/6ebbbacb))
* **targetticket,targetchange:** export / import of conditions ([65ff5a33](https://github.com/pluginsglpi/formcreator/commit/65ff5a33))
* **targetticket,targetchange:** handle import failure on missing question ([d2fd6fa4](https://github.com/pluginsglpi/formcreator/commit/d2fd6fa4))
* **targetticket,targetchange:** inverted show / hide for urgenty settings ([03b3d95e](https://github.com/pluginsglpi/formcreator/commit/03b3d95e))
* **targetticket,targetchange:** loss of the target name when duplicating ([8d24bd46](https://github.com/pluginsglpi/formcreator/commit/8d24bd46))
* **targetticket,targetchange:** make name mandatory browser side ([93ef3496](https://github.com/pluginsglpi/formcreator/commit/93ef3496))
* **targetticket,targetchange:** missing update of question ID in targets ([3f998d8b](https://github.com/pluginsglpi/formcreator/commit/3f998d8b))
* **targetticket,targetchange:** repair supplier actors edition ([34597ed3](https://github.com/pluginsglpi/formcreator/commit/34597ed3))
* **targetticket,targetchange:** specific tags not applicable ([062d10e3](https://github.com/pluginsglpi/formcreator/commit/062d10e3))
* **targetticket,targetchange:** too many escaping on target_name ([269e4d7c](https://github.com/pluginsglpi/formcreator/commit/269e4d7c))
* **targetticket,targetchange:** unsaved question ID for tags from question, restore multiple choice ([255d0e7d](https://github.com/pluginsglpi/formcreator/commit/255d0e7d))
* **targetticket,targetchange:** update constants for due date resolution in JS code ([8f964e71](https://github.com/pluginsglpi/formcreator/commit/8f964e71))
* **textarea:** image paste on multiple pages ([5cbab1e2](https://github.com/pluginsglpi/formcreator/commit/5cbab1e2))
* **textarea:** workaround GLPI bug in 9.4.5 ([3d53b181](https://github.com/pluginsglpi/formcreator/commit/3d53b181)), closes [#1613](https://github.com/pluginsglpi/formcreator/issues/1613)
* **textareafield:** allow paste images in textareas ([8b8b244e](https://github.com/pluginsglpi/formcreator/commit/8b8b244e))
* **textareafield:** inline imagein default value converted to document ([14e5f4b3](https://github.com/pluginsglpi/formcreator/commit/14e5f4b3))
* **textareafield:** remove workaround for textarea ([f79ed364](https://github.com/pluginsglpi/formcreator/commit/f79ed364))
* **textareafield:** tinymce may be 0px height ([aceec1b3](https://github.com/pluginsglpi/formcreator/commit/aceec1b3))
* **textareafield:** visual glitches if a textarea is visibles after scrollong and under a condition, triggering on and off teh conditin may lead to a partially visible areal (only the toolbar) ([81ed97a6](https://github.com/pluginsglpi/formcreator/commit/81ed97a6))
* **textareafield,filefield:** better support for GLPI 9.5 ([213ec157](https://github.com/pluginsglpi/formcreator/commit/213ec157))
* **textfield,textareafield:** escaping problem when editing question ([dd585eb0](https://github.com/pluginsglpi/formcreator/commit/dd585eb0))
* **ticket:** put new ticket to trash bin ([90f6b5c2](https://github.com/pluginsglpi/formcreator/commit/90f6b5c2))
* **timefield:** assign a default value ([d08cc0b8](https://github.com/pluginsglpi/formcreator/commit/d08cc0b8))
* **urgencyfield:** static call on non-static method ([db689c0e](https://github.com/pluginsglpi/formcreator/commit/db689c0e))
* **wizard:** JS lib not loaded ([5a4f2e72](https://github.com/pluginsglpi/formcreator/commit/5a4f2e72))
* **wizard:** JS lib not loaded ([0c93dbd4](https://github.com/pluginsglpi/formcreator/commit/0c93dbd4))
* **wizard:** broken HTML, forms are invisible ([aac9c082](https://github.com/pluginsglpi/formcreator/commit/aac9c082))
* **wizard:** main area container overflows behind footer ([f25d6054](https://github.com/pluginsglpi/formcreator/commit/f25d6054))
* **wizard:** missing HTML tag breaking footer ([15208880](https://github.com/pluginsglpi/formcreator/commit/15208880))
* **wizard:** reservation search failure ([24ca70c4](https://github.com/pluginsglpi/formcreator/commit/24ca70c4))


### Features

*  ad menu icon for GLPI 9.5 ([217be26c](https://github.com/pluginsglpi/formcreator/commit/217be26c))
*  compatibility with GLPI's marketplace ([04374b9b](https://github.com/pluginsglpi/formcreator/commit/04374b9b))
*  compatibiliy with glpi 9.5 ([a0591176](https://github.com/pluginsglpi/formcreator/commit/a0591176))
*  drop GLPI 9.4 compatibility ([50541652](https://github.com/pluginsglpi/formcreator/commit/50541652))
*  drop GLPI 9.4 compatibility ([83442969](https://github.com/pluginsglpi/formcreator/commit/83442969))
*  drop GLPI 9.4 comptability ([ed360e26](https://github.com/pluginsglpi/formcreator/commit/ed360e26))
*  drop comaptibility with GLPI 9.4 ([7c48d037](https://github.com/pluginsglpi/formcreator/commit/7c48d037))
*  drop compatibility with GLPI 9.4 ([1170c8b6](https://github.com/pluginsglpi/formcreator/commit/1170c8b6))
*  use font awesome for all icons ([0c842f48](https://github.com/pluginsglpi/formcreator/commit/0c842f48))
* **actorfield:** drop compatibility with GLPI 9.4 ([d46b8904](https://github.com/pluginsglpi/formcreator/commit/d46b8904))
* **actorsfield:** drop compatibility with GLPI 9.4 ([4c968368](https://github.com/pluginsglpi/formcreator/commit/4c968368))
* **condition:** condition on visibility of a question ([76ca5ac6](https://github.com/pluginsglpi/formcreator/commit/76ca5ac6))
* **datefield:** default value for date ([5a83b325](https://github.com/pluginsglpi/formcreator/commit/5a83b325))
* **datetimefield:** default value ([120433fe](https://github.com/pluginsglpi/formcreator/commit/120433fe))
* **descriptionfield:** allow description field in targets ([099e9001](https://github.com/pluginsglpi/formcreator/commit/099e9001))
* **entityconfig:** setting to define sort order ([180f6c23](https://github.com/pluginsglpi/formcreator/commit/180f6c23))
* **form:** abort import from JSON without a version tag ([1264ac1d](https://github.com/pluginsglpi/formcreator/commit/1264ac1d))
* **form:** captcha for anonymous forms ([9576f2b5](https://github.com/pluginsglpi/formcreator/commit/9576f2b5))
* **form:** case insensitive captcha ([b463d3a1](https://github.com/pluginsglpi/formcreator/commit/b463d3a1))
* **form:** conditions for submot button ([bfeabe54](https://github.com/pluginsglpi/formcreator/commit/bfeabe54))
* **form:** disable floating elements ([26d2fdb6](https://github.com/pluginsglpi/formcreator/commit/26d2fdb6))
* **form:** make forms (slightly) more responsive ([3857b000](https://github.com/pluginsglpi/formcreator/commit/3857b000))
* **form:** progressbar for form import ([deb1e20e](https://github.com/pluginsglpi/formcreator/commit/deb1e20e))
* **form:** setting to enable captcha ([f6a93bb5](https://github.com/pluginsglpi/formcreator/commit/f6a93bb5))
* **form:** version check of file on import ([61266614](https://github.com/pluginsglpi/formcreator/commit/61266614))
* **glpioblectfield:** support for native appliance object ([5592f155](https://github.com/pluginsglpi/formcreator/commit/5592f155))
* **glpiselectfield:** access to appliances from appliances plugin ([3f03e529](https://github.com/pluginsglpi/formcreator/commit/3f03e529))
* **glpiselectfield:** drop compatibility with GLPI 9.4 ([63abdff5](https://github.com/pluginsglpi/formcreator/commit/63abdff5))
* **glpiselectfield:** support for new passive DC equipment ([74859125](https://github.com/pluginsglpi/formcreator/commit/74859125))
* **issue:** deprecate SyncIsssues ([1c456ff1](https://github.com/pluginsglpi/formcreator/commit/1c456ff1))
* **issue:** group validator in issue ([f9183db2](https://github.com/pluginsglpi/formcreator/commit/f9183db2))
* **knowbaseitem:** new UI for KB ([f1d34de8](https://github.com/pluginsglpi/formcreator/commit/f1d34de8))
* **question:** give more room for value of select question ([7b6dd6b1](https://github.com/pluginsglpi/formcreator/commit/7b6dd6b1))
* **question,section:** drag drop accross sections ([24d6cc8c](https://github.com/pluginsglpi/formcreator/commit/24d6cc8c))
* **question,section:** show conditions count ([f4625263](https://github.com/pluginsglpi/formcreator/commit/f4625263))
* **question,section:** tip on conditions count ([68f29f9b](https://github.com/pluginsglpi/formcreator/commit/68f29f9b))
* **requesttypefield:** allow empty value ([30c86ae3](https://github.com/pluginsglpi/formcreator/commit/30c86ae3))
* **section:** make section title required ([aa0c7dd4](https://github.com/pluginsglpi/formcreator/commit/aa0c7dd4))
* **targetticket:** set type from question ([d39a522d](https://github.com/pluginsglpi/formcreator/commit/d39a522d))
* **targetticket,targetchange:** assign a group from the technician group of an objec ([55b4318f](https://github.com/pluginsglpi/formcreator/commit/55b4318f))
* **targetticket,targetchange:** conditions backported to 2.10 ([a236f5e4](https://github.com/pluginsglpi/formcreator/commit/a236f5e4))
* **targetticket,targetchange:** conditions to generate the targets ([ae1cdf66](https://github.com/pluginsglpi/formcreator/commit/ae1cdf66))
* **targetticket,targetchange:** set a group from an object from a question ([8d7af9f9](https://github.com/pluginsglpi/formcreator/commit/8d7af9f9))
* **urgencyfield:** allow empty value ([0712662c](https://github.com/pluginsglpi/formcreator/commit/0712662c))
* **wizard:** always show saved searches menu item ([13ee7034](https://github.com/pluginsglpi/formcreator/commit/13ee7034))
* **wizard:** handle GLPI's impersonate feature ([20a90249](https://github.com/pluginsglpi/formcreator/commit/20a90249))



## [2.10.4](https://github.com/pluginsglpi/formcreator/compare/v2.10.3...v2.10.4) (2020-11-09)


### Bug Fixes

* **dropdownfield:** compatibility with Document itemtype ([bdd533a](https://github.com/pluginsglpi/formcreator/commit/bdd533a2dcc4257258191ae287095c247292b7cb))
* **form:** missinb closing tag ([faab454](https://github.com/pluginsglpi/formcreator/commit/faab454304907be07602557b75dc3289ed2ea8e8))
* **formanswer:** uploads lost when re-submitting answers ([962eafa](https://github.com/pluginsglpi/formcreator/commit/962eafa6e6f74ee028a6bb1cdd57f1e165038831))
* **issue:** enhance error message when canceling an issue ([cf21817](https://github.com/pluginsglpi/formcreator/commit/cf21817507b15caf33b8fe814e32b915b2f448a2))
* **issue:** handle deletion of validation ([385eee4](https://github.com/pluginsglpi/formcreator/commit/385eee465eb2e76220ae6d4ad7292d5d4e52a995))
* **issue:** update issue status on ticket validation update ([ca68eb4](https://github.com/pluginsglpi/formcreator/commit/ca68eb4f6402dbebef12f534aaa8ece3402c4c04))
* **locales:** reapply bad translation fix ([b8d6466](https://github.com/pluginsglpi/formcreator/commit/b8d64662d011b41f4e036d2f1e2d57d88935efef))
* **notificationtargetformanswer:** not rendered fullform tag ([f83ce3f](https://github.com/pluginsglpi/formcreator/commit/f83ce3f270b1edce3fda5d10d8dad9d0f5da118e))
* **targetticket,targetchange:** handle import failure on missing question ([e17c5a3](https://github.com/pluginsglpi/formcreator/commit/e17c5a37adc6ab5bcd479f7d1e9a7e0a6206c935))
* **wizard:** reservation search failure ([b6e0dd8](https://github.com/pluginsglpi/formcreator/commit/b6e0dd868a03bc5bdf6832e08b416e9c01b3b6ad))
* update hooks ([d2d9980](https://github.com/pluginsglpi/formcreator/commit/d2d99805c63dd6a51edde6ab1da01733e8915cd2))


### Features

* **targetticket,targetchange:** add tag for notification ([9da6fb5](https://github.com/pluginsglpi/formcreator/commit/9da6fb5c71a87c105ee68a62ff85e7ccc90f120c))



## [2.10.3](https://github.com/pluginsglpi/formcreator/compare/v2.10.2...v2.10.3) (2020-10-13)


### Bug Fixes

* **description:** simple text may render HTML tags ([dc82b19](https://github.com/pluginsglpi/formcreator/commit/dc82b1955148217f57d9f573408fc8b271dafe1a))
* **dropdownfield:** bad entity restriction ([9f4b1ad](https://github.com/pluginsglpi/formcreator/commit/9f4b1addaaa39095a4a1ebcff5e64a579676bd20))
* **dropdownfield:** SQL error to find curent user's groups ([c86bc4e](https://github.com/pluginsglpi/formcreator/commit/c86bc4e5880bf5bca15c4e3776991d89465dc5c8))
* **form:** bad path to css ([ffff1b4](https://github.com/pluginsglpi/formcreator/commit/ffff1b4e55c9aaca6d0ef01eef534d1924cfb34b))
* **form:** bad sharing URL ([6ea4dff](https://github.com/pluginsglpi/formcreator/commit/6ea4dff2a38b3c9c6b89aa27fdfe6b910470bb69))
* **glpiselectfield:** missing caps un classnames ([203b5b9](https://github.com/pluginsglpi/formcreator/commit/203b5b9503d08bcf2fad1357a8970b93073311cf))
* **issue:** update issue status on ticket validation update ([4819b26](https://github.com/pluginsglpi/formcreator/commit/4819b268824f45da3f97ca74ff7bcc858dc67c2c))
* **locales:** bad locale string ([766cea3](https://github.com/pluginsglpi/formcreator/commit/766cea3cda586b3b2351e80ef0cbc37fce0f5355))
* **targetticket:** undefined type after creation ([e3188ef](https://github.com/pluginsglpi/formcreator/commit/e3188efc2cd31329290f4a43e4c5a2d41b67591d))
* **textfield,textareafield:** escaping problem when editing question ([231e9e4](https://github.com/pluginsglpi/formcreator/commit/231e9e4cef6db0ccd46466bebbfe2a39a4a81233))
* **wizard:** missing HTML tag breaking footer ([a299a14](https://github.com/pluginsglpi/formcreator/commit/a299a141229762164bb8a55d67947542fdb4596b))


### Features

* use font awesome for all icons ([25cb6b7](https://github.com/pluginsglpi/formcreator/commit/25cb6b7fff47d6fe7a5f5c309cf8c1191fc20d94))
* **descriptionfield:** allow description field in targets ([5fedd96](https://github.com/pluginsglpi/formcreator/commit/5fedd96aabf93671bb69b30cd9b1ab8fbd814a7c))
* **question,section:** tip on conditions count ([303fe86](https://github.com/pluginsglpi/formcreator/commit/303fe860691dc4753a581c68c8be256ff388d4c7))


### Performance Improvements

* **condition:** conditions evaluation ([879bdaf](https://github.com/pluginsglpi/formcreator/commit/879bdaf1967278c7aaf9c320cfe32d89de0bdc09))



## [2.10.2](https://github.com/pluginsglpi/formcreator/compare/v2.10.1...v2.10.2) (2020-09-07)


### Bug Fixes

* **condition:** duplicated JS function ([acbe985](https://github.com/pluginsglpi/formcreator/commit/acbe985bf3ddd710ddeacd4c30c7f47597f155b2))
* **confition:** hide garbage conditions ([8810cd6](https://github.com/pluginsglpi/formcreator/commit/8810cd68186414902d5322ff938dff271a6ea129))
* **filefield:** broken mandatory check ([f70a847](https://github.com/pluginsglpi/formcreator/commit/f70a84725da99d14980a69e9af7c258bf406dafd))
* **form:** bad session var type when using anonymous form ([9d43e80](https://github.com/pluginsglpi/formcreator/commit/9d43e80f97c9aa3d4575e31771b3e1cb6bbda838))
* **form:** doubling starcauses SQL error ([41101ca](https://github.com/pluginsglpi/formcreator/commit/41101ca151c9e21e9205a1ba8e63ed6dd1d2f9b7))
* **form:** error in displayed form URL ([d21c5b3](https://github.com/pluginsglpi/formcreator/commit/d21c5b3a4edaa1eebe4f81de82a78ea5a3db4f79))
* **form:** forbid clone massive action in GLPI 9.5 ([2947d6f](https://github.com/pluginsglpi/formcreator/commit/2947d6f683dae27f854bdd1500322f281c8ea7e3))
* **form:** prevent SQL errors, remove natural language search ([2eabddf](https://github.com/pluginsglpi/formcreator/commit/2eabddf5e93e777c2051adb741a3a9cd5c025fee))
* **form_profile:** HTML form name mismatch ([f201f37](https://github.com/pluginsglpi/formcreator/commit/f201f37a5a9635fdd163955356ed5eb49be1a6bd))
* **form_profile:** not rendered selection of profiles ([1c0d27d](https://github.com/pluginsglpi/formcreator/commit/1c0d27d502fa358589013230890265b559328710))
* **formanswer:** do not render section title if invisible ([6bb6be3](https://github.com/pluginsglpi/formcreator/commit/6bb6be33c782ea6668875a7251d1ba0f38f06bd7))
* **formanswer:** missing validation checks when user updates a refused form ([788ac89](https://github.com/pluginsglpi/formcreator/commit/788ac89cf06e6fbc53ff8d4357514eae054b5275))
* **issue:** adjust ticket status n automatic action ([397a912](https://github.com/pluginsglpi/formcreator/commit/397a9127acd05421eed3513737085fc32d4e6c6c))
* **issue:** repopulate table on upgrade ([90727ae](https://github.com/pluginsglpi/formcreator/commit/90727ae21ed3986c3c84a477bae0273a129872e7))
* **issue:** status conversion for ticket ([9aae13d](https://github.com/pluginsglpi/formcreator/commit/9aae13d08a32b8d65511f8b22e15482791cce458))
* **issue:** syncissues drops most requesters ([1fa10c8](https://github.com/pluginsglpi/formcreator/commit/1fa10c82774b5beadd0e252e2d0c796f768eee4f))
* **issue:** validated ticket status ([24dacd2](https://github.com/pluginsglpi/formcreator/commit/24dacd2a071a64be6590480bca9d696eabab8750))
* **question:** parameters duplicated twice ([e6889cc](https://github.com/pluginsglpi/formcreator/commit/e6889cc0768a0f90743cab9523da927051623e12))
* **section:** order of sections not respected on import ([e9bf84b](https://github.com/pluginsglpi/formcreator/commit/e9bf84b7c507f6dbfb15b1a095143bbac1bc23aa))
* **target:** load all tag questions ([bbcfc8a](https://github.com/pluginsglpi/formcreator/commit/bbcfc8a5015682ea516c40255788448f3a95a6a0))
* **targetchange:** do not geenrate HTML for simple text fields ([2d7a5f6](https://github.com/pluginsglpi/formcreator/commit/2d7a5f68eb0758bc4f64efa35a1a55246f48538b))
* **targetticket:** last valid category ignored visibility state ([f6e09f0](https://github.com/pluginsglpi/formcreator/commit/f6e09f09e8f901794959bc5fae1a649f22d5bf0d))
* **targetticket,targetchange:** too many escaping on target_name ([fcfbed9](https://github.com/pluginsglpi/formcreator/commit/fcfbed98ba92841dddf51b1efebaf2773fd58627))
* modal positionning ([a0e0873](https://github.com/pluginsglpi/formcreator/commit/a0e0873fd853804f65a61aed67a4eb3fd9cfc5da))


### Features

* **question,section:** show conditions count ([dd22ca0](https://github.com/pluginsglpi/formcreator/commit/dd22ca02b091c8b4f70243d98ef64fc69b9f4394))



<a name="2.10.1"></a>
## [2.10.1](https://github.com/pluginsglpi/formcreator/compare/v2.10.0..2.10.1) (2020-07-16)


### Bug Fixes

*  bad path for marketplace ([9e49a028](https://github.com/pluginsglpi/formcreator/commit/9e49a028))
*  keep backward compatibility with GLPI 9.4 ([dedde0b5](https://github.com/pluginsglpi/formcreator/commit/dedde0b5))
*  keep compatibility <ith GLPI 9.4 ([b4335c54](https://github.com/pluginsglpi/formcreator/commit/b4335c54))
*  marketplace compatibility (again) ([1070cb6f](https://github.com/pluginsglpi/formcreator/commit/1070cb6f))
*  path for marketplace compatibility ([1da588e1](https://github.com/pluginsglpi/formcreator/commit/1da588e1))
*  unloaded JS library ([a71d468e](https://github.com/pluginsglpi/formcreator/commit/a71d468e))
* **category:** fix SQL error ([3470b61e](https://github.com/pluginsglpi/formcreator/commit/3470b61e))
* **dropdownfield:** compatibility with Tags plugin ([c40177a8](https://github.com/pluginsglpi/formcreator/commit/c40177a8))
* **filefield:** documentt upload with GLPI 9.5 ([9658946c](https://github.com/pluginsglpi/formcreator/commit/9658946c))
* **form:** broken link to forms ([1195d5bd](https://github.com/pluginsglpi/formcreator/commit/1195d5bd))
* **form:** entity restrict problem ([58a79101](https://github.com/pluginsglpi/formcreator/commit/58a79101))
* **form:** incorrect font on add target link ([4880b95f](https://github.com/pluginsglpi/formcreator/commit/4880b95f))
* **form:** version comparison for export / import ([d9dff6cb](https://github.com/pluginsglpi/formcreator/commit/d9dff6cb))
* **issue:** load tinymce ([820bd7ac](https://github.com/pluginsglpi/formcreator/commit/820bd7ac))
* **issue:** possible SQL error ([0688d13c](https://github.com/pluginsglpi/formcreator/commit/0688d13c))
* **issue:** self service is able to reopen a closed issue / ticket ([f483ddbb](https://github.com/pluginsglpi/formcreator/commit/f483ddbb))
* **issue:** ticket status when approval request is used ([d9b46773](https://github.com/pluginsglpi/formcreator/commit/d9b46773))
* **targetticket,targetcategory:** category question not properly set on display ([5fb8fd4b](https://github.com/pluginsglpi/formcreator/commit/5fb8fd4b))
* **targetticket,targetchange:** avoid possible PHP warning ([eb312921](https://github.com/pluginsglpi/formcreator/commit/eb312921))
* **targetticket,targetchange:** covnert question ID into UUID for export of target settings ([a17e4590](https://github.com/pluginsglpi/formcreator/commit/a17e4590))
* **targetticket,targetchange:** dynamic entity computation may fail ([85b1e76b](https://github.com/pluginsglpi/formcreator/commit/85b1e76b))
* **targetticket,targetchange:** missing update of question ID in targets ([5ecb8283](https://github.com/pluginsglpi/formcreator/commit/5ecb8283))
* **targetticket,targetchange:** specific tags not applicable ([b2330387](https://github.com/pluginsglpi/formcreator/commit/b2330387))
* **targetticket,targetchange:** unsaved question ID for tags from question, restore multiple choice ([ae3188ab](https://github.com/pluginsglpi/formcreator/commit/ae3188ab))
* **textareafield:** tinymce may be 0px height ([3f1c262b](https://github.com/pluginsglpi/formcreator/commit/3f1c262b))
* **wizard:** impersonation exists in GLPI 9.5 only ([eb6f43e0](https://github.com/pluginsglpi/formcreator/commit/eb6f43e0))


### Features

* **wizard:** handle GLPI's impersonate feature ([9cd0c8be](https://github.com/pluginsglpi/formcreator/commit/9cd0c8be))



<a name="2.10.0"></a>
# [2.10.0](https://github.com/pluginsglpi/formcreator/compare/v2.10.0-rc.1...v2.10.0) (2020-06-23)


### Bug Fixes

* **category:** entity restriction not applied ([333fefe](https://github.com/pluginsglpi/formcreator/commit/333fefe))
* **dropdownfield:** empty dropdown ([324bd74](https://github.com/pluginsglpi/formcreator/commit/324bd74))
* **form:** bad expression to acess form name ([d7dda48](https://github.com/pluginsglpi/formcreator/commit/d7dda48))
* **form:** duplication exception ([#1818](https://github.com/pluginsglpi/formcreator/issues/1818)) ([c66e518](https://github.com/pluginsglpi/formcreator/commit/c66e518))
* **form:** export versin number as string ([475e190](https://github.com/pluginsglpi/formcreator/commit/475e190))
* **form:** php warning in import ([db49e89](https://github.com/pluginsglpi/formcreator/commit/db49e89))
* **formanswer:** view of textarea with rich text ([c175396](https://github.com/pluginsglpi/formcreator/commit/c175396))
* **import:** more explicit error message ([8bb3c3d](https://github.com/pluginsglpi/formcreator/commit/8bb3c3d))
* **issue:** cancel ticket with simplified service catalog ([b46b64f](https://github.com/pluginsglpi/formcreator/commit/b46b64f))
* **item_targetticket:** bad relation expression for SQL ([4565197](https://github.com/pluginsglpi/formcreator/commit/4565197))
* **question:** delete all conditions of a question being deleted ([8ba3031](https://github.com/pluginsglpi/formcreator/commit/8ba3031))
* **target_actor:** bad key for users, groups or suppliers ([6e05962](https://github.com/pluginsglpi/formcreator/commit/6e05962))
* **target_actor:** make string localazable ([bc4befe](https://github.com/pluginsglpi/formcreator/commit/bc4befe))
* **target_actor:** tell the ID of missing actor ([81a6c01](https://github.com/pluginsglpi/formcreator/commit/81a6c01))
* **targettichet,targetchange:** question tags not updated on duplication ([22f765d](https://github.com/pluginsglpi/formcreator/commit/22f765d))
* bad redirection ([3ba9748](https://github.com/pluginsglpi/formcreator/commit/3ba9748))
* other bad redirections ([6869b64](https://github.com/pluginsglpi/formcreator/commit/6869b64))


### Features

* **glpiselectfield:** support for new passive DC equipment ([41e59c1](https://github.com/pluginsglpi/formcreator/commit/41e59c1))



## [2.10.0](https://github.com/pluginsglpi/formcreator/compare/v2.10.0-rc.1..v2.10.0) (2020-06-23)


### Bug Fixes

*  bad redirection ([3ba9748f](https://github.com/pluginsglpi/formcreator/commit/3ba9748f))
*  other bad redirections ([6869b646](https://github.com/pluginsglpi/formcreator/commit/6869b646))
* **category:** entity restriction not applied ([333fefe0](https://github.com/pluginsglpi/formcreator/commit/333fefe0))
* **dropdownfield:** empty dropdown ([324bd74f](https://github.com/pluginsglpi/formcreator/commit/324bd74f))
* **form:** bad expression to acess form name ([d7dda48b](https://github.com/pluginsglpi/formcreator/commit/d7dda48b))
* **form:** duplication exception (#1818) ([c66e5187](https://github.com/pluginsglpi/formcreator/commit/c66e5187))
* **form:** export versin number as string ([475e1902](https://github.com/pluginsglpi/formcreator/commit/475e1902))
* **form:** php warning in import ([db49e89a](https://github.com/pluginsglpi/formcreator/commit/db49e89a))
* **formanswer:** view of textarea with rich text ([c1753960](https://github.com/pluginsglpi/formcreator/commit/c1753960))
* **import:** more explicit error message ([8bb3c3d7](https://github.com/pluginsglpi/formcreator/commit/8bb3c3d7))
* **issue:** cancel ticket with simplified service catalog ([b46b64f5](https://github.com/pluginsglpi/formcreator/commit/b46b64f5))
* **item_targetticket:** bad relation expression for SQL ([45651977](https://github.com/pluginsglpi/formcreator/commit/45651977))
* **question:** delete all conditions of a question being deleted ([8ba30316](https://github.com/pluginsglpi/formcreator/commit/8ba30316))
* **target_actor:** bad key for users, groups or suppliers ([6e059627](https://github.com/pluginsglpi/formcreator/commit/6e059627))
* **target_actor:** make string localazable ([bc4befed](https://github.com/pluginsglpi/formcreator/commit/bc4befed))
* **target_actor:** tell the ID of missing actor ([81a6c01d](https://github.com/pluginsglpi/formcreator/commit/81a6c01d))
* **targettichet,targetchange:** question tags not updated on duplication ([22f765d8](https://github.com/pluginsglpi/formcreator/commit/22f765d8))


### Features

* **glpiselectfield:** support for new passive DC equipment ([41e59c10](https://github.com/pluginsglpi/formcreator/commit/41e59c10))



<a name="2.10.0-rc.1"></a>
# [2.10.0-rc.1](https://github.com/pluginsglpi/formcreator/compare/v2.10.0-beta.1...v2.10.rc.1) (2020-06-10)


### Bug Fixes

* **category:** use short name ([ddf3eff](https://github.com/pluginsglpi/formcreator/commit/ddf3eff))
* **central:** list of forms displayed twice ([718724e](https://github.com/pluginsglpi/formcreator/commit/718724e))
* **condition:** change again the way to hide questions and sections ([d5f3a6d](https://github.com/pluginsglpi/formcreator/commit/d5f3a6d))
* **condition:** fix export ([7db999d](https://github.com/pluginsglpi/formcreator/commit/7db999d))
* **condition:** php warning if a wuestion does not exists ([6006bad](https://github.com/pluginsglpi/formcreator/commit/6006bad))
* **condition:** remove conditions when disabled ([1068e97](https://github.com/pluginsglpi/formcreator/commit/1068e97))
* **conditionnable:** consistency check for conditions ([9c75f62](https://github.com/pluginsglpi/formcreator/commit/9c75f62))
* **dropdownfield:** check existence of itemtype in prerequisite ([d26197f](https://github.com/pluginsglpi/formcreator/commit/d26197f))
* **dropdownfield:** label for change categories and request categories ([9b59e45](https://github.com/pluginsglpi/formcreator/commit/9b59e45))
* **dropdownfield:** SQL error : ambiguous column id ([9366773](https://github.com/pluginsglpi/formcreator/commit/9366773))
* **dropdownfield,glpiobjectfield:** sub type not dosplayed ([461fbe4](https://github.com/pluginsglpi/formcreator/commit/461fbe4))
* **dropdownfield,glpiselectfield:** empty value parameter not honored ([12a02fe](https://github.com/pluginsglpi/formcreator/commit/12a02fe))
* **dropdownfields:** handle empty value for entities dropdown ([edaa13b](https://github.com/pluginsglpi/formcreator/commit/edaa13b))
* **exportable:** implement missing method ([9865058](https://github.com/pluginsglpi/formcreator/commit/9865058))
* **exportable:** implement missing method ([990a1ad](https://github.com/pluginsglpi/formcreator/commit/990a1ad))
* **exportable:** implement missing method ([249728d](https://github.com/pluginsglpi/formcreator/commit/249728d))
* **form:** bad rendering when printing from the service catalog ([0efd014](https://github.com/pluginsglpi/formcreator/commit/0efd014))
* **form:** bad rendering when printing from the service catalog ([357c8c7](https://github.com/pluginsglpi/formcreator/commit/357c8c7))
* **form:** fix malformed sql ([eec2a2e](https://github.com/pluginsglpi/formcreator/commit/eec2a2e))
* **form:** hidden questions still consume 10 pixels height ([948ddde](https://github.com/pluginsglpi/formcreator/commit/948ddde))
* **form:** list of forms on homepage ([6d8a318](https://github.com/pluginsglpi/formcreator/commit/6d8a318))
* **form:** multiple selection of validators ([5901908](https://github.com/pluginsglpi/formcreator/commit/5901908))
* **form:** restore padding ([f345c9f](https://github.com/pluginsglpi/formcreator/commit/f345c9f))
* **form:** unused class usage ([de2d2aa](https://github.com/pluginsglpi/formcreator/commit/de2d2aa))
* **form:** validators must show when more than 2 available ([fb37c46](https://github.com/pluginsglpi/formcreator/commit/fb37c46))
* **form,question:** duplicate fail on form without section ([4db8455](https://github.com/pluginsglpi/formcreator/commit/4db8455))
* **formanswer:** display of status shall show a label ([ea392e3](https://github.com/pluginsglpi/formcreator/commit/ea392e3))
* **formanswer:** display of status shall show a label ([d3e5904](https://github.com/pluginsglpi/formcreator/commit/d3e5904))
* **formanswer:** save update on refused form ([74b817d](https://github.com/pluginsglpi/formcreator/commit/74b817d))
* **formanswer:** use of  in static method ([f6411d8](https://github.com/pluginsglpi/formcreator/commit/f6411d8))
* **formanswer:** word wrap on display long lines with long words ([e1c40c7](https://github.com/pluginsglpi/formcreator/commit/e1c40c7))
* **glpiselectfield:** appliance plugin name is plural ([3a6968f](https://github.com/pluginsglpi/formcreator/commit/3a6968f))
* **glpiselectfield:** prevent use of the field with non existing itemtype ([cfbdef9](https://github.com/pluginsglpi/formcreator/commit/cfbdef9))
* **glpiselectfield:** restrict to items associatable to tickets ([4377baf](https://github.com/pluginsglpi/formcreator/commit/4377baf))
* **glpiselectfield:** update test ([ed3f2f6](https://github.com/pluginsglpi/formcreator/commit/ed3f2f6))
* **import:** cannot factorize deleteObsoleteItems ([b13c01a](https://github.com/pluginsglpi/formcreator/commit/b13c01a))
* **import:** don't handle immediately conditions on import ([0d989b3](https://github.com/pluginsglpi/formcreator/commit/0d989b3))
* **install:** missing column reorder on upgrade ([668ee2a](https://github.com/pluginsglpi/formcreator/commit/668ee2a))
* **install:** upgrade to 2.5.0 fails on categories ([46f8aa7](https://github.com/pluginsglpi/formcreator/commit/46f8aa7))
* **issue:** distinguish requester and author ([ae8e9dc](https://github.com/pluginsglpi/formcreator/commit/ae8e9dc))
* **issue:** handle redirection to satisfaction survey from email ([57d8074](https://github.com/pluginsglpi/formcreator/commit/57d8074))
* **issue:** handle survey expiration ([8810dad](https://github.com/pluginsglpi/formcreator/commit/8810dad))
* **issue:** localization problem impacting picture ([076a97e](https://github.com/pluginsglpi/formcreator/commit/076a97e))
* **issue:** properly set validation data on ticket restore ([0631ece](https://github.com/pluginsglpi/formcreator/commit/0631ece))
* **issue:** show satisfaction for tickets on service catalog ([7c17518](https://github.com/pluginsglpi/formcreator/commit/7c17518))
* **issue:** SQL error ([0bb3905](https://github.com/pluginsglpi/formcreator/commit/0bb3905))
* **issue:** support of ticket waiting for approval ([f7cdcb1](https://github.com/pluginsglpi/formcreator/commit/f7cdcb1))
* **issue:** take ticket valdiation status into account ([ac02f86](https://github.com/pluginsglpi/formcreator/commit/ac02f86))
* **issue:** warning with GLPI 9.5 ([5899d27](https://github.com/pluginsglpi/formcreator/commit/5899d27))
* **locales:** en_US coontained a foreign language ([44d63fd](https://github.com/pluginsglpi/formcreator/commit/44d63fd))
* **question:** default value edition for dropdown types ([9c9dc89](https://github.com/pluginsglpi/formcreator/commit/9c9dc89))
* **question:** prevent double escaping of description ([831985a](https://github.com/pluginsglpi/formcreator/commit/831985a))
* **questionparameter:** bad data for add item ([f633b21](https://github.com/pluginsglpi/formcreator/commit/f633b21))
* **questionparameter:** duplicate with GLPI 9.5 ([ba2e9dd](https://github.com/pluginsglpi/formcreator/commit/ba2e9dd))
* **radiosfield:** bad rendering of buttons when printing ([489251a](https://github.com/pluginsglpi/formcreator/commit/489251a))
* **requesttypefield:** update interface ([679c4d9](https://github.com/pluginsglpi/formcreator/commit/679c4d9))
* **section:** don't allocate height on hidden section ([aa01a1d](https://github.com/pluginsglpi/formcreator/commit/aa01a1d))
* **section:** unsupported use of QueryExpression ([8e40be8](https://github.com/pluginsglpi/formcreator/commit/8e40be8))
* **section,question:** ensure the modal window for edition is on screen ([76b2aae](https://github.com/pluginsglpi/formcreator/commit/76b2aae))
* **targetchange:** value of checklist not rendered ([965b10b](https://github.com/pluginsglpi/formcreator/commit/965b10b))
* **targetchange,targetticket:** no order column for those types ([adb70b3](https://github.com/pluginsglpi/formcreator/commit/adb70b3))
* **targetticket:** request type may be unset ([b1a94f8](https://github.com/pluginsglpi/formcreator/commit/b1a94f8))
* **targetticket,targetchange:** duplicate actors run twice ([7ef3496](https://github.com/pluginsglpi/formcreator/commit/7ef3496))
* **targetticket,targetchange:** escape text fields of targets ([6c0d775](https://github.com/pluginsglpi/formcreator/commit/6c0d775))
* **targetticket,targetchange:** make name mandatory browser side ([2e81497](https://github.com/pluginsglpi/formcreator/commit/2e81497))
* **textarea:** better file uplaods handling ([488e2d5](https://github.com/pluginsglpi/formcreator/commit/488e2d5))
* **textarea:** image paste on multiple pages ([3adcc20](https://github.com/pluginsglpi/formcreator/commit/3adcc20))
* **textareafield:** handle with constant the availability of [#6939](https://github.com/pluginsglpi/formcreator/issues/6939) for GLPI ([cd8ca59](https://github.com/pluginsglpi/formcreator/commit/cd8ca59))
* **textareafield:** remove workaround for textarea ([45e3030](https://github.com/pluginsglpi/formcreator/commit/45e3030))
* **textareafield:** visual glitches ([91364c2](https://github.com/pluginsglpi/formcreator/commit/91364c2))
* **ticket:** put new ticket to trash bin ([b110ed3](https://github.com/pluginsglpi/formcreator/commit/b110ed3))
* **timefield:** assign a default value ([d125c7c](https://github.com/pluginsglpi/formcreator/commit/d125c7c))
* class should not be accessed directly ([7800c3b](https://github.com/pluginsglpi/formcreator/commit/7800c3b))
* class should not be accessed directly ([23447b4](https://github.com/pluginsglpi/formcreator/commit/23447b4))
* compatibility with GLPI 9.5 ([1929cd3](https://github.com/pluginsglpi/formcreator/commit/1929cd3))
* import inconsistency and possible infinite loop ([8939329](https://github.com/pluginsglpi/formcreator/commit/8939329))
* keep the user in the service catalog ([f368b46](https://github.com/pluginsglpi/formcreator/commit/f368b46))
* remove code left for debug ([ecd0d85](https://github.com/pluginsglpi/formcreator/commit/ecd0d85))


### Features

* **condition:** condition on visibility of a question ([b8fcfcd](https://github.com/pluginsglpi/formcreator/commit/b8fcfcd))
* **form:** version check of file on import ([b380b79](https://github.com/pluginsglpi/formcreator/commit/b380b79))
* **form,question:** mandatory check in browser ([3e98ca7](https://github.com/pluginsglpi/formcreator/commit/3e98ca7))
* ad menu icon for GLPI 9.5 ([0670e4f](https://github.com/pluginsglpi/formcreator/commit/0670e4f))
* backport of PR 1681 for 2.10 ([7e79785](https://github.com/pluginsglpi/formcreator/commit/7e79785))
* compatibiliy with glpi 9.5 ([aaefdd2](https://github.com/pluginsglpi/formcreator/commit/aaefdd2))
* compatibiliy with GLPI's marketplace ([542bfb3](https://github.com/pluginsglpi/formcreator/commit/542bfb3))
* **issue:** group validator in issue ([539604d](https://github.com/pluginsglpi/formcreator/commit/539604d))
* **section:** make section title required ([e3ecf02](https://github.com/pluginsglpi/formcreator/commit/e3ecf02))
* **targetticket,targetchange:** backport of conditions ([15660bf](https://github.com/pluginsglpi/formcreator/commit/15660bf))
* **wizard:** always show saved searches menu item ([8117edf](https://github.com/pluginsglpi/formcreator/commit/8117edf))



<a name="v2.10.0-beta.1"></a>
## [v2.10.0-beta.1](https://github.com/pluginsglpi/formcreator/compare/v2.9.1..v2.10.0-beta.1) (2020-01-29)


### Bug Fixes

*  avoid caps in filenames ([918a88da](https://github.com/pluginsglpi/formcreator/commit/918a88da))
* **build:** invert order of versions in changelog ([ff604eef](https://github.com/pluginsglpi/formcreator/commit/ff604eef))
* **common:** getMax fails with PHP 7.4 ([5ac11e7e](https://github.com/pluginsglpi/formcreator/commit/5ac11e7e))
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
* **question:** SQL errors when deleting a question ([488b6593](https://github.com/pluginsglpi/formcreator/commit/488b6593))
* **question:** duplication of condition ([8e4e7382](https://github.com/pluginsglpi/formcreator/commit/8e4e7382))
* **question:** inoperant buttons to move questions ([3a191ebd](https://github.com/pluginsglpi/formcreator/commit/3a191ebd))
* **question:** javascript code was displayed ([a7be9314](https://github.com/pluginsglpi/formcreator/commit/a7be9314))
* **question:** show / hode specific properties ([1ae7765e](https://github.com/pluginsglpi/formcreator/commit/1ae7765e))
* **question:** space betwen icon and name ([4019a4d2](https://github.com/pluginsglpi/formcreator/commit/4019a4d2))
* **question:** update JS selectors for question edit ([0969ad1b](https://github.com/pluginsglpi/formcreator/commit/0969ad1b))
* **question:** update of parameters broken ([14188596](https://github.com/pluginsglpi/formcreator/commit/14188596))
* **radiosfield:** better overlap prevention ([4ae31601](https://github.com/pluginsglpi/formcreator/commit/4ae31601))
* **radiosfield:** overlapped long labels ([6528a167](https://github.com/pluginsglpi/formcreator/commit/6528a167))
* **robo:** line formatting for changelog ([9ccd5567](https://github.com/pluginsglpi/formcreator/commit/9ccd5567))
* **robo:** prevent exception when computong log with commit without body ([e5aa246a](https://github.com/pluginsglpi/formcreator/commit/e5aa246a))
* **section:** conditions not duplicated ([78a3d9c5](https://github.com/pluginsglpi/formcreator/commit/78a3d9c5))
* **section:** duplication of conditions: bad itemtype ([cfcbea30](https://github.com/pluginsglpi/formcreator/commit/cfcbea30))
* **selectfield:** comparison with empty string ([bcb929e3](https://github.com/pluginsglpi/formcreator/commit/bcb929e3))
* **target_actor:** unable to add a specific person ([2ca54982](https://github.com/pluginsglpi/formcreator/commit/2ca54982))
* **target_actor:** update import / export ([cfdc6295](https://github.com/pluginsglpi/formcreator/commit/cfdc6295))
* **targetchange:** bad label ([3f15aedb](https://github.com/pluginsglpi/formcreator/commit/3f15aedb))
* **targetchange:** bad url when delete an actor ([62e0de19](https://github.com/pluginsglpi/formcreator/commit/62e0de19)), closes [#1607](https://github.com/pluginsglpi/formcreator/issues/1607)
* **targetticket:** empty dropdown for target ticket linking" ([3cea85a4](https://github.com/pluginsglpi/formcreator/commit/3cea85a4))
* **targetticket,targetchange:** inverted show / hide for urgenty settings ([9be5e3aa](https://github.com/pluginsglpi/formcreator/commit/9be5e3aa))
* **targetticket,targetchange:** repair supplier actors edition ([5c7aeb56](https://github.com/pluginsglpi/formcreator/commit/5c7aeb56))
* **targetticket,targetchange:** update constants for due date resolution in JS code ([4ddb6e8f](https://github.com/pluginsglpi/formcreator/commit/4ddb6e8f))
* **textarea:** workaround GLPI bug in 9.4.5 ([8de43588](https://github.com/pluginsglpi/formcreator/commit/8de43588)), closes [#1613](https://github.com/pluginsglpi/formcreator/issues/1613)


### Features

* **form:** condition on submit button ([73af77db](https://github.com/pluginsglpi/formcreator/commit/73af77db))
* **glpiselectfield:** access to appliances from appliances plugin ([25848d5e](https://github.com/pluginsglpi/formcreator/commit/25848d5e))
* **targetticket:** set type from question ([d23759aa](https://github.com/pluginsglpi/formcreator/commit/d23759aa))
* **targetticket,targetchange:** assign a group from the technician group of an objec ([24c365c7](https://github.com/pluginsglpi/formcreator/commit/24c365c7))
* **targetticket,targetchange:** set a group from an object from a question ([0461a918](https://github.com/pluginsglpi/formcreator/commit/0461a918))



<a name="2.9.2"></a>
## [2.9.2](https://github.com/pluginsglpi/formcreator/compare/v2.9.1...v2.9.2) (2020-05-12)


### Bug Fixes

* **build:** invert order of versions in changelog ([ff604ee](https://github.com/pluginsglpi/formcreator/commit/ff604ee))
* **central:** list of forms displayed twice ([00d3180](https://github.com/pluginsglpi/formcreator/commit/00d3180))
* **condition:** change again the way to hide questions and sections ([590fbd5](https://github.com/pluginsglpi/formcreator/commit/590fbd5))
* **condition:** incomplete export ([d4ffca2](https://github.com/pluginsglpi/formcreator/commit/d4ffca2))
* **condition:** php warning if a wuestion does not exists ([fcc49a9](https://github.com/pluginsglpi/formcreator/commit/fcc49a9))
* **dropdownfield:** SQL error : ambiguous column id ([ded963f](https://github.com/pluginsglpi/formcreator/commit/ded963f))
* **dropdownfield,glpiobjectfield:** sub type not dosplayed ([164d5b9](https://github.com/pluginsglpi/formcreator/commit/164d5b9))
* **dropdownfield,glpiselectfield:** empty value parameter not honored ([080271a](https://github.com/pluginsglpi/formcreator/commit/080271a))
* **dropdownfields:** handle empty value for entities dropdown ([47b47ef](https://github.com/pluginsglpi/formcreator/commit/47b47ef))
* **filefield:** unable to create / edit a file field ([58e0101](https://github.com/pluginsglpi/formcreator/commit/58e0101))
* **form:** duplicate empty form ([5944935](https://github.com/pluginsglpi/formcreator/commit/5944935))
* **form:** hidden questions still consume 10 pixels height ([afa2ea3](https://github.com/pluginsglpi/formcreator/commit/afa2ea3))
* **form:** list of forms on homepage ([328e591](https://github.com/pluginsglpi/formcreator/commit/328e591))
* **form:** multiple selection of validators ([e7e1642](https://github.com/pluginsglpi/formcreator/commit/e7e1642))
* **form:** my last form (validator) were not sorted ([6c2b0be](https://github.com/pluginsglpi/formcreator/commit/6c2b0be))
* **form:** restore padding ([54d4892](https://github.com/pluginsglpi/formcreator/commit/54d4892))
* **form:** single quotes around a table name ([0465095](https://github.com/pluginsglpi/formcreator/commit/0465095)), closes [#1606](https://github.com/pluginsglpi/formcreator/issues/1606)
* **form:** validators must show when more than 2 available ([4e29073](https://github.com/pluginsglpi/formcreator/commit/4e29073))
* **formanswer:** use of  in static method ([bb0ff99](https://github.com/pluginsglpi/formcreator/commit/bb0ff99))
* **fotrm:** some icons may be not displayed ([8fc9587](https://github.com/pluginsglpi/formcreator/commit/8fc9587))
* **glpiselectfield:** restrict to items associatable to tickets ([29b0a47](https://github.com/pluginsglpi/formcreator/commit/29b0a47))
* **install:** missing column reorder on upgrade ([5b81c4e](https://github.com/pluginsglpi/formcreator/commit/5b81c4e))
* **install:** upgrade to 2.5.0 fails on categories ([918e46f](https://github.com/pluginsglpi/formcreator/commit/918e46f))
* **issue:** distinguish requester and author ([051c1fd](https://github.com/pluginsglpi/formcreator/commit/051c1fd))
* **issue:** handle redirection to satisfaction survey from email ([b9aa843](https://github.com/pluginsglpi/formcreator/commit/b9aa843))
* **issue:** handle survey expiration ([cb37daf](https://github.com/pluginsglpi/formcreator/commit/cb37daf))
* **issue:** localization problem impacting picture ([d1e6e1c](https://github.com/pluginsglpi/formcreator/commit/d1e6e1c))
* **issue:** show satisfaction for tickets on service catalog ([7bde576](https://github.com/pluginsglpi/formcreator/commit/7bde576))
* **issue:** warning with GLPI 9.5 ([fe5b2df](https://github.com/pluginsglpi/formcreator/commit/fe5b2df))
* **multiselectfield:** visible JS ([bb77c0e](https://github.com/pluginsglpi/formcreator/commit/bb77c0e))
* **question:** inoperant buttons to move questions ([3a191eb](https://github.com/pluginsglpi/formcreator/commit/3a191eb))
* **question:** javascript code was displayed ([a7be931](https://github.com/pluginsglpi/formcreator/commit/a7be931))
* **question:** prevent double escaping of description ([ca2450c](https://github.com/pluginsglpi/formcreator/commit/ca2450c))
* **question:** show / hode specific properties ([490356e](https://github.com/pluginsglpi/formcreator/commit/490356e))
* **question:** space betwen icon and name ([e24ea5b](https://github.com/pluginsglpi/formcreator/commit/e24ea5b))
* **question,section:** duplication failure ([08e934b](https://github.com/pluginsglpi/formcreator/commit/08e934b))
* **questionparameter:** bad data for add item ([a635e8a](https://github.com/pluginsglpi/formcreator/commit/a635e8a))
* **questionparameter:** duplicate with GLPI 9.5 ([d3cc090](https://github.com/pluginsglpi/formcreator/commit/d3cc090))
* **radiosfield:** bad rendering of buttons when printing ([5b78f65](https://github.com/pluginsglpi/formcreator/commit/5b78f65))
* **radiosfield:** better overlap prevention ([a2f1ed1](https://github.com/pluginsglpi/formcreator/commit/a2f1ed1))
* **radiosfield:** overlapped long labels ([f7cbdde](https://github.com/pluginsglpi/formcreator/commit/f7cbdde))
* **robo:** prevent exception when computong log with commit without body ([e5aa246](https://github.com/pluginsglpi/formcreator/commit/e5aa246))
* **section:** don't allocate height on hidden section ([b655842](https://github.com/pluginsglpi/formcreator/commit/b655842))
* **targetchange:** bad url when delete an actor ([62e0de1](https://github.com/pluginsglpi/formcreator/commit/62e0de1)), closes [#1607](https://github.com/pluginsglpi/formcreator/issues/1607)
* **targetticket:** display of selected category question ([b571115](https://github.com/pluginsglpi/formcreator/commit/b571115))
* **targetticket:** empty dropdown for target ticket linking" ([79dd0bc](https://github.com/pluginsglpi/formcreator/commit/79dd0bc))
* **targetticket,targetchange:** assign group from questin answer ([947ce5c](https://github.com/pluginsglpi/formcreator/commit/947ce5c))
* **targetticket,targetchange:** bad handling of group from question ([80f405b](https://github.com/pluginsglpi/formcreator/commit/80f405b))
* **targetticket,targetchange:** duplicate actors run twice ([f80307f](https://github.com/pluginsglpi/formcreator/commit/f80307f))
* **targetticket,targetchange:** escape text fields of targets ([df0855d](https://github.com/pluginsglpi/formcreator/commit/df0855d))
* **targetticket,targetchange:** escape text fields of targets ([559424e](https://github.com/pluginsglpi/formcreator/commit/559424e))
* **targetticket,targetchange:** inverted show / hide for urgenty settings ([9be5e3a](https://github.com/pluginsglpi/formcreator/commit/9be5e3a))
* **targetticket,targetchange:** update constants for due date resolution in JS code ([4ddb6e8](https://github.com/pluginsglpi/formcreator/commit/4ddb6e8))
* **textarea:** better file uplaods handling ([0a163d5](https://github.com/pluginsglpi/formcreator/commit/0a163d5))
* **textarea:** image paste on multiple pages ([da30f86](https://github.com/pluginsglpi/formcreator/commit/da30f86))
* **textarea:** workaround GLPI bug in 9.4.5 ([8de4358](https://github.com/pluginsglpi/formcreator/commit/8de4358)), closes [#1613](https://github.com/pluginsglpi/formcreator/issues/1613)
* **textareafield:** handle with constant the availability of [#6939](https://github.com/pluginsglpi/formcreator/issues/6939) for GLPI ([258ad70](https://github.com/pluginsglpi/formcreator/commit/258ad70))
* **textareafield:** have file uploads work without file drop area ([4e5ed45](https://github.com/pluginsglpi/formcreator/commit/4e5ed45))
* **textareafield:** visual glitches ([b414ee0](https://github.com/pluginsglpi/formcreator/commit/b414ee0))
* **timefield:** assign a default value ([029c33f](https://github.com/pluginsglpi/formcreator/commit/029c33f))
* **timefield:** assign a default value ([2b57477](https://github.com/pluginsglpi/formcreator/commit/2b57477))
* avoid caps in filenames ([918a88d](https://github.com/pluginsglpi/formcreator/commit/918a88d))
* compatibility with GLPI 9.5 ([a49a6e0](https://github.com/pluginsglpi/formcreator/commit/a49a6e0))
* keep the user in the service catalog ([8648807](https://github.com/pluginsglpi/formcreator/commit/8648807))
* remove code left for debug ([351a36b](https://github.com/pluginsglpi/formcreator/commit/351a36b))


### Features

* ad menu icon for GLPI 9.5 ([6a8c361](https://github.com/pluginsglpi/formcreator/commit/6a8c361))
* compatibiliy with glpi 9.5 ([20ca8e0](https://github.com/pluginsglpi/formcreator/commit/20ca8e0))
* **form,question:** mandatory check in browser ([bfe91dc](https://github.com/pluginsglpi/formcreator/commit/bfe91dc))
* **section:** make section title required ([6b90673](https://github.com/pluginsglpi/formcreator/commit/6b90673))
* **wizard:** always show saved searches menu item ([1ffcc10](https://github.com/pluginsglpi/formcreator/commit/1ffcc10))



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
