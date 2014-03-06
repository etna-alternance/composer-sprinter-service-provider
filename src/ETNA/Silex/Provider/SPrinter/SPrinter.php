<?php

namespace ETNA\Silex\Provider\SPrinter;

use ETNA\Silex\Provider\RabbitMQ\Queue;
/**
 *
 */
class SPrinter
{
    public function __construct($exchange, $options)
    {
        $this->exchange     = $exchange;
        $this->routing_key  = $options["default.routing_key"];
    }

    public function getDefaultRoutingKey()
    {
        return $this->routing_key;
    }

    public function sendPrint($template, $data, $print_flag, $routing_key = null, $opt = null)
    {
        $queue_opt = [
            "passive"     => false,
            "durable"     => true,
            "exclusive"   => false,
            "auto_delete" => false,
        ];
        $params = [
            "template"   => $template,
            "data"       => $data,
            "printflag"  => $print_flag
        ];
        if ($opt) {
            $params = array_merge($params, $opt);
        }

        // crée la queue au besoin
        $queue = new Queue($routing_key, $this->exchange, $this->exchange->getChannel(), $queue_opt);

        $this->exchange->send($params, $routing_key ?: $this->routing_key);
    }
}
