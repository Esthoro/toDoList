<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class DefaultControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private User $admin;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();
        $executor = $databaseTool->loadFixtures([UserFixtures::class]);
        $references = $executor->getReferenceRepository();

        $this->admin = $references->getReference(UserFixtures::ADMIN_REFERENCE, User::class);
    }

    public function testIndexAsAdmin(): void
    {
        // Connecter l'admin
        $this->client->loginUser($this->admin);

        $url = $this->client->getContainer()->get('router')->generate('app_homepage');
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Bienvenue', $this->client->getResponse()->getContent());
    }

    public function testIndexAsAnonymous(): void
    {
        $url = $this->client->getContainer()->get('router')->generate('app_homepage');
        $this->client->request('GET', $url);

        // Vérifie la redirection vers /login si non connecté
        $this->assertResponseRedirects('/login');
    }
}
