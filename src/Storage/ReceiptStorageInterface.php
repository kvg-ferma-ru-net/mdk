<?php

namespace Innokassa\MDK\Storage;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Exceptions\StorageException;
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
     * Извлечение коллекции чеков (сортировка по id по возврастанию)
     *
     * @throws StorageException
     *
     * @param ReceiptFilter $filter
     * @param int $limit лимит выборки, 0 - нет лимита
     * @return ReceiptCollection
     */
    public function getCollection(ReceiptFilter $filter, int $limit = 0): ReceiptCollection;

    /**
     * Получить минимальное значение столбца
     *
     * @param ReceiptFilter $filter
     * @param string $column
     * @return mixed
     */
    public function min(ReceiptFilter $filter, string $column);

    /**
     * Получить максимальное значение столбца
     *
     * @param ReceiptFilter $filter
     * @param string $column
     * @return mixed
     */
    public function max(ReceiptFilter $filter, string $column);

    /**
     * Получить количество записей
     *
     * @param ReceiptFilter $filter
     * @return int
     */
    public function count(ReceiptFilter $filter): int;
}
