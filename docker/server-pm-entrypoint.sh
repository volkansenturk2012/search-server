#!/bin/bash

rm -Rf /var/www/apisearch/var/cache
php /var/www/apisearch/bin/console cache:warmup --env=prod --no-debug --no-interaction
php /var/www/apisearch/bin/console apisearch-server:server-configuration --env=prod --no-debug --no-interaction
php /var/www/apisearch/vendor/bin/ppm start --host=0.0.0.0 --port=8200 --workers=20 --bootstrap=Apisearch\\Server\\PPM\\Adapter --app-env=prod
