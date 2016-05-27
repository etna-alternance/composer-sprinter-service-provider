<?php

namespace ETNA\Silex\Provider\SPrinter;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

/**
 *
 */
class SPrinterServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        if (false === isset($app["sprinter.options"]) || false === isset($app["sprinter.options"]["default.routing_key"])) {
            $environment_key = "{$app["application_name"]}_SPRINTER_ROUTING_KEY";
            $routing_key     = getenv(strtoupper($environment_key));

            if (false === $routing_key) {
                throw new \Exception("No sprinter routing key found for environment {$environment_key}");
            }

            $app["sprinter.options"] = [
                "default.routing_key" => $routing_key,
            ];
        }

        // On vérifie que la connection rmq est bien settée
        if (false === isset($app['rabbit.connections']) || false === isset($app['rabbit.connections']['default'])) {
            throw new \Exception("RabbitMQ default connection not set");
        }

        $app["sprinter"] = function ($app) {
            return new SPrinter($app);
        };
    }
}
