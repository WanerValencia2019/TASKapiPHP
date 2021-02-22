<?php

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    function tokenError(){
        $response = [
            "error" => "Bad token authentication"
        ];
        http_response_code(401);
        echo json_encode($response);
    }
    function paramsError($msg){
        $response = [
            "error" => $msg
        ];
        http_response_code(401);
        echo json_encode($response);
    }
    $headers = apache_request_headers();
    $_PUT = json_decode(file_get_contents('php://input'),true);

    if(!isset($_PUT['username']) | !isset($_PUT['password']) | !isset($_PUT['password_confirm']) ){
        paramsError('parameters required (username, password, password_confirm)');
        exit();
    }
    if (!($_PUT['password'] == $_PUT['password_confirm'])) {
        paramsError("passwords don't match");
        exit();
    }

    $username = $_PUT['username'];
    $password = $_PUT['password'];
    $password_confirm = $_PUT['password_confirm'];


    if (isset($headers['Authorization'])) {    
        include './../database/connection.php';

        $token = $headers['Authorization'];
        $conn = $connection;
        $sql="DELETE FROM auth_tokens WHERE token = ?";


    }else{
        tokenError();
        exit();
    }

}else{
    http_response_code(405);
}


?>