<?php

namespace ETNA\Silex\Provider\SPrinter;

use Silex\Application;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

/**
 *
 */
class SPrinterServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        if (!isset($app["sprinter.options"]) || !isset($app["sprinter.options"]["default.routing_key"])) {
            $environment_key = "{$app["application_name"]}_SPRINTER_ROUTING_KEY";
            $routing_key     = getenv(strtoupper($environment_key));

            if (false === $routing_key) {
                throw new Exception("No sprinter routing key found for environment {$environment_key}");
            }

            $app["sprinter.options"] = [
                "default.routing_key" => $routing_key,
            ];
        }

        $app["sprinter"] = $app->share(
            function (Application $app) {
                return new SPrinter($app["amqp.exchanges"]["SPrinter"], $app["sprinter.options"]);
            }
        );
    }
}
