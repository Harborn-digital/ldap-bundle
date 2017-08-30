<?php

namespace ConnectHolland\LdapBundle\Security\User\Factory;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Component\Localization\Manager\LocalizationManagerInterface;
use Sulu\Component\Persistence\Repository\RepositoryInterface;
use Sulu\Component\Security\Authentication\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User factory for retrieving and creating Sulu CMS user entities.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class SuluUserFactory extends DoctrineUserFactory
{
    /**
     * @var RepositoryInterface
     */
    private $userRepository;

    /**
     * @var RepositoryInterface
     */
    private $contactRepository;

    /**
     * @var RepositoryInterface
     */
    private $roleRepository;

    /**
     * @var LocalizationManagerInterface
     */
    private $localizationManager;

    /**
     * Constructs a new SuluUserFactory instance.
     *
     * @param Registry                     $doctrineRegistry
     * @param RepositoryInterface          $userRepository
     * @param RepositoryInterface          $contactRepository
     * @param RepositoryInterface          $roleRepository
     * @param LocalizationManagerInterface $localizationManager
     * @param array                        $userPropertyMap
     */
    public function __construct(Registry $doctrineRegistry, RepositoryInterface $userRepository, RepositoryInterface $contactRepository, RepositoryInterface $roleRepository, LocalizationManagerInterface $localizationManager, array $userPropertyMap = array('uid' => 'username'))
    {
        parent::__construct($doctrineRegistry, $userRepository->getClassName(), 'username', $userPropertyMap);

        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
        $this->roleRepository = $roleRepository;
        $this->localizationManager = $localizationManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function createUserEntity($username)
    {
        $objectManager = $this->doctrineRegistry->getManager();
        $locales = $this->getLocales();

        $user = $this->userRepository->createNew();

        $contact = $this->contactRepository->createNew();
        $objectManager->persist($contact);

        $user->setContact($contact);
        $user->setUsername($username);
        $user->setSalt('');
        $user->setPassword('');
        $user->setLocale(current($locales));

        return $user;
    }

    /**
     * Adds the default roles to the existing user roles.
     *
     * @param UserInterface $user
     * @param array         $defaultRoles
     */
    protected function addDefaultRoles(UserInterface $user, array $defaultRoles)
    {
        $objectManager = $this->doctrineRegistry->getManager();
        $locales = $this->getLocales();

        foreach ($defaultRoles as $defaultRole) {
            $role = $this->roleRepository->findOneBy(
                array('name' => $defaultRole)
            );

            if ($role instanceof RoleInterface === false) {
                continue;
            }

            $userRole = new UserRole();
            $userRole->setRole($role);
            $userRole->setUser($user);
            $userRole->setLocale(json_encode($locales));

            $objectManager->persist($userRole);
        }
    }

    /**
     * Returns the locales of Sulu.
     *
     * @return array
     */
    private function getLocales()
    {
        $locales = array();
        $localizations = $this->localizationManager->getLocalizations();
        foreach ($localizations as $localization) {
            /* @var Localization $localization */
            $locales[] = $localization->getLocale();
        }

        return $locales;
    }
}
