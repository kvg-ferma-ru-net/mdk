<?php

namespace Innokassa\MDK\Exceptions;

class TransferException extends BaseException
{
    const CODE_401 = 'Неверный actor_id или actor_token';

    const CODE_402 = 'Необходима оплата для совершения запроса';

    const CODE_403 = 'Актор, от лица которого совершается запрос, деактивирован';

    const CODE_404 = 'Указанная группа касс не существует или недоступна для актора либо чек с таким receipt_id не существует у данной группы касс';

    const CODE_406 = 'Этот запрос невозможен для группы касс с данным типом';

    const CODE_409 = 'Чек с таким же receipt_id уже существует';

    const CODE_422 = 'Ошибка ... исключительная ситуация, мы уже работаем над решением';

    const CODE_500 = 'Внутренняя ошибка сервера';

    const CODE_503 = 'Сервер не может обработать запрос в данный момент';

    //######################################################################

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);

        switch($code)
        {
            case 400:
                if($aBody = json_decode($message, true))
                {
                    $sType = implode(" > ", $aBody["type"]);
                    $sPath = $aBody["path"];
                    $sPath = (strlen($sPath) > 1 ? " - $sPath" : "");
                    $this->message = "$sType: ".$aBody["desc"].$sPath;
                }
                else
                    $this->message = $message;
            break;
            case 401:
                $this->message = static::CODE_401; 
            break;
            case 402:
                $this->message = static::CODE_402; 
            break;
            case 403:
                $this->message = static::CODE_403; 
            break;
            case 404:
                $this->message = static::CODE_404;
            break;
            case 406:
                $this->message = static::CODE_406;
            break;
            case 409:
                $this->message = static::CODE_409;
            break;
            case 422:
                $this->message = static::CODE_422;
            break;
            case 500:
                $this->message = static::CODE_500;
            break;
            case 503:
                $this->message = static::CODE_503;
            break;
            default:
                $this->message = $message;
                break;
        }

        //$this->message = $code.' => '.$this->message;
    }
}
