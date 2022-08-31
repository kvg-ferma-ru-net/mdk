#!/bin/bash

docker compose build --build-arg user=runner --build-arg uid=1000
docker compose up -d
docker compose exec -T backend composer install
docker compose exec -T backend php -f /opt/wait-db.php
