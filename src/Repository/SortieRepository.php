<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

        /**
         * @return Sortie[] Returns an array of Sortie objects
         */
        public function findByFiltre($filtre, $user): array
        {
            $entityManager = $this->getEntityManager();
                $dql = 'SELECT s
                    FROM App\Entity\Sortie s
                    JOIN s.lieu l
                    JOIN l.ville v 
                    WHERE v.nom = :nomVille';
            if ($filtre['dateDebut']){
                $dql .= ' AND s.dateHeureDebut > :dateDebut';
            }
            if ($filtre['dateFin']){
                $dql .= ' AND s.dateHeureDebut < :dateFin';
            }
            if(in_array(1, $filtre['filtre'])){
                $dql .= ' AND s.organisateur = :idOrganisateur';
            }


            $query = $entityManager->createQuery($dql);
            $query->setParameter('nomVille', $filtre['site']->getNom() );
            if ($filtre['dateDebut']){
                $query->setParameter('dateDebut', $filtre['dateDebut']);
            }
            if ($filtre['dateFin']){
                $query->setParameter('dateFin', $filtre['dateFin']);
            }
            if(in_array(1, $filtre['filtre'])){
                //dd($user);
                $query->setParameter('idOrganisateur', $user->getId());
            }


            return $query->getResult();

//           $query = $this-> createQueryBuilder('s');
//           $query->andWhere('s.lieu.ville = :ville')
//               ->setParameter('ville',$filtre['site']);
//           return $query->getQuery()->getResult();


        }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
