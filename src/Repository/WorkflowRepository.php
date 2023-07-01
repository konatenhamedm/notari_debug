<?php

namespace App\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\{Workflow, Type};

/**
 * @extends ServiceEntityRepository<Workflow>
 *
 * @method Workflow|null find($id, $lockMode = null, $lockVersion = null)
 * @method Workflow|null findOneBy(array $criteria, array $orderBy = null)
 * @method Workflow[]    findAll()
 * @method Workflow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workflow::class);
    }

    public function add(Workflow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Workflow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getNombre($value)
    {
        return $this->createQueryBuilder("w")
            ->select("count(w.id)")
            ->innerJoin('w.typeActe', 'a')
            ->where('a.id=:id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getFichier($value)
    {
        return $this->createQueryBuilder("w")
            ->innerJoin('w.typeActe', 'a')
            ->where('a.id=:id')
            /*  ->where('a.active equal 1')*/
            ->setParameter('id', $value)
            ->orderBy('w.numeroEtape')
            ->getQuery()
            ->getResult();
    }


    public function getNext($typeActe, $current)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableWorkflow = $this->getTableName(Workflow::class, $em);
        $tableTypeActe = $this->getTableName(Type::class, $em);
        $sql = <<<SQL
SELECT `route`, t.code
FROM {$tableWorkflow} w
JOIN `{$tableTypeActe}` t ON t.id = w.type_acte_id
WHERE (numero_etape - :current) = 1
AND w.type_acte_id = :type_acte
AND w.active = 1
AND w.route <> ''
LIMIT 1
SQL;
        $params['current'] = $current;
        $params['type_acte'] = $typeActe;
        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAssociative();
    }


    public function getPrev($typeActe, $current)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT `route`, t.code
FROM workflow w
JOIN `type` t ON t.id = w.type_acte_id
WHERE numero_etape < :current
AND w.type_acte_id = :type_acte
LIMIT 1
SQL;
        $params['current'] = $current;
        $params['type_acte'] = $typeActe;
        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchOne();
    }


    public function countAll($searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableWorkflow = $this->getTableName(Workflow::class, $em);
        $tableTypeActe = $this->getTableName(Type::class, $em);
        $sql = <<<SQL
SELECT COUNT(w.id),w.libelle_etape as libelle_etape
FROM {$tableWorkflow} w
JOIN {$tableTypeActe} t ON w.type_acte_id = t.id
WHERE  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['libelle_etape', 'nombre_jours', 'propriete', 't.titre']);


        $stmt = $connection->executeQuery($sql, $params);


        return intval($stmt->fetchOne());
    }


    public function getAll($limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableWorkflow = $this->getTableName(Workflow::class, $em);
        $tableTypeActe = $this->getTableName(Type::class, $em);
        $sql = <<<SQL
        SELECT
        w.id,
        w.numero_etape,
        t.titre AS type_acte,
        w.libelle_etape as libelle_etape,w.nombre_jours as nombre_jours,w.propriete 
        FROM {$tableWorkflow} w
        JOIN {$tableTypeActe} t ON w.type_acte_id = t.id
        WHERE  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['libelle_etape', 'nombre_jours', 'propriete', 't.titre']);

        $sql .= ' ORDER BY t.titre, numero_etape';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }


        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }

//    /**
//     * @return Workflow[] Returns an array of Workflow objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Workflow
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
