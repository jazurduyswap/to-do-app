<?php

namespace App\Controller;

use App\Form\TaskType;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class TaskController extends AbstractController
{
    #[Route('/task', name: 'task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/{id<\d+>}', name: 'task_mostrar', methods: ['GET'])]
    public function mostrar(TaskRepository $taskRepository, int $id): Response
    {
        $task = $taskRepository->find($id);
        return $this->render('task/mostrar.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/task/editar/{id<\d+>}', name: 'task_editar', methods: ['GET', 'POST'])]
    public function editar(Request $request, TaskRepository $taskRepository, int $id): Response
    {
        $task = $taskRepository->find($id);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task);
            return $this->redirectToRoute('task_mostrar', ['id' => $task->getId()]);
        }

        return $this->render('task/editar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/task/eliminar/{id<\d+>}', name: 'task_eliminar', methods: ['POST'])]
    public function eliminar(Request $request, TaskRepository $taskRepository, int $id): Response
    {
        $task = $taskRepository->find($id);
        if ($task) {
            $taskRepository->remove($task);
        }

        return $this->redirectToRoute('task_index');
    }

    #[Route('/task/nuevo', name: 'task_nuevo', methods: ['GET', 'POST'])]
    public function nuevo(Request $request, TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task);
            return $this->redirectToRoute('task_mostrar', ['id' => $task->getId()]);
        }

        return $this->render('task/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}