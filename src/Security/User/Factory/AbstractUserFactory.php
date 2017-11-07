<?php

namespace ConnectHolland\LdapBundle\Security\User\Factory;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * AbstractUserFactory.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
abstract class AbstractUserFactory implements UserFactoryInterface
{
    /**
     * @var array
     */
    protected $userPropertyMap;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * Constructs a new AbstractUserFactory instance.
     *
     * @param array $userPropertyMap
     */
    public function __construct(array $userPropertyMap)
    {
        $this->userPropertyMap = $userPropertyMap;
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * Updates the user entity with the user properties from LDAP.
     *
     * @param UserInterface $user
     * @param array         $ldapUserProperties
     */
    protected function updateUserWithLdapProperties(UserInterface $user, array $ldapUserProperties)
    {
        foreach ($this->userPropertyMap as $ldapKey => $propertyPath) {
            if (isset($ldapUserProperties[$ldapKey][0]) === false) {
                continue;
            }

            $this->propertyAccessor->setValue($user, $propertyPath, $ldapUserProperties[$ldapKey][0]);
        }
    }

    /**
     * Adds the default roles to the existing user roles.
     *
     * @param UserInterface $user
     * @param array         $defaultRoles
     */
    protected function addDefaultRoles(UserInterface $user, array $defaultRoles)
    {
        if (method_exists($user, 'setRoles')) {
            $roles = array_unique(
                array_merge($user->getRoles(), $defaultRoles)
            );

            $user->setRoles($roles);
        }
    }
}
