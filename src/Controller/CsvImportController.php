<?php

namespace App\Controller;

use App\Form\UserImportType;
use App\Helper\UserImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CsvImportController extends AbstractController
{
    #[Route('/csv/import', name: 'app_csv_import')]

    public function importUsers(Request $request, UserImporter $userImporter): Response
    {
        $form = $this->createForm(UserImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('file')->getData();
            $userImporter->importUsersFromCsv($csvFile);

            $this->addFlash('success', 'Users imported successfully.');

            return $this->redirectToRoute('app_admin_liste');
        }

       ;
        return $this->render('csv_import/index.html.twig', [
            'controller_name' => 'CsvImportController',
            'form' => $form,
        ]);
    }


}
