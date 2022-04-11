<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Exceptions\TransferException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Exceptions\TransferException
 */
class TransferExceptionTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Exceptions\TransferException::__construct
     */
    public function testConstruct()
    {
        $exception = new TransferException(
            '[{"type": "UNEXPECTED_FIELD", "desc": "Отправлено лишнее поле", "path": "$.notify[0].email"}]',
            400
        );
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(
            'UNEXPECTED_FIELD: Отправлено лишнее поле - $.notify[0].email',
            $exception->getMessage()
        );

        $exception = new TransferException(
            '[{"type": "MISSED_REQUIRED_FIELD", "desc": "Пропущено обязательное поле", "path": "$.items[0].amount"}]',
            400
        );
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(
            'MISSED_REQUIRED_FIELD: Пропущено обязательное поле - $.items[0].amount',
            $exception->getMessage()
        );

        $exception = new TransferException(
            '[{"type": "BAD_VALUE", "desc": "Ожидается тип `array`", "path": "$.notify"}]',
            400
        );
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(
            'BAD_VALUE: Ожидается тип `array` - $.notify',
            $exception->getMessage()
        );

        $exception = new TransferException(
            '[{"type": "UNAVAILABLE_VALUE", "desc": "Данное место расчетов недоступно этой группе касс", "path": "$.loc.billing_place"}]',
            400
        );
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(
            'UNAVAILABLE_VALUE: Данное место расчетов недоступно этой группе касс - $.loc.billing_place',
            $exception->getMessage()
        );


        $exception = new TransferException('error', 400);
        $this->assertSame(400, $exception->getCode());
        $this->assertSame('error', $exception->getMessage());

        $exception = new TransferException('', 401);
        $this->assertSame(401, $exception->getCode());
        $this->assertSame(TransferException::CODE_401, $exception->getMessage());

        $exception = new TransferException('', 402);
        $this->assertSame(402, $exception->getCode());
        $this->assertSame(TransferException::CODE_402, $exception->getMessage());

        $exception = new TransferException('', 403);
        $this->assertSame(403, $exception->getCode());
        $this->assertSame(TransferException::CODE_403, $exception->getMessage());

        $exception = new TransferException('', 404);
        $this->assertSame(404, $exception->getCode());
        $this->assertSame(TransferException::CODE_404, $exception->getMessage());

        $exception = new TransferException('', 406);
        $this->assertSame(406, $exception->getCode());
        $this->assertSame(TransferException::CODE_406, $exception->getMessage());

        $exception = new TransferException('', 409);
        $this->assertSame(409, $exception->getCode());
        $this->assertSame(TransferException::CODE_409, $exception->getMessage());

        $exception = new TransferException('', 422);
        $this->assertSame(422, $exception->getCode());
        $this->assertSame(TransferException::CODE_422, $exception->getMessage());

        $exception = new TransferException('', 500);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame(TransferException::CODE_500, $exception->getMessage());

        $exception = new TransferException('', 503);
        $this->assertSame(503, $exception->getCode());
        $this->assertSame(TransferException::CODE_503, $exception->getMessage());

        $exception = new TransferException('504', 504);
        $this->assertSame(504, $exception->getCode());
        $this->assertSame('504', $exception->getMessage());
    }
}
