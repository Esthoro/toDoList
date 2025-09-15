<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testConstructorInitializesDefaults(): void
    {
        $task = new Task();

        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
        $this->assertFalse($task->isDone());

        // La date doit être proche de now
        $now = new \DateTimeImmutable();
        $this->assertLessThan(2, abs($task->getCreatedAt()->getTimestamp() - $now->getTimestamp()));
    }

    public function testSetAndGetId(): void
    {
        $task = new Task();
        $task->setId(42);
        $this->assertSame(42, $task->getId());
    }

    public function testSetAndGetAuthor(): void
    {
        $task = new Task();
        $user = new User();

        $task->setAuthor($user);
        $this->assertSame($user, $task->getAuthor());
    }

    public function testSetAndGetTitle(): void
    {
        $task = new Task();
        $task->setTitle("Faire les courses");
        $this->assertSame("Faire les courses", $task->getTitle());

    }

    public function testSetAndGetContent(): void
    {
        $task = new Task();
        $task->setContent("Acheter du lait et du pain");
        $this->assertSame("Acheter du lait et du pain", $task->getContent());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $task = new Task();
        $date = new \DateTimeImmutable('2025-01-01 12:00:00');
        $task->setCreatedAt($date);
        $this->assertSame($date, $task->getCreatedAt());
    }

    public function testSetAndGetIsDone(): void
    {
        $task = new Task();
        $task->setIsDone(true);
        $this->assertTrue($task->isDone());

        $task->setIsDone(false);
        $this->assertFalse($task->isDone());
    }

    public function testFluentInterface(): void
    {
        $task = new Task();
        $user = new User();
        $date = new \DateTimeImmutable('2025-01-01');

        $return = $task
            ->setId(10)
            ->setTitle("Titre")
            ->setContent("Contenu")
            ->setAuthor($user)
            ->setCreatedAt($date)
            ->setIsDone(true);

        $this->assertSame($task, $return);
    }

    public function testTypeSafety(): void
    {
        $task = new Task();

        $user = new User();
        $task->setAuthor($user);

        $this->assertNull($task->getId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
        $this->assertIsBool($task->isDone());
        $this->assertNull($task->getTitle());
        $this->assertNull($task->getContent());
        $this->assertInstanceOf(User::class, $task->getAuthor());
    }
}
