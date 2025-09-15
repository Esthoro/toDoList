<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use App\DataFixtures\UserFixtures;

class SecurityControllerTest extends WebTestCase
{
    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([UserFixtures::class]);
    }

    public function testLogin(): void
    {
        // #1 : wrong email address
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Se connecter', [
            '_username' => 'faux-user',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        self::assertSelectorExists('.alert-danger');

        // #2 : wrong password
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Se connecter', [
            '_username' => 'user',
            '_password' => 'faux-mdp',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        self::assertSelectorExists('.alert-danger');

        // #3 : right username & password
        $this->client->submitForm('Se connecter', [
            '_username' => 'user',
            '_password' => '1234',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        self::assertSelectorNotExists('.alert-danger');
        self::assertResponseIsSuccessful();
    }

    public function testAlreadyLoggedInRedirect(): void
    {
        $user = static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user@test.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/login');
        self::assertResponseRedirects('/');
    }

    public function testLogout(): void
    {
        $this->expectException(\LogicException::class);
        $controller = new \App\Controller\SecurityController();
        $controller->logout();
    }

    public function testLoginFormRendered(): void
    {
        $this->client->request('GET', '/login');
        self::assertSelectorExists('form[name="login_type"]');
    }


}
