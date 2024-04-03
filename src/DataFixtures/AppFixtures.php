<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sortie = new Sortie();
        for($i=0; $i<10; $i++ ){
            $sortie->setNom();
            $sortie->setDateHeureDebut();
            $sortie->setDuree();
            $sortie->setDateLimiteInscription();
            $sortie->setNbInscriptionsMax();
            $sortie->setInfosSortie();
            $sortie->setEtat();
            $sortie->setLieu();

        }
        $manager->persist($sortie);

        $manager->flush();
    }
}
