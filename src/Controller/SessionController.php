<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Programme;
use App\Form\SessionType;
use App\Form\ProgrammeType;
use App\Repository\ModuleRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    //liste des sessions
    public function index(SessionRepository $sessionRepository): Response
    //faire passer directement en argument le repository pour appeler des méthodes plus rapidement
    {
        //SELECT * from session ORDER BY date_debut ASC || "dateDebut" est le nom dans l'entité, pas la bdd
        $sessions = $sessionRepository->findBy([], ["dateDebut" => "ASC"]);
        return $this->render('session/index.html.twig', [
            'sessions' => $sessions
        ]);
    }

    //crée une nouvelle session ou modifie une existante
    #[Route('/session/new', name: 'new_session')] //pour ajout
    #[Route('/session/{id}/edit', name: 'edit_session')] //pour modif
    public function new_edit(Session $session =null, SessionRepository $sessionRepository, Request $request, EntityManagerInterface $entityManager)
    {
        //si le session n'a pas été trouvé, on en crée un nouveau, sinon ça veut dire qu'on est sur un formulaire de modification
        if(!$session){
            $session = new Session();
        }


        //crée le form
        $form = $this->createForm(SessionType::class, $session);

        //prend en charge
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //récupère les données du formulaire 
            $session = $form->getData();

            $entityManager->persist($session); //prepare
            $entityManager->flush(); //execute

            //redirige vers la liste des sessions
            return $this->redirectToRoute("app_session");
        }

        //renvoie la vue
        return $this->render('session/new.html.twig', [
            'form' => $form,
            //s'il reçoit l'id, il le renvoie et donc on est sur la modification, sinon il renvoie false et on est sur l'ajout
            'sessionId' => $session->getId()
        ]);

    }

    // crée un programme ou modifie un existant
    // #[Route('/programme/new', name: 'new_programme')] //pour ajout
    // #[Route('/programme/{id}/edit', name: 'edit_programme')] //pour modif
    // public function new_editProgramme(Programme $programme = null, Request $request, EntityManagerInterface $entityManager): Response 
    // {
    //     //si le programme n'a pas été trouvé, on en crée un nouveau, sinon ça veut dire qu'on est sur un formulaire de modification
    //     if (!$programme) {
    //         $programme = new Programme();
    //     }
            
            
    //     // crée le formulaire
    //     $form = $this->createForm(ProgrammeType::class, $programme);
                    
    //     //prend en charge
    //     $form->handleRequest($request);
                    
            
    //     if ($form->isSubmitted() && $form->isValid()) {
                        
    //         // récupère les données du formulaire
    //         $programme = $form->getData();
            
    //         $entityManager->persist($programme); //prepare
    //         $entityManager->flush(); //execute
            
    //         // redirige vers la session
    //         return $this->redirectToRoute('');
    //         }
                    
    //         //renvoie la vue
    //     return $this->render('programme/new.html.twig', [
    //         'formAddProgramme' => $form,
    //         //s'il reçoit l'id, il le renvoie et donc on est sur la modification, sinon il renvoie false et on est sur l'ajout
    //         'edit' => $programme->getId()
    //         ]);
    // }

    //detail d'une session
    #[Route('/session/{id}', name: 'show_session')]
    public function show(Session $session): Response
    // public function show(Session $session, SessionRepository $sessionRepository, ModuleRepository $moduleRepository, ProgrammeRepository $programmeRepository): Response
    {
        return $this->render('session/show.html.twig', [
            'session' => $session
        ]);
    }

}
