<?php

namespace App\Repository;

use App\Entity\DocumentReception;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentReception>
 *
 * @method DocumentReception|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentReception|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentReception[]    findAll()
 * @method DocumentReception[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentReceptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentReception::class);
    }

    public function add(DocumentReception $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DocumentReception $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getFichier($value){
        return $this->createQueryBuilder("d")
            ->select("f.path","f.alt")
            ->innerJoin('d.fichier','f')
            ->where('d.courier=:id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return DocumentReception[] Returns an array of DocumentReception objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DocumentReception
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
