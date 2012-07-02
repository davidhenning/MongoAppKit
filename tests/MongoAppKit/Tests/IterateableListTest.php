<?php

namespace MongoAppKit\Tests;

use MongoAppKit\Lists\IterateableList;

class IterateableListTest extends \PHPUnit_Framework_TestCase {

	public function testAssign() {
		$array = array('food' => 'shrimps', 'sauce' => 'cocktail');
		$list = new IterateableList();
		$list->assign($array);
		
		$this->assertEquals($array, $list->getProperties());
	}

	public function testProperty() {
		$value = 'bar';
		$list = new IterateableList();
		$list->setProperty('foo', $value);

		$this->assertEquals($value, $list->getProperty('foo'));
	}

	public function testNonExistingProperty() {
		try {
			$list = new IterateableList();
			$value = $list->getProperty('foo');
		} catch(\OutOfBoundsException $e) {
			return;
		}

		$this->fail('Expected OutOfBoundsException was not thrown.');
	}

	public function testRemoveProperty() {
		try {
			$property = 'foo';
			$value = 'bar';
			$list = new IterateableList();
			$list->setProperty($property, $value);
			$list->removeProperty($property);
			$value = $list->getProperty($property);
		} catch(\OutOfBoundsException $e) {
			return;
		}

		$this->fail('Expected OutOfBoundsException was not thrown.');
	}

	public function testRemoveNonExistingProperty() {
		try {
			$list = new IterateableList();
			$list->removeProperty('foo');
		} catch(\OutOfBoundsException $e) {
			return;
		}

		$this->fail('Expected OutOfBoundsException was not thrown.');
	}

	public function testCount() {
		$list = new IterateableList();
		$list->setProperty('foo', 'bar');

		$this->assertEquals(1, count($list));
	}

	public function testIteration() {
		$array = array('food' => 'shrimps', 'sauce' => 'cocktail');
		$list = new IterateableList();
		$list->assign($array);

		$newArray = array();

		foreach($list as $key => $value) {
			$newArray[$key] = $value;
		}

		$this->assertEquals($array, $newArray);
	}

	public function testArrayAccess() {
		$list = new IterateableList();
		$property = 'foo';
		$value = 'bar';
		$array = array('foo' => 'bar');
		$list[$property] = $value;

		$this->assertEquals($array[$property], $list[$property]);
	}

	public function testArrayAccessExists() {
		$list = new IterateableList();
		$this->assertFalse(isset($list['foo']));
	}

	public function testArrayAccessUnset() {
		$list = new IterateableList();
		$list['foo'] = 'bar';
		unset($list['foo']);

		$this->assertFalse(isset($list['foo']));
	}

	public function testUpdateProperties() {
		$values = array('foo' => 'bar');
		$list = new IterateableList();
		$list->assign($values);
		$newValues = array('bar' => 'foo');
		$list->updateProperties($newValues);
		$expected = array_merge($values, $newValues);
		$this->assertEquals($expected, $list->getProperties());
	}
}