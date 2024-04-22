<?php
// ----------------------------------------------------------controller pour session et formation-----------------------------------

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Programme;
use App\Form\SessionType;
use App\Form\ProgrammeType;
use App\Form\EditSessionType;
use App\Repository\ModuleRepository;
use App\Repository\SessionRepository;
use App\Repository\FormationRepository;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionFormationController extends AbstractController
{
    // ---------------------------------------------------------- session --------------------------------------------

    //liste des sessions
    #[Route('/session', name: 'app_session')]
    public function index(SessionRepository $sessionRepository): Response
    //faire passer directement en argument le repository pour appeler des méthodes plus rapidement
    {
        //SELECT * from session ORDER BY date_debut ASC || "dateDebut" est le nom dans l'entité, pas la bdd
        $sessions = $sessionRepository->findBy([], ["dateDebut" => "DESC"]);
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


    //ajoute un stagiaire à la session
    #[Route('/session/{idSession}/addStagiaireSession/{idStagiaire}', name: 'add_stagiaire_session')] 
    public function addStagiaireSession(SessionRepository $sr, EntityManagerInterface $entityManager, StagiaireRepository $stagiaireR ,Request $request, int $idStagiaire, int $idSession) : Response
    {
        $stagiaire = $stagiaireR->findOneById($idStagiaire); //objet stagiaire
        $session = $sr->findOneById($idSession); //objet session
        //cette methode dans la classe session attend en argument le stagiaire
        $add = $session->addInscription($stagiaire);

        $entityManager->persist($add); //prepare
        $entityManager->flush(); //execute

        //redirige vers le detail de la session
        return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
        
    }

    //supprimer le stagiaire de la session
    #[Route('/session/{idSession}/removeStagiaireSession/{idStagiaire}', name: 'remove_stagiaire_session')] 
    public function removeStagiaireSession(SessionRepository $sr, EntityManagerInterface $entityManager, StagiaireRepository $stagiaireR ,Request $request, int $idStagiaire, int $idSession) : Response
    {
        $stagiaire = $stagiaireR->findOneById($idStagiaire); //objet stagiaire
        $session = $sr->findOneById($idSession); //objet session
        //cette methode dans la classe session attend en argument le stagiaire
        $delete = $session->removeInscription($stagiaire);

        $entityManager->persist($delete); //prepare
        $entityManager->flush(); //execute

        //redirige vers le detail de la session
        return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
        
    }



    //modifie la session
    #[Route('/session/{id}/edit', name: 'edit_session')] 
    public function edit(Session $session, SessionRepository $sessionRepository, EntityManagerInterface $entityManager, Request $request){
        //crée le form
        $form = $this->createForm(EditSessionType::class, $session);

        //prend en charge
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //récupère les données du formulaire 
            $session = $form->getData();

            $entityManager->persist($session); //prepare
            $entityManager->flush(); //execute

            //redirige vers le detail de la session
            return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
        }

        //renvoie la vue
        return $this->render('session/edit.html.twig', [
            'form' => $form,
            'sessionId' => $session->getId()
        ]);



    }

    //supprimer un programme d'une session
    #[Route('/session/{id}/delete', name: 'delete_programme')] 
    public function delete(Programme $programme, EntityManagerInterface $entityManager){
        
        $entityManager->remove($programme); //prepare la requete
        $entityManager->flush(); //execute
        
        $sessionId = $programme->getSession()->getId(); //id de la session pour la rédirection
        // redirection
        return $this->redirectToRoute('show_session', ['id'=>$sessionId]);
    }

    //detail d'une session
    #[Route('/session/{id}', name: 'show_session')]
    public function show(Session $session, SessionRepository $sessionRepository): Response
    {
        $nonInscrits = $sessionRepository->findNonInscrits($session->getId()); //stagiaires pas inscrits pour l'ajout d'un stagiaire à l'inscription


        return $this->render('session/show.html.twig', [
            'session' => $session,
            'nonInscrits' => $nonInscrits
        ]);
    }

    // --------------------------------------------------------formation--------------------------------------------------

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
