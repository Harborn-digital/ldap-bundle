<?php

namespace ConnectHolland\LdapBundle\Test\Security\User\Factory;

use ConnectHolland\LdapBundle\Security\User\Factory\SuluUserFactory;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Localization\Manager\LocalizationManagerInterface;
use Sulu\Component\Persistence\Repository\RepositoryInterface;
use Sulu\Component\Security\Authentication\RoleInterface;

/**
 * SuluUserFactoryTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class SuluUserFactoryTest extends TestCase
{
    /**
     * @var Registry
     */
    private $doctrineRegistryMock;

    /**
     * @var RepositoryInterface
     */
    private $userRepositoryMock;

    /**
     * @var RepositoryInterface
     */
    private $contactRepositoryMock;

    /**
     * @var RepositoryInterface
     */
    private $roleRepositoryMock;

    /**
     * @var LocalizationManagerInterface
     */
    private $localizationManagerMock;

    /**
     * The SuluUserFactory instance being tested.
     *
     * @var SuluUserFactory
     */
    private $userFactory;

    /**
     * Creates a SuluUserFactory for testing.
     */
    public function setUp()
    {
        $this->doctrineRegistryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->userRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)
            ->getMock();
        $this->userRepositoryMock->expects($this->once())
            ->method('getClassName')
            ->willReturn(User::class);

        $this->contactRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)
            ->getMock();

        $this->roleRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)
            ->getMock();

        $this->localizationManagerMock = $this->getMockBuilder(LocalizationManagerInterface::class)
            ->getMock();

        $this->userFactory = new SuluUserFactory($this->doctrineRegistryMock, $this->userRepositoryMock, $this->contactRepositoryMock, $this->roleRepositoryMock, $this->localizationManagerMock);
    }

    /**
     * Tests if constructing a new SuluUserFactory sets the instance properties.
     */
    public function testConstruct()
    {
        $this->assertAttributeSame($this->doctrineRegistryMock, 'doctrineRegistry', $this->userFactory);
        $this->assertAttributeSame(User::class, 'userClass', $this->userFactory);
        $this->assertAttributeSame('username', 'usernameColumn', $this->userFactory);
        $this->assertAttributeSame($this->userRepositoryMock, 'userRepository', $this->userFactory);
        $this->assertAttributeSame($this->contactRepositoryMock, 'contactRepository', $this->userFactory);
        $this->assertAttributeSame($this->roleRepositoryMock, 'roleRepository', $this->userFactory);
        $this->assertAttributeSame($this->localizationManagerMock, 'localizationManager', $this->userFactory);
        $this->assertAttributeSame(array('uid' => 'username'), 'userPropertyMap', $this->userFactory);
    }

    /**
     * Tests if SuluUserFactory::getUserClass returns the expected user class set during construction.
     */
    public function testGetUserClass()
    {
        $this->assertSame(User::class, $this->userFactory->getUserClass());
    }

    /**
     * Tests if SuluUserFactory::getOrCreate returns the expected user when the user exists in the database.
     */
    public function testGetOrCreateWithExistingUser()
    {
        $user = new User();

        $repositoryMock = $this->getMockBuilder(ObjectRepository::class)
            ->getMock();
        $repositoryMock->expects($this->once())
            ->method('findOneBy')
            ->willReturn($user);

        $managerMock = $this->getMockBuilder(ObjectManager::class)
            ->getMock();
        $managerMock->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive(
                array($this->isInstanceOf(UserRole::class)),
                array($user)
            );
        $managerMock->expects($this->once())
            ->method('flush')
            ->with($user);

        $this->doctrineRegistryMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(User::class))
            ->willReturn($repositoryMock);
        $this->doctrineRegistryMock->expects($this->exactly(2))
            ->method('getManager')
            ->willReturn($managerMock);

        $roleMock = $this->getMockBuilder(RoleInterface::class)
            ->getMock();

        $this->roleRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('name' => 'Admin')))
            ->willReturn($roleMock);

        $localizationMock = $this->getMockBuilder(Localization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localizationMock->expects($this->once())
            ->method('getLocale')
            ->willReturn('en');

        $this->localizationManagerMock->expects($this->once())
            ->method('getLocalizations')
            ->willReturn(array($localizationMock));

        $this->assertSame($user, $this->userFactory->getOrCreate('john', array('uid' => array('count' => 1, 0 => 'john')), array('Admin')));
    }

    /**
     * Tests if SuluUserFactory::getOrCreate returns the expected user when the user does not exist in the database.
     */
    public function testGetOrCreateWithoutExistingUser()
    {
        $user = new User();

        $repositoryMock = $this->getMockBuilder(ObjectRepository::class)
            ->getMock();

        $managerMock = $this->getMockBuilder(ObjectManager::class)
            ->getMock();
        $managerMock->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive(
                array($this->isInstanceOf(Contact::class)),
                array($user)
            );
        $managerMock->expects($this->once())
            ->method('flush')
            ->with($this->isInstanceOf(User::class));

        $this->doctrineRegistryMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(User::class))
            ->willReturn($repositoryMock);
        $this->doctrineRegistryMock->expects($this->exactly(3))
            ->method('getManager')
            ->willReturn($managerMock);

        $this->userRepositoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn($user);

        $this->contactRepositoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn(new Contact());

        $localizationMock = $this->getMockBuilder(Localization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localizationMock->expects($this->exactly(2))
            ->method('getLocale')
            ->willReturn('en');

        $this->localizationManagerMock->expects($this->exactly(2))
            ->method('getLocalizations')
            ->willReturn(array($localizationMock));

        $this->assertSame($user, $this->userFactory->getOrCreate('john', array(), array('Admin')));
    }
}
