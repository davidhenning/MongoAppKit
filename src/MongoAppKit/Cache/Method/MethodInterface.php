<?php

namespace MongoAppKit\Cache\Method;

interface MethodInterface
{
    public function store($name, $value);
    public function retrieve($name);
    public function setOptions(array $options);
    public function cleanUp();
}
