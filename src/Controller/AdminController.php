<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class AdminController extends AbstractController
{
//    #[Route('/', name: 'app_admin')]
//    public function index(): Response
//    {
//        return $this->render('admin/index.html.twig', [
//            'controller_name' => 'AdminController',
//        ]);
//    }


    #[Route('/liste', name:'app_admin_liste')]
    #[IsGranted('ROLE_ADMIN')]
    public function listerUser(UserRepository $userRepository):Response
    {
        return $this->render('admin/liste.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function modifierUtilisateur(User $user, Request $request, /*UserPasswordHasherInterface $userPasswordHasher,*/ EntityManagerInterface $entityManager):Response
    {
        $form = $this->createForm(UserType::class, $user)
        ->add('roles', ChoiceType::class,[
//                'placeholer'=> '--Choisissez le rôle du nouvel utilisateur--',
                'choices' => [
                    'Role_Admin'=>'ROLE_ADMIN',
                    'Role_User'=>'ROLE_USER'
                ],
                'multiple' => true,
                'expanded' => true,
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])


        ->add('isActif');
//        ->add('isAdmin');



        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//            $user->setPassword(
//                $userPasswordHasher->hashPassword(
//                    $user,
//                    $form->get('password')->getData()
//                )
//            );
//            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_liste'/*, [], Response::HTTP_SEE_OTHER*/);
        }

        return $this->render('admin/new.html.twig', [
            'user' => $user,
            'formUser' => $form,
        ]);
    }



    #[Route('/detail/{id}', name: 'app_admin_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(User $user): Response
    {
        return $this->render('admin/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_delete', methods: ['GET','POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
//        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
//        }

        return $this->redirectToRoute('app_admin_liste'/*, [], Response::HTTP_SEE_OTHER*/);
    }

    #[Route('/AdminCreate', name: 'app_admin_create')]
    public function adminCreate(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user)


            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER',
                ]
            ])

        ->add('reset', ResetType::class, [
        'label' => 'Réinitialiser',
        'attr' => ['class' => 'btn btn-secondary']
    ]);

        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )

            );

            $user->setIsActif('1');
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

//            return $security->login($user, AppAuthenticator::class, 'main');
        }

        return $this->render('admin/AdminCreate.html.twig', [
            'registrationForm' => $form,
        ]);
    }

}
