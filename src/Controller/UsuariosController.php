<?php 

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UsuariosRepository;
use App\Entity\Usuarios;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/usuarios')]
class UsuariosController extends AbstractController
{   
    // Get All collection
    #[Route('', name: "usuarios_index", methods: ['GET'])]
    public function getCollection(UsuariosRepository $usuariosRepository): Response
    {
        $usuarios = $usuariosRepository->findAll();
        return $this->render('usuarios/usuarios_index.html.twig', [
            'usuarios' => $usuarios
        ]);
    }

    // Get by ID
    #[Route('/{id<\d+>}', name: "usuarios_mostrar", methods: ['GET'])]
    public function getById($id, UsuariosRepository $usuariosRepository): Response
    {
        $usuario = $usuariosRepository->findById($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario not found');
        }
        return $this->render('usuarios/usuarios_mostrar.html.twig', [
            'usuario' => $usuario
        ]);
    }

    // New User
    #[Route('/nuevo', name: "usuarios_nuevo", methods: ['GET'])]
    public function newUser(): Response
    {
        return $this->render('usuarios/usuarios_nuevo.html.twig', []);
    }

    // Edit By Id
    #[Route('/editar/{id<\d+>}', name: "usuarios_editar", methods: ['GET'])]
    public function editUser($id, UsuariosRepository $usuariosRepository): Response
    {
        $usuario = $usuariosRepository->findById($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario not found');
        }
        return $this->render('usuarios/usuarios_editar.html.twig', [
            'usuario' => $usuario
        ]);
    }

    // Delete User
    #[Route('/borrar/{id<\d+>}', name: "usuarios_borrar", methods: ['POST'])]
    public function delete($id, UsuariosRepository $usuariosRepository, ManagerRegistry $doctrine): Response
    { 

        $em = $doctrine->getManager();
        $usuario = $usuariosRepository->findById($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario not found');
        }
        $em->remove($usuario);
        $em->flush();
        $this->addFlash('success', 'Usuario eliminado correctamente.');
        return $this->redirectToRoute('usuarios_index');
    }

    // Create a new User
    #[Route('', name: "usuarios_crear", methods: ['POST'])]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();

        $id = trim((string) $request->request->get('id', ''));
        $nombre = trim((string) $request->request->get('nombre', ''));
        if ($nombre === '') {
            $this->addFlash('error', 'El nombre es obligatorio.');
            return $this->redirectToRoute('usuarios_new');
        }

        // Crea Usuario
        if(!$id || $id ==""){
            $usuario = new Usuarios();
            $usuario->setNombre($nombre);

            $em->persist($usuario);
            $em->flush();
            $this->addFlash('success', 'Usuario creado correctamente.');
         } else {
            // Editar usuario existente
            $usuario = $em->getRepository(Usuarios::class)->find($id);

            if (!$usuario) {
                $this->addFlash('error', 'Usuario no encontrado.');
                return $this->redirectToRoute('usuarios_index');
            }

            $usuario->setNombre($nombre);
            $this->addFlash('success', 'Usuario actualizado correctamente.');
        }

        $em->flush();

        return $this->redirectToRoute('usuarios_index');
        
    }
}