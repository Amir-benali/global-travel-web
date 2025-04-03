<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/auth', name: 'app_auth')]
    public function index(): Response
    {
        return $this->render('auth/index.html.twig', [
        ]);
    }
    #[Route('/signin', name: 'app_signin')]
    public function signinPage(): Response
    {
        return $this->render('auth/signin.html.twig', [
        ]);
    }
    #[Route('/signup', name: 'app_signup')]
    public function signupPage(): Response
    {
        return $this->render('auth/signup.html.twig', [
        ]);
    }
    
}
