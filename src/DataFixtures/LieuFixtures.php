<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{


    private Faker\Generator $faker;
    private VilleRepository $fakerVille;


    public function __construct(VilleRepository $villeRepository)
    {
        $this->faker = Faker\Factory::create();
        $this->fakerVille = $villeRepository;
    }

    public function load(ObjectManager $manager): void
    {

        for($i=0; $i<10; $i++ ){
            $lieu = new Lieu();
            $lieu->setNom($this->faker->words(3, true));
            $lieu->setRue($this->faker->address());
            $lieu->setLatitude($this->faker->latitude);
            $lieu->setLongitude($this->faker->longitude);
            $lieu->setVille($this->faker->randomDigit());

        }
        $manager->persist($lieu);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }
}
