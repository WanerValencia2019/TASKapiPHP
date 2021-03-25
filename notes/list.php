 <?php   
 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json');
 if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    function tokenError(){
        $response = [
            "message" => "Bad token authentication"
        ];
        http_response_code(401);
      	echo json_encode($response);
       	exit();
    }


    $headers = apache_request_headers();

    if (isset($headers['authorization'])) {
    	require './../database/connection.php';

    	$token = $headers['authorization'];
    	if(!isset($_GET['id'])) {
    		http_response_code(401);
    		$response = [
    			'message'=>'Parameter (id) is required'
    		];
    		echo json_encode($response, JSON_UNESCAPED_UNICODE);
    		exit();
    	}
    	$id_user = $_GET['id'];

    	$sql = "SELECT id, title, content, favorite, completed,created_at FROM notes WHERE id_user = (SELECT id FROM users as u INNER JOIN auth_tokens as auth ON u.id = ? and auth.token=? ) ";


    	$conn = $connection;

    	$consulta = $conn->prepare($sql);
    	$consulta -> bind_param('is',$id_user, $token);


        if(!$consulta->execute()){
            tokenError();
            exit();
        }else{
            $result = $consulta->get_result();
            $response = array();
            $order   = array("\r\n", "\n", "\r");
			$replace = '<br />';
            while ($fila = $result->fetch_assoc()) {
        		array_push($response, [
        			'id'=>$fila['id'],
        			'title'=>$fila['title'],
        			'content'=>str_replace($order,$replace, $fila['content']),
        			'favorite'=>$fila['favorite'],
        			'completed'=>$fila['completed'],
        			'created_at'=>$fila['created_at'],
        		]);
    		}
            if(isset($response)){
            	http_response_code(200);
            	echo json_encode(['data'=>$response],JSON_UNESCAPED_UNICODE);
            	exit();
            }else{
            	tokenError();
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






