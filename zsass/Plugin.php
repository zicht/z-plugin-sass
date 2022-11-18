<?php
/**
 * @author Joppe Aarts <joppe@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Plugin\Zsass;

use \Zicht\Tool\Plugin as BasePlugin;
use \Zicht\Tool\Container\Container;
use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class Plugin
 *
 * @package Zicht\Tool\Plugin\Zsass
 */
class Plugin extends BasePlugin
{
    /**
     * @param ArrayNodeDefinition $rootNode
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('zsass')
                    ->children()
                        ->scalarNode('sass_dir')->defaultValue('sass')->end()
                        ->scalarNode('css_dir')->defaultValue('style')->end()
                        ->arrayNode('dirs')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $container->decl(
            ['zsass', 'command'],
            function (Container $container) {
                $root = trim(str_replace(getcwd(), '', $container->resolve('_root')), '/');
                $local = ($root ? $root . '/' : '') . 'node_modules/.bin/zsass';
                return file_exists($local) ? 'node ' . $local : 'zsass';
            }
        );

        $container->method('zsass.dir_spec', function($container, $root, $dirs) {
            if (!count($dirs)) {
                throw new \InvalidArgumentException("Passed dirs to zsass.dir_spec() are invalid, at least 1 element is required");
            }
            $root = ltrim(str_replace(getcwd(), '', $root), '/');
            $ret = array();
            foreach ($dirs as $dir) {
                $src = rtrim($dir, '/') . '/' . $container->resolve('zsass.sass_dir');
                $tgt = rtrim($dir, '/') . '/' . $container->resolve('zsass.css_dir');

                $ret[] = sprintf('%s %s', ltrim(rtrim($root, '/') . '/' . $src, '/'), ltrim(rtrim($root, '/') . '/' . $tgt, '/'));
            }
            return join(' ', $ret);
        });
    }
}