<?php
// ----------------------------------------------------------controller pour categorie et ses modules-----------------------------------
namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Module;
use App\Form\CategorieType;
use App\Form\ModuleType;
use App\Repository\CategorieRepository;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CategorieModuleController extends AbstractController
{
    // ---------------------------------------------------------categorie----------------------------------------------------------

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

    // //-----------------------------------------------------module--------------------------------------------------------
    //liste des modules
    #[Route('/module', name: 'app_module')]
    public function listModule(ModuleRepository $moduleRepository): Response
    {
        $modules = $moduleRepository->findBy([], ["nom" => "ASC"]);
        return $this->render('module/index.html.twig', [
            'modules' => $modules,
        ]);
    }

    //crée un nouveau module ou modifie un existant
    #[Route('/module/new', name: 'new_module')] //pour ajout
    #[Route('/module/{id}/edit', name: 'edit_module')] //pour modif
    public function new_edit(Module $module =null, ModuleRepository $moduleRepository, Request $request, EntityManagerInterface $entityManager)
    {
        //si le module n'a pas été trouvé, on en crée un nouveau, sinon ça veut dire qu'on est sur un formulaire de modification
        if(!$module){
            $module = new Module();
        }


        //crée le form
        $form = $this->createForm(ModuleType::class, $module);

        //prend en charge
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //récupère les données du formulaire 
            $module = $form->getData();

            $entityManager->persist($module); //prepare
            $entityManager->flush(); //execute

            //redirige vers la liste des modules
            return $this->redirectToRoute("app_module");
        }

        //renvoie la vue
        return $this->render('module/new.html.twig', [
            'formAddModule' => $form,
            //s'il reçoit l'id, il le renvoie et donc on est sur la modification, sinon il renvoie false et on est sur l'ajout
            'edit' => $module->getId()
        ]);

    }
}
