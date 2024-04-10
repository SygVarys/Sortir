<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Repository\LieuRepository;


use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
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
        $lieu = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {

            $ville = $form->get('Ville')->getData();
            $lieu = $form->getData();
//            var_dump($lieu);
//            var_dump($ville);
//            $get_data = callAPI('GET', 'https://api.example.com/get_url/'.$user['User']['customer_id'], false);
//            $response = json_decode($get_data, true);
//            $errors = $response['response']['errors'];
//            $data = $response['response']['data'][0];

            $url = 'https://api-adresse.data.gouv.fr/search/?q=';
            $adresse = implode("+", explode(" ",$lieu->getRue()));
            $adresse .= '+' . $ville;
            //var_dump($url.$adresse . '&limit=1');
            //$ch = curl_init($url.$adresse .'&limit=1');
            //var_dump($ch);
            var_dump($url.$adresse);
            $client = new Client(['verify' => false]);
            $response = $client->request('GET', $url.$adresse);

            if ($response->getStatusCode() === 200) {
                $results = json_decode($response->getBody(), true);
                var_dump($results);}

            $ville = $villeRepository->find(61);
            $lieu->setVille($ville);
            $entityManager->persist($lieu);
            $entityManager->flush();

            //return $this->redirectToRoute('app_sortie_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
            'form' => $form,
        ]);
    }
}
