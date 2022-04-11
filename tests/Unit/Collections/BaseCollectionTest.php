<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Collections\BaseCollection;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Collections\BaseCollection
 */
class BaseCollectionTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Collections\BaseCollection::offsetSet
     * @covers Innokassa\MDK\Collections\BaseCollection::offsetExists
     * @covers Innokassa\MDK\Collections\BaseCollection::offsetUnset
     * @covers Innokassa\MDK\Collections\BaseCollection::offsetGet
     * @covers Innokassa\MDK\Collections\BaseCollection::count
     * @covers Innokassa\MDK\Collections\BaseCollection::key
     * @covers Innokassa\MDK\Collections\BaseCollection::valid
     * @covers Innokassa\MDK\Collections\BaseCollection::current
     * @covers Innokassa\MDK\Collections\BaseCollection::rewind
     * @covers Innokassa\MDK\Collections\BaseCollection::next
     * @covers Innokassa\MDK\Collections\BaseCollection::shuffle
     */
    public function test()
    {
        $collection  = new BaseCollection();

        $collection[] = 'str1';
        $collection[1] = 'str2';

        $this->assertTrue(is_countable($collection));
        $this->assertTrue(is_iterable($collection));

        $this->assertSame('str1', $collection[0]);
        $this->assertSame('str2', $collection[1]);
        $this->assertSame(2, count($collection));
        $this->assertSame(2, $collection->count());
        $this->assertTrue(isset($collection[1]));

        unset($collection[1]);
        $this->assertFalse(isset($collection[1]));


        $collection  = new BaseCollection();
        $collection[] = 'str1';
        $collection[] = 'str2';

        $this->assertSame(0, $collection->key());
        $this->assertTrue($collection->valid());
        $this->assertSame('str1', $collection->current());
        $collection->next();

        $this->assertSame(1, $collection->key());
        $this->assertTrue($collection->valid());
        $this->assertSame('str2', $collection->current());
        $collection->next();

        $this->assertSame(2, $collection->key());
        $this->assertFalse($collection->valid());
        $collection->rewind();
        $this->assertSame(0, $collection->key());
        $this->assertTrue($collection->valid());
        $this->assertSame('str1', $collection->current());

        $this->assertTrue($collection->shuffle());
        $this->assertSame(2, $collection->count());
    }
}
