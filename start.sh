#!/bin/bash

[ -d logs ] || mkdir logs -m=777
[ -d coverage-report-html ] || mkdir coverage-report-html -m=777
docker compose up --force-recreate --build -d
