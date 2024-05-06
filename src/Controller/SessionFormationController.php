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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;


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
        return $this->render('session/index.html.twig', [
            'sessions' => $sessions
        ]);
    }

    //crée une nouvelle session
    // #[IsGranted(new Expression('is_granted("ROLE_USER")'))] //autorisation pour effectuer certaines fonctionnalités
    #[Route('/session/newSession', name: 'new_session')]
    public function newSession(SessionRepository $sessionRepository, EntityManagerInterface $entityManager, Request $request)
    {
        if ($this->isGranted('ROLE_USER')){

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
                    return $this->redirectToRoute("app_session") ;
                }
            }
            
        } else {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'effectuer cette action.');
            $this->redirectToRoute('app_session');
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

        if ($this->isGranted('ROLE_USER')){
            
            //crée le form
            $form = $this->createForm(EditSessionType::class, $session);
        
            //prend en charge
            $form->handleRequest($request);
        
            if($form->isSubmitted() && $form->isValid()){
                //récupère les données du formulaire 
                $session = $form->getData();
    
                $entityManager->persist($session); //prepare
                $entityManager->flush(); //execute
    
                //notif et redirige vers le detail de la session
                $this->addFlash('success', 'Session bien modifiée');
                return $this->redirectToRoute('show_session', ['id'=>$session->getId()]);      
            }

            //renvoie la vue
            return $this->render('session/edit.html.twig', [
                'form' => $form,
                'sessionId' => $session->getId()
            ]);

        } else {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'effectuer cette action.');
            $this->redirectToRoute('app_session');
        }
            
    }

    //ajoute un stagiaire à la session
    #[Route('/session/{idSession}/addStagiaireSession/{idStagiaire}', name: 'add_stagiaire_session')] 
    public function addStagiaireSession(SessionRepository $sr, EntityManagerInterface $entityManager, StagiaireRepository $stagiaireR ,Request $request, int $idStagiaire, int $idSession) : Response
    {
        //si l'user a l'autorisation
        if ($this->isGranted('ROLE_USER')){
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
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'effectuer cette action.');
            $this->redirectToRoute('app_session');
        }
        

        
    }

    //supprimer le stagiaire de la session
    #[Route('/session/{idSession}/removeStagiaireSession/{idStagiaire}', name: 'remove_stagiaire_session')] 
    public function removeStagiaireSession(SessionRepository $sr, EntityManagerInterface $entityManager, StagiaireRepository $stagiaireR ,Request $request, int $idStagiaire, int $idSession) : Response
    {
        if ($this->isGranted('ROLE_USER')){

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
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'effectuer cette action.');
            $this->redirectToRoute('app_session');
        }
            
    }




    //supprimer un programme d'une session
    #[Route('/session/{id}/deleteProgramme', name: 'delete_programme')] 
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

        if($this->isGranted('ROLE_ADMIN')){
            $entityManager->remove($session);
            $entityManager->flush();
    
            //notif et redirection
            $this->addFlash('succes', 'Session supprimée');
            return $this->redirectToRoute('app_session');
        
        } else {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'effectuer cette action.');
            $this->redirectToRoute('app_session');
        }

    }


    //detail d'une session
    #[Route('/session/{id}', name: 'show_session')]
    public function show(Session $session = null, SessionRepository $sessionRepository): Response
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

    //crée une nouvelle formation
    #[Route('/formation/newFormation', name: 'new_formation')]
    public function newFormation(FormationRepository $formationRepository, EntityManagerInterface $entityManager, Request $request)
    {
        if($this->isGranted('ROLE_USER')){

            $formation = new Formation();

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
                $this->addFlash('success', 'Formation créée');
                return $this->redirectToRoute("app_formation");
            }
    
            //renvoie la vue
            return $this->render('formation/new.html.twig', [
                'form' => $form
            ]);

        } else {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'effectuer cette action.');
            $this->redirectToRoute('app_session');
        }

    }

}
