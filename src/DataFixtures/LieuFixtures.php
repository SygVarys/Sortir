<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    private Faker\Generator $faker;

    public function __construct(private readonly VilleRepository $villeRepository) {
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        for($i=0; $i<10; $i++ ){
            $lieu = new Lieu();
            $lieu->setNom($this->faker->words(3, true));
            $lieu->setRue($this->faker->address());
            $lieu->setLatitude($this->faker->latitude);
            $lieu->setLongitude($this->faker->longitude);
            $test = $this->villeRepository->findOneBy([]);
            $lieu->setVille($test);
            $manager->persist($lieu);
        }


        $manager->flush();
    }

    public function getDependencies()
    {
      return [VilleFixtures::class,];
    }
}