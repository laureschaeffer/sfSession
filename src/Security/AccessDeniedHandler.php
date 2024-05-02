<?php
// --------------------fichier qui redirige et renvoie un message d'erreur lorsque l'user a un accès refusé en fonction de son role 


// namespace App\Security;

// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Security\Core\Exception\AccessDeniedException;
// use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

// class AccessDeniedHandler implements AccessDeniedHandlerInterface
// {
//     public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
//     {
//         $this->addFlash('error', 'Vous n\'êtes pas autorisés');
//         return $this->redirectToRoute("app_session") ;

//         return new Response($content, 403);
//     }
// }