<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/profil', name: 'app_profil')]
    public function profil(Security $security): Response
    {
        $user= $security->getUser();
        return $this->render('user/profil.html.twig',[
            'user' => $user
        ]);
    }

    #[Route('/profil/{id}', name: 'user_update',requirements: ['id' =>'\d+'])]
    #[IsGranted('ROLE_USER')]
    public function update(UserRepository $userRepository, User $user, Request $request, EntityManagerInterface $em,SluggerInterface $slugger): Response
    {

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->has('delete_image') && $form->get('delete_image')->getData()) {
                $user->deleteImage();
                $user->setPoster(null);
            }

            if ($form->get('poster_file')->getData() instanceof UploadedFile) {
                $posterFile = $form->get('poster_file')->getData();
                $fileTitle = $slugger->slug($user->getTitle()).'-'.uniqid() . '.'.$posterFile->guessExtension();
                $posterFile->move('/img/avatar', $fileTitle);



                $user->deleteImage();
                $user->setPoster($fileTitle);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'La modification a été faite ');

            return $this->redirectToRoute('user_update', ['id' => $user->getId()]);
        }






        return $this->render('user/profil.html.twig', [
            'userForm' => $form,
            'user' => $user,
        ]);
    }
}

