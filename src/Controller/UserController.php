<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\File;

class UserController extends AbstractController
{

//    affiche le profil public d'un utilisateur ( accessible à tous les autres utilisateurs)
    #[Route('/user/{id}', name: 'app_user')]
    public function infosUtilisateur(int $id, UserRepository $userRepository, User $user): Response
    {
        $user = $userRepository->find($id);


        return $this->render('user/index.html.twig',[
            'user'=>$user,
        ]);
    }

//    permet de modifier son profil utilisateur et ajouter une photo
    #[Route('/profil/{id}', name: 'user_update',requirements: ['id' =>'\d+'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, UserRepository $userRepository, User $user, Request $request, EntityManagerInterface $em,SluggerInterface $slugger): Response
    {

        $user = $userRepository->find($id);

        $form = $this->createForm(UserType::class, $user)

        ->add('poster_file', FileType::class, [
        'mapped' => false,
        'required' => false,
        'constraints' => [
            new File([
                'maxSize' => '2500k',
                'maxSizeMessage' => 'Ton image est trop lourde',
                'mimeTypes' => [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                ],
                'mimeTypesMessage' => "Ce Format n'est pas pris en charge"
            ])
        ]
    ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->has('delete_image') && $form->get('delete_image')->getData()) {
                $user->deleteImage();
                $user->setPoster(null);
            }

            if ($form->get('poster_file')->getData() instanceof UploadedFile) {
                $posterFile = $form->get('poster_file')->getData();
                $fileTitle = $slugger->slug($user->getNom()).'-'.uniqid() . '.'.$posterFile->guessExtension();
                $posterFile->move('uploads/img/avatar', $fileTitle);



                $user->deleteImage();
                $user->setPoster($fileTitle);
            }

            $user->setIsActif('1');

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

