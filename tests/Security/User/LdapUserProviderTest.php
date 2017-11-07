<?php

namespace ConnectHolland\LdapBundle\Test\Security\User;

use ConnectHolland\LdapBundle\Security\User\Factory\UserFactoryInterface;
use ConnectHolland\LdapBundle\Security\User\LdapUserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * LdapUserProviderTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class LdapUserProviderTest extends TestCase
{
    /**
     * @var UserFactoryInterface
     */
    private $userFactoryMock;

    /**
     * @var LdapClientInterface
     */
    private $ldapClientMock;

    /**
     * The LdapUserProvider being tested.
     *
     * @var LdapUserProvider
     */
    private $ldapUserProvider;

    /**
     * Creates a LdapUserProvider instance for testing.
     */
    public function setUp()
    {
        if (class_exists(Entry::class)) {
            $this->markTestSkipped('The LdapUserProvider is only functional with Symfony 2.8 - 3.0');
        }

        $this->userFactoryMock = $this->getMockBuilder(UserFactoryInterface::class)
            ->getMock();

        $this->ldapClientMock = $this->getMockBuilder(LdapClientInterface::class)
            ->getMock();

        $this->ldapUserProvider = new LdapUserProvider($this->userFactoryMock, $this->ldapClientMock, 'ou=users,dc=example,dc=com', null, null, array('ROLE_ADMIN'));
    }

    /**
     * Tests if constructing a new LdapUserProvider instance sets the instance properties.
     */
    public function testConstruct()
    {
        $this->assertAttributeSame($this->userFactoryMock, 'userFactory', $this->ldapUserProvider);
        $this->assertAttributeSame(array('ROLE_ADMIN'), 'defaultRoles', $this->ldapUserProvider);
    }

    /**
     * Tests if LdapUserProvider::supportsClass uses the user class from the user factory to validate if the LdapUserProvider supports the class.
     */
    public function testSupportsClass()
    {
        $this->userFactoryMock->expects($this->once())
            ->method('getUserClass')
            ->willReturn('User');

        $this->assertTrue($this->ldapUserProvider->supportsClass('User'));
    }

    /**
     * Tests if LdapUserProvider::loadUser calls the getOrCreate method on the user factory and returns a user instance.
     */
    public function testLoadUser()
    {
        $userMock = $this->getMockBuilder(UserInterface::class)
            ->getMock();

        $this->userFactoryMock->expects($this->once())
            ->method('getOrCreate')
            ->with($this->equalTo('john'), $this->equalTo(array()))
            ->willReturn($userMock);

        $this->assertInstanceOf(UserInterface::class, $this->ldapUserProvider->loadUser('john', array()));
    }

    /**
     * Tests if LdapUserProvider::refreshUser calls the getOrCreate method on the user factory to refresh the user instance.
     */
    public function testRefreshUser()
    {
        $userMock = $this->getMockBuilder(UserInterface::class)
            ->getMock();
        $userMock->expects($this->once())
            ->method('getUsername')
            ->willReturn('john');

        $this->userFactoryMock->expects($this->once())
            ->method('getUserClass')
            ->willReturn(UserInterface::class);
        $this->userFactoryMock->expects($this->once())
            ->method('getOrCreate')
            ->with($this->equalTo('john'), $this->equalTo(array()))
            ->willReturn($userMock);

        $this->assertInstanceOf(UserInterface::class, $this->ldapUserProvider->refreshUser($userMock));
    }

    /**
     * Tests if LdapUserProvider::refreshUser throws an UnsupportedUserException when the user factory does not support the user instance.
     */
    public function testRefreshUserThrowsUnsupportedUserException()
    {
        $userMock = $this->getMockBuilder(UserInterface::class)
            ->setMockClassName('NoUser')
            ->getMock();
        $userMock->expects($this->never())
            ->method('getUsername');

        $this->userFactoryMock->expects($this->once())
            ->method('getUserClass')
            ->willReturn('User');
        $this->userFactoryMock->expects($this->never())
            ->method('getOrCreate');

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('Instances of "NoUser" are not supported.');

        $this->ldapUserProvider->refreshUser($userMock);
    }
}
