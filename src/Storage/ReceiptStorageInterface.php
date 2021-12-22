<?php

namespace Innokassa\MDK\Storage;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Collections\ReceiptCollection;

/**
 * Хранилище чеков.
 * Реализуется в конкретном модуле
 */
interface ReceiptStorageInterface
{
    /**
     * Сохранение чека
     * 
     * @throws StorageException
     *
     * @param Receipt $receipt
     * @return int идентификатор в хранилище
     */
    public function save(Receipt $receipt): int;

    //######################################################################

    /**
     * Извлечение одного чека
     * 
     * @throws StorageException
     *
     * @param integer $id
     * @return Receipt|null
     */
    public function getOne(int $id): ?Receipt;

    /**
     * Извлечение коллекции чеков
     * 
     * @throws StorageException
     *
     * @param ReceiptFilter $filter
     * @param int $limit лимит выборки, 0 - нет лимита
     * @return ReceiptCollection
     */
    public function getCollection(ReceiptFilter $filter, int $limit=0): ReceiptCollection;
};
