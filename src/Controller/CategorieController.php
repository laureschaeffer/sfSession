<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategorieController extends AbstractController
{
    //liste des categories
    #[Route('/categorie', name: 'app_categorie')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findBy([], ["nom" => "ASC"]);
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories
        ]);
    }

    //crée une nouvelle categorie
    #[Route('/categorie/new', name: 'new_categorie')]
    public function new_cat(CategorieRepository $categorieRepository, Request $request, EntityManagerInterface $entityManager)
    {
        //crée une categorie
        $categorie = new Categorie();

        //crée le form
        $form = $this->createForm(CategorieType::class, $categorie);

        //prend en charge
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //récupère les données du formulaire 
            $categorie = $form->getData();

            $entityManager->persist($categorie); //prepare
            $entityManager->flush(); //execute

            //redirige vers la liste des categories
            return $this->redirectToRoute("app_categorie");
        }

        //renvoie la vue
        return $this->render('categorie/new.html.twig', [
            'formAddCategorie' => $form
        ]);

    }
}
