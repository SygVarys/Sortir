<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class VilleFixtures extends Fixture
{
    private Faker\Generator $faker;

    public function __construct() {
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        for($i=0; $i<10; $i++ ){
            $ville = new Ville();
            $ville->setNom($this->faker->city());
            $ville->setCodePostal($this->faker->postcode());
            $manager->persist($ville);
        }


        $manager->flush();
    }
}