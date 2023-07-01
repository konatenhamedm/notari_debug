<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    use TableInfoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Client $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Client $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }



    public function countAll($typeClient, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(id)
FROM client
WHERE  type_client_id = :type_client
SQL;
        $params = ['type_client' => $typeClient];
      
       
        

        
        $sql .= $this->getSearchColumns($searchValue, $params, ['nom', 'prenom', 'raison_social', 'tel_portable', 'site_web', 'email', 'boite_postal', 'registre_commercial']);
        
       

        $stmt = $connection->executeQuery($sql, $params);

       
        return intval($stmt->fetchOne());
    }



    public function getAll($typeClient, $limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT
id, 
COALESCE(nom, raison_social) AS intitule,
nom,
prenom,
raison_social,
tel_portable,
site_web,
email,
profession,
boite_postal,
registre_commercial
FROM client

WHERE  type_client_id = :type_client
SQL;
    $params = ['type_client' => $typeClient];
       

    $sql .= $this->getSearchColumns($searchValue, $params, ['nom', 'prenom', 'raison_social', 'tel_portable', 'site_web', 'email', 'boite_postal', 'registre_commercial']);
if($typeClient = 1 ){

    $sql .= ' ORDER BY nom';
}else{
    $sql .= ' ORDER BY raison_social';

}

    if ($limit && $offset == null) {
        $sql .= " LIMIT {$limit}";
    } else if ($limit && $offset) {
        $sql .= " LIMIT {$offset},{$limit}";
    }
        
       

        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }
    public function getClient($val){
        return $this->createQueryBuilder('c')
            ->select('c.raisonSocial','c.nom','c.prenom','t.titre')
            ->leftJoin('c.vendeurs','v')
            ->innerJoin('c.type_client','t')
            ->andWhere('v.dossier = :val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Client[] Returns an array of Client objects
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
    public function findOneBySomeField($value): ?Client
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
