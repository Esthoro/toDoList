<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testSetAndGetId(): void
    {
        $user = new User();
        $user->setId(42);
        $this->assertSame(42, $user->getId());
    }

    public function testSetAndGetEmail(): void
    {
        $user = new User();

        $email = "test@example.com";
        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($email, $user->getUserIdentifier());
    }

    public function testSetAndGetUsername(): void
    {
        $user = new User();

        $username = "esther";
        $user->setUsername($username);
        $this->assertSame($username, $user->getUsername());

    }

    public function testSetAndGetPassword(): void
    {
        $user = new User();

        $password = "secret123";
        $user->setPassword($password);
        $this->assertSame($password, $user->getPassword());
    }

    public function testSetAndGetRoles(): void
    {
        $user = new User();

        // Tableau de rôles vide
        $user->setRoles([]);
        $roles = $user->getRoles();
        $this->assertContains("ROLE_USER", $roles);
        $this->assertCount(1, $roles);

        // Plusieurs rôles
        $user->setRoles(["ROLE_ADMIN"]);
        $roles = $user->getRoles();
        $this->assertContains("ROLE_USER", $roles);
        $this->assertContains("ROLE_ADMIN", $roles);
        $this->assertCount(2, $roles);

        // Doublons
        $user->setRoles(["ROLE_ADMIN", "ROLE_USER"]);
        $roles = $user->getRoles();
        $this->assertCount(2, $roles); // ROLE_USER ajouté automatiquement et doublon supprimé
    }

    public function testFluentInterface(): void
    {
        $user = new User();

        $return = $user
            ->setId(10)
            ->setEmail("test@example.com")
            ->setUsername("esther")
            ->setPassword("secret")
            ->setRoles(["ROLE_ADMIN"]);

        $this->assertSame($user, $return);
    }

    public function testSerialize(): void
    {
        $user = new User();
        $user->setPassword("mypassword");
        $data = $user->__serialize();

        // Vérifie que le password est bien hashé en crc32c
        $this->assertSame(hash("crc32c", "mypassword"), $data["\0".User::class."\0password"]);
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        // Cette méthode ne fait rien pour l'instant, juste vérifier qu'elle existe
        $user->eraseCredentials();
        $this->assertTrue(method_exists($user, 'eraseCredentials'));
    }

    public function testTypeSafety(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getPassword());
        $this->assertIsArray($user->getRoles());
        $this->assertContains("ROLE_USER", $user->getRoles());
    }

}
