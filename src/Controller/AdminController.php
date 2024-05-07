<?php
//-----------------------------------------panel admin
//grace au 'access_control:' dans le fichier security.yaml pannel accessible seulement aux admins

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userRepository): Response
    {

        $users = $userRepository->findBy([], ["pseudo" => "ASC"]);
        return $this->render('admin/index.html.twig', [
            'users' => $users
        ]);
        
    }

    //change le role d'un user
    #[Route('admin/{id}/upgrade', name: 'upgrade_role')]
    public function upgradeUser(User $user, EntityManagerInterface $entityManager){
        
        //cherche le/les role de l'utilisateur et s'il contient ROLE_ADMIN, vu qu'il s'agit d'un tableau et que symfony ajoute role_user à CHAQUE utilisateur lors du getRoles(), il peut en avoir plusieurs
        //ici il n'y a que deux roles: user et admin, donc on ne fait qu'une verification et un 'sinon'

        if (in_array("ROLE_ADMIN", $user->getRoles())){
            $add = $user->setRoles(["ROLE_USER"]); //setter dans la classe User attend un tableau

            $entityManager->persist($add); //prepare
            $entityManager->flush(); //execute
        } else {
            $add = $user->setRoles(["ROLE_ADMIN"]);

            $entityManager->persist($add); //prepare
            $entityManager->flush(); //execute
        }
        
        //redirection
        $this->addFlash('success', 'Role changé');
        return $this->redirectToRoute('app_admin');
    }

    //detail d'un user avec ses roles et les sessions où il est formateur référent, s'il y en a
    #[Route('/admin/{id}', name: 'show_user')]
    public function show(User $user=null): Response
    {
        //si l'id passé dans l'url existe; possible comme je mets user en null par defaut en argument, sinon erreur
        if($user){
            return $this->render('admin/show.html.twig', [
                'user' => $user
            ]);
        } else {
            $this->addFlash('error', 'Cet utilisateur n\'existe pas');
            return $this->redirectToRoute('app_admin');
        }
    }

}
