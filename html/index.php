<h1>Je te test!</h1>
<h4>Tentative d'affichage des ip du conteneur PHP-FPM et de la VM!</h4>

<?php
// Afficher l'adresse IP du conteneur
echo "L'adresse IP du conteneur est : " . $_SERVER['SERVER_ADDR'] . "<br>";

// Afficher l'adresse IP externe de la VM
$ipExterne = file_get_contents('http://ipecho.net/plain');
echo "L'adresse IP externe de la VM est : " . $ipExterne;
?>


