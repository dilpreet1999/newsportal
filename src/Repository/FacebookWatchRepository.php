<?php

namespace App\Repository;

use App\Entity\FacebookWatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FacebookWatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method FacebookWatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method FacebookWatch[]    findAll()
 * @method FacebookWatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FacebookWatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacebookWatch::class);
    }

    // /**
    //  * @return FacebookWatch[] Returns an array of FacebookWatch objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FacebookWatch
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
