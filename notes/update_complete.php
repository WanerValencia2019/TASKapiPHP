 <?php   
 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json');
 if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    function tokenError(){
        $response = [
            "message" => "Bad token authentication"
        ];
        http_response_code(401);
      	echo json_encode($response);
       	exit();
    }

    $_POST = json_decode(file_get_contents('php://input'),true);
    $headers = apache_request_headers();

    if (isset($headers['authorization'])) {
    	require './../database/connection.php';

    	$token = $headers['authorization'];
    	if(!isset($_POST['id_user']) || !isset($_POST['id_tarea'])  || !isset($_POST['complete'])) {
    		http_response_code(401);
    		$response = [
    			'message'=>'Parameter (id_user,id_tarea, complete) is required'
    		];
    		echo json_encode($response, JSON_UNESCAPED_UNICODE);
    		exit();
    	}
    	$id_user = $_POST['id_user'];
        $id_tarea = $_POST['id_tarea'];
        $complete_updated=$_POST['complete'];


    	$sql = "UPDATE notes SET completed = ".$complete_updated."  WHERE id = ? and id_user = (SELECT id FROM users as u INNER JOIN auth_tokens as auth ON u.id = ? and auth.token=? ) ";


    	$conn = $connection;

    	$consulta = $conn->prepare($sql);
    	$consulta -> bind_param('iis',$id_tarea,$id_user, $token);

        if(!$consulta->execute()){
            tokenError();
            exit();
        }else{
            $result = $consulta->affected_rows;
            if($result > 0){
                $response = [
                    'message'=>'Actualizado correctamente'
                ];
                http_response_code(200);
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();
            }else{
                $response = [
                    'message'=>'No se ha podido actualizar la tarea'
                ];
                http_response_code(406);
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();
            }
        }

    }else{
    	tokenError();
    }

}else{
	http_response_code(405);
	echo "error request method";
	exit();
}

 ?>






