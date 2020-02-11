#!/bin/sh
  composer install --no-scripts
bin/console doctrine:migrations:migrate -n
bin/console doctrine:fixtures:load -n
php-fpm
