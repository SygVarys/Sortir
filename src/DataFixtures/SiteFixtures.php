<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class SiteFixtures extends Fixture
{
    private Faker\Generator $faker;

    public function __construct() {
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $tableau = ['NANTES','RENNES', 'NIORT', 'QUIMPER', 'EN LIGNE'];
        for($i=0; $i<5; $i++ ){
            $site = new Site();
            $site->setNom( $tableau[$i]);
            $manager->persist($site);
        }


        $manager->flush();
    }


}