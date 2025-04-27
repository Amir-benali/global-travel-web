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
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/settings', name: 'app_settings')]
    public function settingsPage(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        
        return $this->render('user/settings.html.twig', [
            'users' => $users,
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
        EntityManagerInterface $em
    ): Response {
        // Suppression de l'image associée
        if ($user->getImage()) {
            $imagePath = $this->getParameter('kernel.project_dir').'/public/uploads/users/'.$user->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
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