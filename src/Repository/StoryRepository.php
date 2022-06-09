<?php

namespace App\Repository;

use App\Entity\Story;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Story|null find($id, $lockMode = null, $lockVersion = null)
 * @method Story|null findOneBy(array $criteria, array $orderBy = null)
 * @method Story[]    findAll()
 * @method Story[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Story::class);
    }

    // /**
    //  * @return Story[] Returns an array of Story objects
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
    public function findOneBySomeField($value): ?Story
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
   
     *  */
 public function findByQuery($q) {
        $ignore = ['a', 'is', 'by', 'the', 'it','this','that','an','for','of','where','there','when','then'];
        $qb = explode(' ', $q);

        $query = $this->createQueryBuilder('p')
                ->where('p.title LIKE :q')
                ->andWhere('p.tags LIKE :q')
                ->andWhere('p.body LIKE :q');
        if ($qb > 1) {
            foreach ($qb as $k => $v) {
                if(!in_array($v, $ignore)) {
                    $query->orWhere("p.title LIKE :P_$k")
                            ->orWhere("p.tags LIKE :P_$k")
                            ->orWhere("p.body LIKE :P_$k")
                            ->setParameter("P_$k", '%' . $v . '%');
                }
            }
        }
        $query->setParameter('q', '%' . $q . '%');
        return $query->getQuery()
                        ->getResult()
        ;
        return $query;
    }
    
    }
