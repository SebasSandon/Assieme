<?php

namespace App\Repository;

use App\Entity\Hello;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Hello|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hello|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hello[]    findAll()
 * @method Hello[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HelloRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Hello::class);
    }

    // /**
    //  * @return Hello[] Returns an array of Hello objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Hello
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
