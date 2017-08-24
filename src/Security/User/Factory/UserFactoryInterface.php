<?php

namespace ConnectHolland\LdapBundle\Security\User\Factory;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserFactoryInterface.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
interface UserFactoryInterface
{
    /**
     * Returns the class name of the user class.
     *
     * @return string
     */
    public function getUserClass();

    /**
     * Returns a user entity from the database or a newly created entity.
     *
     * @param string $username
     * @param array  $ldapUserProperties
     *
     * @return UserInterface
     */
    public function getOrCreate($username, array $ldapUserProperties, array $defaultRoles);
}
