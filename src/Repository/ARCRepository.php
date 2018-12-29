<?php

namespace App\Repository;

use App\Entity\ARC;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ARC|null find($id, $lockMode = null, $lockVersion = null)
 * @method ARC|null findOneBy(array $criteria, array $orderBy = null)
 * @method ARC[]    findAll()
 * @method ARC[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ARCRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ARC::class);
    }

//    /**
//     * @return ARC[] Returns an array of ARC objects
//     */
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
    public function findOneBySomeField($value): ?ARC
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
