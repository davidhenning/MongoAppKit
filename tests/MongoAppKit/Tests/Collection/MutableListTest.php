<?php

namespace MongoAppKit\Tests\Collection;

use MongoAppKit\Collection\MutableList;

class MutableListTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $list = new MutableList('bubba', 'gump', 'shrimps');
        $array = array('bubba', 'gump', 'shrimps');

        $this->assertEquals($array, $list->getProperties());
    }

    public function testPropertyLength()
    {
        $list = new MutableList('bubba', 'gump', 'shrimps');

        $this->assertEquals(3, $list->length);
    }

    public function testAssign()
    {
        $array = array('food' => 'shrimps', 'sauce' => 'cocktail');
        $list = new MutableList();
        $list->assign($array);

        $this->assertEquals($array, $list->getProperties());
    }

    public function testProperty()
    {
        $value = 'bar';
        $list = new MutableList();
        $list->setProperty('foo', $value);

        $this->assertEquals($value, $list->getProperty('foo'));
    }

    public function testMagicProperty()
    {
        $value = 'bar';
        $list = new MutableList();
        $list->foo = $value;

        $this->assertEquals($value, $list->foo);
    }

    public function testNonExistingProperty()
    {
        try {
            $list = new MutableList();
            $value = $list->getProperty('foo');
        } catch (\OutOfBoundsException $e) {
            return;
        }

        $this->fail('Expected OutOfBoundsException was not thrown.');
    }

    public function testRemoveProperty()
    {
        try {
            $property = 'foo';
            $value = 'bar';
            $list = new MutableList();
            $list->setProperty($property, $value);
            $list->removeProperty($property);
            $value = $list->getProperty($property);
        } catch (\OutOfBoundsException $e) {
            return;
        }

        $this->fail('Expected OutOfBoundsException was not thrown.');
    }

    public function testMagicRemoveProperty()
    {
        try {
            $property = 'foo';
            $value = 'bar';
            $list = new MutableList();
            $list->{$property} = $value;
            unset($list->{$property});
            $value = $list->{$property};
        } catch (\OutOfBoundsException $e) {
            return;
        }

        $this->fail('Expected OutOfBoundsException was not thrown.');
    }

    public function testRemoveNonExistingProperty()
    {
        try {
            $list = new MutableList();
            $list->removeProperty('foo');
        } catch (\OutOfBoundsException $e) {
            return;
        }

        $this->fail('Expected OutOfBoundsException was not thrown.');
    }

    public function testCount()
    {
        $list = new MutableList();
        $list->setProperty('foo', 'bar');

        $this->assertEquals(1, count($list));
    }

    public function testIteration()
    {
        $array = array('food' => 'shrimps', 'sauce' => 'cocktail');
        $list = new MutableList();
        $list->assign($array);

        $newArray = array();

        foreach ($list as $key => $value) {
            $newArray[$key] = $value;
        }

        $this->assertEquals($array, $newArray);
    }

    public function testUpdateProperties()
    {
        $values = array('foo' => 'bar');
        $list = new MutableList();
        $list->assign($values);
        $newValues = array('bar' => 'foo');
        $list->updateProperties($newValues);
        $expected = array_merge($values, $newValues);
        $this->assertEquals($expected, $list->getProperties());
    }

    public function testToString()
    {
        $list = new MutableList('bubba', 'gump', 'shrimps');
        $array = array('bubba', 'gump', 'shrimps');

        $this->assertEquals(serialize($array), (string)$list);
    }

    public function testReverse()
    {
        $expected = new MutableList('bar', 'foo');
        $list = new MutableList('foo', 'bar');

        $this->assertEquals($expected, $list->reverse());
    }

    public function testMap()
    {
        $expected = new MutableList('FOO', 'BAR');
        $list = new MutableList('foo', 'bar');

        $list->map(function ($value) {
            return strtoupper($value);
        });

        $this->assertEquals($expected, $list);
    }

    public function testFilter()
    {
        $expected = new MutableList('foo');
        $list = new MutableList('foo', 'bar');

        $filter = function ($value) {
            if ($value === 'foo') {
                return true;
            }
        };

        $this->assertEquals($expected, $list->filter($filter));
    }

    public function testSlice()
    {
        $expected = new MutableList('bar');
        $list = new MutableList('foo', 'bar');

        $this->assertEquals($expected, $list->slice(1, 1));
    }

    public function testChaining()
    {
        $expected = new MutableList('FOO');
        $list = new MutableList('foo', 'bar');
        $filter = function ($value) {
            if (strtolower($value) == 'foo') {
                return true;
            }
        };

        $list->map(function ($value) {
            return strtoupper($value);
        });

        $this->assertEquals($expected->getProperties(), $list->filter($filter)->getProperties());
    }
}