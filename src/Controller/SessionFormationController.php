<?php
// ----------------------------------------------------------controller pour session et formation-----------------------------------

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Formation;
use App\Entity\Programme;
use App\Form\SessionType;
use App\Form\FormationType;
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
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SessionFormationController extends AbstractController
{
    // ---------------------------------------------------------- session --------------------------------------------

    //liste des sessions
    #[Route('/', name: 'app_session')] //chemin 'path', '/' pour que l'application s'ouvre directement avec ça
    public function index(SessionRepository $sessionRepository): Response
    //faire passer directement en argument le repository pour appeler des méthodes plus rapidement
    {
        //SELECT * from session ORDER BY date_debut ASC || "dateDebut" est le nom dans l'entité, pas la bdd
        $sessions = $sessionRepository->findBy([], ["dateDebut" => "DESC"]);
        $nextSessions = $sessionRepository->findAVenir(); //sessions à venir
        $finishedSessions = $sessionRepository->findFini(); //sessions finies
        $currentSession = $sessionRepository->findEnCours(); //sessions en cours

        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
            'nextSessions' => $nextSessions,
            'finishedSessions' => $finishedSessions,
            'currentSession' => $currentSession
        ]);
    }

    //crée une nouvelle session
    // #[IsGranted(new Expression('is_granted("ROLE_USER")'))] //autorisation pour effectuer certaines fonctionnalités
    #[Route('/session/newSession', name: 'new_session')]
    public function newSession(SessionRepository $sessionRepository, EntityManagerInterface $entityManager, Request $request)
    {

        $session = new Session();

        //crée le form
        $form = $this->createForm(SessionType::class, $session);

        //prend en charge
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //récupère les données du formulaire 
            $session = $form->getData();
            
            //verifie la cohérence des dates, fonction dans l'entité qui renvoie un boolean
            if($session->getValidDate()){
                $entityManager->persist($session); //prepare
                $entityManager->flush(); //execute
    
                //notif et redirige vers la liste des sessions
                $this->addFlash('success', 'Session créée');
                return $this->redirectToRoute("app_session");
            } else {
                $this->addFlash('error', 'La date de début est soit déjà passée, soit après la date de fin');
                return $this->redirectToRoute("new_session") ;
            }
        }
                    
        
        //renvoie la vue
        return $this->render('session/new.html.twig', [
            'form' => $form,
            'sessionId' => $session->getId()
        ]);

    }


    
    //modifie la session, accessible aux admins et la personne qui a créée sa session
    #[Route('/session/{id}/edit', name: 'edit_session')] 
    public function editSession(Session $session, SessionRepository $sessionRepository, EntityManagerInterface $entityManager, Request $request, UserInterface $user){

        //$user correspond à la personne connectée
        if($session->getUser() == $user || $this->isGranted('ROLE_ADMIN')){
            
            //crée le form
            $form = $this->createForm(EditSessionType::class, $session);
            //prend en charge
            $form->handleRequest($request);

            
            if($form->isSubmitted() && $form->isValid()){
                //récupère les données du formulaire 
                $session = $form->getData();

                //si la nouvelle entrée du nb de place est supérieur ou égal au nb d'inscription
                if($session->getNbPlace() >= $session->getNbInscription()){
                    $entityManager->persist($session); //prepare
                    $entityManager->flush(); //execute
        
                    //notif et redirige vers le detail de la session
                    $this->addFlash('success', 'Session bien modifiée');
                    return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);      
                } else {
                    //notif et redirige vers le detail de la session
                    $this->addFlash('error', 'Le nombre de places est inférieur au nombre de stagiaires déjà inscrits.');
                    return $this->redirectToRoute('edit_session', ['id'=>$session->getId()]); 
                }
    
            }
    
            //renvoie la vue
            return $this->render('session/edit.html.twig', [
                'form' => $form,
                'sessionId' => $session->getId()
            ]);

        } else {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'effectuer cette action.');
            return $this->redirectToRoute('show_session', ['id'=>$session->getId()]); 
        }
         
    }

    //ajoute un stagiaire à la session
    #[Route('/session/{idSession}/addStagiaireSession/{idStagiaire}', name: 'add_stagiaire_session')] 
    public function addStagiaireSession(SessionRepository $sr, EntityManagerInterface $entityManager, StagiaireRepository $stagiaireR ,Request $request, int $idStagiaire, int $idSession, UserInterface $user) : Response
    {
        if($user){
            
            $stagiaire = $stagiaireR->findOneById($idStagiaire); //objet stagiaire
            $session = $sr->findOneById($idSession); //objet session
        
            //s'il ne restait plus qu'une place, on ajoute le stagiaire et on ferme l'inscription
            if($session->getNbDispo() ==1){
                $session->setOuvert(false); //ferme la session
                
                //cette methode dans la classe session attend en argument le stagiaire
                $add = $session->addInscription($stagiaire);
    
                $entityManager->persist($add); //prepare
                $entityManager->flush(); //execute
    
                //notif redirige vers le detail de la session
                $this->addFlash('success', 'Stagiaire '. $stagiaire .' ajouté à la session, session fermée' );
                return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
                
            } elseif($session->getNbDispo() > 1){
                //cette methode dans la classe session attend en argument le stagiaire
                $add = $session->addInscription($stagiaire);
    
                $entityManager->persist($add); //prepare
                $entityManager->flush(); //execute
                
                //notif redirige vers le detail de la session
                $this->addFlash('success', 'Stagiaire '. $stagiaire .' ajouté à la session' );
                return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
                
            } else {
                $this->addFlash('error', 'Session complète');
                return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
            }

        } else {
            $this->redirectToRoute('app_login');
        }
    
    }

    //supprimer le stagiaire de la session
    #[Route('/session/{idSession}/removeStagiaireSession/{idStagiaire}', name: 'remove_stagiaire_session')] 
    public function removeStagiaireSession(SessionRepository $sr, EntityManagerInterface $entityManager, StagiaireRepository $stagiaireR, int $idStagiaire, int $idSession, UserInterface $user) : Response
    {
        //si la personne est connectée
        if($user){
            
            $stagiaire = $stagiaireR->findOneById($idStagiaire); //objet stagiaire
            $session = $sr->findOneById($idSession); //objet session
            
            //si on enleve un stagiaire alors qu'avant elle était fermée
            if($session->getNbDispo() ==0){
                $session->setOuvert(true); //rouvre la session
    
                //cette methode dans la classe session attend en argument le stagiaire
                $delete = $session->removeInscription($stagiaire);
        
                $entityManager->persist($delete); //prepare
                $entityManager->flush(); //execute
    
    
                //notif redirige vers le detail de la session
                $this->addFlash('success', 'Stagiaire ' . $stagiaire . ' supprimé de la session');
                return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
    
            } elseif($session->getNbDispo() > 0){
                //cette methode dans la classe session attend en argument le stagiaire
                $delete = $session->removeInscription($stagiaire);
        
                $entityManager->persist($delete); //prepare
                $entityManager->flush(); //execute
    
                //notif redirige vers le detail de la session
                $this->addFlash('success', 'Stagiaire ' . $stagiaire . ' supprimé de la session');
                return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
            } else {
    
                return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
            }

        } else {
            $this->redirectToRoute('app_login');
        }
            
    }




    //supprimer un programme d'une session
    #[Route('/session/{id}/removeProgramme', name: 'remove_programme')] 
    public function deleteProgramme(Programme $programme, EntityManagerInterface $entityManager){
        
        $entityManager->remove($programme); //prepare la requete
        $entityManager->flush(); //execute
        
        $sessionId = $programme->getSession()->getId(); //id de la session pour la rédirection
        // notif et redirection
        $this->addFlash('success', 'Programme supprimé');
        return $this->redirectToRoute('show_session', ['id'=>$sessionId]);
    }

    //supprimer la session, avec son programme et ses inscriptions
    #[Route('/session/{id}/deleteSession', name:'delete_session')]
    public function deleteSession(Session $session, EntityManagerInterface $entityManager){

        $entityManager->remove($session);
        $entityManager->flush();

        //notif et redirection
        $this->addFlash('succes', 'Session supprimée');
        return $this->redirectToRoute('app_session');
        
    }

    //verrouille OU deverrouille une session, admin
    #[Route('/session/{id}/lock', name: 'lock_session')]
    public function lockSession(Session $session = null, EntityManagerInterface $entityManager){
        //s'il trouve la session
        if($session){

            if($session->isOuvert()){
                $session->setOuvert(false); //ferme la session

                $entityManager->persist($session);
                $entityManager->flush();

                $this->addFlash('succes', 'Session verrouillée');
                return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
            } else {
                $session->setOuvert(true); //ouvre la session

                $entityManager->persist($session);
                $entityManager->flush();

                $this->addFlash('succes', 'Session ouverte');
                return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);
            }

        } else {
            $this->addFlash('error', 'Session inexistante');
            return $this->redirectToRoute('app_session');
        }

    }

    //detail d'une session
    #[Route('/session/{id}', name: 'show_session')]
    public function showSession(Session $session = null, SessionRepository $sessionRepository): Response
    {
        //si l'id passé dans l'url existe; possible comme je mets session en null par defaut en argument, sinon erreur
        if($session){
            
            $nonInscrits = $sessionRepository->findNonInscrits($session->getId()); //stagiaires pas inscrits pour l'ajout d'un stagiaire à l'inscription
            return $this->render('session/show.html.twig', [
                'session' => $session,
                'nonInscrits' => $nonInscrits
            ]);

        } else {
            $this->addFlash('error', 'Session inexistante');
            return $this->redirectToRoute('app_session');
        }
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

    //crée une nouvelle formation ou modifie une existante
    #[Route('/formation/newFormation', name: 'new_formation')] //ajout
    #[Route('/formation/{id}/edit', name: 'edit_formation')] //pour modif
    public function newEditFormation(Formation $formation=null, EntityManagerInterface $entityManager, Request $request)
    {
        //si la formation n'a pas été trouvée on en crée un nouveau
        if(!$formation){
            $formation = new Formation();
            $text = "créé"; //pour le message de succes
        } else {
            $text= "modifié";
        }

        //crée le form
        $form = $this->createForm(FormationType::class, $formation);

        //prend en charge
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //récupère les données du formulaire 
            $formation = $form->getData();

            $entityManager->persist($formation); //prepare
            $entityManager->flush(); //execute

            //notif et redirige vers la liste des formations
            $this->addFlash('success', 'Formation \''. $formation->getNom() .'\' '. $text);
            return $this->redirectToRoute("app_formation");
        }

        //renvoie la vue
        return $this->render('formation/new.html.twig', [
            'form' => $form,
            //s'il reçoit l'id, il le renvoie et donc on est sur la modification, sinon il renvoie false et on est sur l'ajout
            'edit' => $formation->getId()
        ]);


    }

    //detail d'une formation
    #[Route('/formation/{id}', name: 'show_formation')]
    public function showFormation(Formation $formation = null, FormationRepository $formationRepository): Response
    {
        //si l'id passé dans l'url existe; possible comme je mets formation en null par defaut en argument, sinon erreur
        if($formation){
            
            return $this->render('formation/show.html.twig', [
                'formation' => $formation
            ]);

        } else {
            $this->addFlash('error', 'Formation inexistante');
            return $this->redirectToRoute('app_formation');
        }
    }


}
