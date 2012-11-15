<?php

namespace MongoAppKit\Tests\Collection;

use MongoAppKit\Collection\ImmutableMap;

class ImmutableMapTest extends \PHPUnit_Framework_TestCase
{

    public function testAssign()
    {
        $list = new ImmutableMap();
        $exceptionThrown = false;

        try {
            $list->assign(array('foo'));
        } catch (\BadMethodCallException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testUpdateProperties()
    {
        $list = new ImmutableMap();
        $exceptionThrown = false;

        try {
            $list->updateProperties(array('foo'));
        } catch (\BadMethodCallException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testSetProperty()
    {
        $list = new ImmutableMap();
        $exceptionThrown = false;

        try {
            $list->setProperty('foo', 'bar');
        } catch (\BadMethodCallException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testRemoveProperty()
    {
        $list = new ImmutableMap();
        $exceptionThrown = false;

        try {
            $list->removeProperty('foo');
        } catch (\BadMethodCallException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }
}