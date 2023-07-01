<?php

namespace App\Repository;

use App\Entity\DocumentSigne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentSigne>
 *
 * @method DocumentSigne|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentSigne|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentSigne[]    findAll()
 * @method DocumentSigne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentSigneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentSigne::class);
    }

    public function add(DocumentSigne $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DocumentSigne $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function getLength($value){
        return $this->createQueryBuilder("p")
            ->select('count(p.id)')
            ->innerJoin('p.dossier','d')
            ->where('d.id=:id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getSingleScalarResult();
    }
//    /**
//     * @return DocumentSigne[] Returns an array of DocumentSigne objects
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

//    public function findOneBySomeField($value): ?DocumentSigne
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
