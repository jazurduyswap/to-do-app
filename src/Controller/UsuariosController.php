<?php 

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UsuariosRepository;
use App\Entity\Usuarios;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Request;

#[Route('/usuarios')]
class UsuariosController extends AbstractController
{   
    // Get All collection
    #[Route('', methods: ['GET'])]
    public function getCollection(UsuariosRepository $usuariosRepository): Response
    {
        $usuarios = $usuariosRepository->findAll();
        return $this->render('main/usuarios.html.twig', [
            'usuarios' => $usuarios
        ]);
    }

    // Get by ID
    #[Route('/{id<\d+>}', methods: ['GET'])]
    public function getById($id, UsuariosRepository $usuariosRepository): Response
    {
        $usuario = $usuariosRepository->findById($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario not found');
        }
        return $this->render('main/usuario.html.twig', [
            'usuario' => $usuario
        ]);
    }

    // New User
    #[Route('/nuevo', methods: ['GET'])]
    public function newUser(): Response
    {
        return $this->render('main/usuario_new.html.twig', []);
    }

    // Edit By Id
    #[Route('/edit/{id<\d+>}', methods: ['GET'])]
    public function editUser($id, UsuariosRepository $usuariosRepository): Response
    {
        $usuario = $usuariosRepository->findById($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario not found');
        }
        return $this->render('main/usuario_edit.html.twig', [
            'usuario' => $usuario
        ]);
    }

    // Create a new User
    #[Route('', methods: ['POST'])]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();

        $nombre = trim((string) $request->request->get('nombre', ''));
        if ($nombre === '') {
            $this->addFlash('error', 'El nombre es obligatorio.');
            return $this->redirectToRoute('usuarios_new');
        }

        $usuario = new Usuarios();
        $usuario->setNombre($nombre);

        $em->persist($usuario);
        $em->flush();

        $this->addFlash('success', 'Usuario creado correctamente.');
        return $this->redirectToRoute('usuarios_index');
    }
}