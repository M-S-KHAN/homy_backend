<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->username) &&
    !empty($data->email) &&
    !empty($data->password)
) {
    $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
    $stmt = $pdo->prepare($query);

    $stmt->bindParam(':username', $data->username);
    $stmt->bindParam(':email', $data->email);
    $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
    $stmt->bindParam(':password', $password_hash);

    try {
        if ($stmt->execute()) {
            $user_id = $pdo->lastInsertId(); // Get the ID of the newly created user
            http_response_code(201);
            echo json_encode(
                array(
                    "message" => "User was successfully registered.",
                    "user" => array(
                        "id" => $user_id,
                        "username" => $data->username,
                        "email" => $data->email,
                        "role" => "client",
                    )
                )
            );
        } else {
            throw new Exception("Unable to register the user due to an unknown error.");
        }
    } catch (PDOException $e) {
        http_response_code(503);
        if ($e->errorInfo[1] == 1062) { // Check if the error code is for a duplicate entry
            echo json_encode(array("message" => "User with the given email/username already exists."));
        } else {
            echo json_encode(array("message" => "Server error occurred.", "error" => $e->getMessage()));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to register the user. Data is incomplete."));
}
