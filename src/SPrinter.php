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
        $this->producer    = $app['rabbit.producer']['sprinter'];
        $this->routing_key = $app["sprinter.options"]["default.routing_key"];
    }

    public function getDefaultRoutingKey()
    {
        return $this->routing_key;
    }

    public function sendPrint($template, $data, $print_flag, $routing_key = null, $opt = null)
    {
        $print_params = [
            "template"   => $template,
            "data"       => $data,
            "printFlag"  => $print_flag
        ];
        if ($opt) {
            $print_params = array_merge($print_params, $opt);
        }

        $routing_key = $routing_key ?: $this->routing_key;

        $this->producer->publish(json_encode($print_params), $routing_key);
    }

    /**
     * Renvoie la conf du producer rabbitmq pour SPrinter
     *
     * @return array La conf du producer
     */
    public static function getProducerConfig()
    {
        $sprinter_exchange = [
            "name"        => "SPrinter",
            "channel"     => "default",
            "type"        => "direct",
            "passive"     => false,
            "durable"     => true,
            "auto_delete" => false,
        ];

        $producer_config = [
            'sprinter' => [
                'connection'        => 'default',
                'exchange_options'  => $sprinter_exchange
            ]
        ];

        return $producer_config;
    }
}
