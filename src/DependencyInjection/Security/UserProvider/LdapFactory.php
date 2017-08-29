<?php

namespace ConnectHolland\LdapBundle\DependencyInjection\Security\UserProvider;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
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
        return 'connect_holland_ldap';
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
                    ->isRequired()
                    ->children()
                        ->scalarNode('host')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->end()
                        ->integerNode('port')
                            ->defaultValue(389)
                            ->end()
                        ->enumNode('encryption')
                            ->values(array('ssl', 'tls'))
                            ->end()
                        ->arrayNode('options')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('protocol_version')
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
                    ->isRequired()
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
        $this->createUserFactoryDefinition($container, $id, $config['user_factory']);
        $this->createLdapClientDefinition($container, $id, $config['connection']);
        $this->createLdapUserProviderDefinition($container, $id, $config);
    }

    /**
     * Adds an user factory service definition to the service container.
     *
     * @param ContainerBuilder $container
     * @param string           $id
     * @param array            $configuration
     */
    private function createUserFactoryDefinition(ContainerBuilder $container, $id, array $configuration)
    {
        $container->setParameter($id.'.user.factory.user_property_map', $configuration['user_property_map']);

        if (isset($configuration['user_factory']['service']) || isset($configuration['type']) === false) {
            return;
        }

        $definitionDecorator = new DefinitionDecorator(
            sprintf('connect_holland_ldap.security.user.factory.%s', $configuration['type'])
        );

        $userPropertyMapArgumentIndex = 5;
        if ($configuration['type'] === 'doctrine') {
            $userPropertyMapArgumentIndex = 3;

            $definitionDecorator->replaceArgument(1, $configuration['user_class']);
            $definitionDecorator->replaceArgument(2, $configuration['username_column']);
        }

        $definitionDecorator->replaceArgument($userPropertyMapArgumentIndex, $configuration['user_property_map']);

        $container->setDefinition($id.'.user.factory', $definitionDecorator);
    }

    /**
     * Adds an LDAP client service definition to the service container.
     *
     * @param ContainerBuilder $container
     * @param string           $id
     * @param array            $configuration
     */
    private function createLdapClientDefinition(ContainerBuilder $container, $id, array $configuration)
    {
        $encryptionSsl = isset($configuration['encryption']) && $configuration['encryption'] === 'ssl';
        $encryptionTls = isset($configuration['encryption']) && $configuration['encryption'] === 'tls';

        $container->setDefinition($id.'.client', new DefinitionDecorator('connect_holland_ldap.ldap.client'))
            ->replaceArgument(0, $configuration['host'])
            ->replaceArgument(1, $configuration['port'])
            ->replaceArgument(2, $configuration['options']['protocol_version'])
            ->replaceArgument(3, $encryptionSsl)
            ->replaceArgument(4, $encryptionTls)
            ->replaceArgument(5, $configuration['options']['referrals']);
    }

    /**
     * Adds an LDAP user provider service definition to the service container.
     *
     * @param ContainerBuilder $container
     * @param string           $id
     * @param array            $configuration
     */
    private function createLdapUserProviderDefinition(ContainerBuilder $container, $id, array $configuration)
    {
        $userFactoryId = $id.'.user.factory';
        if (isset($configuration['user_factory']['service'])) {
            $userFactoryId = $configuration['user_factory']['service'];
        }

        $container->setDefinition($id, new DefinitionDecorator('connect_holland_ldap.security.user.provider.ldap'))
            ->replaceArgument(0, new Reference($userFactoryId))
            ->replaceArgument(1, new Reference($id.'.client'))
            ->replaceArgument(2, $configuration['base_dn'])
            ->replaceArgument(3, $configuration['search_dn'])
            ->replaceArgument(4, $configuration['search_password'])
            ->replaceArgument(5, $configuration['default_roles'])
            ->replaceArgument(6, $configuration['uid_key'])
            ->replaceArgument(7, $configuration['filter']);
    }
}
