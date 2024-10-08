<h1>Je te test!</h1>
<h4>Tentative de connexion Mysql depuis PHP...</h4>

<?php
$host = 'mysql';
$user = 'root';
$pass = 'rootpassword';
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die ("La connection a échoué: " . $conn->connect_error);
}
echo "Connexion réussi à MariaDB!";
?
