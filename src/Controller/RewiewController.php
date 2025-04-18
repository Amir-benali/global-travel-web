<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ReviewFormType;
use App\Entity\Review; 
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;

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
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewFormType::class, $review);
        
        $form->handleRequest($request);
        
/*************  ✨ Windsurf Command ⭐  *************/
/**
 * Displays a list of all reviews.
 *
 * This action fetches all reviews from the database using the ReviewRepository
 * and renders them in the 'rewiew/index.html.twig' template.
 *
 * @param ReviewRepository $reviewRepository The repository to fetch reviews
 * 
 * @return Response The response containing the rendered template
 */

/*******  4a771e16-14b5-47b4-97b1-b6f50bdc1124  *******/        
if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_review'); // or redirect to another route
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

}
