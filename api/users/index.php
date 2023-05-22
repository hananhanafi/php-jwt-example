<?php
    include_once '../../config/database.php';
    require "../../vendor/autoload.php";
    use \Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $secret_key = "YOUR_SECRET_KEY";
    $jwt = null;
    $databaseService = new DatabaseService();
    $dbConnection = $databaseService->getConnection();

    // Get JWT token
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $arr = explode(" ", $authHeader);
    $jwt = $arr[1];

    if($jwt){

        try {

            // Decoded JWT
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
            $data = $decoded->data;
            $role = $data->role;

            // Check role for authorization
            if($role == 'admin') {
                $table_name = 'users';
                $query = "SELECT first_name, last_name, email, role FROM " . $table_name;
                $stmt = $dbConnection->prepare( $query );
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Access granted",
                    "data" => $result
                ));
            }
            else {
                http_response_code(401);
                echo json_encode(array(
                    "error" => [
                        "code" => 401,
                        "message" => "Access Denied",
                    ]
                ));
            }


        }catch (Exception $e){
            http_response_code(401);
            echo json_encode(array(
                "error" => [
                    "code" => 401,
                    "message" => $e->getMessage()
                ]
            ));
        }

    }
?>