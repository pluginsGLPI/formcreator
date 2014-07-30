#!/bin/bash

# Only strings with domain specified are extracted (use Xt args of keyword param to set number of args needed)

xgettext *.php */*.php --copyright-holder='Resources Development Team' --package-name='GLPI - Resources plugin' --package-version='1.5.0' -o locales/glpi.pot -L PHP --add-comments=TRANS --from-code=UTF-8 --force-po  \
	--keyword=_n:1,2,4t --keyword=__s:1,2t --keyword=__:1,2t --keyword=_e:1,2t --keyword=_x:1c,2,3t \
	--keyword=_ex:1c,2,3t --keyword=_nx:1c,2,3,5t --keyword=_sx:1c,2,3t



