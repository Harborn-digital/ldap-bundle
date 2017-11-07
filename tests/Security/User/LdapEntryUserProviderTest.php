<?php

namespace ConnectHolland\LdapBundle\Test\Security\User;

use ConnectHolland\LdapBundle\Security\User\Factory\UserFactoryInterface;
use ConnectHolland\LdapBundle\Security\User\LdapEntryUserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * LdapEntryUserProviderTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class LdapEntryUserProviderTest extends TestCase
{
    /**
     * @var UserFactoryInterface
     */
    private $userFactoryMock;

    /**
     * @var LdapInterface
     */
    private $ldapMock;

    /**
     * The LdapEntryUserProvider being tested.
     *
     * @var LdapEntryUserProvider
     */
    private $ldapUserProvider;

    /**
     * Creates a LdapEntryUserProvider instance for testing.
     */
    public function setUp()
    {
        if (class_exists(Entry::class) === false) {
            $this->markTestSkipped('The LdapEntryUserProvider is only functional with Symfony 3.1+');
        }

        $this->userFactoryMock = $this->getMockBuilder(UserFactoryInterface::class)
            ->getMock();

        $this->ldapMock = $this->getMockBuilder(LdapInterface::class)
            ->getMock();

        $this->ldapUserProvider = new LdapEntryUserProvider($this->userFactoryMock, $this->ldapMock, 'ou=users,dc=example,dc=com', null, null, array('ROLE_ADMIN'), 'uid');
    }

    /**
     * Tests if constructing a new LdapEntryUserProvider instance sets the instance properties.
     */
    public function testConstruct()
    {
        $this->assertAttributeSame($this->userFactoryMock, 'userFactory', $this->ldapUserProvider);
        $this->assertAttributeSame(array('ROLE_ADMIN'), 'defaultRoles', $this->ldapUserProvider);
    }

    /**
     * Tests if LdapEntryUserProvider::supportsClass uses the user class from the user factory to validate if the LdapUserProvider supports the class.
     */
    public function testSupportsClass()
    {
        $this->userFactoryMock->expects($this->once())
            ->method('getUserClass')
            ->willReturn('User');

        $this->assertTrue($this->ldapUserProvider->supportsClass('User'));
    }

    /**
     * Tests if LdapEntryUserProvider::loadUser calls the getOrCreate method on the user factory and returns a user instance.
     */
    public function testLoadUser()
    {
        $entry = new Entry('uid=john,ou=users,dc=example,dc=com', array('uid' => array('john')));

        $queryMock = $this->getMockBuilder(QueryInterface::class)
            ->getMock();
        $queryMock->expects($this->once())
            ->method('execute')
            ->willReturn(array($entry));

        $this->ldapMock->expects($this->once())
            ->method('query')
            ->willReturn($queryMock);

        $userMock = $this->getMockBuilder(UserInterface::class)
            ->getMock();

        $this->userFactoryMock->expects($this->once())
            ->method('getOrCreate')
            ->with($this->equalTo('john'), $this->equalTo(array('uid' => array('john'))))
            ->willReturn($userMock);

        $this->assertInstanceOf(UserInterface::class, $this->ldapUserProvider->loadUserByUsername('john'));
    }

    /**
     * Tests if LdapEntryUserProvider::refreshUser calls the getOrCreate method on the user factory to refresh the user instance.
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
     * Tests if LdapEntryUserProvider::refreshUser throws an UnsupportedUserException when the user factory does not support the user instance.
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
