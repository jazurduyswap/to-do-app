<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: "app_homepage", methods: ['GET'])]
    public function homepage(): Response
    {
        return $this->render('main/home.html.twig');
    }
}