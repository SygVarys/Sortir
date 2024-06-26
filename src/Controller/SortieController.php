<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Entity\User;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie_index', methods: ['GET', 'POST'])]
    public function index(Security $security, Request $request, SortieRepository $sortieRepository, VilleRepository $villeRepository): Response
    {
        // passage de la date du jour moins un pour les sorties archivées
        $date = new dateTime();
        $date = date_modify($date, '-1 month');

        $user = $this->getUser();
        $form = $this->createFormBuilder()
            ->add('site', EntityType::class, [
                'placeholder' => '--Veuillez choisir une ville--',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'required' => false,

            ])
            ->add('contains', SearchType::class, [
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, ['widget' => 'single_text', 'required' => false,])
            ->add('dateFin', DateType::class, ['widget' => 'single_text', 'required' => false,])
            ->add('filtre', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices' => ["Sorties dont je suis l'organisateur/trice" => 1,
                    'Sorties auxquelles je suis inscrit/e' => 2,
                    'Sorties auxquelles je ne suis pas inscrit/e' => 3,
                    'Sorties passées' => 4],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filtre = $form->getData();
            $user = $this->getUser();
            $sorties = $sortieRepository->findByFiltre($filtre, $user);


            return $this->render('sortie/index.html.twig', [
                'sorties' => $sorties,
                'form' => $form,
                'date' => $date,
            ]);
        }

        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
            'form' => $form,
            'date' => $date,
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {



        $errors = "";
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $sortie->setSite($user->getSite());
            $sortie->setOrganisateur($user);
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Une nouvelle sortie est créée');
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        } else {
            $errors = $form->getErrors(true);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
            'errors' => $errors,

        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie, VilleRepository $repository): Response
    {
        $date = new dateTime();
        $date = date_modify($date, '-1 month');

        if ($sortie->getDateHeureDebut() < $date){
            header('HTTP/1.0 404 Not Found');
            echo "<h1>404 File not found</h1>";
            exit();
        }

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'ville' => $repository->find($sortie->getLieu()->getVille())->getNom(),
            'errors' => "",
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $errors="";
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }else{
            $errors = $form->getErrors(true);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
            'errors' => $errors,

        ]);
    }

    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/addParticipant', name: 'app_sortie_addParticipant')]
    public function addParticipant(VilleRepository $villeRepository, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {

        $errors="";
        $nbParticipants = $sortie->getParticipants()->count();
        $nbMaxParticipants = $sortie->getNbInscriptionsMax();
        $today = new \dateTime();

        if ($sortie->getEtat() == 'Ouvert' && $nbParticipants < $nbMaxParticipants && $today <= $sortie->getDateLimiteInscription()) {
            $sortie->addParticipant($this->getUser());
            var_dump("tout va bien");

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Votre participation a bien été enregistrée');
        }else{
            $errors = "Désolé, inscription impossible";

            var_dump("tout va mal");

        }
        return $this->redirectToRoute('app_sortie_show', [
            'id' => $sortie->getId(),
            'errors'=>$errors,
            'sortie'=>$sortie,
            'ville' => $villeRepository->find($sortie->getLieu()->getVille())->getNom(),]);
    }

    #[Route('/{id}/deleteParticipant', name: 'app_sortie_deleteParticipant')]
    public function deleteParticipant(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $sortie->removeParticipant($this->getUser());
        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/annulerSortie/{id}', name: 'app_sortie_annuler')]
    public function annulerSortie(Sortie $sortie, EntityManagerInterface $entityManager, Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('motif', TextareaType::class)
            ->getForm();
        $form->handleRequest($request);
        //$motif = $request->request->get('motif');
        if ($form->isSubmitted() && $form->isValid()) {
//            $motif=$form->get('motif')->getData();
            $motif=$form->getData()['motif'];
            $sortie->setEtat('Annulé');
            $sortie->setMotif($motif);

            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', "l'événement a bien été annulé");
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);

        }


        return $this->render('sortie/annule.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);
    }
}
