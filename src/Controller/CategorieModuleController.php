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
        // $module = new Module();

        //crée le form
        $formCat = $this->createForm(CategorieType::class, $categorie);
        // $formModule = $this->createForm(ModuleType::class, $module);

        //prend en charge
        $formCat->handleRequest($request);
        // $formModule->handleRequest($request);

        if($formCat->isSubmitted() && $formCat->isValid()){
            //récupère les données du formulaire 
            $categorie = $formCat->getData();

            $entityManager->persist($categorie); //prepare
            $entityManager->flush(); //execute

            // if($module && $formModule->isSubmitted() && $formModule->isValid()){
            //     $module = $formModule->getData();
            //     var_dump("test"); die;

            //     $entityManager->persist($module);
            //     $entityManager->flush();
            // }


            //redirige vers la liste des categories
            $this->addFlash('success', 'Catégorie \''. $categorie->getNom() .'\' créée');
            return $this->redirectToRoute("app_categorie");
        }

        //renvoie la vue
        return $this->render('categorie/new.html.twig', [
            'formAddCategorie' => $formCat
            // 'formAddModule' => $formModule
        ]);

    }

    //supprime une categorie, et ses modules avec
    #[Route('categorie/{id}/delete', name: 'delete_categorie')]
    public function deleteCat(Categorie $categorie, EntityManagerInterface $entityManager){

        $entityManager->remove($categorie); //prepare
        $entityManager->flush(); //execute

        //redirection
        $this->addFlash('success', 'Catégorie supprimée');
        return $this->redirectToRoute('app_categorie');
    }

    // //-----------------------------------------------------module--------------------------------------------------------
    //liste des modules
    #[Route('/module', name: 'app_module')]
    public function listModule(ModuleRepository $moduleRepository): Response
    {
        $modules = $moduleRepository->findBy([], ["nom" => "ASC"]);
        // $modules = $moduleRepository->findBy([], ["categorie" => "ASC"]);
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
            $text = "créé"; //pour le message de succes
        } else {
            $text = "modifié";
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
            $this->addFlash('success', 'Module \''. $module->getNom() .'\' '. $text);
            return $this->redirectToRoute("app_module");
        }

        //renvoie la vue
        return $this->render('module/new.html.twig', [
            'formAddModule' => $form,
            //s'il reçoit l'id, il le renvoie et donc on est sur la modification, sinon il renvoie false et on est sur l'ajout
            'edit' => $module->getId()
        ]);

    }

    //supprime un module
    #[Route('module/{id}/delete', name: 'delete_module')]
    public function deleteModule(Module $module, EntityManagerInterface $entityManager){

        $entityManager->remove($module); //prepare
        $entityManager->flush(); //execute

        //redirection
        $this->addFlash('success', 'Module supprimé');
        return $this->redirectToRoute('app_module');
    }
}
