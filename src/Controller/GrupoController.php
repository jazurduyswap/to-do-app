<?php

namespace App\Controller;

use App\Repository\GrupoRepository;
use App\Repository\UsuariosRepository;
use App\Form\GrupoType;
use App\Entity\Grupo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

final class GrupoController extends AbstractController
{
    #[Route('/grupo', name: 'grupo_index', methods: ['GET'])]
    public function index(GrupoRepository $grupoRepository): Response
    {
        $grupos = $grupoRepository->findAll();

        return $this->render('grupo/index.html.twig', [
            'grupos' => $grupos,
        ]);
    }

     #[Route('/grupo/nuevo', name: 'grupo_nuevo', methods: ['GET', 'POST'])]
    public function nuevo(GrupoRepository $grupoRepository, Request $request, ManagerRegistry $doctrine): Response
    {
        $grupo = new Grupo();
        $form = $this->createForm(GrupoType::class, $grupo);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($grupo);
            $entityManager->flush();

            return $this->redirectToRoute('grupo_index');
        }

        return $this->render('grupo/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/grupo/{id}', name: 'grupo_mostrar', methods: ['GET'])]
    public function mostrar(GrupoRepository $grupoRepository, int $id): Response
    {
        $grupo = $grupoRepository->find($id);
        return $this->render('grupo/mostrar.html.twig', [
            'grupo' => $grupo,
        ]);
    }

    #[Route('/grupo/editar/{id}', name: 'grupo_editar', methods: ['GET', 'POST'])]
    public function editar(GrupoRepository $grupoRepository, Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $grupo = $grupoRepository->find($id);
        if (!$grupo) {
            throw $this->createNotFoundException('Grupo not found');
        }

        $form = $this->createForm(GrupoType::class, $grupo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('grupo_index');
        }

        return $this->render('grupo/editar.html.twig', [
            'form' => $form->createView(),
            'grupo' => $grupo,
        ]);
    }

    #[Route('/grupo/eliminar/{id}', name: 'grupo_eliminar', methods: ['POST'])]
    public function eliminar(GrupoRepository $grupoRepository, ManagerRegistry $doctrine, int $id): Response
    {
        $grupo = $grupoRepository->find($id);
        if (!$grupo) {
            throw $this->createNotFoundException('Grupo not found');
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($grupo);
        $entityManager->flush();

        return $this->redirectToRoute('grupo_index');
    }

    #[Route('/grupo/{grupoId}/añadir-usuario', name: 'grupo_añadir_usuario', methods: ['GET', 'POST'])]
    public function añadirUsuario(int $grupoId, 
                                 GrupoRepository $grupoRepo, 
                                 UsuariosRepository $usuarioRepo, 
                                 Request $request,
                                 ManagerRegistry $doctrine): Response
    {
        $grupo = $grupoRepo->find($grupoId);
        if (!$grupo) {
            throw $this->createNotFoundException('Grupo no encontrado');
        }

        // Obtener usuarios que NO están en este grupo
        $usuariosDisponibles = $usuarioRepo->findUsuariosNoEnGrupo($grupoId);

        if ($request->isMethod('POST')) {
            $usuarioId = $request->request->get('usuario_id');
            $usuario = $usuarioRepo->find($usuarioId);
            
            if ($usuario && !$grupo->getUsuarios()->contains($usuario)) {
                $grupo->addUsuario($usuario);
                
                $em = $doctrine->getManager();
                $em->flush();
                
                $this->addFlash('success', 'Usuario añadido al grupo correctamente.');
            }
            
            return $this->redirectToRoute('grupo_mostrar', ['id' => $grupoId]);
        }

        return $this->render('grupo/añadir_usuario.html.twig', [
            'grupo' => $grupo,
            'usuariosDisponibles' => $usuariosDisponibles,
        ]);
    }

    #[Route('/grupo/{grupoId}/quitar-usuario/{usuarioId}', name: 'grupo_quitar_usuario', methods: ['POST'])]
    public function quitarUsuario(int $grupoId, int $usuarioId, 
                                GrupoRepository $grupoRepo, 
                                UsuariosRepository $usuarioRepo, 
                                ManagerRegistry $doctrine): Response
    {
        $grupo = $grupoRepo->find($grupoId);
        $usuario = $usuarioRepo->find($usuarioId);
        
        if ($grupo && $usuario) {
            // Esta línea hace toda la magia ↓
            $grupo->removeUsuario($usuario);
            
            $em = $doctrine->getManager();
            $em->flush(); // Solo flush, no persist (es una relación existente)
            
            $this->addFlash('success', 'Usuario quitado del grupo correctamente.');
        }
        
        return $this->redirectToRoute('grupo_mostrar', ['id' => $grupoId]);
    }
}