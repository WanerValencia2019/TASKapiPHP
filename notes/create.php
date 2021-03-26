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
    	if(!isset($_POST['id_user']) || !isset($_POST['title'])  || !isset($_POST['content']) || !isset($_POST['favorite'])) {
    		http_response_code(406);
    		$response = [
    			'message'=>'Parameter (id_user,title,content, favorite) is required'
    		];
    		echo json_encode($response, JSON_UNESCAPED_UNICODE);
    		exit();
    	}
    	$id_user = $_POST['id_user'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $favorite=$_POST['favorite'];


    	$sql = "INSERT INTO notes(id,title, content, favorite, completed, id_user) VALUES(0, ?, ?, ".$favorite.",0,?)";

        //echo $sql;
    	$conn = $connection;

    	$consulta = $conn->prepare($sql);
    	$consulta -> bind_param('ssi',$title,$content,$id_user);

        if(!$consulta->execute()){
            tokenError();
            exit();
        }else{
            $result = $consulta->affected_rows;
            if($result > 0){
                $response = [
                    'message'=>'Tarea creada correctamente'
                ];
                http_response_code(200);
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();
            }else{
                $response = [
                    'message'=>'No se ha podido crear la tarea'
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






