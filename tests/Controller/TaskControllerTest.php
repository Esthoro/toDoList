<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\TaskFixtures;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private ?KernelBrowser $client;
    private ReferenceRepository $references;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Initialiser DatabaseTool
        $databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();

        // Charger fixtures et garder les références
        $executor = $databaseTool->loadFixtures([
            UserFixtures::class,
            TaskFixtures::class,
        ]);
        $this->references = $executor->getReferenceRepository();
    }

    public function testListTasks(): void
    {
        $user = $this->references->getReference(UserFixtures::USER_REFERENCE, User::class);
        $this->client->loginUser($user);

        $this->client->request('GET', '/tasks');
        self::assertResponseIsSuccessful();
    }

    public function testCreateTaskValid(): void
    {
        $user = $this->references->getReference(UserFixtures::USER_REFERENCE, User::class);
        $this->client->loginUser($user);

        $this->client->request('GET', '/tasks/create');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Ajouter', [
            'task[title]' => 'New Task',
            'task[content]' => 'This is a new task',
        ]);

        self::assertResponseRedirects('/tasks');
        $this->client->followRedirect();

        $task = $this->client->getContainer()->get('doctrine')
            ->getRepository(Task::class)
            ->findOneBy(['title' => 'New Task']);
        self::assertNotNull($task);
        self::assertSame($user->getId(), $task->getAuthor()->getId());
    }

    public function testEditTask(): void
    {
        $user = $this->references->getReference(UserFixtures::USER_REFERENCE, User::class);
        $this->client->loginUser($user);

        $taskRef = $this->references->getReference(TaskFixtures::TASK_REFERENCE_USER, Task::class);
        $task = $this->client->getContainer()->get('doctrine')
            ->getRepository(Task::class)
            ->find($taskRef->getId());

        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Modifier', [
            'task[title]' => 'Updated title',
            'task[content]' => 'Updated content',
        ]);

        self::assertResponseRedirects('/tasks');
        $this->client->followRedirect();

        // Recharger depuis la DB au lieu de refresh()
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $updatedTask = $em->getRepository(Task::class)->find($task->getId());

        self::assertSame('Updated title', $updatedTask->getTitle());
    }


    public function testToggleTask(): void
    {
        $user = $this->references->getReference(UserFixtures::USER_REFERENCE, User::class);
        $this->client->loginUser($user);

        // On récupère une vraie tâche depuis la DB
        $taskRef = $this->references->getReference(TaskFixtures::TASK_REFERENCE_USER, Task::class);
        $task = $this->client->getContainer()->get('doctrine')
            ->getRepository(Task::class)
            ->find($taskRef->getId());

        // Faire un GET sur la page des tâches pour récupérer le formulaire avec le CSRF token
        $crawler = $this->client->request('GET', '/tasks');
        $form = $crawler->filter('form[action="/tasks/' . $task->getId() . '/toggle"]')->form();
        $token = $form->get('_token')->getValue();

        // Envoyer la requête POST avec le token
        $this->client->request('POST', '/tasks/' . $task->getId() . '/toggle', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/tasks');

        // Recharger la tâche depuis la DB pour vérifier la modif
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $updatedTask = $em->getRepository(Task::class)->find($task->getId());

        self::assertTrue($updatedTask->isDone());
    }

    public function testDeleteTaskByAuthor(): void
    {
        $user = $this->references->getReference(UserFixtures::USER_REFERENCE, User::class);
        $this->client->loginUser($user);

        $taskRef = $this->references->getReference(TaskFixtures::TASK_REFERENCE_USER, Task::class);
        $task = $this->client->getContainer()->get('doctrine')
            ->getRepository(Task::class)
            ->find($taskRef->getId());

        // Récupérer la page des tâches pour initialiser la session et trouver le formulaire
        $crawler = $this->client->request('GET', '/tasks');

        // Sélectionner le formulaire delete correspondant à cette tâche
        $formNode = $crawler->filter('form[action="/tasks/' . $task->getId() . '/delete"]')->first();
        $token = $formNode->filter('input[name="_token"]')->attr('value');

        // Envoyer la requête POST avec le vrai token
        $this->client->request('POST', '/tasks/' . $task->getId() . '/delete', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/tasks');

        // Vérifier que la tâche a bien été supprimée
        $deleted = $this->client->getContainer()->get('doctrine')
            ->getRepository(Task::class)
            ->find($task->getId());
        self::assertNull($deleted);
    }

    public function testDeleteTaskForbiddenForOtherUser(): void
    {
        $taskRef = $this->references->getReference(TaskFixtures::TASK_REFERENCE_USER, Task::class);
        $task = $this->client->getContainer()->get('doctrine')
            ->getRepository(Task::class)
            ->find($taskRef->getId());

        $otherUser = $this->references->getReference(UserFixtures::ADMIN_REFERENCE, User::class);
        $this->client->loginUser($otherUser);

        // Envoyer la requête POST
        //On ne peut pas utiliser de vrai token car le formulaire n'existe pas sur la page
        //Impossible d'en créer un manuellement car pas de session...
        //A réfléchir
        $this->client->request('POST', '/tasks/'.$task->getId().'/delete', [
            '_token' => 'faux_token',
        ]);

        // Vérifie que c’est bien interdit
        self::assertResponseStatusCodeSame(403);
    }


    public function testDeleteAnonymousTaskByAdmin(): void
    {
        // Récupérer l'admin et la tâche anonyme
        $admin = $this->references->getReference(UserFixtures::ADMIN_REFERENCE, User::class);
        $this->client->loginUser($admin);

        $taskRef = $this->references->getReference(TaskFixtures::TASK_REFERENCE_ANONYMOUS, Task::class);
        $task = $this->client->getContainer()->get('doctrine')
            ->getRepository(Task::class)
            ->find($taskRef->getId());

        // Faire un GET pour initialiser la session et récupérer le formulaire
        $crawler = $this->client->request('GET', '/tasks');

        // Sélectionner le formulaire delete correspondant à cette tâche
        $formNode = $crawler->filter('form[action="/tasks/' . $task->getId() . '/delete"]')->first();
        $token = $formNode->filter('input[name="_token"]')->attr('value');

        // Envoyer la requête POST avec le vrai token
        $this->client->request('POST', '/tasks/' . $task->getId() . '/delete', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/tasks');

        // Vérifier que la tâche a bien été supprimée
        $deleted = $this->client->getContainer()->get('doctrine')
            ->getRepository(Task::class)
            ->find($task->getId());
        self::assertNull($deleted);
    }

}
