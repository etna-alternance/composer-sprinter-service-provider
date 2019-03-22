<?php
/**
 * PHP version 7.1
 *
 * @author BLU <dev@etna-alternance.net>
 */

declare(strict_types=1);

namespace ETNA\Sprinter\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;       
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

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
    public function prepend(ContainerBuilder $container)
    {
        // récupérer le dossier de configs de la lib
        $configPath  = __DIR__.'/../../config/packages/';
        $sprinterYml = "sprinter.yaml";
        $rabbitMqYml = "old_sound_rabbit_mq.yaml";
        // on parse les configurations
        $sprinterConf = Yaml::parse(file_get_contents($configPath.$sprinterYml));
        $rabbitMqConf = Yaml::parse(file_get_contents($configPath.$rabbitMqYml));
        // on récupère les bundles
        $bundles = $container->getParameter('kernel.bundles');
        // on vérifie que le bundle rabbitMq est bien intégré au projet
        // sinon on renvoi une exception indiquant comment intégré rmq
        // ce cas de figure devrait être géré par composer
        if (!isset($bundles["OldSoundRabbitMqBundle"]))
            throw new \Exception("SprinterBundle require RabbitMQ: try composer require php-amqplib/rabbitmq-bundle", 500);
        // on récupère les extensions et on intervient sur les deux extensions concernées ici
        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
                // sur l'extension sprinter, on définit une default routin_key d'office (cf: SprinterServiceProvider/config/packages)
                // on va pouvoir exécuter le processConfiguration sur Sprinter
                // puisqu'on est dans la classe extension du bundle Sprinter
                case 'sprinter':
                    $container->prependExtensionConfig($name, $sprinterConf);
                    $sprinterConf = $container->getExtensionConfig($name);
                    break;
                // pour rabbitmq, on ne peut pas exécuter le processConfiguration
                // cela ne nous empêche pas d'intervenir sur la configuration .yaml
                // Si le fichier old_sound_rabbit_mq.yaml du bundle parent
                // défini un producer SPrinter, celui défini ici sera écrasé.
                case 'old_sound_rabbit_mq':
                    $container->prependExtensionConfig($name, $rabbitMqConf);
                    break;
                default:
                    break;
            }
        }
        $config = $this->processConfiguration(new Configuration(), $sprinterConf);
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
			new FileLocator(__DIR__.'/../../config')
		);
		$loader->load('services.yaml');
	}
}
