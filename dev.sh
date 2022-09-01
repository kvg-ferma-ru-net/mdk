#!/bin/bash

[ -d logs ] || mkdir logs -m=777
[ -d coverage-report-html ] || mkdir logs -m=777
docker compose -f docker-compose.dev.yml up --force-recreate --build
