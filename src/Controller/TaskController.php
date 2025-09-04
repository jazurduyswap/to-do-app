<?php

namespace App\Controller;

use App\Form\TaskType;
use App\Entity\Task;
use App\Repository\TaskRepository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class TaskController extends AbstractController
{
    #[Route('/task', name: 'task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        // Si es admin, ve todas las tareas; si es usuario normal, solo sus tareas
        if ($this->isGranted('ROLE_ADMIN')) {
            $tasks = $taskRepository->findAll();
        } else {
            $tasks = $taskRepository->findBy(['usuario' => $this->getUser()]);
        }

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/{id<\d+>}', name: 'task_mostrar', methods: ['GET'])]
    public function mostrar(TaskRepository $taskRepository, int $id): Response
    {
        $task = $taskRepository->find($id);
        
        // Verificar permisos: solo admin o el propietario de la tarea puede verla
        if (!$this->isGranted('ROLE_ADMIN') && $task->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para ver esta tarea.');
        }
        
        return $this->render('task/mostrar.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/task/editar/{id<\d+>}', name: 'task_editar', methods: ['GET', 'POST'])]
    public function editar(Request $request, TaskRepository $taskRepository, int $id, ManagerRegistry $doctrine): Response
    {
        $task = $taskRepository->find($id);
        
        // Verificar permisos: solo admin o el propietario de la tarea puede editarla
        if (!$this->isGranted('ROLE_ADMIN') && $task->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para editar esta tarea.');
        }
        
        $form = $this->createForm(TaskType::class, $task, [
            'is_admin' => $this->isGranted('ROLE_ADMIN')
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Si no es admin, asegurar que la tarea quede asignada al usuario actual
            if (!$this->isGranted('ROLE_ADMIN')) {
                $task->setUsuario($this->getUser());
            }
            
            $entityManager = $doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
            
            return $this->redirectToRoute('task_mostrar', ['id' => $task->getId()]);
        }

        return $this->render('task/editar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/task/eliminar/{id<\d+>}', name: 'task_eliminar', methods: ['POST'])]
    public function eliminar(Request $request, TaskRepository $taskRepository, int $id, ManagerRegistry $doctrine): Response
    {
        $task = $taskRepository->find($id);
        
        // Verificar permisos: solo admin o el propietario de la tarea puede eliminarla
        if (!$this->isGranted('ROLE_ADMIN') && $task->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para eliminar esta tarea.');
        }
        
        if ($task) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('task_index');
    }

    #[Route('/task/nuevo', name: 'task_nuevo', methods: ['GET', 'POST'])]
    public function nuevo(Request $request, TaskRepository $taskRepository, ManagerRegistry $doctrine): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task, [
            'is_admin' => $this->isGranted('ROLE_ADMIN')
        ]);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Si no es admin, asignar automáticamente al usuario actual
            if (!$this->isGranted('ROLE_ADMIN')) {
                $task->setUsuario($this->getUser());
            }
            
            $entityManager = $doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
            
            return $this->redirectToRoute('task_mostrar', ['id' => $task->getId()]);
        }
        
        return $this->render('task/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}