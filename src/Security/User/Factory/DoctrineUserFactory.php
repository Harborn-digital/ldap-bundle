<?php

namespace ConnectHolland\LdapBundle\Security\User\Factory;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User factory for retrieving and creating Doctrine entities.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class DoctrineUserFactory extends AbstractUserFactory
{
    /**
     * The Doctrine Registry instance.
     *
     * @var Registry
     */
    protected $doctrineRegistry;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @var string
     */
    private $usernameColumn;

    /**
     * Constructs a new DoctrineUserFactory instance.
     *
     * @param Registry $doctrineRegistry
     * @param string   $userClass
     * @param string   $usernameColumn
     * @param array    $userPropertyMap
     */
    public function __construct(Registry $doctrineRegistry, $userClass, $usernameColumn = 'username', array $userPropertyMap = array('uid' => 'username'))
    {
        $this->doctrineRegistry = $doctrineRegistry;
        $this->userClass = $userClass;
        $this->usernameColumn = $usernameColumn;

        parent::__construct($userPropertyMap);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserClass()
    {
        return $this->userClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrCreate($username, array $ldapUserProperties, array $defaultRoles = array())
    {
        $user = $this->getOrCreateUserEntity($username);

        $this->updateUserWithLdapProperties($user, $ldapUserProperties);
        $this->addDefaultRoles($user, $defaultRoles);

        $objectManager = $this->doctrineRegistry->getManager();
        $objectManager->persist($user);
        $objectManager->flush($user);

        return $user;
    }

    /**
     * Creates and returns a new user entity.
     *
     * @param string $username
     *
     * @return UserInterface
     */
    protected function createUserEntity($username)
    {
        $userClass = $this->getUserClass();
        $user = new $userClass();
        if (method_exists($user, 'setPassword')) {
            $user->setPassword('');
        }
        if (method_exists($user, 'setSalt')) {
            $user->setSalt('');
        }

        return $user;
    }

    /**
     * Returns a user entity from the database or a newly created entity.
     *
     * @param $username
     *
     * @return UserInterface
     */
    private function getOrCreateUserEntity($username)
    {
        $userClass = $this->getUserClass();
        $user = $this->doctrineRegistry->getRepository($userClass)
            ->findOneBy(
                array(
                    $this->usernameColumn => $username,
                )
            );

        if ($user instanceof $userClass === false) {
            $user = $this->createUserEntity($username);
        }

        return $user;
    }
}
