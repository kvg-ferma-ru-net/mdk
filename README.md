
![Innokassa MDK](/logo.png)

# Innokassa Module Development Kit

**Innokassa MDK (Module Development Kit)** - набор программных средств на PHP для использования API облачной кассы [Pangaea V2](https://api.innokassa.ru/v2/doc) от [Innokassa](https://innokassa.ru/), содержащий в себе всю необходимую логику для фискализации заказов интернет-магазинов (ИМ), с поддержкой мультисайтовости. 

Для работы библиотеки требуется `PHP` версии не ниже `7.1` с библиотекой `curl`.

Описание:
* ОО стиль - все есть объект 
* использует [PSR-4](https://www.php-fig.org/psr/psr-4/) автозагрузку классов
* применяется стардарт [PSR12](https://www.php-fig.org/psr/psr-12/)
* оснащен `Unit`/`Server`/`System` тестами [PHPUnit](https://phpunit.readthedocs.io/ru/latest/)
* не имеет зависимостей - концепция многократного использования без зависмостей, поэтому можно использовать в без-`composer`'ной среде

## Оглавление
* [Установка](#установка)
* [Использование](#использование)
    * [Реализация на стороне клиента](#реализация-на-стороне-клиента)
    * [Клиент](#клиент)
        * [Инициализация](#инициализация)
        * [Настройки](#настройки)
        * [Automatic](#automatic)
        * [Pipeline](#pipeline)
    * [Обработка ошибок](#обработка-ошибок)
    * [Логи](#логи)
* [Разработка](#разработка)
* [Issues & Contributing](#issues-и-contributing)
* [License](#license)

## Установка

Клонирование репозитория:
```
git clone https://git.innokassa.ru/Byurrer/mdk.git
```

Через `composer`:
```
composer require innokassa/mdk
```

## Использование

В проектах не использующих `composer` необходимо подключить [автозагрузку классов](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md):
```php
require_once('mdk/src/autoload.php');
```

### Реализация на стороне клиента

Перед использованием необходимо реализовать на стороне клиента:
* [SettingsAbstract](/src/Settings/SettingsAbstract.php) - получение настроек
* [ReceiptStorageInterface](/src/Storage/ReceiptStorageInterface.php) - хранилище данных чеков
* [ReceiptAdapterInterface](/src/Entities/ReceiptAdapterInterface.php) - адаптер чеков под заказы, чтобы сервис `Automatic` мог формировать чек из заказа
* [ReceiptIdFactoryInterface](/src/Entities/ReceiptId/ReceiptIdFactoryInterface.php) - фабрика идентификаторов чеков, есть базовая реализация [ReceiptIdFactoryMeta](/src/Entities/ReceiptId/ReceiptIdFactoryMeta.php), в которой необходимо переопределить метод `getEngine`

Для сериализации/десериализации данных чеков `БД`<=>`MDK` существует базовый конвертер [ConverterStorage](/src/Storage/ConverterStorage.php). При необходимости можно создать новую реализацию [ConverterAbstract](/src/Entities/ConverterAbstract.php).

Для взаимодействия с сервером фискализации используется [NetClientCurl](/src/Net/NetClientCurl.php). При необходимости можно создать новую реализацию [NetClientInterface](/src/Net/NetClientInterface.php).

### Клиент

В базовом варианте использование `MDK` осуществляется через класс `Client`, посредством получения сервисов и лишь в редких случаях через компоненты ([например при проверке настроек](#настройки)).

[Клиент API Pangaea V2](/src/Client.php) состоит из:
* [сервисов](/src/Services/) (для каждого сервиса существует базовая реализация):
    * [Autmotaic](/src/Services/AutomaticInterface.php) - автоматическая фискализация приходов заказов для выдачи чеков без вмешательства администратора ИМ, например при оплате заказа покупателем
    * [Pipeline](/src/Services/PipelineInterface.php) - обработка очереди чеков когда сервер фискализации пробивает чеки не сразу
    * [Connector](/src/Services/ConnectorInterface.php) - проверка введенных настроек на соответсвие данным на кассе
* компонентов (для дополнительного взаимодействия с MDK):
    * [Settings](/src/Settings/SettingsAbstract.php)
    * [Storage](/src/Storage/ReceiptStorageInterface.php)
    * [Logger](/src/Logger/LoggerInterface.php)

#### Инициализация

После того как все необходимые интерфейсы реализованы можно создавать объект `Client`:
```php
// создание компонентов
$settings = new SettingsConcrete();
$storage = new ReceiptStorageConcrete(new ConverterStorage());
$adapter = new ReceiptAdapterConcrete();
$logger = new LoggerFile();
$receiptIdFactory = new ReceiptIdFactoryMetaConcrete();

$transfer = new Transfer(
    new NetClientCurl(),
    new ConverterApi(),
    $logger
);

// создание сервисов
$automatic = new AutomaticBase(
    $settings,
    $storage,
    $transfer,
    $adapter,
    $receiptIdFactory
);
$pipeline = new PipelineBase($settings, $storage, $transfer, $receiptIdFactory);
$connector = new ConnectorBase($transfer);

// создание клиента
$mdk = new Client(
    $settings,
    $storage,
    $automatic,
    $pipeline,
    $connector,
    $logger
);
```

#### Настройки

Перед сохранением настроек необходимо проверить корректность введенных данных на соответствие данным на кассе:
```php
// ассоциативный массив новых настроек введенных пользователем в интерфейсе сайта
$settings = [
    'actor_id' => 'actor_id',
    'actor_token' => 'actor_token',
    'cashbox' => 'cashbox',
    'site' => 'https://example.com/',
    'taxation' => Taxation::USN,
    'scheme' => SettingsInterface::SCHEME_PRE_FULL,
    'vat_shipping' => Vat::CODE_WITHOUT,
    'type_default_items' => ReceiptItemType::PRODUCT,
    'vat_default_items' => Vat::CODE_WITHOUT,
    'order_status_receipt_pre' => 'payed',
    'order_status_receipt_full' => 'delivered'
];

try {
    // формирование трансфера с указанием минимальных данных для соединения
    $transfer = new Transfer(
        new NetClientCurl(),
        new ConverterApi(),
        new LoggerFile()
    );

    // создание коннектора и тестирование настроек
    $conn = new ConnectorBase($transfer);
    $conn->testSettings(new SettingsConcrete($settings));
} catch(SettingsException $e) {
    throw new Exception($e->getMessage());
}
```

#### Automatic

> Автоматическая фискализация действует в контексте конкретного заказа.

Пример использования ([список исключений](/src/Services/AutomaticInterface.php), [типы чеков](/src/Entities/Atoms/ReceiptSubType.php)):
```php
try {
    $automatic = $mdk->serviceAutomatic();

    // автоматическое определение типа чека
    $automatic->fiscalize($idOrder);

    // указание конкретного типа чека, например полный расчет в момент передачи товара покупателю
    // automatic->fiscalize($idOrder, 's1', ReceiptSubType::FULL);
} catch(Exception $e) {
    throw $e;
}
```

#### Pipeline

Сервер фискализации может не сразу пробить чек по разным причинам, например ответив кодом 202. После чего необходимо узнать текущий статус чеков. Эту задачу решает `Pipeline` ([PipelineBase](/src/Services/PipelineBase.php) базовая реализация [PipelineInterface](/src/Services/PipelineInterface.php)), методы которого должны запускаться (каждый в отдельном экземпляре) в планировщике задач (например через `cron`), желательно каждые 10 минут.

Существует несколько [статусов чека](/src/Entities/Atoms/ReceiptStatus.php), но все чеки со статусом подлежащим фискализации обработаются вместе.

Пример:
```php
$pipeline = $mdk->servicePipeline();

// обновление статусов чеков
$pipeline->update();
```

> `Pipeline` также обрабатывает 50x коды ответов, ответы подобного рода не должны быть, но для предсказуемости введена обработка.

### Обработка ошибок

Сервисы и компоненты могут выбрасывать исключения, это однозначно означает что **операция не удалась и с теми же данными не пройдет**, за исключением проблем со связью. Каждый объект выбрасывает свойственные ему исключения (подробнее в исходном коде интерфейсов/классов).

Ответсвенность за обработку исключений ложится на клиентский код.

Рекомендации:
* при автоматических действиях оповещать пользователя интеграции через email или другими доступными средствами

### Логи

Для логирования используются файловые логи [LoggerFile](/src/Logger/LoggerFile.php) интерфейса [LoggerInterface](/src/Logger/LoggerInterface.php), хранимые в директории `logs`. 

Логи применяются в классе [Transfer](/src/Net/Transfer.php) для хранения истории взаимодействия с сервером фискализации.

## Разработка

> Для разработки потребуется `docker compose`

Репозиторий содержит [docker-compose.dev.yml](/docker-compose.dev.yml) для организации среды разработки `MDK`, состоит из двух контейнеров:
* `mdk-backend` - основан на [php:7.1-cli](https://hub.docker.com/_/php) с модификациями, внутри используется `xdebug` (2.8.1) для отладки и `composer` (2.2) для установки зависимостей разработки
* `mdk-db` - основан [mysql:5.7](https://hub.docker.com/_/mysql) без модификаций (логин:пароль от БД `root`:`root`)

Запуск контейнеров:
```bash
./dev.sh
```

После запуска будет развернута изолированная среда со всем необходимым ПО для разработки `MDK`.

Запуск тестов исходного кода `MDK`:
```bash
./test.sh
```

Рекомендуемые расширения для `VS Code` (нужные настройки подгрузятся из конфига в репозитории):
* [PHP Debug](https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug)
* [intelephense](https://marketplace.visualstudio.com/items?itemName=bmewburn.vscode-intelephense-client)
* [phpcs](https://marketplace.visualstudio.com/items?itemName=ikappas.phpcs)
* [phpstan](https://marketplace.visualstudio.com/items?itemName=swordev.phpstan)

## Issues и Contributing

Если при использовании библиотеки у вас возникли проблемы, вы можете составить `Issue`.

Вы можете предложить свои изменения исходного кода, через `Issue`/`Pull request`.

Каждые внесенные изменения должны быть протестированы, а соответствующие тесты должны быть внесены в директорию с тестами.

Процент покрытия `unit` тестами должен составлять >= 98%

## License

MIT
