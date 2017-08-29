<?php

namespace ConnectHolland\LdapBundle\Test\Security\User\Factory;

use ConnectHolland\LdapBundle\Security\User\Factory\DoctrineUserFactory;
use ConnectHolland\LdapBundle\Test\Security\User\DummyUser;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

/**
 * DoctrineUserFactoryTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class DoctrineUserFactoryTest extends TestCase
{
    /**
     * @var Registry
     */
    private $doctrineRegistryMock;

    /**
     * The DoctrineUserFactory instance being tested.
     *
     * @var DoctrineUserFactory
     */
    private $userFactory;

    /**
     * Creates a DoctrineUserFactory for testing.
     */
    public function setUp()
    {
        $this->doctrineRegistryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->userFactory = new DoctrineUserFactory($this->doctrineRegistryMock, DummyUser::class);
    }

    /**
     * Tests if constructing a new DoctrineUserFactory sets the instance properties.
     */
    public function testConstruct()
    {
        $this->assertAttributeSame($this->doctrineRegistryMock, 'doctrineRegistry', $this->userFactory);
        $this->assertAttributeSame(DummyUser::class, 'userClass', $this->userFactory);
        $this->assertAttributeSame('username', 'usernameColumn', $this->userFactory);
        $this->assertAttributeSame(array('uid' => 'username'), 'userPropertyMap', $this->userFactory);
    }

    /**
     * Tests if DoctrineUserFactory::getUserClass returns the expected user class set during construction.
     */
    public function testGetUserClass()
    {
        $this->assertSame(DummyUser::class, $this->userFactory->getUserClass());
    }

    /**
     * Tests if DoctrineUserFactory::getOrCreate returns the expected user when the user exists in the database.
     */
    public function testGetOrCreateWithExistingUser()
    {
        $user = new DummyUser();

        $repositoryMock = $this->getMockBuilder(ObjectRepository::class)
            ->getMock();
        $repositoryMock->expects($this->once())
            ->method('findOneBy')
            ->willReturn($user);

        $managerMock = $this->getMockBuilder(ObjectManager::class)
            ->getMock();
        $managerMock->expects($this->once())
            ->method('persist')
            ->with($user);
        $managerMock->expects($this->once())
            ->method('flush')
            ->with($user);

        $this->doctrineRegistryMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(DummyUser::class))
            ->willReturn($repositoryMock);
        $this->doctrineRegistryMock->expects($this->once())
            ->method('getManager')
            ->willReturn($managerMock);

        $this->assertSame($user, $this->userFactory->getOrCreate('john', array('uid' => array('count' => 1, 0 => 'john')), array('ROLE_ADMIN')));
    }

    /**
     * Tests if DoctrineUserFactory::getOrCreate returns the expected user when the user does not exist in the database.
     */
    public function testGetOrCreateWithoutExistingUser()
    {
        $repositoryMock = $this->getMockBuilder(ObjectRepository::class)
            ->getMock();

        $managerMock = $this->getMockBuilder(ObjectManager::class)
            ->getMock();
        $managerMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(DummyUser::class));
        $managerMock->expects($this->once())
            ->method('flush')
            ->with($this->isInstanceOf(DummyUser::class));

        $this->doctrineRegistryMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(DummyUser::class))
            ->willReturn($repositoryMock);
        $this->doctrineRegistryMock->expects($this->once())
            ->method('getManager')
            ->willReturn($managerMock);

        $this->assertInstanceOf(DummyUser::class, $this->userFactory->getOrCreate('john', array(), array('ROLE_ADMIN')));
    }
}
