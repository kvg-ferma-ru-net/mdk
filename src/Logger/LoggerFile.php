<?php

namespace Innokassa\MDK\Logger;

/**
 * Реализация LoggerInterface на файлах
 */
class LoggerFile implements LoggerInterface
{
    /** Директория логов */
    public const DIR = __DIR__ . '/../../logs';

    /** Признак окончания одной сессии */
    public const EOL = '####################';

    //######################################################################

    public function __construct()
    {
        $dir = dirname(dirname(__DIR__)) . '/logs/' . date("Y-m");
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->file = $dir . '/' . date("Y-m-d") . ".txt";
    }

    /**
     * Получить файл логов
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Получить массив последнего сообщения
     *
     * @return array
     */
    public function getLastMsg(): array
    {
        return $this->last;
    }

    /**
     * @inheritDoc
     */
    public function log(int $level, string $message, array $context = []): void
    {
        $a = [];
        $a['level'] = LogLevel::getName($level);
        $a['message'] = $message;

        if ($context) {
            $a['context'] = print_r($context, true);
        }

        if ($this->canStacktrace($level)) {
            $a['stacktrace'] = print_r($this->stacktrace(), true);
        }

        $this->last = $a;

        $msg = '';
        foreach ($a as $key => $value) {
            $msg .= "$key: $value\n";
        }

        file_put_contents(
            $this->file,
            '[' . date("Y-m-d H:i:s") . "]\n" . $msg . self::EOL . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $file = '';
    private $last = [];

    //######################################################################

    /**
     * Трассировка стека вызовов
     *
     * @return array линейный массив строк с вызовами
     */
    private function stacktrace(): array
    {
        $a = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);
        $a2 = [];

        for ($i = 1; $i < count($a); ++$i) {
            $value = $a[$i];
            $a2[] = $value['file'] . ':' . $value['line'] . ' - ' . $value['class'] . '::' . $value['function'];
        }

        return $a2;
    }

    /**
     * Нужно ли выводить трассировку стека
     *
     * @param integer $level уровень сообщения
     * @return boolean
     */
    private function canStacktrace(int $level)
    {
        switch ($level) {
            case LogLevel::ERROR:
                return true;
            default:
                return false;
        }
    }
}
