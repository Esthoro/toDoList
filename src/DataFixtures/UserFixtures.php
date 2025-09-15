<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USER_REFERENCE = 'user';
    public const ADMIN_REFERENCE = 'admin';
    public const ANONYMOUS_REFERENCE = 'anonymous';

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ----- Utilisateur normal -----
        $user = new User();
        $user->setUsername('user');
        $user->setEmail('user@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, '1234'));
        $manager->persist($user);
        $this->addReference(self::USER_REFERENCE, $user);

        // ----- Admin -----
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, '1234'));
        $manager->persist($admin);
        $this->addReference(self::ADMIN_REFERENCE, $admin);

        // ----- Anonyme (username = anonymous) -----
        $anonymous = new User();
        $anonymous->setUsername('anonymous');
        $anonymous->setEmail('anonymous@test.com');
        $anonymous->setRoles(['ROLE_USER']);
        $anonymous->setPassword($this->passwordHasher->hashPassword($anonymous, '1234'));
        $manager->persist($anonymous);
        $this->addReference(self::ANONYMOUS_REFERENCE, $anonymous);

        $manager->flush();
    }
}
