<?php
include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    if (!validate_email($data->email)) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid email format."));
        exit;
    }

    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($query);

    // Binding parameters with explicit data types
    $stmt->bindParam(':email', $data->email, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = $row['id'];
            $username = $row['username'];
            $password = $row['password'];
            $image = $row['profile_image_url'];
            $role = $row['role'];

            if (password_verify($data->password, $password)) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Successful login.",
                    "user" => array(
                        "id" => $id,
                        "username" => $username,
                        "email" => $data->email,
                        "profile_image_url" => $image,
                        "role" => $role
                    )
                ));
                
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Invalid Username or Password."));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No user found."));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Internal Server Error", "error" => $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}
