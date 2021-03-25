<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    
    require './../database/connection.php';

    $_POST = json_decode(file_get_contents('php://input'),true);
    //echo json_encode($_POST);

    $sql_user = "SELECT first_name,last_name,username, email, id, pwd FROM users WHERE  username = ? OR email = ?";
    function generateToken(){
        //GENERATE RANDOM BINARY DATA
        $token = openssl_random_pseudo_bytes(20); 
        //CONVERT RANDOM BINARY DATA TO HEXADECIMAL
        $token = bin2hex($token);
        return $token;
    }

    function getOrCreateToken($id, $connection){
        $sql_token = "SELECT token FROM auth_tokens WHERE id_user = ?";
        $sql_insert_token = "INSERT INTO auth_tokens(id_user, token) VALUES(?, ?) ";
        $consulta = $connection->prepare($sql_token);
        $consulta -> bind_param('s',$id);
        if (!$consulta -> execute()){

        }else{
            $result = $consulta->get_result();
            $data = $result->fetch_assoc();
            if(!isset($data)){
                $query_token = $connection->prepare($sql_insert_token);
                $token_generated=generateToken();
                $query_token -> bind_param('ss',$id,$token_generated);
                if (!$query_token -> execute()){}
                else{
                    return $token_generated;
                }
            }else{
                return $data['token'];
            }
        }
    }

    if (!$sentencia = $connection->prepare($sql_user)){
        echo "Falló la preparación: (" . $connection->errno . ") " . $connection->error;
    }
    if(addslashes((!isset($_POST['username'])) || !isset($_POST['username'])) || !isset($_POST['password'])){
        http_response_code(401);
        $response = [ 
            'message' => 'params requireds (username and password)'
        ];
        echo json_encode($response,JSON_UNESCAPED_UNICODE);
        exit();
    }   
    if(!isset($_POST['username'])){
        $username = '';
    }else {
        $username = addslashes($_POST['username']);
    }
    if(!isset($_POST['email'])) {
        $email = '';
    } else{
        $email = addslashes($_POST['email']);
    }

    $password = addslashes($_POST['password']);

    $sentencia->bind_param('ss',$username,$email);


    if (!$sentencia -> execute()){
        echo "error";
    }
    else {
         $result = $sentencia ->get_result();
         $data = $result ->fetch_assoc();
         if (isset($data)) {
            $password_verified = password_verify($password, $data['pwd']);
         }else {
            $password_verified = false;
         }

         if($password_verified==true){
             $user_token= getOrCreateToken($data['id'],$connection);
             $response = [
                 'data' => [
                 	 'id'=>$data['id'],
                     'first_name'=>$data['first_name'],
                     'last_name'=>$data['last_name'],
                     'username'=>$data['username'],
                     'email' => $data['email'],
                     'token' => $user_token
                 ]
             ];
             echo json_encode($response, JSON_UNESCAPED_UNICODE);
         }else{
             $response = [
                 "message" => "Credentials  incorrects"
             ];
             http_response_code(401);
             echo json_encode($response, JSON_UNESCAPED_UNICODE);
             exit();
         }
    }

}else{
    http_response_code(405);
}


?>