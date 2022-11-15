<?php

$pdo = null;
while (!$pdo) {
    try {
        $pdo = new PDO('mysql:host=db', 'root', 'root');
        echo "MySQL is available\n";
    } catch (Exception $e) {
        echo "MySQL is unavailable - sleeping\n";
        $pdo = null;
        sleep(1);
    }
}
