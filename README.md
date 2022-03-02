[![pipeline status](http://git.innokassa.ru/Byurrer/mdk/badges/main/pipeline.svg)](http://git.innokassa.ru/Byurrer/mdk/-/commits/main) [![coverage report](http://git.innokassa.ru/Byurrer/mdk/badges/main/coverage.svg)](http://git.innokassa.ru/Byurrer/mdk/-/commits/main) [![Latest Release](http://git.innokassa.ru/Byurrer/mdk/-/badges/release.svg)](http://git.innokassa.ru/Byurrer/mdk/-/releases)

![Innokassa MDK](/logo.png)

# Innokassa Module Development Kit

**Innokassa MDK (Module Development Kit)** - набор программных средств на PHP для использования API облачной кассы [Pangaea V2](https://api.kassavoblake.com/v2/docs/pangaea_api.html) от [Innokassa](https://innokassa.ru/), содержащий в себе всю необходимую логику для фискализации заказов интернет-магазинов (ИМ). Для работы библиотеки требуется PHP версии не ниже 7.1 с библиотекой curl.

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
        * [Manual](#manual)
        * [Printer](#printer)
        * [Pipeline](#pipeline)
    * [Обработка ошибок](#обработка-ошибок)
    * [Логи](#логи)
* [Разработка](#разработка)
* [Issues & Contributing](#issues-и-contributing)
* [License](#license)

## Установка

```
git clone https://git.innokassa.ru/Byurrer/mdk.git
```

## Использование

В проектах не использующих `composer` необходимо подключить [автозагрузку классов](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md):
```php
include_once('mdk/src/autoload.php');
```

### Реализация на стороне клиента

Перед использованием необходимо реализовать на стороне клиента:
* [SettingsInterface](/src/Settings/SettingsInterface.php) - получение настроек
* [ReceiptStorageInterface](/src/Storage/ReceiptStorageInterface.php) - хранилище данных чеков
* [ReceiptAdapterInterface](/src/Entities/ReceiptAdapterInterface.php) - адаптер чеков под заказы, чтобы сервис `Automatic` мог формировать чек из заказа

Для сериализации/десериализации данных чеков `БД`<=>`MDK` существует базовый конвертер [ConverterStorage](/src/Storage/ConverterStorage.php). При необходимости можно создать новую реализацию [ConverterAbstract](/src/Entities/ConverterAbstract.php).

Для взаимодействия с сервером фискализации используется [NetClientCurl](/src/Net/NetClientCurl.php). При необходимости можно создать новую реализацию [NetClientInterface](/src/Net/NetClientInterface.php).

### Клиент

В базовом варианте использование `MDK` осуществляется через класс `Client`, посредством получения сервисов и лишь в редких случаях через компоненты ([например при проверке настроек](#настройки)).

[Клиент API Pangaea V2](/src/Client.php) состоит из:
* [сервисов](/src/Services/) (для каждого сервиса существует базовая реализация):
    * [Autmotaic](/src/Services/AutomaticInterface.php) - автоматическая фискализация приходов заказов для выдачи чеков без вмешательства администратора ИМ, например при оплате заказа покупателем
    * [Manual](/src/Services/ManualInterface.php) - ручная фискализация заказов (приход, расход) для осуществления дополнительных расчетов, например в случае измнения заказа после оформления, а также для осуществления возврата (возвратов)
    * [Pipeline](/src/Services/PipelineInterface.php) - обработка очереди чеков когда сервер фискализации пробивает чеки не сразу
    * [Connector](/src/Services/ConnectorInterface.php) - проверка соответсвия введенных настроек на соответсвие данным на кассе
    * [Printer](/src/Services/PrinterInterface.php) - печать чеков (получение ссылки на электронный чек), для показа ссылки на чек администратору ИМ
* компонентов (для дополнительного взаимодействия с MDK):
    * [Settings](/src/Settings/SettingsInterface.php)
    * [Adapter](/src/Entities/ReceiptAdapterInterface.php)
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

$transfer = new Transfer(
    new NetClientCurl(), 
    new ConverterApi(), 
    $settings->getActorId(), 
    $settings->getActorToken(), 
    $settings->getCashbox(),
    $logger
);

// создание сервисов
$automatic = new AutomaticBase($settings, $storage, $transfer, $adapter);
$manual = new ManualBase($storage, $transfer, $settings);
$pipeline = new PipelineBase($storage, $transfer);
$printer = new PrinterBase($storage, $transfer);
$connector = new ConnectorBase($transfer);

// создание клиента
$checkoutClient = new Client(
    $settings, 
    $adapter, 
    $storage,
    $automatic, 
    $manual, 
    $pipeline, 
    $printer, 
    $connector,
    $logger
);
```

#### Настройки

> Минимальный список настроек содержится в файле [SettingsInterface](/src/Settings/SettingsInterface.php).

Перед сохранением настроек необходимо проверить корректность введенных данных на соответствие данным на кассе:
```php
// $settings - ассоциативный массив новых настроек
try {
    $transfer = new Transfer(
        new NetClientCurl(), 
        new ConverterApi(), 
        $settings['actor_id'], 
        $settings['actor_token'], 
        $settings['cashbox']
    );
    $conn = new ConnectorBase($transfer);
    $conn->testSettings(new SettingsConcrete($settings));
} catch(SettingsException $e) {
    throw new Exception($e->getMessage());
}
```

#### Automatic

> Автоматическая фискализация действует в контексте конкретного заказа. Если по заказу были созданы чеки вручную, тогда автоматическая фискализация для данного заказа будет отключена.

Пример использования ([список исключений](/src/Services/AutomaticInterface.php)):
```php
try {
    $automatic = $checkoutClient->serviceAutomatic();

    // автоматическое определение типа чека
    $automatic->fiscalize($idOrder);

    // указание конкретного типа чека, например полный расчет в момент передачи товара покупателю
    // automatic->fiscalize($idOrder, ReceiptSubType::FULL);
} catch(Exception $e) {
    throw $e;
}
```

Интеграция с `MDK` может предусматривать:
* конкретные типы создаваемых чеков:
    * для предоплаты `automatic->fiscalize($idOrder, ReceiptSubType::PRE)`
    * для полного расчета `automatic->fiscalize($idOrder, ReceiptSubType::FULL)`
* автоматическое определение типа чека на основании настроек `$automatic->fiscalize($idOrder)`

#### Manual

Для ручной фискализации нужно самостоятельно сформировать:
* [items](/src/Collections/ReceiptItemCollection.php) - коллекцию [позиций](/src/Entities/ReceiptItem.php) к возврату
* [notify](/src/Entities/Primitives/Notify.php) - объект контактов покупателя
* [amount](/src/Entities/Primitives/Amount.php) - объект данных об оплате, если не передать данные тогда вся сумма позиций будет в счет `Amount::CASHLESS`.

Пример ручной фискализации прихода:
```php
try {
    $manual = $checkoutClient->serviceManual();
    $receipt = $manual->fiscalize($idOrder, $items, $notify);
} catch(Exception $e) {
    throw $e;
}
```

Аналогичным образом оформляется возврат:
```php
try {
    $manual = $checkoutClient->serviceManual();
    $receipt = $manual->refund($idOrder, $items, $notify);
} catch(Exception $e) {
    throw $e;
}
```

Однако, внутри сервиса происходит вычисление возможности осуществить возврат, если оставшаяся сумма приходов по данному заказу >= сумме возврата, тогда будет осуществлен возврат, иначе будет выброшено исключение `ManualException`.

> Интеграция должна предоставлять интерфейс для ручного формирования чека

#### Printer

Пример получения ссылки на рендер чека:
```php
$printer = $checkoutClient->servicePrinter();

// получение ссылки на чек без валидации
$link = $printer->getLinkRaw($receipt);

try {
    // получение ссылки на рендер чека с проверкой на существование чека и факт его пробития
    $link = $printer->getLinkVerify($idReceipt);
} catch(PrinterException $e) {

}
```

#### Pipeline

Сервер фискализации может не сразу пробить чек по разным причинам, но может принять его. После чего необходимо узнать текущий статус чеков. Эту задачу решает `Pipeline` ([PipelineBase](/src/Services/PipelineBase.php) базовая реализация [PipelineInterface](/src/Services/PipelineInterface.php)), методы которого должны запускаться (каждый в отдельном экземпляре) в планировщике задач (например в `cron`), желательно каждые 10 минут.

Существует 2 вида [статусов чека](/src/Entities/Atoms/ReceiptStatus.php) (2 метода `Pipeline`):
* `accepted` - приняты сервером (`WAIT` | `ASSUME`), но еще не пробились
* `unaccepted` - не были приняты сервером по причинам отказа доступа или связи с сервером (`PREPARED` | `REPEAT`)

Пример:
```php
$pipeline = $checkoutClient->servicePipeline();

// обновление статусов принятых чеков
$pipeline->updateAccepted();

// обновление статусов непринятых чеков
$pipeline->updateUnaccepted();
```

### Обработка ошибок

Сервисы и компоненты могут выбрасывать исключения, это однозначно означает что **операция не удалась и с теми же данными не пройдет**. Каждый объект выбрасывает свойственные ему исключения (подробнее к исходном коде интерфейсов/классов).

Ответсвенность за обработку исключений ложится на клиентский код.

Рекомендации:
* при ручных действиях (ручная фискализация, проверка настроек и прочее) показывать ошибку через интерфейс
* при автоматических действиях оповещать пользователя интеграции через email или другими доступными средствами

### Логи

Для логирования используются файловые логи [LoggerFile](/src/Logger/LoggerFile.php) интерфейса [LoggerInterface](/src/Logger/LoggerInterface.php), хранимые в директории `logs`, которая должна быть доступна извне для анализа работы `MDK`. 

Логи применяются в классе [Transfer](/src/Net/Transfer.php) для хранения истории взаимодействия с сервером фискализации.

Клиентский код также может использовать логирование предоставляемое `MDK`.

## Разработка

> Для разработки потребуется `docker` и `docker-compose`

Репозиторий содержит [docker-compose-dev.yml](/docker-compose-dev.yml) для организации среды разработки `MDK`, состоит из двух контейнеров:
* `mdk-php-dev` - основан на [php:7.3-cli](https://hub.docker.com/_/php) с модификациями, внутри используется `xdebug` для отладки и `composer` для установки `phpunit`
* `mdk-mysql-dev` - основан [mysql:5.7](https://hub.docker.com/_/mysql) без модификаций (логин:пароль от БД root:root)

Запуск контейнеров:
```bash
docker-compose -f docker-compose-dev.yml up
```

После запуска будет развернута изолированная среда со всем необходимым ПО для разработки `MDK`.

Для `VS Code` есть вспомогательные инструменты:
* отладчик [PHP Debug](https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug), настройки которого можно найти в [launch.json](/.vscode/launch.json) послеовательность действий:
    * запускается отладчик в редакторе
    * в docker контейнере запускается нужный скрипт, например `docker exec -it mdk-php-dev /bin/bash -c "php -f file.php"`
    * отладчик в редакторе получает отладочные данные из контейнера
* задания  [tasks.json](/.vscode/tasks.json) (задания запускаются в `docker` контейнере):
    * Run unit tests all - запуск всех unit тестов
    * Run unit test current file - запуск текущего тестового скрипта на тестирование

> Рекомендуемые расширения для `VS Code` [intelephense](https://marketplace.visualstudio.com/items?itemName=bmewburn.vscode-intelephense-client), [phpcs](https://marketplace.visualstudio.com/items?itemName=ikappas.phpcs), нужные настройки для них подгрузятся из конфига в репозитории

## Issues и Contributing

Если при использовании библиотеки у вас возникли проблемы, вы можете составить `Issue`.

Вы можете предложить свои изменения исходного кода, через `Issue`/`Pull request`. Они будут рассмотрены и приняты/отклонены или проигнорированы если в этих изменениях нет необходимости в данный момент.

Каждые внесенные изменения должны быть протестированы, а соответствующие тесты должны быть внесены в директорию с тестами.

Процент покрытия `unit` тестами должен составлять >= 98%

## License

MIT
