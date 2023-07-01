<?php

namespace App\Repository;

use App\Entity\DocumentCourrier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentCourrier>
 *
 * @method DocumentCourrier|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentCourrier|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentCourrier[]    findAll()
 * @method DocumentCourrier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentCourrierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentCourrier::class);
    }

    public function add(DocumentCourrier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DocumentCourrier $entity, bool $flush = false): void
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
            ->innerJoin('d.courier','c')
            ->where('c.id=:id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return DocumentCourrier[] Returns an array of DocumentCourrier objects
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

//    public function findOneBySomeField($value): ?DocumentCourrier
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
