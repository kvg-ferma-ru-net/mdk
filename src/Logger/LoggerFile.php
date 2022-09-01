<?php

namespace Innokassa\MDK\Logger;

/**
 * Реализация LoggerInterface на файлах
 */
class LoggerFile implements LoggerInterface
{
    /**
     * Директория логов
     *
     * @param string $dir
     */
    public function __construct(string $dir = __DIR__ . '/../../logs')
    {
        $dirCurrDate = $dir . '/' . date("Y-m");
        if (!file_exists($dirCurrDate)) {
            mkdir($dirCurrDate, 0777, true);
        }

        $dirCurrDate = realpath($dirCurrDate);

        $this->file = $dirCurrDate . '/' . date("Y-m-d") . ".txt";
        if (!file_exists($this->file)) {
            file_put_contents($this->file, '');
            chmod($this->file, 0777);
        }
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
     * @return array<string, mixed>
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
            sprintf("[%s]\n%s\n", date("Y-m-d H:i:s"), $msg),
            FILE_APPEND | LOCK_EX
        );
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    /** @var string */
    private $file = '';

    /** @var array<string, mixed> */
    private $last = [];

    //######################################################################

    /**
     * Трассировка стека вызовов
     *
     * @return array<string> линейный массив строк с вызовами
     */
    private function stacktrace(): array
    {
        $a = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);
        $a2 = [];

        for ($i = 1; $i < count($a); ++$i) {
            $value = $a[$i];
            $a2[] = sprintf(
                "%s:%d - %d::%s",
                $value['file'],
                $value['line'],
                $value['class'],
                $value['function']
            );
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
