<?php

namespace ConnectHolland\LdapBundle\Test\DependencyInjection\Security\UserProvider;

use ConnectHolland\LdapBundle\DependencyInjection\Security\UserProvider\LdapFactory;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * LdapFactoryTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class LdapFactoryTest extends AbstractContainerBuilderTestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * The LdapFactory instance being tested.
     *
     * @var LdapFactory
     */
    private $ldapFactory;

    /**
     * Creates the LdapFactory instance for testing.
     */
    public function setUp()
    {
        $this->ldapFactory = new LdapFactory();

        parent::setUp();
    }

    /**
     * Tests if LdapFactory::getKey returns the expected value.
     */
    public function testGetKey()
    {
        $this->assertSame('custom_user_ldap', $this->ldapFactory->getKey());
    }

    /**
     * Tests if LdapFactory::addConfiguration with minimal configuration results in the expected configuration defaults.
     */
    public function testAddConfiguration()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'connection' => array(
                        'host' => 'ldap.example.com',
                    ),
                    'user_factory' => array(
                        'user_property_map' => array(
                            'uid' => 'username',
                        ),
                    ),
                    'base_dn' => 'ou=users,dc=example,dc=com',
                ),
            ),
            array(
                'connection' => array(
                    'host' => 'ldap.example.com',
                    'port' => 389,
                    'options' => array(
                        'protocol_version' => 3,
                        'referrals' => false,
                    ),
                ),
                'user_factory' => array(
                    'user_property_map' => array(
                        'uid' => 'username',
                    ),
                ),
                'base_dn' => 'ou=users,dc=example,dc=com',
                'default_roles' => array(),
                'uid_key' => 'sAMAccountName',
                'filter' => '({uid_key}={username})',
            )
        );
    }

    /**
     * Tests if LdapFactory::create creates the service definitions.
     */
    public function testCreate()
    {
        $this->ldapFactory->create(
            $this->container,
            'security.user.provider.concrete.my_ldap',
            array(
                'connection' => array(
                    'host' => 'ldap.example.com',
                    'port' => 389,
                    'options' => array(
                        'protocol_version' => 3,
                        'referrals' => false,
                    ),
                ),
                'user_factory' => array(
                    'type' => 'doctrine',
                    'user_class' => 'SomeUser',
                    'username_column' => 'username',
                    'user_property_map' => array(
                        'uid' => 'username',
                    ),
                ),
                'base_dn' => '',
                'search_dn' => null,
                'search_password' => null,
                'default_roles' => array(
                    'Admin',
                ),
                'uid_key' => 'sAMAccountName',
                'filter' => '({uid_key}={username})',
            )
        );

        $this->assertContainerBuilderHasParameter('security.user.provider.concrete.my_ldap.user.factory.user_property_map', array('uid' => 'username'));
        $this->assertContainerBuilderHasServiceDefinitionWithParent('security.user.provider.concrete.my_ldap.user.factory', 'connect_holland_ldap.security.user.factory.doctrine');
        $this->assertContainerBuilderHasServiceDefinitionWithParent('security.user.provider.concrete.my_ldap.client', 'connect_holland_ldap.ldap.client');
        $this->assertContainerBuilderHasServiceDefinitionWithParent('security.user.provider.concrete.my_ldap', 'connect_holland_ldap.security.user.provider.ldap');
    }

    /**
     * Tests if LdapFactory::create creates the service definitions, but not a service definition for the user factory.
     */
    public function testCreateWithCustomUserFactory()
    {
        $this->ldapFactory->create(
            $this->container,
            'security.user.provider.concrete.my_ldap',
            array(
                'connection' => array(
                    'host' => 'ldap.example.com',
                    'port' => 389,
                    'options' => array(
                        'protocol_version' => 3,
                        'referrals' => false,
                    ),
                ),
                'user_factory' => array(
                    'service' => 'some.user.factory.service',
                    'user_property_map' => array(
                        'uid' => 'username',
                    ),
                ),
                'base_dn' => '',
                'search_dn' => null,
                'search_password' => null,
                'default_roles' => array(
                    'Admin',
                ),
                'uid_key' => 'sAMAccountName',
                'filter' => '({uid_key}={username})',
            )
        );

        $this->assertContainerBuilderHasParameter('security.user.provider.concrete.my_ldap.user.factory.user_property_map', array('uid' => 'username'));
        $this->assertContainerBuilderNotHasService('security.user.provider.concrete.my_ldap.user.factory');
        $this->assertContainerBuilderHasServiceDefinitionWithParent('security.user.provider.concrete.my_ldap.client', 'connect_holland_ldap.ldap.client');
        $this->assertContainerBuilderHasServiceDefinitionWithParent('security.user.provider.concrete.my_ldap', 'connect_holland_ldap.security.user.provider.ldap');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('custom_user_ldap');
        $this->ldapFactory->addConfiguration($rootNode);

        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
            ->getMock();
        $configurationMock->expects($this->any())
            ->method('getConfigTreeBuilder')
            ->willReturn($treeBuilder);

        return $configurationMock;
    }
}
