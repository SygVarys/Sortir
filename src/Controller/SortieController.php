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
        $user = $security->getUser();
        $form = $this->createFormBuilder()
            ->add('site', EntityType::class, [
                'placeholder' => '--Veuillez choisir une ville--',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'required' => true,

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
        $tableau = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $filtre = $form->getData();
            $sorties = $sortieRepository->findByFiltre($filtre, $user);

//            if (in_array(2, $filtre['filtre']) xor in_array(3, $filtre['filtre'])) {
//                $sortie = new Sortie();
//                var_dump(count($sorties));
//
//                for ($i = 0; $i < count($sorties); $i++) {
//                    //var_dump($sorties[$i]);
//                    $sortie = $sorties[$i];
//                    $participants = $sortie->getParticipants();
//                    for ($j = 0; $j < count($participants); $i++) {
//                        if ($user == $participants[$j]) {
//                            $tableau[] = $sortie;
//                        }
//                    }
//                }
//            }
//
//            $sorties = $tableau;
//            //var_dump($tableau);




        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
            'form' => $form,
        ]);
    }


        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $lieu = new Lieu();
        $form2 = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form2->isSubmitted() && $form2->isValid()) {
            $key = "key=67fW3PVAqC1HMiyOvZ9d9CgiohZBqs67N6hiRelVusAVbhpXr1hxwCBcl65uL2ti";

        }


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
            'form2' => $form2,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
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
    public function addParticipant(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $sortie->addParticipant($this->getUser());
        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/deleteParticipant', name:'app_sortie_deleteParticipant')]
    public function deleteParticipant(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $sortie->removeParticipant($this->getUser());
        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }


}
