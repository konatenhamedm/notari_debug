<?php

namespace App\Repository;

use App\Entity\CourierArrive;
use App\Entity\Fichier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CourierArrive>
 *
 * @method CourierArrive|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourierArrive|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourierArrive[]    findAll()
 * @method CourierArrive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourierArriveRepository extends ServiceEntityRepository
{ use TableInfoTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourierArrive::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CourierArrive $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getNumero($type){
        //$replace = substr($annee,-2);
        $query = $this->createQueryBuilder("a")
            ->select("count(a.numero)")
            //->select('MAX(CAST(a.numero,\''.$replace.'-\', \'\') as SIGNED))')
            ->andWhere('a.type =:type')
            ->setParameter('type',$type);


           /* return intval($query->getQuery()->getSingleScalarResult())+1
            ;*/

        $nb = $query->getQuery()->getSingleScalarResult();

        if ($nb == 0){
            $nb = 1;
        }else{
            $nb =$nb + 1;
        }
        if($type=="DEPART"){
            $typeCourrier ="CD";
        }else if($type == "ARRIVE"){
            $typeCourrier ="CA";
        }else{
            $typeCourrier ="CI";
        }

//dd(str_pad($nb, 3, '0', STR_PAD_LEFT));
        return ($typeCourrier.date("y").'-'.str_pad($nb, 3, '0', STR_PAD_LEFT));
    }

    public function getNumeroIncrementation($type){
        //$replace = substr($annee,-2);
        $query = $this->createQueryBuilder("a")
            ->select("max(a.numero)")
            //->select('MAX(CAST(a.numero,\''.$replace.'-\', \'\') as SIGNED))')
            ->andWhere('a.type =:type')
            ->setParameter('type',$type);


        /* return intval($query->getQuery()->getSingleScalarResult())+1
         ;*/

        $nb = $query->getQuery()->getSingleScalarResult();

        if ($nb == 0){
            $nb = 1;
        }else{
            $nb =$nb + 1;
        }

//dd(str_pad($nb, 3, '0', STR_PAD_LEFT));
        return $nb;
    }

    public function getFichier($value){
        return $this->createQueryBuilder("a")
            ->select("f.path","f.alt")
            ->innerJoin('a.documentCourriers','d')
            ->innerJoin('d.fichier','f')
            ->where('a.id=:id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(CourierArrive $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function countAll($searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(id),t.numero
FROM courier_arrive as t
WHERE t.type = "ARRIVE" and  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero']);



        $stmt = $connection->executeQuery($sql, $params);


        return intval($stmt->fetchOne());
    }


    public function countAllDepart($etat,$searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(t.id)
FROM courier_arrive as t
WHERE t.etat = :etat and t.type = "DEPART"
SQL;
        $params = ['etat' => $etat];

        $sql .= $this->getSearchColumns($searchValue, $params, ['t.numero']);
        $stmt = $connection->executeQuery($sql, $params);


        return intval($stmt->fetchOne());
    }


    public function countAllInterne($etat,$searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(id),t.numero
FROM courier_arrive as t
WHERE t.etat = :etat and t.type = "INTERNE"
SQL;
        $params = ['etat' => $etat];

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero']);



        $stmt = $connection->executeQuery($sql, $params);


        return intval($stmt->fetchOne());
    }



    public function getAll($limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT
t.id,
t.numero as numero,t.date_reception ,t.objet,t.expediteur
FROM courier_arrive t
WHERE t.type = "ARRIVE" and  1 = 1
SQL;
        $params = [];
//dd(date_reception);
        $sql .= $this->getSearchColumns($searchValue, $params, ['numero','expediteur','date_reception','objet']);

        $sql .= ' ORDER BY id desc';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }



        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }
    public function getAllDepart($etat,$limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT
t.id,
t.numero as numero,t.date_envoi ,t.objet,t.destinataire
FROM courier_arrive t
WHERE t.etat = :etat and t.type = "DEPART"
SQL;
        $params = ['etat' => $etat];
/*
        if ($etat == 'termine') {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.{$etat}') = 1)";
        } else {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.termine') = 0)";
        }*/

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero','destinataire','date_envoi','objet']);

        $sql .= ' ORDER BY id desc';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }



        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }


    public function getAllInterne($etat,$limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT
t.id,
t.numero as numero,t.date_envoi ,t.objet,concat(u.nom,' ', u.prenoms) as fullname,u.nom,u.prenoms
FROM courier_arrive t
left join utilisateur u on t.user_id = u.id
WHERE t.etat = :etat and t.type = "INTERNE" 
SQL;
        $params = ['etat' => $etat];

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero','objet','nom','prenoms','date_envoi']);

        $sql .= ' ORDER BY id desc';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }



        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }


    // /**
    //  * @return CourierArrive[] Returns an array of CourierArrive objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CourierArrive
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
