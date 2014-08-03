<?php

namespace ETNA\Silex\Provider\SPrinter;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 *
 */
class SPrinterServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
    }

    public function register(Application $app)
    {
        if (!isset($app["sprinter.options"]["default.routing_key"])) {
            throw new Exception("No routing key found", 400);
        }

        $app["sprinter"] = $app->share(
            function (Application $app) {
                return new SPrinter($app["amqp.exchanges"]["SPrinter"], $app["sprinter.options"]);
            }
        );
    }
}
