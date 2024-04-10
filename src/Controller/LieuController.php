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

            $villeNom = $form->get('Ville')->getData();
            $lieu = $form->getData();
//            var_dump($lieu);
//            var_dump($ville);
//            $get_data = callAPI('GET', 'https://api.example.com/get_url/'.$user['User']['customer_id'], false);
//            $response = json_decode($get_data, true);
//            $errors = $response['response']['errors'];
//            $data = $response['response']['data'][0];

            $url = 'https://api-adresse.data.gouv.fr/search/?q=';
            $adresse = implode("+", explode(" ",$lieu->getRue()));
            $adresse .= '+' . $villeNom;
            var_dump($url.$adresse);
            $client = new Client(['verify' => false]);
            $response = $client->request('GET', $url.$adresse);

            if ($response->getStatusCode() === 200) {
                $results = json_decode($response->getBody(), true);
                var_dump($results['features'][0]);
                $ville->setNom($results['features'][0]['properties']['city']);
                $ville->setCodePostal($results['features'][0]['properties']['postcode']);
                var_dump($ville);
                $entityManager->persist($ville);
                $entityManager->flush();
                $lieu->setVille($ville);
                $lieu->setLatitude($results['features'][0]['geometry']['coordinates'][1]);
                $lieu->setLongitude($results['features'][0]['geometry']['coordinates'][0]);
                $entityManager->persist($lieu);
                $entityManager->flush();}



            return $this->redirectToRoute('app_sortie_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
            'form' => $form,
        ]);
    }
}
