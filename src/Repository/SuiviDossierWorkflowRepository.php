<?php

namespace App\Repository;

use App\Entity\SuiviDossierWorkflow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuiviDossierWorkflow>
 *
 * @method SuiviDossierWorkflow|null find($id, $lockMode = null, $lockVersion = null)
 * @method SuiviDossierWorkflow|null findOneBy(array $criteria, array $orderBy = null)
 * @method SuiviDossierWorkflow[]    findAll()
 * @method SuiviDossierWorkflow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuiviDossierWorkflowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuiviDossierWorkflow::class);
    }

    public function add(SuiviDossierWorkflow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SuiviDossierWorkflow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SuiviDossierWorkflow[] Returns an array of SuiviDossierWorkflow objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SuiviDossierWorkflow
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
