#!/bin/bash

composer install
php -f wait-db.php
while true; do sleep 10; done;
