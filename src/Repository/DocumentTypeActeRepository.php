<?php

namespace App\Repository;

use App\Entity\DocumentTypeActe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentTypeActe>
 *
 * @method DocumentTypeActe|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentTypeActe|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentTypeActe[]    findAll()
 * @method DocumentTypeActe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentTypeActeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentTypeActe::class);
    }

    public function add(DocumentTypeActe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DocumentTypeActe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getListeDocument()
    {
        return $this->createQueryBuilder("d")
            ->innerJoin('d.type','t')
            ->where('t.titre=:titre')
            ->setParameter('titre', 'acte de vente')
            ->getQuery()
            ->getResult();
    }


    public function getDocumentsEtape($typeActe, $etape)
    {
        return $this->createQueryBuilder("d")
            ->where('d.type = :typeActe')
            ->andWhere("d.etapes LIKE :etape")
            ->setParameter('typeActe', $typeActe)
            ->setParameter('etape', "%{$etape}%")
            ->getQuery()
            ->getResult();
    }

    public function getFichierLibelle($value)
    {
        return $this->createQueryBuilder("a")
            ->innerJoin('a.type','t')
            ->where('t.titre=:libelle')
            ->setParameter('libelle', $value)
            ->getQuery()
            ->getResult();
    }
//    /**
//     * @return DocumentTypeActe[] Returns an array of DocumentTypeActe objects
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

//    public function findOneBySomeField($value): ?DocumentTypeActe
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
