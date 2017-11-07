<?php

namespace ConnectHolland\LdapBundle\Test\DependencyInjection;

use ConnectHolland\LdapBundle\DependencyInjection\ConnectHollandLdapExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Ldap\Entry;

/**
 * ConnectHollandLdapExtensionTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class ConnectHollandLdapExtensionTest extends AbstractExtensionTestCase
{
    /**
     * Tests if the ConnectHollandLdapExtension loads the abstract services.
     */
    public function testLoad()
    {
        if (class_exists(Entry::class)) {
            $this->markTestSkipped('This test is only functional with Symfony 2.8 - 3.0');
        }

        $this->load();

        $this->assertContainerBuilderHasService('connect_holland_ldap.security.user.provider.ldap', 'ConnectHolland\\LdapBundle\\Security\\User\\LdapUserProvider');
        $this->assertContainerBuilderHasService('connect_holland_ldap.ldap.client', 'Symfony\\Component\\Ldap\\LdapClient');
        $this->assertContainerBuilderHasService('connect_holland_ldap.security.user.factory.doctrine', 'ConnectHolland\\LdapBundle\\Security\\User\\Factory\\DoctrineUserFactory');
        $this->assertContainerBuilderHasService('connect_holland_ldap.security.user.factory.sulu', 'ConnectHolland\\LdapBundle\\Security\\User\\Factory\\SuluUserFactory');
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return array(
            new ConnectHollandLdapExtension(),
        );
    }
}
