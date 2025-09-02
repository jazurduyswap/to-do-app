<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GrupoController extends AbstractController
{
    #[Route('/grupo', name: 'grupo_index')]
    public function index(): Response
    {
        return $this->render('grupo/index.html.twig', [
            'controller_name' => 'GrupoController',
        ]);
    }
}
