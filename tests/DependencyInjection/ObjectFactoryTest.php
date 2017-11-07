<?php

namespace ConnectHolland\LdapBundle\Test\DependencyInjection;

use ConnectHolland\LdapBundle\DependencyInjection\ObjectFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Ldap;

/**
 * ObjectFactoryTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class ObjectFactoryTest extends TestCase
{
    /**
     * The ObjectFactory being tested.
     *
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * Creates a new ObjectFactory instance for testing.
     */
    public function setUp()
    {
        if (class_exists(Entry::class) === false) {
            $this->markTestSkipped('This test is only functional with Symfony 3.1+');
        }

        $this->objectFactory = new ObjectFactory();
    }

    /**
     * Tests if ObjectFactory::createLdap creates a new Ldap instance.
     */
    public function testCreateLdap()
    {
        $ldap = $this->objectFactory->createLdap(array());

        $this->assertInstanceOf(Ldap::class, $ldap);
    }
}
