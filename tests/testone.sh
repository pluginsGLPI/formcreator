#!/bin/sh
SELF=`readlink -f $0`
SELFDIR=`dirname $SELF`
DB=unit_test_01
PHP=`which php7.0`

oldpath=`pwd`
cd $SELFDIR/..
oldpath=`pwd`
cd $SELFDIR/..

echo "=== Unit tests ==="
if [ -f $1 ]; then
    RESOURCE_TYPE="-f"
elif [ -d $1 ]; then
    RESOURCE_TYPE="-d"
fi
$PHP vendor/bin/atoum --debug -bf tests/bootstrap.php -no-cc $RESOURCE_TYPE $1

