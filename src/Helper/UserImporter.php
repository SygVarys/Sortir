<?php

namespace App\Helper;

use App\Entity\User;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserImporter
{
    private $entityManager;
    private SiteRepository $siteRepository;

    public function __construct(EntityManagerInterface $entityManager, SiteRepository $siteRepository)
    {
        $this->entityManager = $entityManager;
        $this->siteRepository = $siteRepository;
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
            $user->setIsActif($data[5]);
            $user->setSite($this->siteRepository->find($data[6]));
            $user->setPseudo($data[7]);

            $this->entityManager->persist($user);
        }
        fclose($file);


        $this->entityManager->flush();
    }
}
