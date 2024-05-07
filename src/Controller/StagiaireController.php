<?php

namespace App\Controller;

use App\Entity\Stagiaire;
use App\Form\StagiaireType;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StagiaireController extends AbstractController
{
    //liste des stagiaires
    #[Route('/stagiaire', name: 'app_stagiaire')]
    public function index(StagiaireRepository $stagiaireRepository): Response
    {
        $stagiaires = $stagiaireRepository->findBy([], ["nom" => "ASC"]);
        return $this->render('stagiaire/index.html.twig', [
            'stagiaires' => $stagiaires
        ]);
    }

    //crée un nouveau stagiaire ou modifie un existant
    #[Route('/stagiaire/new', name: 'new_stagiaire')] //pour ajout
    #[Route('/stagiaire/{id}/edit', name: 'edit_stagiaire')] //pour modif
    public function new_edit(Stagiaire $stagiaire =null, StagiaireRepository $stagiaireRepository, Request $request, EntityManagerInterface $entityManager)
    {
        //si le stagiaire n'a pas été trouvé, on en crée un nouveau, sinon ça veut dire qu'on est sur un formulaire de modification
        if(!$stagiaire){
            $stagiaire = new Stagiaire();
            $text = " créé"; //pour le message de notif
        } else {
            $text = " modifié";
        }


        //crée le form
        $form = $this->createForm(StagiaireType::class, $stagiaire);

        //prend en charge
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //récupère les données du formulaire 
            $stagiaire = $form->getData();

            $entityManager->persist($stagiaire); //prepare
            $entityManager->flush(); //execute

            //notif et redirige vers la liste des stagiaires
            $this->addFlash('success', 'Stagiaire ' . $stagiaire . $text);
            return $this->redirectToRoute("app_stagiaire");
        }

        //renvoie la vue
        return $this->render('stagiaire/new.html.twig', [
            'formAddStagiaire' => $form,
            //s'il reçoit l'id, il le renvoie et donc on est sur la modification, sinon il renvoie false et on est sur l'ajout
            'edit' => $stagiaire->getId()
        ]);

    }

    //supprimer le stagiaire
    //supprime également son inscription dans la table session_stagiaire, car "cascade lors d'un delete"
    #[Route('stagiaire/{id}/delete', name: 'delete_stagiaire')]
    public function delete(Stagiaire $stagiaire, EntityManagerInterface $entityManager){
        
        $entityManager->remove($stagiaire); //prepare
        $entityManager->flush(); //execute

        //redirection
        $this->addFlash('success', 'Stagiaire supprimé');
        return $this->redirectToRoute('app_stagiaire');
            
    }


    //detail d'un stagiaire
    #[Route('/stagiaire/{id}', name: 'show_stagiaire')]
    public function show(Stagiaire $stagiaire = null): Response
    {
        //si l'id passé dans l'url existe; possible comme je mets stagiaire en null par defaut en argument, sinon erreur
        if($stagiaire){
            return $this->render('stagiaire/show.html.twig', [
                'stagiaire' => $stagiaire
            ]);

        } else {
            return $this->redirectToRoute('app_stagiaire'); 
        }
    }
}
