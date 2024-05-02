<?php

namespace App\Controller;

use App\Repository\ModuleRepository;
use App\Repository\SessionRepository;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\StagiaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    //renvoie le resultat de la recherche en fonction d'un mot, fonction dans chaque repository (sauf programme et user) ; mettre par défaut word à null pour ne pas avoir d'erreur
    public function index(CategorieRepository $cr, FormationRepository $fr, ModuleRepository $mr, StagiaireRepository $st, SessionRepository $sr, Request $request, $word = null): Response
    {
        //utilise la methode get pour récupérer le mot tapé dans la barre de recherche
        $word = $request->query->get('search');
        return $this->render('search/index.html.twig', [
            'word' => $word,
            'categorie' => $cr->findByWord($word),
            'formation' => $fr->findByWord($word),
            'module' => $mr->findByWord($word),
            'stagiaire' => $st->findByWord($word),
            'session' => $sr->findByWord($word)
        ]);
    }


}