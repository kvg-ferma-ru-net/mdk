#!/bin/bash

docker compose exec -T backend /bin/bash -c 'composer run cs' \
&& docker compose exec -T backend /bin/bash -c 'composer run lint' \
&& docker compose exec -T backend /bin/bash -c 'composer run test-unit' \
&& docker compose exec -T backend /bin/bash -c 'composer run test-server' \
&& docker compose exec -T backend /bin/bash -c 'php -f wait-db.php' \
&& docker compose exec -T backend /bin/bash -c 'composer run test-system'
