<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 *
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    //trouve les stagiaires de la bdd non inscrits à une session précise
    //  SELECT * FROM stagiaire WHERE id_stagiaire NOT IN (SELECT id_stagiaire FROM session_stagiaire WHERE id_session = : id_session)
    public function findNonInscrits($session_id){
        $em = $this->getEntityManager(); //em=EntityManager
        $sub = $em->createQueryBuilder();

        $qb = $sub;
        //trouve tous les stagiaires d'une session
        $qb->select('s') //s=stagiaire
            ->from('App\Entity\Stagiaire', 's')
            ->leftJoin('s.sessions', 'se')
            ->where('se.id = :id');

        $sub = $em->createQueryBuilder();
        //trouve tous les stagiaires pas dans le résultat précédent
        $sub->select('st')
        ->from('App\Entity\Stagiaire', 'st')
        ->where($sub->expr()->notIn('st.id', $qb->getDQL()))
        ->setParameter('id', $session_id)
        ->orderBy('st.nom');

        //renvoie le resultat
        $query = $sub->getQuery();
        return $query->getResult();
    }

    //     //recherche en fonction d'un mot clé dans les enregistrements dans la bdd
    // public function findByWord($word) {
    //     $em = $this->getEntityManager();

    //     $sub = $em->createQueryBuilder();

    //     $qb = $sub;

    //     $qb->select('a')
    //         ->from('App\Entity\Session', 'a')
    //         ->where('a. LIKE :word')
    //         ->setParameter('word', '%'.$word.'%');

    //     $query = $sub->getQuery();
    //     return $query->getResult();
    // }


    //    /**
    //     * @return Session[] Returns an array of Session objects
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

    //    public function findOneBySomeField($value): ?Session
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
