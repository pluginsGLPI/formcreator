#!/bin/sh
SELF=`readlink -f $0`
SELFDIR=`dirname $SELF`
DB=unit_test_01
PHP=`which php7.0`

oldpath=`pwd`
cd $SELFDIR/..
mysql -u glpi -pglpi -e "DROP DATABASE IF EXISTS \`$DB\`"
php ../../scripts/cliinstall.php --db=$DB --user=glpi --pass=glpi --lang=en_US --tests --force
#php tools/cliinstall.php --tests
php -S localhost:8088 -t ../.. ../../tests/router.php &
PID=$!
#echo $PID
echo "=== Install tests ==="
$PHP vendor/bin/atoum -ncc -bf tests/bootstrap.php -d tests/suite-install

