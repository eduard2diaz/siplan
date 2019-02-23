<?php

namespace App\Repository;

use App\Entity\Fichero;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Fichero|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fichero|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fichero[]    findAll()
 * @method Fichero[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FicheroRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Fichero::class);
    }

//    /**
//     * @return Fichero[] Returns an array of Fichero objects
//     */
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
    public function findOneBySomeField($value): ?Fichero
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
