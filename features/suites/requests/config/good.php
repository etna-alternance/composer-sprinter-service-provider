<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $container->parameters()->set("application_name", "Super appli pour sprinter");
    $container->parameters()->set("version", "1.4.2");

    $container->extension("framework", [
        "secret" => 'pouet',
        "test"   => true
    ]);

    $container->extension("sprinter", array(
        "default" => [
            "routing_key" => "sprinter.lefranc"
        ]
    ));

    $container->extension("old_sound_rabbit_mq", array(
        "connections" => [
            "default" => [
                "url" => 'amqp://guest:guest@localhost:5672',
                "vhost" => '/test-behat'
            ]
        ]
    ));
};
