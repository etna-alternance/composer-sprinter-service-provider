<?php

namespace ETNA\Silex\Provider\SPrinter;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Silex\Application;

/**
 *
 */
class SPrinter
{
    public function __construct($app)
    {
        // On crée un producer pour publier des jobs
        $connection = $app['rabbit.connections']['default'];
        $producer   = new Producer($connection);
        $producer->setExchangeOptions([
            "name"        => "SPrinter",
            "channel"     => "default",
            "type"        => "direct",
            "passive"     => false,
            "durable"     => true,
            "auto_delete" => false,
        ]);

        $this->producer    = $producer;
        $this->routing_key = $app["sprinter.options"]["default.routing_key"];
    }

    public function getDefaultRoutingKey()
    {
        return $this->routing_key;
    }

    public function getProducer()
    {
        return $this->producer;
    }

    public function sendPrint($template, $data, $print_flag, $routing_key = null, $opt = null)
    {
        $print_params = [
            "template"   => $template,
            "data"       => $data,
            "printflag"  => $print_flag
        ];
        if ($opt) {
            $print_params = array_merge($print_params, $opt);
        }

        $routing_key = $routing_key ?: $this->routing_key;

        $this->producer->publish($print_params, $routing_key);
    }
}
