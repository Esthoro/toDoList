<?php

namespace App\Tests\Repository;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserRepositoryTest extends WebTestCase
{
    private UserRepository $repository;
    private User $user;

    protected function setUp(): void
    {
        self::bootKernel();

        // Récupérer le repository
        $this->repository = self::getContainer()->get(UserRepository::class);

        // Charger les fixtures
        $databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();

        $executor = $databaseTool->loadFixtures([UserFixtures::class]);

        $references = $executor->getReferenceRepository();

        // Récupérer l'utilisateur à tester
        $this->user = $references->getReference(UserFixtures::USER_REFERENCE, User::class);
    }

    public function testUpgradePassword(): void
    {
        $oldPassword = $this->user->getPassword();
        $newPassword = 'newHashedPassword';

        $this->repository->upgradePassword($this->user, $newPassword);

        $this->assertNotSame($oldPassword, $this->user->getPassword());
        $this->assertSame($newPassword, $this->user->getPassword());
    }

    public function testUpgradePasswordWithUnsupportedUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $mockUser = $this->createMock(\Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface::class);

        $this->repository->upgradePassword($mockUser, 'whatever');
    }
}
