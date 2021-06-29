<?php

namespace App\Repository;

use App\Entity\DeliciousPizza;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeliciousPizza|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliciousPizza|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliciousPizza[]    findAll()
 * @method DeliciousPizza[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliciousPizzaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliciousPizza::class);
    }

    // /**
    //  * @return DeliciousPizza[] Returns an array of DeliciousPizza objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DeliciousPizza
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
