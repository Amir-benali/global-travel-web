<?php

namespace App\Controller;

use App\Entity\Activity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ReviewFormType;
use App\Entity\Review; 
use App\Repository\ReviewRepository;
use App\Service\AkismetSpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\CloudBuild\Security;

final class RewiewController extends AbstractController
{
    // #[Route('/review', name: 'app_review')]
    // public function index(EntityManagerInterface $em): Response
    // {
    //     $reviews = $em->getRepository(Review::class)->findAll();
        
    //     return $this->render('rewiew/index.html.twig', [
    //         'reviews' => $reviews,
    //     ]);
    // }
    #[Route('/review/create', name: 'app_review_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        AkismetSpamChecker $akismet
    ): Response {
        $review = new Review();
        $form = $this->createForm(ReviewFormType::class, $review);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Modification ici : Utilisation de $this->getUser()
            $user = $this->getUser();
            $userEmail = $user ? $user->getUserIdentifier() : null;
            $clientIp = $request->getClientIp();
            $userAgent = $request->headers->get('User-Agent');

            if ($akismet->isSpam(
                $review->getCommentaire(),
                $userEmail,
                $clientIp,
                $userAgent
            )) {
                $this->addFlash('error', 'Your review has been detected as spam.');
                return $this->redirectToRoute('app_review_create');
            }

            $entityManager->persist($review);
            $entityManager->flush();
            
            $this->addFlash('success', 'successfully! Continue to the next review ');
            return $this->redirectToRoute('app_review');
        }
        
        return $this->render('rewiew/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reviews', name: 'app_review')]
public function index(ReviewRepository $reviewRepository): Response
{
    $reviews = $reviewRepository->findAll(); // Fetch all reviews from the database
    return $this->render('rewiew/index.html.twig', [
        'reviews' => $reviews,
    ]);
}

#[Route('/review/update/{id}', name: 'app_review_update')]
public function update(Request $request, EntityManagerInterface $entityManager, Review $review): Response
{
    $form = $this->createForm(ReviewFormType::class, $review);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        
        $this->addFlash('success', 'Review updated successfully!');
        return $this->redirectToRoute('app_review');
    }
    
    return $this->render('rewiew/update.html.twig', [
        'form' => $form->createView(),
        'review' => $review,
    ]);
}



    #[Route('/review/{id}/delete', name: 'app_review_delete')]
    public function delete(Review $review, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($review);
        $entityManager->flush();

        return $this->redirectToRoute('app_review');
    }

    #[Route('/travel/reviews', name: 'front_review')]
    public function travelReviewIndex(ReviewRepository $reviewRepository): Response
    {
        $reviews = $reviewRepository->findAll(); // Fetch all reviews from the database
        return $this->render('front/activity/review/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/review/create-for-activity/{activityId}', name: 'app_review_create_for_activity')]
public function createForActivity(
    Request $request,
    EntityManagerInterface $entityManager,
    int $activityId,
    AkismetSpamChecker $akismet
): Response {
    $activity = $entityManager->getRepository(Activity::class)->find($activityId);
    if (!$activity) {
        throw $this->createNotFoundException('Activité non trouvée');
    }

    $user = $this->getUser();
    $review = new Review();
    $review->setActivityid($activity);
    $review->setUserid($user);
    $review->setDatereview(new \DateTime());

    $form = $this->createForm(ReviewFormType::class, $review);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        try {
            // Vérification anti-spam
            $userEmail = $user ? $user->getUserIdentifier() : null;
            $clientIp = $request->getClientIp();
            $userAgent = $request->headers->get('User-Agent');

            if ($akismet->isSpam($review->getCommentaire(), $userEmail, $clientIp, $userAgent)) {
                $this->addFlash('error', 'Votre commentaire a été détecté comme spam.');
                return $this->redirectToRoute('app_review_create_for_activity', ['activityId' => $activityId]);
            }

            $entityManager->persist($review);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre commentaire a été enregistré avec succès!');
            return $this->redirectToRoute('front_list_activity');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    return $this->render('front/activity/createForActivity.html.twig', [
        'form' => $form->createView(),
        'activity' => $activity,
    ]);
}

}
