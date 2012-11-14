<?php

namespace MongoAppKit\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface;

use MongoAppKit\Storage;

class StorageServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['storage'] = function () use ($app) {
            return new Storage($app['config']);
        };
    }

    public function boot(Application $app)
    {

    }
}
