<?php
/**
 * PHP version 7.1
 * @author BLU <dev@etna-alternance.net>
 */

declare(strict_types=1);

namespace ETNA\Sprinter\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * On définit cette classe pour personnaliser le processus de parsing de la configuration de notre bundle.
 *
 * Entre autres on ajoute la configuration dans les paramêtres du container Symfony
 * Ici, on va aussi dumper les fichiers de conf .yaml du bundle dans le bundle parent
 * Grâce à l'interface PrependExtensionInterface, on va pouvoir intervenir sur les
 * conf .yaml d'extensions loadées depuis l'extérieur.
 */
class SprinterExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Implémentation de l'interface PrependExtensionInterface
     * Cette fonction permet d'intervenir sur les fichiers de configuration
     * du dossier config/packages/*.yaml du bundle parent
     *
     * @param ContainerBuilder $container Le container du bundle parent
     */
    public function prepend(ContainerBuilder $container): void
    {
        $rmq_conf = [
            'producers' => [
                'SPrinter' => [
                    'connection'       => 'default',
                    'exchange_options' => [
                        'name'        => 'SPrinter',
                        'type'        => 'direct',
                        'passive'     => false,
                        'durable'     => true,
                        'auto_delete' => false
                    ]
                ]
            ]
        ];

        $rmq_current_config = $container->getExtensionConfig('old_sound_rabbit_mq');
        $rmq_current_config = array_shift($rmq_current_config);

        if (empty($rmq_current_config['connections']) || empty($rmq_current_config['connections']['default'])) {
            $rmq_conf['connections'] = [
                'default' => [
                    'url'   => '%env(RABBITMQ_URL)%',
                    'vhost' => '%env(RABBITMQ_VHOST)%'
                ]
            ];
        }

        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles["OldSoundRabbitMqBundle"])) {
            throw new \Exception("Error Processing Request", 1);
        }
        $container->prependExtensionConfig('old_sound_rabbit_mq', $rmq_conf);
    }

    /**
     * Cette fonction est appelée par symfony et permet
     * le chargement de la configuration du bundle
     * dans les paramètres du container.
     * La config provient du bundle appelant localisé dans le fichier config/packages/sprinter.yaml
     * mais qui a été modifiée dans le processus de prepend.
     *
     * Ensuite on load la config du/des services dans le dossier Resources/config.
     *
     * @param array            $configs   Les éventuels paramètres
     * @param ContainerBuilder $container Le container de la configuration
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter("sprinter.default.routing_key", $config["default"]["routing_key"]);
		$loader = new YamlFileLoader(
			$container,
			new FileLocator(__DIR__ . '/../Resources/config')
		);
		$loader->load('services.yaml');
	}
}
