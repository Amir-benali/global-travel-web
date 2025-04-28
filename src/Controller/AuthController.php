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
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordAuthenticatedToken;
use Symfony\Component\Security\Core\Security;






class AuthController extends AbstractController
{
    #[Route('/signup', name: 'app_signup')]
    public function signup(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        HttpClientInterface $httpClient
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }
    
        $user = new User();
        $form = $this->createForm(AuthType::class, $user);
        $form->remove('imageFile');
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // 🎯 Vérifier hCaptcha d'abord
            $captchaToken = $request->request->get('h-captcha-response');
            if (!$captchaToken) {
                $this->addFlash('error', 'Please complete the captcha.');
                return $this->render('auth/signup.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        
            $captchaResponse = $httpClient->request('POST', 'https://hcaptcha.com/siteverify', [
                'body' => [
                    'secret' => 'ES_b5a839be8aa14b89a97dc1d64e0bdc1f', 
                    'response' => $captchaToken,
                ],
            ]);
        
            $captchaResult = $captchaResponse->toArray();
        
            if (!$captchaResult['success']) {
                $this->addFlash('error', 'Invalid captcha. Please try again.');
                return $this->render('auth/signup.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        
            // 📧 Vérification email avec Abstract API
            $email = $user->getEmail();
            $apiKey = '3ef5f391c43b47e18c9746877b762f3a';
            $url = "https://emailvalidation.abstractapi.com/v1/?api_key=$apiKey&email=$email";
        
            try {
                $response = $httpClient->request('GET', $url);
                $data = $response->toArray();
        
                if (!isset($data['deliverability']) || $data['deliverability'] !== 'DELIVERABLE') {
                    $this->addFlash('error', 'The provided email address is not valid or not deliverable.');
                    return $this->render('auth/signup.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            } catch (ClientExceptionInterface|TransportExceptionInterface $e) {
                $this->addFlash('error', 'Error verifying email address. Please try again.');
                return $this->render('auth/signup.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
    
            // 🔥 Vérification du mot de passe avec API Azure
            $password = $form->get('password')->getData();
            try {
                $apiResponse = $httpClient->request('POST', 'https://password-exposed-aramgmhfh4f5dpc0.germanywestcentral-01.azurewebsites.net/predict', [
                    'json' => [
                        'password' => $password,
                    ],
                ]);
    
                $result = $apiResponse->toArray();
                if (isset($result['breached']) && $result['breached'] === true) {
                    $this->addFlash('error', 'This password has been exposed in a data breach. Please choose a different password.');
                    return $this->render('auth/signup.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            } catch (ClientExceptionInterface|TransportExceptionInterface $e) {
                $this->addFlash('error', 'Error verifying password exposure. Please try again.');
                return $this->render('auth/signup.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        
            // 💾 Création du compte
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword)
                ->setRoles(['ROLE_EMPLOYEE'])
                ->setStatut('actif');
        
            $em->persist($user);
            $em->flush();
        
            $this->addFlash('success', 'Sign-up successful! You can now log in.');
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


    #[Route('/connect/google', name: 'connect_google_start')]
public function connectGoogle(ClientRegistry $clientRegistry)
{
    return $clientRegistry
        ->getClient('google') // nom du client défini dans yaml
        ->redirect([], []); // Redirige vers Google
}
#[Route('/connect/google/check', name: 'connect_google_check')]
public function connectGoogleCheck(Request $request, $security): Response
{
    /** @var UsernamePasswordAuthenticatedToken|null $userToken */
    $userToken = $security->getToken();
    
    if (!$userToken) {
        throw $this->createAccessDeniedException('No authenticated user.');
    }

    $user = $userToken->getUser();

    if (!$user) {
        throw $this->createAccessDeniedException('No user found.');
    }

    // Ici, tu peux utiliser $user (par exemple afficher son email)
    // Exemple: dd($user);

    return $this->redirectToRoute('app_dashboard'); // Redirige vers une page de ton choix
}




    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method is intercepted by the firewall.');
    }
    
}
