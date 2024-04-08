import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './vendor/bootstrap/dist/css/bootstrap.min.css'


console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


// Aller chercher l'adresse du lieu
let $element = document.getElementById('Adresse');
$element.addEventListener("click", rechercherLatitudeLongitude)
console.log("Ca marche !!!")
console.log($element)
function rechercherLatitudeLongitude(){
    console.log("Ca marche bien ? !!!")
    let $adresse = document.getElementById('lieu_nom').value
    let $ville = document.getElementById('lieu_ville').value
    console.log("Cela donne cela =" + $adresse + $ville)
}


// Affichage sur la carte

var map = L.map('map').setView([51.505, -0.09], 13);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);