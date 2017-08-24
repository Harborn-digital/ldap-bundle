<?php

namespace ConnectHolland\LdapBundle\Security\User;

use ConnectHolland\LdapBundle\Security\User\Factory\UserFactoryInterface;
use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\LdapUserProvider as BaseLdapUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * LdapUserProvider.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class LdapUserProvider extends BaseLdapUserProvider
{
    /**
     * @var UserFactoryInterface
     */
    private $userFactory;

    /**
     * @var array
     */
    private $defaultRoles;

    /**
     * Constructs a new LdapUserProvider instance.
     *
     * @param UserFactoryInterface $userFactory
     * @param LdapClientInterface  $ldap
     * @param string               $baseDn
     * @param string               $searchDn
     * @param string               $searchPassword
     * @param array                $defaultRoles
     * @param string               $uidKey
     * @param string               $filter
     */
    public function __construct(UserFactoryInterface $userFactory, LdapClientInterface $ldap, $baseDn, $searchDn = null, $searchPassword = null, array $defaultRoles = array(), $uidKey = 'sAMAccountName', $filter = '({uid_key}={username})')
    {
        $this->userFactory = $userFactory;
        $this->defaultRoles = $defaultRoles;

        parent::__construct($ldap, $baseDn, $searchDn, $searchPassword, $defaultRoles, $uidKey, $filter);
    }

    /**
     * @param string $username
     * @param array  $user
     */
    public function loadUser($username, $user)
    {
        return $this->userFactory->getOrCreate($username, $user, $this->defaultRoles);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $userClass = $this->userFactory->getUserClass();
        if ($user instanceof $userClass === false) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->userFactory->getOrCreate($user->getUsername(), array());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->userFactory->getUserClass();
    }
}
