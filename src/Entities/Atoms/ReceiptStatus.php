<?php

namespace Innokassa\MDK\Entities\Atoms;

use Innokassa\MDK\Entities\AtomAbstract;

/**
 * Статусы чека на основании ответов от сервера фискализации
 */
class ReceiptStatus extends AtomAbstract
{
    /** Чек подготовлен, но еще не отправлен */
    public const PREPARED   = 0;

    /** Нет ошибок, чек фискализирован */
    public const COMPLETED  = 1;

    /** Нет ошибок, ждем пока чек фискализируется, нужно проверить статус */
    public const ACCEPTED   = 2;

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
            case self::ACCEPTED:
                $this->code = self::ACCEPTED;
                $this->name = 'Чек в очереди';
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
        } elseif ($code == 202) {
            // чек принят сервером, нужно узнать статус
            $this->code = self::ACCEPTED;
            $this->name = 'Чек в очереди';
        } elseif ($code >= 500 && $code < 600) {
            // пробовать еще раз фискализировать с тем же КИ (чек отправлен на сервер, но не известно что с там с ним)
            $this->code = self::PREPARED;
            $this->name = 'Не удалось отправить на сервер';
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
        $a[] = new self(self::ACCEPTED);
        $a[] = new self(self::ERROR);
        $a[] = new self(self::EXPIRED);

        return $a;
    }
}
