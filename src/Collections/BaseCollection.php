<?php

namespace Innokassa\MDK\Collections;

/**
 * Базовый класс коллекций
 *
 * @implements \Iterator<object>
 * @implements \ArrayAccess<int,object>
 */
class BaseCollection implements \Countable, \ArrayAccess, \Iterator
{
    //######################################################################
    // ArrayAccess

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->objects[] = $value;
        } else {
            $this->objects[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->objects[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->objects[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->objects[$offset]) ? $this->objects[$offset] : null;
    }

    //######################################################################
    // Iterator

    public function current()
    {
        return $this->objects[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->objects[$this->position]);
    }

    //######################################################################
    // Countable

    public function count(): int
    {
        return count($this->objects);
    }

    //######################################################################

    /**
     * Перемешать элементы коллекции в случайном порядке
     *
     * @return bool
     */
    public function shuffle(): bool
    {
        return shuffle($this->objects);
    }

    //######################################################################
    // PROTECTED
    //######################################################################

    /** @var array<object> */
    protected $objects = [];

    /** @var int */
    protected $position = 0;
}
