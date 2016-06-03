sprinter-service-provider
=========================

[![Dependency Status](https://www.versioneye.com/user/projects/53dde6d38e78abc422000010/badge.svg)](https://www.versioneye.com/user/projects/53dde6d38e78abc422000010)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/etna-alternance/composer-sprinter-service-provider/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/etna-alternance/composer-sprinter-service-provider/?branch=master)

Installation
------------

Modifier `composer.json` :

```
{
    // ...
    "require": {
        "etna/sprinter-service-provider": "~0.1"
    },
    "repositories": [
       {
           "type": "composer",
           "url": "http://blu-composer.herokuapp.com"
       }
   ]
}
```

Configuration
-------------

Créer le producer rabbitmq :

```
$app['rmq_producers'] = array_merge($app['rmq_producers'], SPrinter::getProducerConfig());
```

Attention, le provider Sprinter doit être register après le provider RabbitMQ :

```
$app->register(new RabbitConfig($this->rabbitmq_config));
$app->register(new SPrinterServiceProvider());
```
