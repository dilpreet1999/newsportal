<?php

namespace App\Repository;

use App\Entity\NewsChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsChannel|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsChannel|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsChannel[]    findAll()
 * @method NewsChannel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsChannel::class);
    }

    // /**
    //  * @return NewsChannel[] Returns an array of NewsChannel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NewsChannel
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
