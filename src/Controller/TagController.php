<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;

final class TagController extends AbstractController
{
    #[Route('/tag', name: 'tag_index', methods: ['GET'])]
    public function index(TagRepository $tagRepository): Response
    {
        $tags = $tagRepository->findAll();
        
        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
        ]);
    }

    #[Route('/tag/nuevo', name: 'tag_nuevo', methods: ['GET', 'POST'])]
    public function nuevo(Request $request, ManagerRegistry $doctrine): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        
        $form->handleRequest($request);
       
       if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($tag);
            $entityManager->flush();

            $this->addFlash('success', 'Tag creado exitosamente');
            return $this->redirectToRoute('tag_index');
        }
        
        
        return $this->render('tag/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tag/{id<\d+>}', name: 'tag_mostrar', methods: ['GET'])]
    public function mostrar(TagRepository $tagRepository, int $id): Response
    {
        $tag = $tagRepository->find($id);
        
        if (!$tag) {
            throw $this->createNotFoundException('Tag no encontrado.');
        }
        
        return $this->render('tag/mostrar.html.twig', [
            'tag' => $tag,
        ]);
    }

    #[Route('/tag/editar/{id<\d+>}', name: 'tag_editar', methods: ['GET', 'POST'])]
    public function editar(Request $request, TagRepository $tagRepository, int $id, ManagerRegistry $doctrine): Response
    {
        $tag = $tagRepository->find($id);
        
        if (!$tag) {
            throw $this->createNotFoundException('Tag no encontrado.');
        }
        
        $form = $this->createForm(TagType::class, $tag);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($tag);
            $entityManager->flush();
            
            $this->addFlash('success', 'Tag actualizado exitosamente');
            return $this->redirectToRoute('tag_mostrar', ['id' => $tag->getId()]);
        }

        return $this->render('tag/editar.html.twig', [
            'form' => $form->createView(),
            'tag' => $tag,
        ]);
    }

    #[Route('/tag/eliminar/{id<\d+>}', name: 'tag_eliminar', methods: ['POST'])]
    public function eliminar(Request $request, TagRepository $tagRepository, int $id, ManagerRegistry $doctrine): Response
    {
        $tag = $tagRepository->find($id);
        
        if (!$tag) {
            throw $this->createNotFoundException('Tag no encontrado.');
        }
        
        // Verificar si el tag está siendo usado en tareas
        if ($tag->getTasks()->count() > 0) {
            $this->addFlash('danger', 'No se puede eliminar el tag porque está siendo usado en ' . $tag->getTasks()->count() . ' tarea(s).');
            return $this->redirectToRoute('tag_index');
        }
        
        $entityManager = $doctrine->getManager();
        $entityManager->remove($tag);
        $entityManager->flush();
        
        $this->addFlash('success', 'Tag eliminado exitosamente');
        return $this->redirectToRoute('tag_index');
    }
}
