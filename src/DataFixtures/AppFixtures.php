<?php

namespace App\DataFixtures;

use App\DataFixtures\LieuFixtures;
use App\Entity\Sortie;
use App\Repository\LieuRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{


    private Faker\Generator $faker;
    private LieuRepository $fakerLieu;


    public function __construct(LieuRepository $lieuRepository)
    {
        $this->faker = Faker\Factory::create('fr_FR');
        $this->fakerLieu = $lieuRepository;
    }

    public function load(ObjectManager $manager): void
    {

        for($i=0; $i<10; $i++ ){
            $sortie = new Sortie();
            $sortie->setNom($this->faker->words(3, true));
            $sec = strtotime($this->faker->date());
            $date = date("d/m/y H:i", $sec);
            $sortie->setDateHeureDebut($this->faker->dateTime());
            $sortie->setDuree($this->faker->numerify('##'));
            $sec = strtotime($this->faker->date());
            $date = date("d/m/y H:i", $sec);
            $sortie->setDateLimiteInscription($this->faker->dateTime());
            $sortie->setNbInscriptionsMax($this->faker->randomDigit());
            $sortie->setInfosSortie($this->faker->sentence());
            $sortie->setEtat("En cours");
            $sortie->setLieu($this->fakerLieu->find(3));

        }
        $manager->persist($sortie);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LieuFixtures::class,
        ];
    }
}
