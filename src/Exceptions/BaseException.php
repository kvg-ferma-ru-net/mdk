<?php

namespace Innokassa\MDK\Exceptions;

use Exception;

class BaseException extends Exception
{
    public function toArray(): array
    {
        return [
            'code' => $this->getCode(),
            'message' => $this->getMessage()
        ];
    }
}
