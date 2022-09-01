#!/bin/bash

[ -d logs ] || mkdir logs -m=777
[ -d coverage-report-html ] || mkdir logs -m=777
docker compose -f docker-compose.test.yml up -d --force-recreate --build
docker compose exec -T backend /bin/bash -c 'composer install'
docker compose exec -T backend /bin/bash -c 'composer run-script cs'
docker compose exec -T backend /bin/bash -c 'composer run-script lint'
docker compose exec -T backend /bin/bash -c 'composer run-script test-unit'
docker compose exec -T backend /bin/bash -c 'composer run-script test-server'
docker compose exec -T backend /bin/bash -c 'php -f wait-db.php'
docker compose exec -T backend /bin/bash -c 'composer run-script test-system'
docker compose down
