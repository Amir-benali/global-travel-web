<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{


    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(ActivityRepository $activityRepository): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // Get current date and calculate previous month
        $now = new \DateTime();
        $lastMonth = (clone $now)->modify('-1 month');
        
        // Total activities stats
        $totalActivities = $activityRepository->count([]);
        $lastMonthActivities = $activityRepository->countActivitiesBeforeDate($lastMonth);
        $activityGrowth = $lastMonthActivities > 0 
            ? round(($totalActivities - $lastMonthActivities) / $lastMonthActivities * 100)
            : 100;
        
        // Revenue stats
        $totalRevenue = $activityRepository->getTotalRevenue();
        $lastMonthRevenue = $activityRepository->getRevenueBeforeDate($lastMonth);
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round(($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100)
            : 100;
        
        // Flight activities stats
        $flightActivities = $activityRepository->countByType('flight');
        $lastMonthFlights = $activityRepository->countByTypeBeforeDate('flight', $lastMonth);
        $flightGrowth = $lastMonthFlights > 0 
            ? round(($flightActivities - $lastMonthFlights) / $lastMonthFlights * 100)
            : ($flightActivities > 0 ? 100 : 0);
        
        // Hotel activities stats
        $hotelActivities = $activityRepository->countByType('hotel');
        $lastMonthHotels = $activityRepository->countByTypeBeforeDate('hotel', $lastMonth);
        $hotelGrowth = $lastMonthHotels > 0 
            ? round(($hotelActivities - $lastMonthHotels) / $lastMonthHotels * 100)
            : ($hotelActivities > 0 ? 100 : 0);
        
        // Monthly activities data for line chart
        $monthlyData = $activityRepository->getMonthlyActivitiesData(6); // Last 6 months
        $monthlyLabels = array_map(function($item) {
            return date('M Y', strtotime($item['month']));
        }, $monthlyData);
        $monthlyValues = array_column($monthlyData, 'count');
        
        // Revenue by type for bar chart
        $revenueByType = $activityRepository->getRevenueByType();
        $revenueLabels = array_column($revenueByType, 'type');
        $revenueValues = array_column($revenueByType, 'revenue');
        
        // Activity distribution by type for pie chart
        $typeDistribution = $activityRepository->getActivityTypeDistribution();
        $typeLabels = array_column($typeDistribution, 'type');
        $typeValues = array_column($typeDistribution, 'count');
        
        // Activity status for pie chart
        $statusData = [
            'completed' => $activityRepository->countCompletedActivities(),
            'upcoming' => $activityRepository->countUpcomingActivities(),
            'ongoing' => $activityRepository->countOngoingActivities(),
            'cancelled' => $activityRepository->countCancelledActivities(),
        ];
        
        // Recent activities
        $recentActivities = $activityRepository->findRecentActivities(5);
        
        return $this->render('dashboard/index.html.twig', [
            'total_activities' => $totalActivities,
            'activity_growth' => $activityGrowth,
            'total_revenue' => $totalRevenue,
            'revenue_growth' => $revenueGrowth,
            'flight_activities' => $flightActivities,
            'flight_growth' => $flightGrowth,
            'hotel_activities' => $hotelActivities,
            'hotel_growth' => $hotelGrowth,
            'monthly_labels' => json_encode($monthlyLabels),
            'monthly_data' => json_encode($monthlyValues),
            'revenue_labels' => json_encode($revenueLabels),
            'revenue_data' => json_encode($revenueValues),
            'type_labels' => json_encode($typeLabels),
            'type_data' => json_encode($typeValues),
            'status_data' => json_encode(array_values($statusData)),
            'recent_activities' => $recentActivities,
        ]);

       
        
        return $this->render('dashboard/index.html.twig');
    }
}