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

var latitude = document.getElementById("latitude").value;
var longitude = document.getElementById("longitude").value;

// Affichage sur la carte

var map = L.map('map').setView([latitude, longitude], 8);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
var marker = L.marker([latitude, longitude]).addTo(map);