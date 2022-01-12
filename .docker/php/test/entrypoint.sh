#!/bin/bash

composer install

# запуск unit тестов
vendor/bin/phpunit --colors=always --coverage-text --bootstrap tests/Unit/bootstrap.php tests/Unit/

# ожидание соединения с БД
php -f /opt/wait-db.php

# запуск системных тестов
vendor/bin/phpunit --colors=always --bootstrap tests/System/bootstrap.php tests/System/
