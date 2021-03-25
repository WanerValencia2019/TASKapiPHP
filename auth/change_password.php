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
    function paramsError($msg){
        $response = [
            "error" => $msg
        ];
        http_response_code(401);
        echo json_encode($response);
    }
    function tokenUsernameVerify($connection, $token, $username){
        $sql_token = 'SELECT pwd, id FROM users AS u INNER JOIN auth_tokens AS auth ON u.id=auth.id_user AND auth.token = ? AND u.username = ?';
        $consulta = $connection->prepare($sql_token);
        $consulta -> bind_param('ss',$token,$username);
        if (!$consulta -> execute()){
            return [];
        } else{
            $result = $consulta -> get_result();
            $data = $result->fetch_assoc();
            if(isset($data)) {
                return [
                    "hash_pwd"=>$data['pwd'],
                    'id' => $data['id']
                ];
            }else{
                return [];
            }
        }

    }
    function passwordHashVerify($password, $hash){
        $veryfied = password_verify($password, $hash);
        return $veryfied;
    }
    function changePassword($connection, $id,$new_password) {
        $sql = "UPDATE users SET pwd=? WHERE id=?";
        $hash = password_hash($new_password,PASSWORD_BCRYPT);
        $consulta=$connection->prepare($sql);
        $consulta -> bind_param('si',$hash,$id);
        if (!$consulta -> execute()){
            paramsError("password cannot be changed");
            exit();
        } else{
            if($consulta->affected_rows == 0){
                paramsError("password cannot be changed");
                exit();
            }else{
                return true;
            }
        }
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

    if (isset($headers['Authorization'])) {    
        include './../database/connection.php';

        $username = $_PUT['username'];
        $old_password = $_PUT['old_password'];
        $password = $_PUT['password'];
        $password_confirm = $_PUT['password_confirm'];
        $token = $headers['Authorization'];
        $conn = $connection;

        $user = tokenUsernameVerify($conn,$token,$username);
        
        if(isset($user['hash_pwd'])){
            $hash = $user['hash_pwd'];
            $id_user = $user['id'];
            if(passwordHashVerify($old_password,$hash)){
                $changed = changePassword($conn, $id_user,$password_confirm);
                if($changed){
                    $response = [
                        "message" => "password changed successfully"
                    ];
                    echo json_encode($response);
                    exit();
                }
            }else{
                paramsError("Old password incorrect");
                exit();
            }
        }else{
            paramsError("Wrong parameters, verify your token or username,old_password");
            exit();
        }
    }else{
        tokenError();
        exit();
    }

}else{
    http_response_code(405);
}


?>