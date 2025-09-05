<?php

namespace App\Controller;

use App\Form\TaskType;
use App\Form\TaskNewType;
use App\Form\TaskEditType;
use App\Form\FileType;

use App\Entity\File;
use App\Entity\Task;

use App\Repository\FileRepository;
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
        
        $form = $this->createForm(TaskEditType::class, $task);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Si no es admin, asegurar que la tarea quede asignada al usuario actual
            if (!$this->isGranted('ROLE_ADMIN')) {
                $task->setUsuario($this->getUser());
            }
            
            $entityManager = $doctrine->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
            
            $this->addFlash('success', 'Tarea actualizada exitosamente');
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
        $form = $this->createForm(TaskNewType::class, $task);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            
            // Si no es admin, asignar automáticamente al usuario actual
            if (!$this->isGranted('ROLE_ADMIN')) {
                $task->setUsuario($this->getUser());
            }
            
            // Manejar tags existentes seleccionados
            $tagsExistentes = $form->get('tagsExistentes')->getData();
            if ($tagsExistentes) {
                foreach ($tagsExistentes as $tag) {
                    $task->addTag($tag);
                }
            }
            
            // Manejar tags nuevos
            $newTags = $form->get('tags')->getData();
            if ($newTags) {
                foreach ($newTags as $tagData) {
                    if (!empty($tagData['nombre'])) {
                        // Buscar si el tag ya existe
                        $existingTag = $entityManager->getRepository(\App\Entity\Tag::class)->findOneBy(['nombre' => $tagData['nombre']]);
                        
                        if (!$existingTag) {
                            // Crear nuevo tag
                            $newTag = new \App\Entity\Tag();
                            $newTag->setNombre($tagData['nombre']);
                            $entityManager->persist($newTag);
                            $task->addTag($newTag);
                        } else {
                            // Usar tag existente
                            $task->addTag($existingTag);
                        }
                    }
                }
            }
            
            // Asignar usuario a subtareas si no es admin
            if (!$this->isGranted('ROLE_ADMIN')) {
                foreach ($task->getChildTasks() as $childTask) {
                    if (!$childTask->getUsuario()) {
                        $childTask->setUsuario($this->getUser());
                    }
                }
            }
            
            $entityManager->persist($task);
            $entityManager->flush();
            
            $this->addFlash('success', 'Tarea creada exitosamente');
            return $this->redirectToRoute('task_mostrar', ['id' => $task->getId()]);
        }
        
        return $this->render('task/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/task/{id<\d+>}/file/', name: 'task_file', methods: ['GET', 'POST'])] 
    public function mostrar_file(Request $request, ManagerRegistry $doctrine, TaskRepository $taskRepository, int $id): Response
    {
        $task = $taskRepository->find($id);

        // Verificar permisos: solo admin o el propietario de la tarea puede verla
        if (!$this->isGranted('ROLE_ADMIN') && $task->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para ver esta tarea.');
        }

        $fileEntity = new File();
        $form = $this->createForm(FileType::class, $fileEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('uploadedFile')->getData();
            
            if ($uploadedFile) {
                // Crear directorio específico para la tarea
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/tasks/' . $task->getId();
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Generar nombre único para el archivo
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                try {
                    // Mover archivo al directorio
                    $uploadedFile->move($uploadDir, $newFilename);
                    
                    // Guardar información en la base de datos
                    $fileEntity->setNombre($fileEntity->getNombre() ?: $originalFilename);
                    $fileEntity->setRuta('uploads/tasks/' . $task->getId() . '/' . $newFilename);
                    $fileEntity->setMimeType($uploadedFile->getClientMimeType());
                    $fileEntity->setTask($task);

                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($fileEntity);
                    $entityManager->flush();

                    $this->addFlash('success', 'Archivo subido correctamente.');
                    return $this->redirectToRoute('task_file', ['id' => $task->getId()]);
                    
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Error al subir el archivo: ' . $e->getMessage());
                }
            } else {
                $this->addFlash('warning', 'No se seleccionó ningún archivo.');
            }
        }

        return $this->render('task/task_file.html.twig', [
            'form' => $form->createView(),
            'task' => $task
        ]);
    }

    #[Route('/task/{id<\d+>}/file/{id_file<\d+>}/delete', name: 'task_file_delete', methods: ['POST'])]
    public function task_file_delete(Request $request, TaskRepository $taskRepository, FileRepository $fileRepository, ManagerRegistry $doctrine, int $id, int $id_file): Response
    {
        $task = $taskRepository->find($id);
        $file = $fileRepository->find($id_file);
        
        // Verificar que el archivo existe y pertenece a la tarea
        if (!$file || $file->getTask()->getId() !== $task->getId()) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }
        
        // Verificar permisos: solo admin o el propietario de la tarea puede eliminar archivos
        if (!$this->isGranted('ROLE_ADMIN') && $task->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para eliminar este archivo.');
        }
        
        // Construir la ruta del archivo físico
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $file->getRuta();
        
        // Eliminar archivo físico si existe
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Eliminar registro de la base de datos
        $entityManager = $doctrine->getManager();
        $entityManager->remove($file);
        $entityManager->flush();
        
        $this->addFlash('success', 'Archivo eliminado correctamente.');
        return $this->redirectToRoute('task_file', ['id' => $task->getId()]);
    }
}