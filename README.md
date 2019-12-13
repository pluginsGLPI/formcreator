# Form Creator

![GLPI Banner](https://user-images.githubusercontent.com/29282308/31666160-8ad74b1a-b34b-11e7-839b-043255af4f58.png)

[![License GPL 3.0](https://img.shields.io/badge/License-GPL%203.0-blue.svg)](https://github.com/pluginsGLPI/formcreator/blob/master/LICENSE.md)
[![Telegram GLPI](https://img.shields.io/badge/Telegram-GLPI-blue.svg)](https://t.me/glpien)
[![IRC Chat](https://img.shields.io/badge/IRC-%23GLPI-green.svg)](http://webchat.freenode.net/?channels=GLPI)
[![Follow Twitter](https://img.shields.io/badge/Twitter-GLPI%20Project-26A2FA.svg)](https://twitter.com/GLPI_PROJECT)
[![Join the chat at https://gitter.im/TECLIB/formcreator](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/TECLIB/formcreator?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Project Status: Active](http://www.repostatus.org/badges/latest/active.svg)](http://www.repostatus.org/#active)
[![Conventional Commits](https://img.shields.io/badge/Conventional%20Commits-1.0.0-yellow.svg)](https://conventionalcommits.org)

Extend GLPI with Plugins.

## Table of Contents

* [Synopsis](#synopsis)
* [Build Status](#build-status)
* [Documentation](#documentation)
* [Versioning](#versioning)
* [Contact](#contact)
* [Professional Services](#professional-services)
* [Build from source](#build-from-source)
* [Contribute](#contribute)
* [Copying](#copying)

## Synopsis

Formcreator is a plugin which allow creation of custom forms of easy access.
At the same time, the plugin allow the creation of one or more tickets when the form is filled.

### Features

1. Direct access to forms self-service interface in main menu
2. Highlighting forms in homepages
3. Access to forms controlled: public access, identified user access, restricted access to some profiles
4. Simple and customizable forms
5. Forms organized by categories, entities and languages.
6. Questions of any type of presentation: Textareas, lists, LDAP, files, etc.
7. Questions organised in sections. Choice of the display order.
8. Possibility to display a question based on certain criteria (response to a further question)
9. A sharp control on responses from forms: text, numbers, size of fields, email, mandatory fields, regular expressions, etc.
10. Creation of one or more tickets from form answers
11. Adding a description per fields, per sections, per forms, entities or languages.
12. Formatting the ticket set: answers to questions displayed, tickets templates.
13. Preview form created directly in the configuration.
14. An optional service catalog to browse for forms and FAQ in an unified interface.

## Build Status

| **LTS** | **Bleeding Edge** |
|:---:|:---:|
| [![Build Status](https://travis-ci.org/pluginsGLPI/formcreator.svg?branch=master)](https://travis-ci.org/pluginsGLPI/formcreator) | [![Build Status](https://travis-ci.org/pluginsGLPI/formcreator.svg?branch=develop)](https://travis-ci.org/pluginsGLPI/formcreator) |

## Documentation

We maintain a detailed documentation of the project on the website, check the [How-tos](https://pluginsglpi.github.io/formcreator/howtos/) and [Development](https://pluginsglpi.github.io/formcreator/) section.

For more information you can visit [formcreator on the GLPI Plugins documentation](http://glpi-plugins.readthedocs.io/en/latest/formcreator/)

## Versioning

In order to provide transparency on our release cycle and to maintain backward compatibility, this project is maintained under [the Semantic Versioning guidelines](http://semver.org/). We are committed to following and complying with the rules, the best we can.

See [the tags section of our GitHub project](https://github.com/pluginsGLPI/formcreator/tags) for changelogs for each release version. Release announcement posts on [the official Teclib' blog](http://www.teclib-edition.com/en/communities/blog-posts/) contain summaries of the most noteworthy changes made in each release.

## Contact

For notices about major changes and general discussion of development, subscribe to the [/r/glpi](http://www.reddit.com/r/glpi) subreddit.
You can also chat with us via IRC in [#GLPI on freenode](http://webchat.freenode.net/?channels=GLPI) if you get stuck, and [@glpien on Telegram](https://t.me/glpien).

## Professional Services

The GLPI Network services are available through our [Partner's Network](http://www.teclib-edition.com/en/partners/). We provide special training, bug fixes with editor subscription, contributions for new features, and more.

Obtain a personalized service experience, associated with benefits and opportunities.

## Build from source

To build the plugin you need [Composer](http://getcomposer.org) and an internet access to download some resources from Github.

After dowloading the source of Formcreator, go in its folder and run the following
* composer install
* php vendor/bin/robo build:fa-data

## Contribute

Want to file a bug, contribute some code, or improve documentation? Excellent! Read up on our
guidelines for [contributing](https://github.com/pluginsGLPI/formcreator/blob/master/.github/CONTRIBUTING.md) and then check out one of our issues in the [Issues Dashboard](https://github.com/pluginsGLPI/formcreator/issues).

## Copying

* **Name**: [GLPI](http://glpi-project.org/) is a registered trademark of [Teclib'](http://www.teclib-edition.com/en/).
* **Code**: you can redistribute it and/or modify it under the terms of the GNU General Public License ([GPLv3](https://www.gnu.org/licenses/gpl-3.0.en.html)).
* **Documentation**: released under Attribution 4.0 International ([CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)).
