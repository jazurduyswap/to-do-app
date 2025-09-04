<?php 

namespace App\Controller;

use App\Repository\UsuariosRepository;
use App\Entity\Usuarios;
use App\Form\UsuariosType;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/usuarios')]
#[IsGranted('ROLE_ADMIN')]
class UsuariosController extends AbstractController
{   
    // Get All collection
    #[Route('', name: "usuarios_index", methods: ['GET'])]
    public function getCollection(UsuariosRepository $usuariosRepository): Response
    {
        $usuarios = $usuariosRepository->findAllForDisplay();
        return $this->render('usuarios/usuarios_index.html.twig', [
            'usuarios' => $usuarios
        ]);
    }

    // Get by ID
    #[Route('/{id<\d+>}', name: "usuarios_mostrar", methods: ['GET'])]
    public function getById($id, UsuariosRepository $usuariosRepository): Response
    {
        $usuario = $usuariosRepository->findByIdForDisplay($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario not found');
        }
        return $this->render('usuarios/usuarios_mostrar.html.twig', [
            'usuario' => $usuario
        ]);
    }

    // New User
    #[Route('/nuevo', name: "usuarios_nuevo", methods: ['GET', 'POST'])]
    public function newUser(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usuario = new Usuarios();
        $form = $this->createForm(UsuariosType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Usuarios $usuario */
            $usuario = $form->getData();

            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                // Hashear la contraseña
                $hashedPassword = $passwordHasher->hashPassword($usuario, $plainPassword);
                $usuario->setPassword($hashedPassword);
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', 'Usuario creado correctamente.');
            return $this->redirectToRoute('usuarios_mostrar', ['id' => $usuario->getId()]);
        }

        return $this->render('usuarios/usuarios_nuevo.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // Edit By Id
    #[Route('/editar/{id<\d+>}', name: "usuarios_editar", methods: ['GET', 'POST'])]
    public function editUser($id, UsuariosRepository $usuariosRepository, Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usuario = $usuariosRepository->findById($id); // Usar versión completa para edición
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario not found');
        }
        
        $form = $this->createForm(UsuariosType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Usuarios $usuario */
            $usuario = $form->getData();

            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                // Solo hashea si se ha enviado una nueva contraseña
                $hashedPassword = $passwordHasher->hashPassword($usuario, $plainPassword);
                $usuario->setPassword($hashedPassword);
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', 'Usuario actualizado correctamente.');
            return $this->redirectToRoute('usuarios_mostrar', ['id' => $usuario->getId()]);
        }
        
        return $this->render('usuarios/usuarios_editar.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView()
        ]);
    }

    // Delete User
    #[Route('/borrar/{id<\d+>}', name: "usuarios_borrar", methods: ['POST'])]
    public function delete($id, UsuariosRepository $usuariosRepository, ManagerRegistry $doctrine): Response
    { 
        $em = $doctrine->getManager();
        $usuario = $usuariosRepository->findByIdComplete($id); // Usar versión completa para eliminación
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario not found');
        }
        $em->remove($usuario);
        $em->flush();
        $this->addFlash('success', 'Usuario eliminado correctamente.');
        return $this->redirectToRoute('usuarios_index');
    }

    // Asign tasks

    #[Route('/{id_usuario<\d+>}/tareas/{id_tarea<\d+>}', name: "usuarios_asignar_tareas", methods: ['GET', 'POST'])]
    public function asignarTareas($id_usuario, $id_tarea, UsuariosRepository $usuariosRepository, TareasRepository $tareasRepository, Request $request, ManagerRegistry $doctrine): Response
    {
        $usuario = $usuariosRepository->findById($id_usuario);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario not found');
        }

        $form = $this->createForm(AsignarTareasType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', 'Tareas asignadas correctamente.');
            return $this->redirectToRoute('usuarios_mostrar', ['id' => $usuario->getId()]);
        }

        return $this->render('usuarios/usuarios_asignar_tareas.html.twig', [
            'form' => $form->createView()
        ]);
    }
}