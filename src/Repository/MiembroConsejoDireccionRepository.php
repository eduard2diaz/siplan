<?php

namespace App\Repository;

use App\Entity\MiembroConsejoDireccion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MiembroConsejoDireccion|null find($id, $lockMode = null, $lockVersion = null)
 * @method MiembroConsejoDireccion|null findOneBy(array $criteria, array $orderBy = null)
 * @method MiembroConsejoDireccion[]    findAll()
 * @method MiembroConsejoDireccion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MiembroConsejoDireccionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MiembroConsejoDireccion::class);
    }

    // /**
    //  * @return MiembroConsejoDireccion[] Returns an array of MiembroConsejoDireccion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MiembroConsejoDireccion
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
