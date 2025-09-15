<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ReferenceRepository $references;
    private User $admin;
    private User $user;
    private User $anonymous;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();

        $executor = $databaseTool->loadFixtures([UserFixtures::class]);

        $this->references = $executor->getReferenceRepository();

        $this->admin = $this->references->getReference(UserFixtures::ADMIN_REFERENCE, User::class);
        $this->user = $this->references->getReference(UserFixtures::USER_REFERENCE, User::class);
        $this->anonymous = $this->references->getReference(UserFixtures::ANONYMOUS_REFERENCE, User::class);
    }

    private function loginAsAdmin(): void
    {
        $this->client->loginUser($this->admin);
    }

    private function loginAsUser(): void
    {
        $this->client->loginUser($this->user);
    }

    // ----------------------------
    // LISTE UTILISATEURS
    // ----------------------------
    public function testListAsAnonymous(): void
    {
        $this->logoutTest();
        $this->client->request('GET', '/users');
        self::assertResponseRedirects('/login');
    }

    public function testListAsUser(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/users');
        self::assertResponseStatusCodeSame(403);
    }

    public function testListAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/users');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('table');
    }

    // ----------------------------
    // CREATION UTILISATEUR
    // ----------------------------
    public function testCreateUserAsAnonymousRedirectsToLogin(): void
    {
        $this->logoutTest();
        $this->client->request('GET', '/users/create');
        self::assertResponseRedirects('/login');
    }

    public function testCreateUserAsUserForbidden(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/users/create');
        self::assertResponseStatusCodeSame(403);
    }

    public function testCreateValidUserAsAdmin(): void
    {
        $this->loginAsAdmin();

        $crawler = $this->client->request('GET', '/users/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'newuser',
            'user[email]' => 'newuser@test.com',
            'user[password][first]' => '1234',
            'user[password][second]' => '1234',
            'user[roles]' => 'ROLE_USER',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/users');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-success');
        self::assertSelectorTextContains('.alert-success', "L'utilisateur a bien été ajouté.");
    }

    public function testCreateInvalidUserAsAdmin(): void
    {
        $this->loginAsAdmin();

        $crawler = $this->client->request('GET', '/users/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => '',
            'user[email]' => 'invalidemail',
            'user[password][first]' => '',
            'user[password][second]' => '',
            'user[roles]' => 'ROLE_USER',
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(200);
        self::assertStringContainsString('pas être vide', $this->client->getResponse()->getContent());
        self::assertStringContainsString('pas un email valide', $this->client->getResponse()->getContent());
    }

    // ----------------------------
    // MODIFICATION UTILISATEUR
    // ----------------------------
    public function testEditUserAsAnonymousRedirectsToLogin(): void
    {
        $this->logoutTest();
        $this->client->request('GET', "/users/{$this->user->getId()}/edit");
        self::assertResponseRedirects('/login');
    }

    public function testEditUserAsUserForbidden(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', "/users/{$this->user->getId()}/edit");
        self::assertResponseStatusCodeSame(403);
    }

    public function testEditUserValidAsAdmin(): void
    {
        $this->loginAsAdmin();

        $crawler = $this->client->request('GET', "/users/{$this->user->getId()}/edit");
        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'updatedUser',
            'user[email]' => 'updated@test.com',
            'user[password][first]' => '1234',
            'user[password][second]' => '1234',
            'user[roles]' => 'ROLE_USER',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/users');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-success');
        self::assertSelectorTextContains('.alert-success', "L'utilisateur a bien été modifié");
    }

    public function testEditUserNotFoundAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/users/9999/edit');
        self::assertResponseStatusCodeSame(404);
    }

    private function logoutTest(): void
    {
        $this->client->getCookieJar()->clear();
    }
}
