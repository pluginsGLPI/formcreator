#!/bin/sh
SELF=`readlink -f $0`
SELFDIR=`dirname $SELF`
DBNAME=unit_test_01
PHP=php7.0
ATOUM=~/.config/composer/vendor/bin/atoum

oldpath=`pwd`
cd $SELFDIR/..
mysql -u glpi -pglpi -e "DROP DATABASE IF EXISTS \`$DBNAME\`"
$PHP ../../scripts/cliinstall.php --db=$DBNAME --user=glpi --pass=glpi --tests --force
#php -S localhost:8088 -t ../.. ../../tests/router.php &>/dev/null &
#PID=$!
#echo $PID

echo "=== Install tests ==="
$PHP $ATOUM --debug -bf atoum/bootstrap.php -no-cc --max-children-number 1 -d atoum/suite-install --no-cc

echo "=== Unit tests ==="
$PHP $ATOUM --debug -bf atoum/bootstrap.php -no-cc --max-children-number 1 -d atoum/suite-unit --no-cc

echo "=== Integration tests ==="
#$PHP $ATOUM --debug -bf atoum/bootstrap.php -no-cc --max-children-number 1 -d atoum/suite-integration

#echo "=== Functional tests ==="

echo "=== Uninstall tests ==="
$PHP $ATOUM --debug -bf atoum/bootstrap.php -no-cc --max-children-number 1 -d atoum/suite-uninstall --no-cc
cd $oldpath
#kill $PID

#vendor/bin/phpcbf -p --standard=vendor/glpi-project/coding-standard/GlpiStandard/ *.php install/ inc/ front/ ajax/ tests/