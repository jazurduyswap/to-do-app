<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Task;
use App\Repository\FileRepository;
use App\Repository\TaskRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class FileController extends AbstractController
{
    #[Route('/file/upload/{taskId<\d+>}', name: 'file_upload', methods: ['POST'])]
    public function upload(Request $request, int $taskId, TaskRepository $taskRepository, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $task = $taskRepository->find($taskId);
        
        if (!$task) {
            throw $this->createNotFoundException('Tarea no encontrada.');
        }
        
        // Verificar permisos: solo admin o el propietario de la tarea puede subir archivos
        if (!$this->isGranted('ROLE_ADMIN') && $task->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para subir archivos a esta tarea.');
        }
        
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        
        if ($uploadedFile) {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();
            
            try {
                // Crear directorio específico para la tarea
                $uploadsDirectory = $this->getParameter('kernel.project_dir').'/uploads/tasks/'.$taskId;
                if (!is_dir($uploadsDirectory)) {
                    mkdir($uploadsDirectory, 0755, true);
                }
                
                $uploadedFile->move($uploadsDirectory, $newFilename);
                
                // Crear entidad File (guardar solo el nombre del archivo, no la ruta completa)
                $file = new File();
                $file->setNombre($uploadedFile->getClientOriginalName());
                $file->setRuta($newFilename); // Solo el nombre del archivo
                $file->setMimeType($uploadedFile->getMimeType() ?: 'application/octet-stream');
                $file->setTask($task);
                
                $entityManager = $doctrine->getManager();
                $entityManager->persist($file);
                $entityManager->flush();
                
                $this->addFlash('success', 'Archivo subido exitosamente.');
                
            } catch (FileException $e) {
                $this->addFlash('error', 'Error al subir el archivo: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'No se seleccionó ningún archivo.');
        }
        
        return $this->redirectToRoute('task_mostrar', ['id' => $taskId]);
    }
    
    #[Route('/file/download/{id<\d+>}', name: 'file_download', methods: ['GET'])]
    public function download(int $id, FileRepository $fileRepository): Response
    {
        $file = $fileRepository->find($id);
        
        if (!$file) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }
        
        // Verificar permisos: solo admin o el propietario de la tarea puede descargar archivos
        if (!$this->isGranted('ROLE_ADMIN') && $file->getTask()->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para descargar este archivo.');
        }
        
        $taskId = $file->getTask()->getId();
        $uploadsDirectory = $this->getParameter('kernel.project_dir').'/uploads/tasks/'.$taskId;
        $filePath = $uploadsDirectory.'/'.$file->getRuta();
        
        if (!file_exists($filePath)) {
            $this->addFlash('error', 'El archivo físico no existe en el servidor.');
            return $this->redirectToRoute('task_mostrar', ['id' => $file->getTask()->getId()]);
        }
        
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getNombre()
        );
        
        return $response;
    }
    
    #[Route('/file/delete/{id<\d+>}', name: 'file_delete', methods: ['POST'])]
    public function delete(int $id, FileRepository $fileRepository, ManagerRegistry $doctrine): Response
    {
        $file = $fileRepository->find($id);
        
        if (!$file) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }
        
        $taskId = $file->getTask()->getId();
        
        // Verificar permisos: solo admin o el propietario de la tarea puede eliminar archivos
        if (!$this->isGranted('ROLE_ADMIN') && $file->getTask()->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para eliminar este archivo.');
        }
        
        // Eliminar archivo físico del servidor
        $taskId = $file->getTask()->getId();
        $uploadsDirectory = $this->getParameter('kernel.project_dir').'/uploads/tasks/'.$taskId;
        $filePath = $uploadsDirectory.'/'.$file->getRuta();
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Eliminar registro de la base de datos
        $entityManager = $doctrine->getManager();
        $entityManager->remove($file);
        $entityManager->flush();
        
        $this->addFlash('success', 'Archivo eliminado exitosamente.');
        
        return $this->redirectToRoute('task_mostrar', ['id' => $taskId]);
    }
    
    #[Route('/file/view/{id<\d+>}', name: 'file_view', methods: ['GET'])]
    public function view(int $id, FileRepository $fileRepository): Response
    {
        $file = $fileRepository->find($id);
        
        if (!$file) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }
        
        // Verificar permisos: solo admin o el propietario de la tarea puede ver archivos
        if (!$this->isGranted('ROLE_ADMIN') && $file->getTask()->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para ver este archivo.');
        }
        
        $taskId = $file->getTask()->getId();
        $uploadsDirectory = $this->getParameter('kernel.project_dir').'/uploads/tasks/'.$taskId;
        $filePath = $uploadsDirectory.'/'.$file->getRuta();
        
        if (!file_exists($filePath)) {
            $this->addFlash('error', 'El archivo físico no existe en el servidor.');
            return $this->redirectToRoute('task_mostrar', ['id' => $file->getTask()->getId()]);
        }
        
        // Para imágenes, mostrar directamente
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $response = new BinaryFileResponse($filePath);
            $response->headers->set('Content-Type', $file->getMimeType());
            return $response;
        }
        
        // Para otros archivos, redirigir a descarga
        return $this->redirectToRoute('file_download', ['id' => $id]);
    }
    
    #[Route('/file/details/{id<\d+>}', name: 'file_details', methods: ['GET'])]
    public function details(int $id, FileRepository $fileRepository): Response
    {
        $file = $fileRepository->find($id);
        
        if (!$file) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }
        
        // Verificar permisos: solo admin o el propietario de la tarea puede ver detalles
        if (!$this->isGranted('ROLE_ADMIN') && $file->getTask()->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para ver este archivo.');
        }
        
        $taskId = $file->getTask()->getId();
        $uploadsDirectory = $this->getParameter('kernel.project_dir').'/uploads/tasks/'.$taskId;
        $filePath = $uploadsDirectory.'/'.$file->getRuta();
        
        $fileInfo = [
            'exists' => file_exists($filePath),
            'size' => file_exists($filePath) ? filesize($filePath) : 0,
            'path' => $filePath,
        ];
        
        return $this->render('file/details.html.twig', [
            'file' => $file,
            'fileInfo' => $fileInfo,
        ]);
    }
    
    #[Route('/file/list/{taskId<\d+>}', name: 'file_list', methods: ['GET'])]
    public function list(int $taskId, TaskRepository $taskRepository): Response
    {
        $task = $taskRepository->find($taskId);
        
        if (!$task) {
            throw $this->createNotFoundException('Tarea no encontrada.');
        }
        
        // Verificar permisos: solo admin o el propietario de la tarea puede ver archivos
        if (!$this->isGranted('ROLE_ADMIN') && $task->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permisos para ver los archivos de esta tarea.');
        }
        
        return $this->render('file/list.html.twig', [
            'task' => $task,
            'files' => $task->getFiles(),
        ]);
    }
}
