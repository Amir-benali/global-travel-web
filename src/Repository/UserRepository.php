<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Recherche multicritère dynamique
     * 
     * @param string $term Le terme de recherche
     * @param int|null $limit Nombre maximum de résultats
     * @param string|null $role Filtre par rôle spécifique
     * @return User[] Tableau des utilisateurs correspondants
     */
    public function findBySearch(string $term, ?int $limit = null, ?string $role = null): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.firstname LIKE :term')
            ->orWhere('u.lastname LIKE :term')
            ->orWhere('u.email LIKE :term')
            ->orWhere('u.phoneNumber LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('u.lastname', 'ASC');

        if ($role) {
            $queryBuilder
                ->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        }

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Trouve un utilisateur par email (insensible à la casse)
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) = LOWER(:email)')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les utilisateurs par rôle
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%')
            ->orderBy('u.lastname', 'ASC')
            ->getQuery()
            ->getResult();
    }
}