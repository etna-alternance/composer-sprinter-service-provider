<?php
/**
 * PHP version 7.1
 *
 * @author BLU <dev@etna-alternance.net>
 */

declare(strict_types=1);

namespace ETNA\Sprinter\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Classe décrivant la configuration du SprinterBundle.
 *
 * Exemple de configuration yaml :
 *
 * <pre>
 *    sprinter:
 *        default:
 *            routing_key: lefran_f
 * </pre>
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Configure la structure de la configuration du SprinterBundle.
     *
     * @return TreeBuilder Contient la config
     */
    public function getConfigTreeBuilder()
    {
        $tree_builder = new TreeBuilder();
        $root_node    = $tree_builder->root('sprinter');

        $root_node
            ->children()
                ->arrayNode('default')
                    ->children()
                        ->scalarNode('routing_key')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $tree_builder;
    }
}
