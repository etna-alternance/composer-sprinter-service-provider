<?php

namespace ETNA\Silex\Provider\SPrinter;

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
        $params = [
            "template"   => $template,
            "data"       => $data,
            "printflag"  => $print_flag
        ];
        if ($opt) {
            $params = array_merge($params, $opt);
        }
        $this->exchange->send($params, $routing_key ?: $this->routing_key);
    }
}
