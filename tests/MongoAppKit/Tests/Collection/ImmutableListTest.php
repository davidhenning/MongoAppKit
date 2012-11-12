<?php

namespace MongoAppKit\Tests\Collection;

use MongoAppKit\Collection\ImmutableList;

class ImmutableListTest extends \PHPUnit_Framework_TestCase {

    public function testAssign() {
        $list = new ImmutableList();
        $exceptionThrown = false;

        try {
            $list->assign(array('foo'));
        } catch(\BadMethodCallException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testUpdateProperties() {
        $list = new ImmutableList();
        $exceptionThrown = false;

        try {
            $list->updateProperties(array('foo'));
        } catch(\BadMethodCallException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testSetProperty() {
        $list = new ImmutableList();
        $exceptionThrown = false;

        try {
            $list->setProperty('foo', 'bar');
        } catch(\BadMethodCallException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testRemoveProperty() {
        $list = new ImmutableList();
        $exceptionThrown = false;

        try {
            $list->removeProperty('foo');
        } catch(\BadMethodCallException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }
}