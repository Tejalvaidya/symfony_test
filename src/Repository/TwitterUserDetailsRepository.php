<?php

namespace App\Repository;

use App\Entity\TwitterUserDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TwitterUserDetails>
 *
 * @method TwitterUserDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method TwitterUserDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method TwitterUserDetails[]    findAll()
 * @method TwitterUserDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TwitterUserDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TwitterUserDetails::class);
    }

//    /**
//     * @return TwitterUserDetails[] Returns an array of TwitterUserDetails objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TwitterUserDetails
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
