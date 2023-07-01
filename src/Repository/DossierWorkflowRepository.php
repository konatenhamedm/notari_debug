<?php

namespace App\Repository;

use App\Entity\DossierWorkflow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DossierWorkflow>
 *
 * @method DossierWorkflow|null find($id, $lockMode = null, $lockVersion = null)
 * @method DossierWorkflow|null findOneBy(array $criteria, array $orderBy = null)
 * @method DossierWorkflow[]    findAll()
 * @method DossierWorkflow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DossierWorkflowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DossierWorkflow::class);
    }

    public function add(DossierWorkflow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DossierWorkflow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function getListe($value){
        return $this->createQueryBuilder("w")
            ->select('f.libelleEtape as libelle','f.numeroEtape as nbre')
            ->innerJoin('w.dossier','d')
            ->innerJoin('w.workflow','f')
            ->where('d.id=:id')
            ->orderBy('f.numeroEtape')
            /*  ->where('a.active equal 1')*/
            ->setParameter('id', $value)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return DossierWorkflow[] Returns an array of DossierWorkflow objects
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

//    public function findOneBySomeField($value): ?DossierWorkflow
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
