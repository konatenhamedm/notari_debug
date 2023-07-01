<?php

namespace App\Repository;

use App\Entity\TypeClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeClient>
 *
 * @method TypeClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeClient[]    findAll()
 * @method TypeClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeClientRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeClient::class);
    }

    public function add(TypeClient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeClient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countAll($searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(id),t.titre as titre
FROM type_client as t
WHERE  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['titre']);



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
t.titre as titre
FROM type_client t
WHERE  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['titre']);

        $sql .= ' ORDER BY t.id DESC';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }



        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }
//    /**
//     * @return TypeClient[] Returns an array of TypeClient objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TypeClient
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
