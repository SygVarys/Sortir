<?php

namespace App\Repository;

use App\Entity\Sortie;
use DateTime;
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
     * Filtrage des sorties à l'aide de Query Builder :
     * part ville, recherche de carctères, dates, organisation, participation, dates passées
     *
     */
    public function findByFiltre($filtre, $user): array
    {

        $q = $this->createQueryBuilder('s');
        $q->join('s.lieu', 'l');
        $q->join('l.ville', 'v');
            if ($filtre['site']) {
                $q->andWhere('v.nom = :ville')
                    ->setParameter('ville', $filtre['site']->getNom());
            }

            if ($filtre['contains']) {
                $q->andWhere('s.nom LIKE :keyword')
                    ->setParameter('keyword', '%' . $filtre['contains'] . '%');
            }
            if ($filtre['dateDebut']) {
                $q->andWhere('s.dateHeureDebut > :dateDebut')
                    ->setParameter('dateDebut', $filtre['dateDebut']);
            }
            if ($filtre['dateFin']) {
                $q->andWhere('s.dateHeureDebut < :dateFin')
                    ->setParameter('dateFin', $filtre['dateFin']);
            }
            if (in_array(1, $filtre['filtre'])) {
                $q->andWhere('s.organisateur = :idOrganisateur')
                    ->setParameter('idOrganisateur', $user->getId());
            }
            if (in_array(2, $filtre['filtre']) xor in_array(3, $filtre['filtre'])) {
                (in_array(2, $filtre['filtre'])) ? $q->andWhere(':idUser MEMBER OF s.participants') :  $q->andWhere(':idUser NOT MEMBER OF s.participants');
                    $q->setParameter('idUser', $user->getId());

            }
            if (in_array(4, $filtre['filtre'])) {
                $q->andWhere('s.dateHeureDebut < :datePresente')
                    ->setParameter('datePresente', new DateTime());
            }
            $q->orderBy('s.dateHeureDebut', 'DESC');


/*
 *      Code en DQL, la même chose que précédemment, c'est beau mais c'est long !!!! Sans doûte optimisable!
 */
/*      $entityManager = $this->getEntityManager();
        $dql = 'SELECT s
                    FROM App\Entity\Sortie s
                    JOIN s.lieu l
                    JOIN l.ville v                   
                    WHERE v.nom = :nomVille';
        if ($filtre['contains']) {
            $dql .= ' AND s.nom LIKE :keyword';
        }
        if ($filtre['dateDebut']) {
            $dql .= ' AND s.dateHeureDebut > :dateDebut';
        }
        if ($filtre['dateFin']) {
            $dql .= ' AND s.dateHeureDebut < :dateFin';
        }
        if (in_array(1, $filtre['filtre'])) {
            $dql .= ' AND s.organisateur = :idOrganisateur';
        }
        if (in_array(2, $filtre['filtre'])) {
            $dql .= ' AND :idUser MEMBER OF s.participants';
        }
        if (in_array(3, $filtre['filtre'])) {
            $dql .= ' AND :idUser NOT MEMBER OF s.participants';
        }
        if (in_array(4, $filtre['filtre'])) {
            $dql .= ' AND s.dateHeureDebut < :datePresente';
        }

        $dql .= ' ORDER BY s.dateHeureDebut DESC';



        $query = $entityManager->createQuery($dql);
        if ($filtre['site']) {
            $query->setParameter('nomVille', $filtre['site']->getNom());
        }
        if ($filtre['contains']) {
            $query->setParameter('keyword', '%' . $filtre['contains'] . '%');
        }
        if ($filtre['dateDebut']) {
            $query->setParameter('dateDebut', $filtre['dateDebut']);
        }
        if ($filtre['dateFin']) {
            $query->setParameter('dateFin', $filtre['dateFin']);
        }
        if (in_array(1, $filtre['filtre'])) {
            //dd($user);
            $query->setParameter('idOrganisateur', $user->getId());
        }
        if (in_array(2, $filtre['filtre'])) {
            $query->setParameter('idUser', $user->getId());
        }
        if (in_array(3, $filtre['filtre'])) {
            $query->setParameter('idUser', $user->getId());
        }
        if (in_array(4, $filtre['filtre'])) {
            $query->setParameter('datePresente', new DateTime());
        }
*/
       // return $query->getResult();
        return $q->getQuery()->getResult();

    }
}
