<?php

namespace MongoAppKit\Tests\Collection;

use MongoAppKit\Collection\ArrayList;

class ArrayListTest extends \PHPUnit_Framework_TestCase
{

    public function testArrayAccess()
    {
        $list = new ArrayList();
        $property = 'foo';
        $value = 'bar';
        $array = array('foo' => 'bar');
        $list[$property] = $value;

        $this->assertEquals($array[$property], $list[$property]);
    }

    public function testArrayAccessExists()
    {
        $list = new ArrayList();
        $this->assertFalse(isset($list['foo']));
    }

    public function testArrayAccessUnset()
    {
        $list = new ArrayList();
        $list['foo'] = 'bar';
        unset($list['foo']);

        $this->assertFalse(isset($list['foo']));
    }
}