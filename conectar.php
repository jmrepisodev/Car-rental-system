<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname="ud2_reservas";

try {
  //abrir una conexión
  $dsn="mysql:host=$servername;dbname=$dbname";
  $dbh = new PDO($dsn, $username, $password);
  // set the PDO error mode to exception
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 // echo "connected successfully";
} catch(PDOException $e) {
    echo "connection failed: " . $e->getMessage();
}
?>