<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
        // return $this->redirectToRoute('app_session');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        $this->redirectToRoute("app_login"); 
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, UserAuthenticatorInterface $authenticator, AuthenticatorInterface $auth): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user); //prepare
            $entityManager->flush(); //execute

            // do anything else you need here, like send an email

            // return $security->login($user, AppAuthenticator::class, 'main');
            //permet de se connecter automatiquement après le register
            return $authenticator->authenticateUser(
                $user,
                $auth,
                $request); 
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    //profil utilisateur
    #[Route('/profil', name: 'app_profil')]
    public function profil() : Response
    {
        return $this->render('profil.html.twig');
    }

    //supprimer son compte
    //grâce à UserInterface l'appli récupère directement le profil de la personne connectée, donc pas besoin de mettre d'id dans l'url, ce qui permet seulement de supprimer son propre compte
    #[Route('/profil/delete', name: 'delete_profil')]
    public function deleteAccount(UserInterface $user, EntityManagerInterface $entityManager){
        
        $entityManager->remove($user); //prepare
        $entityManager->flush(); //execute

        //redirection
        $this->addFlash('success', 'Profil supprimé');
        return $this->redirectToRoute('app_register');
    }
}
