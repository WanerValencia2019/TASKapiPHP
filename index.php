<?php
    //GENERATE RANDOM BINARY DATA
    $token = openssl_random_pseudo_bytes(20); 
    //CONVERT RANDOM BINARY DATA TO HEXADECIMAL
    $token = bin2hex($token);

    echo $token;
    echo "\n";
    echo "TAMAÑO ".strlen($token);
?>
