import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

// const deleteCat = document.querySelector('.delete-categorie');
// deleteCat.addEventListener('click', showAlert);

// function showAlert(){
//     confirm("Attention, supprimer une catégorie supprimera aussi ses modules!")
// }

//diagramme circulaire
// pour l'exemple, il faut faire 100/ nb de modules
// -1e couleur : 0%, nbModule %
// -2e couleur: nbModule%, nbModule*2
// -3e couleur: nbModule*2, nbModule*3
//etc

const diagramme = document.querySelector('#diagramme');
const creux = document.querySelector('#creux');

let nbModule = document.querySelector("#nbModule")