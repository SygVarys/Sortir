<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Repository\LieuRepository;


use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LieuController extends AbstractController
{
    #[Route('/lieu', name: 'app_lieu')]
    public function creer(Request $request, VilleRepository $villeRepository,  EntityManagerInterface $entityManager, LieuRepository $lieuRepository): Response
    {
        $lieu = new Lieu();
        $ville = new Ville();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            var_dump($form->getData());
            $lieu = $form->getData();
            $ville = $villeRepository->find(61);
            $lieu->setVille($ville);
            $entityManager->persist($lieu);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
            'form' => $form,
        ]);
    }
}
