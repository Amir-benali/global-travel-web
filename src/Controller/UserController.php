<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/update/{id}', name: 'app_user_update')]
    public function updatePage(int $id): Response
    {
        return $this->render('user/update.html.twig', [
            'id' => $id,

        ]);
    }

    
    #[Route('/user/settings', name: 'app_settings')]
    public function settingsPage(): Response
    {
        return $this->render('user/settings.html.twig', [

        ]);
    }
}
