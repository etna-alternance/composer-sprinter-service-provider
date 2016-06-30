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

        // On vérifie que le producer rabbitmq est bien créé
        if (false === isset($app['rabbit.producers']) || false === isset($app['rabbit.producers']['sprinter'])) {
            throw new \Exception("RabbitMQ producer 'sprinter' is not defined");
        }

        $app["sprinter"] = function ($app) {
            return new SPrinter($app);
        };
    }
}
