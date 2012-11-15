<?php

namespace MongoAppKit\Cache\Method;

class FileSystem implements MethodInterface
{
    protected $_cacheDir;
    protected $_ttl;

    public function cleanUp() {
        $directory = $this->_getCacheDir();
        $files = new \DirectoryIterator($directory);

        foreach($files as $file) {
            if($file->isFile() && !$this->_isFresh($file->getPathname())) {
                unlink($file->getPathname());
            }
        }
    }

    public function store($name, $value)
    {
        $file = $this->_getCacheFile($name);
        file_put_contents($file, $value);
    }

    public function retrieve($name)
    {
        $file = $this->_getCacheFile($name);

        if (!is_readable($file)) {
            throw new \InvalidArgumentException("Cache file for item '{$name}' is not readable.");
        }

        if ($this->_isFresh($file)) {
            return file_get_contents($file);
        }

        throw new \InvalidArgumentException("Item '{$name}' is not cached.");
    }

    public function setOptions(array $options) {
        $this->_ttl = $options['ttl'];
        $this->_setCacheDir($options['cacheDir']);
    }

    protected function _getCacheFile($name) {
        $hashedName = sha1($name);
        return "{$this->_getCacheDir()}/{$hashedName}.tmp";
    }

    protected function _isFresh($file) {
        return (filemtime($file) + $this->_ttl) >= time();
    }

    protected function _getCacheDir()
    {
        return $this->_cacheDir;
    }

    protected function _setCacheDir($directory)
    {
        if (!is_writable($directory)) {
            throw new \InvalidArgumentException("Cache directory '{$directory}' is not accessible!'");
        }

        $this->_cacheDir = $directory;
    }
}
