<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

define('TEST_ACTOR_ID', 'test');
define('TEST_ACTOR_TOKEN', '#K!-k(D7x[Ro_y40[|-X');
define('TEST_CASHBOX_WITHOUT_AGENT', '1');
define('TEST_CASHBOX_WITH_AGENT', '2');

define('PATH', dirname(dirname(dirname(__FILE__))));

require_once(PATH . "/vendor/autoload.php");

require_once(__DIR__ . "/ReceiptAdapterConcrete.php");
require_once(__DIR__ . "/ReceiptStorageConcrete.php");
require_once(__DIR__ . "/SettingsConcrete.php");
require_once(__DIR__ . "/db.php");
