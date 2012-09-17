<?php

namespace MongoAppKit\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface;

use MongoAppKit\Encryption;

class StorageServiceProvider implements ServiceProviderInterface {

    public function register(Application $oApp) {
        $app['storage'] = function () {
            return new Encryption();
        };
    }

    public function boot(Application $oApp) {

    }
}