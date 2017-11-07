<?php

namespace ConnectHolland\LdapBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Ldap\Entry;

/**
 * Loads and manages the bundle configuration.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class ConnectHollandLdapExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $additionalServicesFile = 'services_ldap.xml';
        if (class_exists(Entry::class)) {
            $additionalServicesFile = 'services_ldap_entry.xml';
        }

        $loader->load($additionalServicesFile);
    }
}
