<?php

namespace App\Repository;

use App\Entity\StoryImages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StoryImages|null find($id, $lockMode = null, $lockVersion = null)
 * @method StoryImages|null findOneBy(array $criteria, array $orderBy = null)
 * @method StoryImages[]    findAll()
 * @method StoryImages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StoryImagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StoryImages::class);
    }

    // /**
    //  * @return StoryImages[] Returns an array of StoryImages objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StoryImages
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
