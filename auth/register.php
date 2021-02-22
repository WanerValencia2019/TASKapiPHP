<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    
    include './../database/connection.php';

    $_POST = json_decode(file_get_contents('php://input'),true);
    //echo json_encode($_POST);

    $sql_user = "SELECT username, email, id FROM users WHERE  username = :username AND password = :password";
    $sql_token = "SELECT token FROM auth_tokens WHERE id_user = :id";

    $sql = "INSERT INTO users (id,username,email,pwd) VALUES(0,?,?,?)";

    if (!$sentencia = $connection->prepare($sql)){
        echo "Falló la preparación: (" . $connection->errno . ") " . $connection->error;
    }
    if(addslashes(!isset($_POST['username'])) || !isset($_POST['username']) || !isset($_POST['username'])){
        http_response_code(200);
        $params_required = [ 
            'params_required' => [
                'username' => '',
                'email' => '',
                'password' => '',
            ]
        ];
        echo json_encode(['error' =>  $params_required ],JSON_UNESCAPED_UNICODE);
        exit();
    }   
    $username = addslashes($_POST['username']);
    $email = addslashes($_POST['email']);
    $password = password_hash(addslashes($_POST['password']),PASSWORD_BCRYPT);



    $sentencia->bind_param('sss',$username,$email,$password);


    if (!$sentencia -> execute()){
        if($connection->errno == 1062){
            http_response_code(200);
            echo json_encode(['error' => 'Este usuario ya se encuentra registrado'],JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
    else {
        http_response_code(200);
        echo json_encode(['message' => 'Registrado con éxito'],JSON_UNESCAPED_UNICODE);
        exit();   
    }
}else{
    http_response_code(405);
}
?>