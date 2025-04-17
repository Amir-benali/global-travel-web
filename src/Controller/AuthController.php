<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AuthType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class AuthController extends AbstractController
{
    #[Route('/signup', name: 'app_signup')]
    public function signup(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $user = new User();
        $form = $this->createForm(AuthType::class, $user);
        
        $form->remove('imageFile'); // Suppression du champ image si non nécessaire à l'inscription
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($hashedPassword)
                 ->setRoles(['ROLE_EMPLOYEE'])
                 ->setStatut('actif');

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');

            return $this->redirectToRoute('app_signin');
        }

        return $this->render('auth/signup.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/signin', name: 'app_signin')]
    public function signin(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Get security error first
        $authError = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Create form
        $form = $this->createForm(AuthType::class, null, ['is_login' => true]);
        $form->handleRequest($request);

        // Prepare form errors to pass to template
        $formErrors = [];
        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $formErrors[] = $error->getMessage();
            }
        }

        return $this->render('auth/signin.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'auth_error' => $authError,
            'form_errors' => $formErrors,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method is intercepted by the firewall.');
    }
    
}
