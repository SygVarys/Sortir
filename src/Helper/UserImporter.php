<?php

namespace App\Helper;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserImporter
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function importUsersFromCsv(UploadedFile $csvFile): void
    {

        $file = fopen($csvFile->getPathname(), 'r');
        while (($data = fgetcsv($file)) !== false) {

            $user = new User();
            $user->setEmail($data[0]);
            $user->setPassword($data[1]);
            $user->setNom($data[2]);
            $user->setPrenom($data[3]);
            $user->setTelephone($data[4]);
            $user->setPseudo($data[5]);

            $this->entityManager->persist($user);
        }
        fclose($file);


        $this->entityManager->flush();
    }
}
