<?php

namespace ConnectHolland\LdapBundle;

use ConnectHolland\LdapBundle\DependencyInjection\Security\UserProvider\LdapFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * ConnectHollandLdapBundle.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class ConnectHollandLdapBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addUserProviderFactory(new LdapFactory());
    }
}
