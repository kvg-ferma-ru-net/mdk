<?php

namespace Innokassa\MDK\Entities\Atoms;

use Innokassa\MDK\Entities\AtomAbstract;

/**
 * Статусы чека на основании ответов от сервера фискализации
 */
class ReceiptStatus extends AtomAbstract
{
    /** Чек подготовлен, но еще не отправлен */
    public const PREPARED  = 0;

    /** Нет ошибок, чек фискализирован */
    public const COMPLETED  = 1;

    /** Нет ошибок, ждем пока чек фискализируется, нужно проверить статус */
    public const WAIT       = 2;

    /** Нет ошибок, чек отправлен на сервер, надеемся на фискализацию,
     * но что там с чеком не известно, потому что сервер ответил некорректно,
     * нужно проверить статус
     */
    public const ASSUME     = 3;

    /** Возникли проблемы с доступом к кассе, но надо попробовать еще раз пробить чек */
    public const REPEAT     = 4;

    /** Ошибка фискализации */
    public const ERROR      = 5;

    /** Время фискализации чека истекло */
    public const EXPIRED    = 6;

    //######################################################################

    /**
     * @param integer $code код ответа сервера фискализации или одно из значений констант
     */
    public function __construct(int $code)
    {
        switch ($code) {
            case self::PREPARED:
                $this->code = self::PREPARED;
                $this->name = 'Чек подготовлен, но еще не отправлен';
                return;
            case self::COMPLETED:
                $this->code = self::COMPLETED;
                $this->name = 'Чек фискализирован';
                return;
            case self::WAIT:
                $this->code = self::WAIT;
                $this->name = 'Чек в очереди';
                return;
            case self::ASSUME:
                $this->code = self::ASSUME;
                $this->name = 'Чек отправлен на сервер';
                return;
            case self::REPEAT:
                $this->code = self::REPEAT;
                $this->name = 'Ошибка авторизации, помещен в отложенную очередь';
                return;
            case self::ERROR:
                $this->code = self::ERROR;
                $this->name = 'Ошибка фискализации';
                return;
            case self::EXPIRED:
                $this->code = self::EXPIRED;
                $this->name = 'Время фискализации чека истекло';
                return;
            default:
                break;
        }

        if ($code == 200 || $code == 201) {
            // все ОК
            $this->code = self::COMPLETED;
            $this->name = 'Чек фискализирован';
        } elseif ($code >= 202 && $code < 300) {
            // пробовать еще раз фискализировать с тем же КИ (чек принят сервером)
            $this->code = self::WAIT;
            $this->name = 'Чек в очереди';
        } elseif ($code >= 500 && $code < 600) {
            // пробовать еще раз фискализировать с тем же КИ (чек отправлен на сервер, но не известно что с там с ним)
            $this->code = self::ASSUME;
            $this->name = 'Чек отправлен на сервер';
        } elseif ($code == 401 || $code == 404) {
            // проблемы авторизации, надо попробовать фискализировать еще раз, но с большим периодом времени
            $this->code = self::REPEAT;
            $this->name = 'Ошибка авторизации, помещен в отложенную очередь';
        } else {
            // [400, 500) - ошибки, повторять фискализизацию с такими же данными нельзя
            $this->code = self::ERROR;
            $this->name = 'Ошибка фискализации';
        }
    }

    /**
     * @inheritDoc
     */
    public static function all(): array
    {
        $a = [];

        $a[] = new self(self::PREPARED);
        $a[] = new self(self::COMPLETED);
        $a[] = new self(self::WAIT);
        $a[] = new self(self::ASSUME);
        $a[] = new self(self::REPEAT);
        $a[] = new self(self::ERROR);
        $a[] = new self(self::EXPIRED);

        return $a;
    }
}
