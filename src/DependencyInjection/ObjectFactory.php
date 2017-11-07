<?php

namespace ConnectHolland\LdapBundle\DependencyInjection;

use Symfony\Component\Ldap\Ldap;

/**
 * Creates objects for the service container.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class ObjectFactory
{
    /**
     * Creates a new Ldap instance.
     *
     * @param array $configuration
     *
     * @return Ldap
     */
    public static function createLdap(array $configuration)
    {
        return Ldap::create('ext_ldap', $configuration);
    }
}
