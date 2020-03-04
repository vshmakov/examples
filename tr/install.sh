#!/usr/bin/env sh
composer install
php bin/console d:d:c -e test
php bin/console d:m:m -n -e test
php bin/console d:f:l -n -e test
