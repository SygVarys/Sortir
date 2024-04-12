<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\SiteRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private Faker\Generator $faker;

    public function __construct(private readonly SiteRepository $siteRepository, private readonly UserPasswordHasherInterface $hasher) {
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {

        for($i=0; $i<10; $i++ ){
            $user = new User();
            $user->setPseudo($this->faker->userName());
            $user->setEmail($this->faker->email());
            $user->setRoles(["ROLE_ADMIN"]);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setNom($this->faker->userName());
            $user->setPrenom($this->faker->name);
            $user->setTelephone($this->faker->phoneNumber());
            $user->setIsActif(1);
            $user->setIsAdmin(1);
            $user->setSite($this->siteRepository->findOneBy([]));
            $manager->persist($user);
        }


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [SiteFixtures::class,];
    }
}