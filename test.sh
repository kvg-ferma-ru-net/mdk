#!/bin/bash

[ -d logs ] || mkdir logs -m=777
[ -d coverage-report-html ] || mkdir coverage-report-html -m=777
docker compose -f docker-compose.test.yml up -d --force-recreate --build
docker compose -f docker-compose.test.yml exec -T backend /bin/bash -c 'composer install'
docker compose -f docker-compose.test.yml exec -T backend /bin/bash -c 'composer run-script cs'
docker compose -f docker-compose.test.yml exec -T backend /bin/bash -c 'composer run-script lint'
docker compose -f docker-compose.test.yml exec -T backend /bin/bash -c 'composer run-script test-unit'
docker compose -f docker-compose.test.yml exec -T backend /bin/bash -c 'composer run-script test-server'
docker compose -f docker-compose.test.yml exec -T backend /bin/bash -c 'php -f wait-db.php'
docker compose -f docker-compose.test.yml exec -T backend /bin/bash -c 'composer run-script test-system'
docker compose down
