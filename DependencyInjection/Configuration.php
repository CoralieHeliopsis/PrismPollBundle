<?php

namespace Prism\PollBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('prism_poll');

        $rootNode
            ->children()
                ->arrayNode('entity')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('poll')->defaultValue('Prism\PollBundle\Entity\Poll')->end()
                        ->scalarNode('opinion')->defaultValue('Prism\PollBundle\Entity\Opinion')->end()
                    ->end()
                ->end()

                ->arrayNode('form')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('poll')->defaultValue('Prism\PollBundle\Form\PollType')->end()
                        ->scalarNode('opinion')->defaultValue('Prism\PollBundle\Form\OpinionType')->end()
                        ->scalarNode('vote')->defaultValue('Prism\PollBundle\Form\VoteType')->end()
                    ->end()
                ->end()

                ->arrayNode('templates_frontend')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('list')->defaultValue('PrismPollBundle:Frontend/Poll:list.html.twig')->end()
                        ->scalarNode('results')->defaultValue('PrismPollBundle:Frontend/Poll:results.html.twig')->end()
                        ->scalarNode('vote')->defaultValue('PrismPollBundle:Frontend/Poll:vote.html.twig')->end()
                        ->scalarNode('confirm')->defaultValue('PrismPollBundle:Frontend/Poll:confirm.html.twig')->end()
                        ->scalarNode('confirm_ajax')->defaultValue('PrismPollBundle:Frontend/Poll:confirm_ajax.html.twig')->end()
                    ->end()
                ->end()

                ->arrayNode('templates_backend')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('list')->defaultValue('PrismPollBundle:Backend/Poll:list.html.twig')->end()
                        ->scalarNode('edit')->defaultValue('PrismPollBundle:Backend/Poll:edit.html.twig')->end()
                    ->end()
                ->end()

                ->arrayNode( 'actions' )
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('poll_submitted')->defaultValue('results')->info('Available values : results|confirm')->end()
                        ->scalarNode('poll_voted')->defaultValue('results')->info('Available values : results|confirm|hide')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
