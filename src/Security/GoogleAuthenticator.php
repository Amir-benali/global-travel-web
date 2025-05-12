<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private ClientRegistry $clientRegistry;
    private RouterInterface $router;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ClientRegistry $clientRegistry,
        RouterInterface $router,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');

        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUser();

        $email = $googleUser->getEmail();

        return new SelfValidatingPassport(
            new UserBadge($email, function (string $userIdentifier) use ($googleUser) {
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userIdentifier]);

                if (!$user) {
                    $user = new User();
                    $user->setEmail($userIdentifier);
                    $user->setRoles(['ROLE_EMPLOYEE']);

                    // Générer un mot de passe aléatoire pour satisfaire le NOT NULL
                    $randomPassword = bin2hex(random_bytes(10)); // 20 caractères hexa random
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $randomPassword);
                    $user->setPassword($hashedPassword);

                    // Tu peux aussi setter ici le prénom / photo de profil si tu veux :
                    $user->setFirstName($googleUser->getFirstName());
                    $lastname = $googleUser->getLastName();

                    // Avant de setter, on sécurise
                    if ($lastname !== null) {
                        $user->setLastname($lastname);
                    } else {
                        $user->setLastname(''); // ou un fallback genre 'Unknown'
                    }

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}
