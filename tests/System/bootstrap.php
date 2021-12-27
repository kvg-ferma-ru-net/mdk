<?php

define('TEST_ACTOR_ID', '1234567');
define('TEST_ACTOR_TOKEN', 'zysx0gMMcg6TlcB0thWrPZBPp');
define('TEST_CASHBOX_WITHOUT_AGENT', '123456789');
define('TEST_CASHBOX_WITH_AGENT', '112233445');

define('PATH', dirname(dirname(dirname(__FILE__))));
require_once(PATH."/vendor/autoload.php");
require_once(PATH."/src/autoload.php");

require_once(__DIR__."/ReceiptAdapterConcrete.php");
require_once(__DIR__."/ReceiptStorageConcrete.php");
require_once(__DIR__."/SettingsConcrete.php");
require_once(__DIR__."/db.php");


