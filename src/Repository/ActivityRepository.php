<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 *
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function save(Activity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Activity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Activity[] Returns an array of Activity objects
     */
    // Removed duplicate searchByName method to avoid redeclaration error.

    /**
     * @return Activity[] Returns an array of upcoming Activity objects
     */
    public function findUpcomingActivities(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.datedebut >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('a.datedebut', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Activity[] Returns paginated Activity objects
     */
    public function findPaginatedActivities(int $page = 1, int $limit = 10): Paginator
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.datedebut', 'DESC')
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $paginator;
    }

    /**
     * @return Activity[] Returns activities by type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.typeactivity = :type')
            ->setParameter('type', $type)
            ->orderBy('a.datedebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Activity[] Returns activities within date range
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.datedebut BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('a.datedebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Activity[] Returns activities by location
     */
    public function findByLocation(string $location): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.localisation LIKE :location')
            ->setParameter('location', '%'.$location.'%')
            ->orderBy('a.datedebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Activity[] Returns activities by user
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user_id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.datedebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function searchByName(string $query): array
{
    return $this->createQueryBuilder('a')
        ->where('a.nomactivity LIKE :query')
        ->orWhere('a.description LIKE :query')
        ->orWhere('a.localisation LIKE :query')
        ->setParameter('query', '%'.$query.'%')
        ->orderBy('a.datedebut', 'DESC')
        ->setMaxResults(50) // Limite pour les performances
        ->getQuery()
        ->getResult();
}
public function countActivitiesByType(): array
{
    return $this->createQueryBuilder('a')
        ->select('a.typeactivity as type, COUNT(a.id) as count')
        ->groupBy('a.typeactivity')
        ->getQuery()
        ->getResult();
}

public function getMonthlyActivityStats(): array
{
    return $this->createQueryBuilder('a')
        ->select("SUBSTRING(a.datedebut, 1, 7) as month, COUNT(a.id) as count")
        ->groupBy('month')
        ->orderBy('month')
        ->setMaxResults(12)
        ->getQuery()
        ->getResult();
}


public function getPriceStatistics(): array
{
    return $this->createQueryBuilder('a')
        ->select('AVG(a.prixtotal) as avgPrice, MAX(a.prixtotal) as maxPrice, MIN(a.prixtotal) as minPrice, SUM(a.prixtotal) as totalRevenue')
        ->getQuery()
        ->getSingleResult();
}

public function findMostPopularActivityType(): array
{
    $result = $this->createQueryBuilder('a')
        ->select('a.typeactivity as type, COUNT(a.id) as count')
        ->groupBy('a.typeactivity')
        ->orderBy('count', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();

    // Calcule le pourcentage
    $total = $this->count([]);
    $result['percentage'] = $total > 0 ? ($result['count'] / $total) * 100 : 0;

    return $result;
}

public function calculateRevenueTrend(): array
{
    // Période actuelle (ex: ce mois-ci)
    $currentStart = new \DateTime('first day of this month');
    $currentEnd = new \DateTime('last day of this month');
    
    // Période précédente (ex: mois dernier)
    $previousStart = new \DateTime('first day of last month');
    $previousEnd = new \DateTime('last day of last month');

    // Requêtes pour les deux périodes
    $currentRevenue = $this->createQueryBuilder('a')
        ->select('SUM(a.prixtotal) as total')
        ->where('a.datedebut BETWEEN :start AND :end')
        ->setParameter('start', $currentStart)
        ->setParameter('end', $currentEnd)
        ->getQuery()
        ->getSingleScalarResult() ?? 0;

    $previousRevenue = $this->createQueryBuilder('a')
        ->select('SUM(a.prixtotal) as total')
        ->where('a.datedebut BETWEEN :start AND :end')
        ->setParameter('start', $previousStart)
        ->setParameter('end', $previousEnd)
        ->getQuery()
        ->getSingleScalarResult() ?? 0;

    // Calcul de la tendance
    $difference = $currentRevenue - $previousRevenue;
    $percentage = $previousRevenue != 0 ? ($difference / $previousRevenue) * 100 : 0;

    return [
        'value' => $difference,
        'percentage' => $percentage
    ];
}


public function countActivitiesBeforeDate(\DateTimeInterface $date): int
{
    return $this->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->where('a.datedebut < :date')
        ->setParameter('date', $date)
        ->getQuery()
        ->getSingleScalarResult();
}

public function getTotalRevenue(): float
{
    return (float) $this->createQueryBuilder('a')
        ->select('SUM(a.prixtotal)')
        ->getQuery()
        ->getSingleScalarResult();
}

public function getRevenueBeforeDate(\DateTimeInterface $date): float
{
    return (float) $this->createQueryBuilder('a')
        ->select('SUM(a.prixtotal)')
        ->where('a.datedebut < :date')
        ->setParameter('date', $date)
        ->getQuery()
        ->getSingleScalarResult();
}

public function countByType(string $type): int
{
    return $this->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->where('a.typeactivity = :type')
        ->setParameter('type', $type)
        ->getQuery()
        ->getSingleScalarResult();
}

public function countByTypeBeforeDate(string $type, \DateTimeInterface $date): int
{
    return $this->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->where('a.typeactivity = :type')
        ->andWhere('a.datedebut < :date')
        ->setParameter('type', $type)
        ->setParameter('date', $date)
        ->getQuery()
        ->getSingleScalarResult();
}

public function getMonthlyActivitiesData(int $months = 6): array
{
    $connection = $this->getEntityManager()->getConnection();
    
    $sql = "
        SELECT 
            DATE_FORMAT(a.datedebut, '%Y-%m') as month,
            COUNT(a.id) as count
        FROM activity a
        WHERE a.datedebut >= DATE_SUB(CURRENT_DATE(), INTERVAL :months MONTH)
        GROUP BY month
        ORDER BY month ASC
    ";
    
    $stmt = $connection->prepare($sql);
    $stmt->bindValue('months', $months);
    $result = $stmt->executeQuery();
    
    return $result->fetchAllAssociative();
}

public function getRevenueByType(): array
{
    return $this->createQueryBuilder('a')
        ->select('a.typeactivity as type, SUM(a.prixtotal) as revenue')
        ->groupBy('a.typeactivity')
        ->getQuery()
        ->getResult();
}

public function getActivityTypeDistribution(): array
{
    return $this->createQueryBuilder('a')
        ->select('a.typeactivity as type, COUNT(a.id) as count')
        ->groupBy('a.typeactivity')
        ->getQuery()
        ->getResult();
}

public function countCompletedActivities(): int
{
    return $this->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->where('a.datefin < CURRENT_DATE()')
        ->getQuery()
        ->getSingleScalarResult();
}

public function countUpcomingActivities(): int
{
    return $this->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->where('a.datedebut > CURRENT_DATE()')
        ->getQuery()
        ->getSingleScalarResult();
}

public function countOngoingActivities(): int
{
    return $this->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->where('a.datedebut <= CURRENT_DATE()')
        ->andWhere('a.datefin >= CURRENT_DATE()')
        ->getQuery()
        ->getSingleScalarResult();
}

public function countCancelledActivities(): int
{
    // Assuming you have a status field or similar to track cancellations
    // Adjust this query based on your actual implementation
    return 0; // Placeholder - implement based on your business logic
}

public function findRecentActivities(int $limit = 5): array
{
    return $this->createQueryBuilder('a')
        ->orderBy('a.datedebut', 'ASC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}



    

    
}