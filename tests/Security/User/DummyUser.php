<?php

namespace ConnectHolland\LdapBundle\Test\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * DummyUser.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class DummyUser implements UserInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var array
     */
    private $roles = array();

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Sets the username.
     *
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Sets the password.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
    }

    /**
     * Sets the password salt.
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
    }

    /**
     * Sets the roles.
     *
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}
