<?php

namespace App\Repository;

use App\Entity\Administrator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Administrator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Administrator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Administrator[]    findAll()
 * @method Administrator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdministratorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Administrator::class);
    }
    
    /**
     * 
     * @param type $pageSize
     * @param type $currentPage
     * @return Paginated
     */
    public function findPaginated($pageSize = 20, $currentPage = 1)
    {
        $em = $this->getEntityManager();
	         
	// Query
	$qb = $em->createQueryBuilder('administrator')
                 ->select('a')
                 ->from('App:Administrator', 'a');

        $qb->orderBy('a.id', 'DESC');       
        
        $query = $qb->getQuery()
                    ->setFirstResult($pageSize * ($currentPage - 1))
	            ->setMaxResults($pageSize);
	$paginated = new Paginator($query, $fetchJoinCollection = true);

	return $paginated;
    }

    // /**
    //  * @return Administrator[] Returns an array of Administrator objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Administrator
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
