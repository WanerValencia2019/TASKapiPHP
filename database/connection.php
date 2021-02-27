<?php 
    include 'config.php';

    $connection = new mysqli($HOST,$USER,$PASSWORD,$DATABASE,$PORT);
    $connection->set_charset("utf8");
    if ($connection->connect_errno){
        echo "Fallo al conectar a MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        exit();
    }

?>