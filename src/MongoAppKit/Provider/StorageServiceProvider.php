<?php

namespace MongoAppKit\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface;

use MongoAppKit\Storage;

class StorageServiceProvider implements ServiceProviderInterface {

    public function register(Application $oApp) {
        $app['storage'] = function () use ($oApp) {
            return new Storage($oApp['config']);
        };
    }

    public function boot(Application $oApp) {

    }
}
