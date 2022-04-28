<?php

namespace Innokassa\MDK\Entities\ReceiptId;

use Innokassa\MDK\Entities\Receipt;

/**
 * Фабрика идентификаторов чеков содержащих метаданные чека
 * Пример:
 *  - 20010310:171618-woo-mdk-1234567891234567-7c0b89b58d4f4af9
 *  - 20010310:171618-oc3-mdk-1234567891234567-7c0b89b58d4f4af9
 *  - 20010310:171618-oc2-mdk-1234567891234567-7c0b89b58d4f4af9
 *  - 20010310:171618-1cb-mdk-1234567891234567-7c0b89b58d4f4af9
 *  - 20010310:171618-dru-mdk-1234567891234567-7c0b89b58d4f4af9
 *  - 20010310:171618-shs-mdk-1234567891234567-7c0b89b58d4f4af9
 *  - 20010310:171618-adv-mdk-1234567891234567-7c0b89b58d4f4af9
 *  - 20010310:171618-lee-pla-6268e7ea7f60a54a506b7bab-7c0b89b5
 */
class ReceiptIdFactoryMeta implements ReceiptIdFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function build(Receipt $receipt): string
    {
        $id = sprintf(
            '%s-%s-%s-%s-%04x%04x',
            date("Ymd:His"),
            $this->getEngine(),
            'mdk',
            $receipt->getOrderId(),
            rand(0, 0xffff),
            rand(0, 0xffff)
        );

        return $id;
    }

    /**
     * @inheritDoc
     */
    public function verify(string $id): bool
    {
        return preg_match('/\d+:\d+-\w+-\w+-\w+-\w+/', $id);
    }

    //######################################################################
    // PROTECTED
    //######################################################################

    protected function getEngine(): string
    {
        return 'cms';
    }
}
