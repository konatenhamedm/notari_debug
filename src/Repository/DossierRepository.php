<?php

namespace App\Repository;

use App\Entity\Dossier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dossier>
 *
 * @method Dossier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dossier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dossier[]    findAll()
 * @method Dossier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DossierRepository extends ServiceEntityRepository
{
    use TableInfoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dossier::class);
    }

    public function add(Dossier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Dossier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getListe($etat,$titre)
    {
        return $this->createQueryBuilder("d")
            ->innerJoin('d.typeActe','t')
            ->where('d.active=:active')
            ->andWhere('d.etat=:etat')
            ->andWhere('t.titre=:titre')
            ->setParameters(array('active'=>1,'etat'=>$etat,'titre'=>$titre))
            ->getQuery()
            ->getResult();
    }

    public function getListeDossier($id)
    {
        return $this->createQueryBuilder("d")
            ->select('d.id','d.objet','d.numeroOuverture')
            ->innerJoin('d.identifications','i')
            ->where('i.vendeur=:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function search($query = null){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $stmt  = $qb->select('a')
            ->from('App\\Entity\\Dossier','a');

        if ($query){
            $stmt->where('a.numeroOuverture LIKE :query');
            $stmt->setParameter('query',"%{$query}%");
        }

        return $stmt->getQuery()->getResult();
    }


    public function countAll($etat, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(id)
FROM dossier
WHERE  1 = 1
SQL;
        $params = [];

        if ($etat == 'cree') {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.{$etat}') = 1)";
        } else if($etat == 'termine'){
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.termine') = 1)";
        }
        else if($etat == 'en_cours'){
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.en_cours') = 1)";
        }
        else {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.termine') = 0)";
        }




        $sql .= $this->getSearchColumns($searchValue, $params, ['d.numero_ouverture']);



        $stmt = $connection->executeQuery($sql, $params);


        return intval($stmt->fetchOne());
    }



    public function getAll($etat, $limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT
id,
date_creation,
numero_ouverture,
objet,
etape,
type_acte_id
FROM dossier
WHERE  1 = 1
SQL;
        $params = [];
/*
        if ($etat == 'cree') {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.{$etat}') = 1)";
        }
        else {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.cree') = 0)";
        }*/

        if ($etat == 'cree') {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.{$etat}') = 1)";
        } else if($etat == 'termine'){
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.termine') = 1)";
        }
        else if($etat == 'en_cours'){
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.en_cours') = 1)";
        }
        else {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.termine') = 0)";
        }

        $sql .= $this->getSearchColumns($searchValue, $params, ['d.numero_ouverture']);

        $sql .= ' ORDER BY date_creation DESC';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }



        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }




    public function getAllEtat($code)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT date_creation
, objet
, w.`libelle_etape`
, d.`numero_ouverture`
, d.`id`
, w.`route`
, dw.date_fin AS date_fin_prevue
, sw.`date_debut` AS date_debut_etape
, sw.`date_fin` AS date_fin_actuelle
FROM dossier d
JOIN type_acte ta ON ta.id = d.type_acte_id
LEFT JOIN workflow w ON (w.`route` = d.`etape` AND w.`route` != '')
LEFT JOIN `dossier_workflow` dw ON dw.`workflow_id` = w.`id` AND d.`id` = dw.`dossier_id`
LEFT JOIN `suivi_dossier_workflow` sw ON sw.`dossier_workflow_id` = dw.`id`
WHERE ta.code = :code
ORDER BY d.`date_creation` DESC
SQL;
        $params = ['code' => $code];


        


      


        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }



//    /**
//     * @return Dossier[] Returns an array of Dossier objects
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

//    public function findOneBySomeField($value): ?Dossier
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
