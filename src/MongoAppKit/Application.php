<?php

namespace MongoAppKit;

use Silex\Application as SilexApplication,
    Silex\Provider\TwigServiceProvider;

use MongoAppKit\Config;

class Application extends SilexApplication {

    public function __construct(Config $oConfig) {
        parent::__construct();

        $sBaseDir = $oConfig->getBaseDir();
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => $sBaseDir . "/views",
            'twig.options' => array(
                'cache' => $sBaseDir .'/tmp',
                'auto_reload' => $oConfig->getProperty('DebugMode')
            )
        ));

    }
}