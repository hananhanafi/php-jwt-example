<?php
    include_once '../../config/database.php';
    require "../../vendor/autoload.php";
    use \Firebase\JWT\JWT;

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $email = '';
    $password = '';

    $databaseService = new DatabaseService();
    $dbConnection = $databaseService->getConnection();


    $dataInput = json_decode(file_get_contents("php://input"));

    $email = $dataInput->email;
    $password = $dataInput->password;

    $table_name = 'users';
    $query = "SELECT * FROM " . $table_name . " WHERE email = ? LIMIT 0,1";

    $stmt = $dbConnection->prepare( $query );
    $stmt->bindParam(1, $email);
    $stmt->execute();
    $num = $stmt->rowCount();

    if($num > 0){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row['id'];
        $firstname = $row['first_name'];
        $lastname = $row['last_name'];
        $role = $row['role'];
        $hashedPassword = $row['password'];

        if(password_verify($password, $hashedPassword)){
            $secret_key = "YOUR_SECRET_KEY";
            $issuer_claim = "THE_ISSUER"; // this can be the servername
            $audience_claim = "THE_AUDIENCE"; // this can be the client name
            $issuedat_claim = time(); // issued at
            $expire_claim = $issuedat_claim + 600; // expire time in seconds (stop time when the token is expired)
            $token = array(
                "iss" => $issuer_claim,
                "aud" => $audience_claim,
                "iat" => $issuedat_claim,
                "exp" => $expire_claim,
                "data" => array(
                    "id" => $id,
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "email" => $email,
                    "role" => $role
                )
            );

            http_response_code(200);
            $jwt = JWT::encode($token, $secret_key, 'HS256');
            echo json_encode(
                array(
                    "message" => "Successful login.",
                    "jwt" => $jwt,
                    "email" => $email,
                    "expireAt" => $expire_claim
                )
            );
        }
        else{
            http_response_code(401);
            echo json_encode(
                array(
                    "error" => [
                        "code" => 401,
                        "message" => "Login failed."
                    ]
                )
            );
        }
    }
?>