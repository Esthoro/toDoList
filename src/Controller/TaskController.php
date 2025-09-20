<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends AbstractController
{
    private TaskRepository $taskRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        TaskRepository $taskRepository,
        EntityManagerInterface $entityManager,
    )
    {
        $this->taskRepository = $taskRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/tasks', name: 'app_task_list')]
    public function list(): Response
    {
        $tasks = $this->taskRepository->findAll();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/tasks/create', name: 'app_task_create')]
    public function create(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Assigne l'utilisateur connecté comme auteur
            if ($this->getUser()) { //Ajouté pour faire plaisir à SymfonyInsight. Pas sûr que c'est nécessaire
                $task->setAuthor($this->getUser());

                $this->entityManager->persist($task);
                $this->entityManager->flush();

                $this->addFlash('success', 'La tâche a bien été ajoutée.');

                return $this->redirectToRoute('app_task_list');
            }
        
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tasks/{id}/edit', name: 'app_task_edit')]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('app_task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'app_task_toggle', methods: ['POST'])]
    public function toggle(Request $request, Task $task): Response
    {
        $submittedToken = (string) $request->request->get('_token') ?? null;

        if (!$this->isCsrfTokenValid('toggle_task_' . $task->getId(), $submittedToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $task->setIsDone(!$task->isDone());
        $this->entityManager->flush();

        $this->addFlash('success', sprintf(
            'La tâche "%s" a bien été marquée comme %s.',
            $task->getTitle(),
            $task->isDone() ? 'faite' : 'non faite'
        ));

        return $this->redirectToRoute('app_task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task): Response
    {
        $user = $this->getUser();

        // Autorisation : l'utilisateur doit être l'auteur de la tâche, ou avoir ROLE_ADMIN si auteur = user anonyme
        $author = $task->getAuthor();
        $isAuthor = $author === $user;
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isAnonymous = $author && $author->getUsername() === 'anonymous'; //Pseudo du user anonyme

        if (!$isAuthor && !($isAdmin && $isAnonymous)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette tâche.');
        }

        $submittedToken = (string) $request->request->get('_token') ??  null;

        if (!$this->isCsrfTokenValid('delete_task_' . $task->getId(), $submittedToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('app_task_list');
    }
}
