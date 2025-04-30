<?php

namespace App\Controller;


use App\Entity\Activity;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Form\ActivityFormType;
use App\Service\GoogleCalendarService;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use GuzzleHttp\Client;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Security\Core\Security;



class ActivityController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/activity', name: 'app_activity')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $searchQuery = $request->query->get('search', '');
        
        $queryBuilder = $em->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->orderBy('a.datedebut', 'DESC');

        if (!empty($searchQuery)) {
            $queryBuilder
                ->andWhere('a.nomactivity LIKE :search')
                ->setParameter('search', '%'.$searchQuery.'%');
        }

        $adapter = new QueryAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(10);
        $pager->setCurrentPage($request->query->getInt('page', 1));

        return $this->render('activity/index.html.twig', [
            'pager' => $pager,
            'searchQuery' => $searchQuery,
        ]);
    }

    #[Route('/activity/search', name: 'app_activity_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $query = $request->query->get('query', '');
        $page = $request->query->getInt('page', 1);
        
        $queryBuilder = $em->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->where('a.nomactivity LIKE :query')
            ->setParameter('query', '%'.$query.'%');

        $adapter = new QueryAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(5);
        $pager->setCurrentPage($page);

        $results = [];
        foreach ($pager->getCurrentPageResults() as $activity) {
            $results[] = [
                'id' => $activity->getId(),
                'name' => $activity->getNomactivity(),
                'location' => $activity->getLocalisation(),
                'startDate' => $activity->getDatedebut()->format('M d, Y H:i'),
                'type' => $activity->getTypeactivity()->value,
            ];
        }

        return $this->json([
            'results' => $results,
            'pagination' => [
                'currentPage' => $pager->getCurrentPage(),
                'hasNextPage' => $pager->hasNextPage(),
                'totalPages' => $pager->getNbPages()
            ]
        ]);
    }

    #[Route('/activity/details/{id}', name: 'app_activity_details')]
    public function details(EntityManagerInterface $em, int $id): Response
    {
        $activity = $em->getRepository(Activity::class)->find($id);
        
        if (!$activity) {
            throw $this->createNotFoundException('Activity not found');
        }
        
        return $this->render('activity/details.html.twig', [
            'activity' => $activity,
        ]);
    }

    #[Route('/activity/create', name: 'app_activity_create')]
    public function create(
        Request $request, 
        EntityManagerInterface $em,
        GoogleCalendarService $calendarService
    ): Response {
        $activity = new Activity();
        $form = $this->createForm(ActivityFormType::class, $activity);
        $form->handleRequest($request);
             
        $session = $this->requestStack->getSession();

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $eventId = $calendarService->createEvent($activity);
                $session->set('google_event_id', $eventId);

                $em->persist($activity);
                $em->flush();

                $this->addFlash('success', 'Activity created successfully!');
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

    #[Route('/activity/update/{id}', name: 'app_activity_update')]
    public function update(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $activity = $em->getRepository(Activity::class)->find($id);
        
        if (!$activity) {
            throw $this->createNotFoundException('Activity not found');
        }

        $form = $this->createForm(ActivityFormType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Activity updated successfully!');
            return $this->redirectToRoute('app_activity_details', ['id' => $id]);
        }

        return $this->render('activity/update.html.twig', [
            'form' => $form->createView(),
            'activity' => $activity,
        ]);
    }

    #[Route('/activity/delete/{id}', name: 'app_activity_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $activity = $em->getRepository(Activity::class)->find($id);

        if (!$activity) {
            throw $this->createNotFoundException('Activity not found');
        }

        if ($this->isCsrfTokenValid('delete'.$activity->getId(), $request->request->get('_token'))) {
            $em->remove($activity);
            $em->flush();
            $this->addFlash('success', 'Activity deleted successfully!');
        }

        return $this->redirectToRoute('app_activity');
    }

    #[Route('/google/connect', name: 'google_connect')]
    public function connectGoogleCalendar(GoogleCalendarService $calendarService): Response
    {
        return $this->redirect($calendarService->getAuthUrl());
    }

    #[Route('/google/auth-callback', name: 'google_auth_callback')]
    public function googleAuthCallback(Request $request, GoogleCalendarService $calendarService): Response
    {
        try {
            $calendarService->handleAuthCallback($request->query->get('code'));
            $this->addFlash('success', 'Google Calendar connected!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Connection failed: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('app_activity_create');
    }

    private function isGoogleConnected(): bool
    {
        $token = $this->requestStack->getSession()->get('google_access_token');
        return !empty($token['access_token']);
    }
    #[Route('/travel/activity', name: 'front_activity')]
    public function travelActivityIndex(Request $request, EntityManagerInterface $em): Response
    {
        try {
            $searchQuery = trim($request->query->get('search', ''));
            
            // Récupération des employés avec rôle EMPLOYEE
            $employees = $em->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.roles LIKE :role')
                ->setParameter('role', '%EMPLOYEE%')
                ->getQuery()
                ->getResult();

            // Récupération des activités
            $queryBuilder = $em->getRepository(Activity::class)
                ->createQueryBuilder('a')
                ->orderBy('a.datedebut', 'DESC');

            if (!empty($searchQuery)) {
                $queryBuilder
                    ->andWhere('a.nomactivity LIKE :query')
                    ->setParameter('query', '%'.$searchQuery.'%');
            }

            $activities = $queryBuilder->getQuery()->getResult();

            return $this->render('front/activity/index.html.twig', [
                'activities' => $activities,
                'employees' => $employees,
                'searchQuery' => $searchQuery,
            ]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error: '.$e->getMessage());
            return $this->redirectToRoute('front_activity');
        }
    }

    #[Route('/activity/{id}/reject', name: 'app_activity_reject', methods: ['POST'])]
    public function rejectActivity(int $id, EntityManagerInterface $em): JsonResponse
    {
        try {
            $activity = $em->getRepository(Activity::class)->find($id);
            
            if (!$activity) {
                return $this->json([
                    'success' => false,
                    'message' => 'Activity not found'
                ], 404);
            }
            
            // Logique de rejet (à adapter)
            // $activity->setStatus('rejected');
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Activity rejected successfully'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage()
            ], 500);
        }
    }

    #[Route('/activity/{id}/assign-employees', name: 'assign_employees', methods: ['POST'])]
    public function assignEmployees(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $activity = $em->getRepository(Activity::class)->find($id);
        if (!$activity) {
            return new JsonResponse(['success' => false, 'message' => 'Activité non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $employeeIds = $data['employeeIds'] ?? [];

        foreach ($employeeIds as $employeeId) {
            $employee = $em->getRepository(User::class)->find($employeeId);

            // Vérifie s'il existe déjà une assignation
            $existing = $em->getRepository(UserActivity::class)->findOneBy([
                'user' => $employee,
                'activity' => $activity
            ]);

            if (!$existing && $employee) {
                $userActivity = new UserActivity();
                $userActivity->setUser($employee);
                $userActivity->setActivity($activity);
                $em->persist($userActivity);
            }
        }

        $em->flush();

        $assignedCount = $em->getRepository(UserActivity::class)->count([
            'activity' => $activity
        ]);

        return new JsonResponse(['success' => true, 'assignedCount' => $assignedCount]);
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

    #[Route('travel/activity/detailsSSS/{id}', name: 'front_activity_detailsEMPLOYEES')]
    public function travelDetailsSSS(EntityManagerInterface $entityManager, int $id): Response
    {
        $activity = $entityManager->getRepository(Activity::class)->find($id);
        
        if (!$activity) {
            throw $this->createNotFoundException('The requested activity does not exist.');
        }
        
        return $this->render('front/activity/detailsEmp.html.twig', [
            'activity' => $activity,
        ]);
    }   
    // #[Route('travel/activity/list', name: 'front_list_activity')]
    // public function travelListActivityIndex(EntityManagerInterface $entityManager): Response
    // {
    //     $activities = $entityManager->getRepository(Activity::class)->findAll();
        
    //     return $this->render('front/activity/list.html.twig', [
    //         'activities' => $activities,
    //     ]);
    // }
    
      

    #[Route('travel/activity/list', name: 'front_list_activity')]
public function employeeActivities(EntityManagerInterface $em): Response
{
    $user = $this->getUser();

    if (!$user instanceof User) {
        throw $this->createAccessDeniedException('Type d\'utilisateur invalide');
    }

    if (!$this->isGranted('ROLE_EMPLOYEE')) {
        throw $this->createAccessDeniedException('Accès réservé aux employés');
    }

    // Récupérer toutes les affectations de l'utilisateur
    $assignments = $em->getRepository(UserActivity::class)
        ->createQueryBuilder('ua')
        ->join('ua.activity', 'a')
        ->where('ua.user = :user')
        ->setParameter('user', $user)
        ->orderBy('a.datedebut', 'DESC')
        ->getQuery()
        ->getResult();

    return $this->render('front/activity/list.html.twig', [
        'assignments' => $assignments,
    ]);
}
    }

