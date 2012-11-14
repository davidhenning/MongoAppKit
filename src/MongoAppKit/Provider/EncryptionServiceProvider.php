<?php

namespace MongoAppKit\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface;

use MongoAppKit\Encryption;

class EncryptionServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['encryption'] = function () {
            return new Encryption();
        };
    }

    public function boot(Application $app)
    {

    }
}