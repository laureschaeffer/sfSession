import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';




//--------------------diagramme chart.js pour le programme d'une session

//------constantes
const ctx = document.getElementById('myChart');
const moduleElement = document.querySelectorAll('#programme-module');
const dureeElement = document.querySelectorAll('#programme-duree');


//recupere un tableau de tous les modules (PHP, JS, CSS...)
let modules = [];
moduleElement.forEach(module => {
    modules.push(module.innerText);
});

//recupere un tableau de toutes les duree de chaque module en jours
let durees = [];
dureeElement.forEach(duree => {
    durees.push(duree.innerText);
});

//calcul du nombre de jours total en additionnant chaque duree de programme
// let nbJourTotal = 0;
// durees.forEach(d => {
//     nbJourTotal += Number(d);
// });
//je divise le nb de jours du module par le nb de jour total pour obtenir un tableau contenant chaque coefficient associé à un module
// let coeffs = [];
// durees.forEach(coeff => {
//     coeffs.push(Math.round((coeff / nbJourTotal)*100));
// });

//diagramme

new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: modules,
    datasets: [{
    //   label: 'My First Dataset',
      data: durees,
      // data: [1, 1, 1],
      backgroundColor: [
        'rgb(255, 99, 132)',
        'rgb(54, 162, 235)',
        'rgb(255, 205, 86)',
        '#711C80',
        '#6D8235',
        '#39F6B1'
      ],
      hoverOffset: 4
    }]
  }
  
//   ,
//   options: {
//     scales: {
//       y: {
//         beginAtZero: true
//       }
//     }
//   }
});

