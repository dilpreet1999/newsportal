<?php

namespace App\Repository;

use App\Entity\ChannelRefference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChannelRefference|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChannelRefference|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChannelRefference[]    findAll()
 * @method ChannelRefference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChannelRefferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChannelRefference::class);
    }

    // /**
    //  * @return ChannelRefference[] Returns an array of ChannelRefference objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ChannelRefference
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
