<?php
// ----------------------------------------------------------controller pour session et formation-----------------------------------

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Programme;
use App\Form\SessionType;
use App\Form\ProgrammeType;
use App\Repository\ModuleRepository;
use App\Repository\SessionRepository;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionFormationController extends AbstractController
{
    // ---------------------------------------------------------- categorie --------------------------------------------

    //liste des sessions
    #[Route('/session', name: 'app_session')]
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
    #[Route('/session/new', name: 'new_session')]
    public function new(SessionRepository $sessionRepository, EntityManagerInterface $entityManager, Request $request)
    {
        $session = new Session();


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
            'sessionId' => $session->getId()
        ]);

    }

    //modifie la session
    #[Route('/session/{id}/edit', name: 'edit_session')] 
    public function edit(Session $session, SessionRepository $sessionRepository, EntityManagerInterface $entityManager, Request $request){
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
        return $this->render('session/edit.html.twig', [
            'form' => $form,
            'sessionId' => $session->getId()
        ]);



    }


    //detail d'une session
    #[Route('/session/{id}', name: 'show_session')]
    public function show(Session $session): Response
    // public function show(Session $session, SessionRepository $sessionRepository, ModuleRepository $moduleRepository, ProgrammeRepository $programmeRepository): Response
    {
        return $this->render('session/show.html.twig', [
            'session' => $session
        ]);
    }

    // liste des formations 
    #[Route('/formation', name: 'app_formation')]
    public function listFormation(FormationRepository $formationRepository): Response
    {
        //SELECT * from formation ORDER BY nom ASC 
        $formations = $formationRepository->findBy([], ["nom" => "ASC"]);
        return $this->render('formation/index.html.twig', [
            'formations' => $formations,
        ]);
    }

}
