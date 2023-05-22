<?php
    include_once '../../config/database.php';

    header("Access-Control-Allow-Origin: * ");
    header("Access-Control-Allow-Methods: POST");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $firstName = '';
    $lastName = '';
    $email = '';
    $password = '';
    $role = '';
    $dbConnection = null;

    $databaseService = new DatabaseService();
    $dbConnection = $databaseService->getConnection();

    $dataInput = json_decode(file_get_contents("php://input"));

    $firstName = $dataInput->first_name;
    $lastName = $dataInput->last_name;
    $email = $dataInput->email;
    $role = $dataInput->role;
    $password = $dataInput->password;
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $table_name = 'users';
    $query = "INSERT INTO " . $table_name . "
                SET first_name = :firstname,
                    last_name = :lastname,
                    email = :email,
                    password = :password,
                    role = :role";

    $stmt = $dbConnection->prepare($query);
    $stmt->bindParam(':firstname', $firstName);
    $stmt->bindParam(':lastname', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':password', $password_hash);

    if($stmt->execute()){
        http_response_code(200);
        echo json_encode(array("message" => "User was successfully registered."));
    }
    else{
        http_response_code(400);
        echo json_encode(array("message" => "Unable to register the user."));
    }
?>