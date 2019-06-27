sprinter-service-provider
Dependency Status Scrutinizer Code Quality

## Installation
Modifier composer.json :

{
    // ...
    "require": {
        "etna/sprinter-service-provider": "~0.1"
    },
    "repositories": [
       {
           "type": "composer",
           "url": "https://blu-composer.herokuapp.com"
       }
   ]
}
## Configuration
### Environnement

Dans le fichier d'env :

Une variable RABBITMQ_URL qui contient l'adresse du serveur rabbitMQ
Une variable RABBITMQ_VHOST qui contient le type d'environnement souhaité ```production```||```development```||```/test-behat```

```
RABBITMQ_URL=amqp://development:ieJoh8sa@rabbitmq.etna.local:5672
RABBITMQ_VHOST=development
```

### Sprinter et RabbitMQ

La description de la default routing_key est située dans SprinterServiceProvider/config/packages/sprinter.yaml

La description du producer rabbitMQ est située dans SprinterServiceProvider/config/packages/old_sound_rabbit_mq.yaml

Lors de l'injection de la dépendance, la configuration rabbitMQ est ajoutée (prepend) à la configuration du parent.

Si le parent redéfini la default routing_key ou le producer SPrinter, il écrase la configuration définie au niveau SprinterServiceProvider

*****Exemple de sprinter.yaml:*****

```
default:
  routing_key: sprinter.lefran_f
```

*****Exemple de old_sound_rabbit_mq.yaml:*****

```
connections:
    default:
        url: '%env(RABBITMQ_URL)%'
        vhost: '%env(RABBITMQ_VHOST)%'
producers:
    # use 'old_sound_rabbit_mq.sprinter_producer' service to send data.
    SPrinter:
        connection:       default
        exchange_options: { name: 'SPrinter', type: direct, passive: false, durable: true, auto_delete: false }
```

L'ordre d'appel des bundles n'a pas d'importance.
