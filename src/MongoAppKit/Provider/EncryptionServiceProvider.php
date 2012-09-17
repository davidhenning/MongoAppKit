<?php

namespace MongoAppKit\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface;

use MongoAppKit\Encryption;

class EncryptionServiceProvider implements ServiceProviderInterface {

    public function register(Application $oApp) {
        $oApp['encryption'] = function () {
            return new Encryption();
        };
    }

    public function boot(Application $oApp) {

    }
}