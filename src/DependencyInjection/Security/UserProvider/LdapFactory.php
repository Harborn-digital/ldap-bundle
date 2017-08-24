<?php

namespace ConnectHolland\LdapBundle\DependencyInjection\Security\UserProvider;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * LdapFactory creates services for the LDAP user provider.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class LdapFactory implements UserProviderFactoryInterface
{
    /**
     * Returns the key identifying this factory.
     *
     * @return string
     */
    public function getKey()
    {
        return 'custom_user_ldap';
    }

    /**
     * Adds the configuration definition for the LDAP user provider.
     *
     * @param NodeDefinition $node
     */
    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('connection')
                    ->children()
                        ->scalarNode('host')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->end()
                        ->integerNode('port')
                            ->cannotBeEmpty()
                            ->defaultValue(389)
                            ->end()
                        ->enumNode('encryption')
                            ->values(array('ssl', 'tls'))
                            ->end()
                        ->arrayNode('options')
                            ->canBeDisabled()
                            ->children()
                                ->integerNode('protocol_version')
                                    ->cannotBeEmpty()
                                    ->defaultValue(3)
                                    ->end()
                                ->booleanNode('referrals')
                                    ->defaultFalse()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->arrayNode('user_factory')
                    ->children()
                        ->enumNode('type')
                            ->values(array('doctrine', 'sulu'))
                            ->end()
                        ->scalarNode('service')->end()
                        ->scalarNode('user_class')->end()
                        ->scalarNode('username_column')->end()
                        ->arrayNode('user_property_map')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->scalarNode('base_dn')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->end()
                ->scalarNode('search_dn')->end()
                ->scalarNode('search_password')->end()
                ->arrayNode('default_roles')
                    ->beforeNormalization()
                        ->ifString()
                            ->then(function ($v) {
                                return preg_split('/\s*,\s*/', $v);
                            })
                        ->end()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                    ->end()
                ->scalarNode('uid_key')
                    ->defaultValue('sAMAccountName')
                    ->end()
                ->scalarNode('filter')
                    ->defaultValue('({uid_key}={username})')
                    ->end()
            ->end();
    }

    /**
     * Creates the service container definitions for the LDAP user provider service and related services.
     *
     * @param ContainerBuilder $container
     * @param string           $id
     * @param array            $config
     */
    public function create(ContainerBuilder $container, $id, $config)
    {
    }
}
