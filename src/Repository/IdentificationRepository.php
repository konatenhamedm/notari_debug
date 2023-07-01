<?php

namespace App\Repository;

use App\Entity\Identification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Identification>
 *
 * @method Identification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Identification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Identification[]    findAll()
 * @method Identification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IdentificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Identification::class);
    }

    public function add(Identification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Identification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function getLength($value){
        return $this->createQueryBuilder("i")
            ->select('count(i.id)')
            ->innerJoin('i.dossier','d')
            ->innerJoin('d.typeActe','t')
            ->where('d.id=:id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getSingleScalarResult();
    }
//    /**
//     * @return Identification[] Returns an array of Identification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Identification
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
