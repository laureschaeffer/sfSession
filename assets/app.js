import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

const deleteCat = document.querySelector('.delete-categorie');
deleteCat.addEventListener('click', showAlert);

function showAlert(){
    console.log("test")
    confirm("Attention, supprimer une catégorie supprimera aussi ses modules!")
}