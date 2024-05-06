<?php
//-----------------------------------------panel admin, accessible aux admins

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
        //si l'user a l'autorisation
        if ($this->isGranted('ROLE_ADMIN')){
            $users = $userRepository->findBy([], ["pseudo" => "ASC"]);
            return $this->render('admin/index.html.twig', [
                'users' => $users
            ]);

        } else {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'accéder à cette partie.');
            return $this->redirectToRoute('app_session');
        }
    }

    //change le role d'un user
    #[Route('admin/{id}/upgrade', name: 'upgrade_role')]
    public function upgradeUser(User $user, EntityManagerInterface $entityManager){

        //vérifie encore si l'user a l'autorisation
        if($this->isGranted('ROLE_ADMIN')){

            //cherche le/les role de l'utilisateur et s'il contient ROLE_ADMIN, vu que comme il s'agit d'un tableau et que symfony ajoute role_user à CHAQUE utilisateur lors du getRoles(), il peut en avoir plusieurs
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
            

        } else {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'effectuer ce changement.');
            return $this->redirectToRoute('app_session');
        }

        //redirection
        $this->addFlash('success', 'Role changé');
        return $this->redirectToRoute('app_admin');
    }
}
