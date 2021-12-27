<?php

namespace Innokassa\MDK\Net;

/**
 * Сетевой интерфейс для приема/передачи данных на/от сервера
 */
interface NetClientInterface
{
    /** Путь (string) */
    public const PATH = 1;

    /** Заголовки (array) */
    public const HEAD = 2;

    /** Тип (string - GET, POST etc) */
    public const TYPE = 3;

    /** Тело */
    public const BODY = 4;

    /** Код ответа (int) */
    public const CODE = 5;

    //######################################################################

    /**
     * Запись данных для сервера
     *
     * Обязательыне данные: PATH
     * Значения по умоланию:
     *  - HEAD = []
     *  - TYPE = 'GET'
     *  - BODY = ''
     *
     * @param integer $code
     * @param mixed $data
     * @return self
     */
    public function write(int $code, $data): self;

    /**
     * Отправка данных
     *
     * @throws NetConnectException проблемы соединения с сервером либо с настройками запроса
     * @return self
     */
    public function send(): self;

    /**
     * Прочитать данные от сервера
     *
     * @param integer $code
     * @return mixed
     */
    public function read(int $code);

    /**
     * Очистить данные для следующего запроса
     *
     * @return self
     */
    public function reset(): self;
}
