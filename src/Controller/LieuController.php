<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LieuController extends AbstractController
{
    #[Route('/lieu', name: 'app_lieu')]
    public function creer(): Response
    {
        $lieu = new Lieu();

        $form = $this->createForm(LieuType::class, $lieu);

        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
            'form' => $form,
        ]);
    }
}
