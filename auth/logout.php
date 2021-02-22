<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    function tokenError(){
        $response = [
            "error" => "Bad token authentication"
        ];
        http_response_code(401);
        echo json_encode($response);
    }

    $headers = apache_request_headers();

    if (isset($headers['Authorization'])) {    
        include './../database/connection.php';

        $token = $headers['Authorization'];
        $conn = $connection;
        $sql="DELETE FROM auth_tokens WHERE token = ?";

        $consulta = $conn -> prepare($sql);

        $consulta -> bind_param('s',$token);

        if(!$consulta->execute()){
            tokenError();
            exit();
        }else{
            if ($consulta->affected_rows == 0){
                tokenError();
                exit();
            }
            $response = [
                "message" => "Logout successfully"
            ];
            echo json_encode($response);
        }

    }else{
        tokenError();
        exit();
    }

}else{
    http_response_code(405);
}


?>