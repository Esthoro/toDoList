<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public const TASK_REFERENCE_USER = 'task_user';
    public const TASK_REFERENCE_ADMIN = 'task_admin';
    public const TASK_REFERENCE_ANONYMOUS = 'task_anonymous';

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(UserFixtures::USER_REFERENCE, User::class);
        $admin = $this->getReference(UserFixtures::ADMIN_REFERENCE, User::class);
        $anonymous = $this->getReference(UserFixtures::ANONYMOUS_REFERENCE, User::class);

        // Tâches pour utilisateur normal
        $task1 = new Task();
        $task1->setTitle('Tâche user')
            ->setContent('Contenu de la tâche user')
            ->setAuthor($user);
        $manager->persist($task1);
        $this->addReference(self::TASK_REFERENCE_USER, $task1);

        // Tâche pour admin
        $taskAdmin = new Task();
        $taskAdmin->setTitle('Tâche admin')
            ->setContent('Contenu de la tâche admin')
            ->setAuthor($admin);
        $manager->persist($taskAdmin);
        $this->addReference(self::TASK_REFERENCE_ADMIN, $taskAdmin);

        // Tâche anonyme
        $taskAnon = new Task();
        $taskAnon->setTitle('Tâche anonyme')
            ->setContent('Contenu de la tâche anonyme')
            ->setAuthor($anonymous);
        $manager->persist($taskAnon);
        $this->addReference(self::TASK_REFERENCE_ANONYMOUS, $taskAnon);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
