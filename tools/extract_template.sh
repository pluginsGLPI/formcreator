#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../"
pushd $DIR > /dev/null

NAME='Formcreator'
POTFILE=${NAME,,}.pot

PHP_SOURCES=`find ./ -name \*.php -not -path "./vendor/*" -not -path "./lib/*"`

if [ ! -d "locales" ]; then
    mkdir locales
fi

# Only strings with domain specified are extracted (use Xt args of keyword param to set number of args needed)
xgettext $PHP_SOURCES -o locales/$POTFILE -L PHP --add-comments=TRANS --from-code=UTF-8 --force-po  \
      --keyword=_n:1,2,4t --keyword=__s:1,2t --keyword=__:1,2t --keyword=_e:1,2t --keyword=_x:1c,2,3t --keyword=_ex:1c,2,3t \
      --keyword=_sx:1c,2,3t --keyword=_nx:1c,2,3,5t

popd > /dev/null