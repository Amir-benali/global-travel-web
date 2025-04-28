<?php

namespace App\Repository;

use App\Entity\GoogleAuthToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GoogleAuthTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GoogleAuthToken::class);
    }

    public function findLatest(): ?GoogleAuthToken
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(GoogleAuthToken $token): void
    {
        $this->_em->persist($token);
        $this->_em->flush();
    }
}