<?php

namespace MongoAppKit;

use Silex\Application as SilexApplication,
    Silex\Provider\TwigServiceProvider;

use MongoAppKit\Config,
    MongoAppKit\Provider\StorageServiceProvider,
    MongoAppKit\Provider\EncryptionServiceProvider;

class Application extends SilexApplication
{

    public function __construct(Config $config)
    {
        parent::__construct();

        $this['config'] = $config;

        $baseDir = $config->getBaseDir();

        $this->register(new TwigServiceProvider(), array(
            'twig.path' => $baseDir . "/views",
            'twig.options' => array(
                'cache' => $baseDir . '/tmp/twig',
                'auto_reload' => $config->getProperty('DebugMode')
            )
        ));

        $this->register(new StorageServiceProvider($config));
        $this->register(new EncryptionServiceProvider());
    }
}
