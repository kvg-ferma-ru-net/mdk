#!/bin/bash

docker-compose build --build-arg user=runner --build-arg uid=1000
docker-compose up -d
docker-compose exec -T php composer install
docker-compose exec -T php php -f /opt/wait-db.php
