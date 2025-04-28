<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search');
        $sortField = $request->query->get('sortField', 'firstname'); // tri par défaut
        $sortDirection = $request->query->get('sortDirection', 'asc');

        $qb = $em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        if ($search) {
            $qb->where('u.firstname LIKE :search')
                ->orWhere('u.lastname LIKE :search')
                ->orWhere('u.email LIKE :search')
                ->orWhere('u.phoneNumber LIKE :search')
                ->orWhere('u.roles LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // Ajouter le tri ici
        if (in_array($sortField, ['firstname', 'lastname', 'email', 'phoneNumber', 'roles']) && in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $qb->orderBy('u.' . $sortField, $sortDirection);
        }

        $users = $qb->getQuery()->getResult();

        // Calculate user statistics
        $totalUsers = count($users);
        
        // Count users by role
        $roleStats = [
            'admin' => 0,
            'responsable' => 0,
            'employee' => 0
        ];
        
        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $roleStats['admin']++;
            } elseif (in_array('ROLE_RESPONSABLE', $user->getRoles())) {
                $roleStats['responsable']++;
            } else {
                $roleStats['employee']++;
            }
        }
        
        // Fixed percentage distribution for user growth
        $monthlyData = [
            'Jan' => intval($totalUsers * 0.1),  // 10% of users in January
            'Feb' => intval($totalUsers * 0.25), // 25% of users in February
            'Mar' => intval($totalUsers * 0.4),  // 40% of users in March
            'Apr' => intval($totalUsers * 0.6),  // 60% of users in April
            'May' => intval($totalUsers * 0.8),  // 80% of users in May
            'Jun' => $totalUsers                 // 100% of users in June
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->render('user/_users_table.html.twig', [
                'users' => $users,
            ]);
        }

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'stats' => [
                'totalUsers' => $totalUsers,
                'roleStats' => $roleStats,
                'monthlyData' => $monthlyData,
                'activeUsers' => intval($totalUsers * 0.8) // Estimate 80% of users are active
            ]
        ]);
    }

   #[Route('/settings', name: 'app_settings')]
public function settingsPage(
    Request $request,
    EntityManagerInterface $em
): Response {
    /** @var \App\Entity\User $user */
    $user = $this->getUser();

    if (!$user) {
        $this->addFlash('error', 'Veuillez vous connecter.');
        return $this->redirectToRoute('app_signin');
    }

    $form = $this->createForm(UserType::class, $user, [
        'is_settings' => true
    ]);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
        return $this->redirectToRoute('app_settings');
    }

    return $this->render('user/settings.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/create', name: 'app_user_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hashage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );
            $user->setPassword($hashedPassword);

            // Gestion de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile instanceof UploadedFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/users',
                    $newFilename
                );
                $user->setImage($newFilename);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update/{id}', name: 'app_user_update')]
    public function update(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_new' => false]); // <-- is_new: false
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/update.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_user_delete')]
    public function delete(
        User $user,
        EntityManagerInterface $em,
        \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage,
        Request $request
    ): Response {
        // Suppression de l'image associée
        if ($user->getImage()) {
            $imagePath = $this->getParameter('kernel.project_dir').'/public/uploads/users/'.$user->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        if ($user === $this->getUser()) {
            // Déconnexion
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();
        }
    
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('app_user');
    }

    #[Route('/travel/settings', name: 'front_settings')]
    public function travelSettingsPage(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        
        return $this->render('front/settings.html.twig', [
            'users' => $users,
        ]);
    }
}