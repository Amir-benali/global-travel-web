<?php
// src/Controller/ActivityController.php

namespace App\Controller;
use App\Form\ActivityFormType;
use App\Form\ActivityType;
use App\Entity\Activity;
use App\Entity\Enum\Type\ActivityTypeType;
use App\Repository\FlightsRepository;
use App\Repository\ActivityRepository;
use App\Service\GoogleCalendarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;




class ActivityController extends AbstractController
{
    #[Route('/activity', name: 'app_activity')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $searchQuery = $request->query->get('search', '');
        $activities = [];
        
        if (!empty($searchQuery)) {
            $activities = $em->getRepository(Activity::class)->searchByName($searchQuery);
        } else {
            $activities = $em->getRepository(Activity::class)->findAll();
        }
        
        return $this->render('activity/index.html.twig', [
            'activities' => $activities,
            'searchQuery' => $searchQuery,
        ]);
    }
    #[Route('/activity/details/{id}', name: 'app_activity_details')]
    public function details(EntityManagerInterface $entityManager, int $id): Response
    {
        
        $activity = $entityManager->getRepository(Activity::class)->find($id);
        
        if (!$activity) {
            throw $this->createNotFoundException('The requested activity does not exist.');
        }
        
        return $this->render('activity/details.html.twig', [
            'activity' => $activity,
        ]);
    }

     ///create activity

 
     private RequestStack $requestStack;

     public function __construct(RequestStack $requestStack)
     {
         $this->requestStack = $requestStack;
     }
 
     #[Route('/activity/create', name: 'app_activity_create')]
     public function create(
         Request $request, 
         EntityManagerInterface $entityManager,
         GoogleCalendarService $calendarService
     ): Response {
         $activity = new Activity();
         $form = $this->createForm(ActivityFormType::class, $activity);
         $form->handleRequest($request);
 
         $session = $this->requestStack->getSession();
 
         if ($form->isSubmitted() && $form->isValid()) {
             try {
                 // Créer l'événement Google Calendar
                 $eventId = $calendarService->createEvent($activity);
                 
                 // Stocker l'ID dans la session
                 $session->set('google_event_id', $eventId);
 
                 $entityManager->persist($activity);
                 $entityManager->flush();
 
                 $this->addFlash('success', 'Activity created successfully! Google Event ID: ' . $eventId);
                 return $this->redirectToRoute('app_activity');
 
             } catch (\Exception $e) {
                 $this->addFlash('error', 'Error: ' . $e->getMessage());
             }
         }
 
         return $this->render('activity/create.html.twig', [
             'form' => $form->createView(),
             'google_connected' => $this->isGoogleConnected()
         ]);
     }
 
     private function isGoogleConnected(): bool
     {
         $token = $this->requestStack->getSession()->get('google_access_token');
         return !empty($token['access_token']);
     }
 

      /// Update activity
      #[Route('/activity/update/{id}', name: 'app_activity_update')]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $activity = $entityManager->getRepository(Activity::class)->find($id);
        
        if (!$activity) {
            throw $this->createNotFoundException('Activity not found');
        }

        $form = $this->createForm(ActivityFormType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Activity updated successfully!');
            return $this->redirectToRoute('app_activity_details', ['id' => $activity->getId()]);
        }

        return $this->render('activity/update.html.twig', [
            'form' => $form->createView(),
            'activity' => $activity,
        ]);
    }

    #[Route('/activity/delete/{id}', name: 'app_activity_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $activity = $entityManager->getRepository(Activity::class)->find($id);

        if (!$activity) {
            throw $this->createNotFoundException('Activity not found');
        }

        if ($this->isCsrfTokenValid('delete'.$activity->getId(), $request->request->get('_token'))) {
            $entityManager->remove($activity);
            $entityManager->flush();

            $this->addFlash('success', 'Activity deleted successfully!');
        }

        return $this->redirectToRoute('app_activity');
    }




    #[Route('/activity/search', name: 'app_activity_search', methods: ['GET'])]
    public function search(Request $request, ActivityRepository $activityRepository): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $query = $request->query->get('query', '');
        $activities = $activityRepository->searchByName($query);
        
        $results = [];
        foreach ($activities as $activity) {
            $results[] = [
                'id' => $activity->getId(),
                'name' => $activity->getNomactivity(),
                'description' => $activity->getDescription(),
                'location' => $activity->getLocalisation(),
                'startDate' => $activity->getDatedebut()->format('d/m/Y H:i'),
                'type' => $activity->getTypeactivity()->value,
            ];
        }
        
        return $this->json($results);
    }

    #[Route('/travel/activity', name: 'front_activity')]
    public function travelActivityIndex(Request $request, EntityManagerInterface $em): Response
    {
        $searchQuery = $request->query->get('search', '');
        $activities = [];
        
        if (!empty($searchQuery)) {
            $activities = $em->getRepository(Activity::class)->searchByName($searchQuery);
        } else {
            $activities = $em->getRepository(Activity::class)->findAll();
        }
        
        return $this->render('front/activity/index.html.twig', [
            'activities' => $activities,
            'searchQuery' => $searchQuery,
        ]);
    }
    #[Route('travel/activity/details/{id}', name: 'front_activity_details')]
    public function travelDetails(EntityManagerInterface $entityManager, int $id): Response
    {
        $activity = $entityManager->getRepository(Activity::class)->find($id);
        
        if (!$activity) {
            throw $this->createNotFoundException('The requested activity does not exist.');
        }
        
        return $this->render('front/activity/details.html.twig', [
            'activity' => $activity,
        ]);
    }   
    #[Route('travel/activity/list', name: 'front_list_activity')]
    public function travelListActivityIndex(EntityManagerInterface $entityManager): Response
    {
        $activities = $entityManager->getRepository(Activity::class)->findAll();
        
        return $this->render('front/activity/list.html.twig', [
            'activities' => $activities,
        ]);
    }
}