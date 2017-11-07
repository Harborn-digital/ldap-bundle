<?php

namespace ConnectHolland\LdapBundle\Security\User;

use ConnectHolland\LdapBundle\Security\User\Factory\UserFactoryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\LdapUserProvider as BaseLdapUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * LdapEntryUserProvider.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class LdapEntryUserProvider extends BaseLdapUserProvider
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
     * @param LdapInterface        $ldap
     * @param string               $baseDn
     * @param string               $searchDn
     * @param string               $searchPassword
     * @param array                $defaultRoles
     * @param string               $uidKey
     * @param string               $filter
     */
    public function __construct(UserFactoryInterface $userFactory, LdapInterface $ldap, $baseDn, $searchDn = null, $searchPassword = null, array $defaultRoles = array(), $uidKey = 'sAMAccountName', $filter = '({uid_key}={username})')
    {
        $this->userFactory = $userFactory;
        $this->defaultRoles = $defaultRoles;

        parent::__construct($ldap, $baseDn, $searchDn, $searchPassword, $defaultRoles, $uidKey, $filter);
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

        return $this->userFactory->getOrCreate($user->getUsername(), array(), array());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->userFactory->getUserClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function loadUser($username, Entry $entry)
    {
        return $this->userFactory->getOrCreate($username, $entry->getAttributes(), $this->defaultRoles);
    }
}
