<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

// Check if user ID and admin verification data are present
if (empty($data->admin_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing admin credentials."));
    exit();
}

// Verify that the provided admin_id belongs to an admin
$adminQuery = "SELECT role FROM users WHERE id = :admin_id AND role = 'admin'";
$adminStmt = $pdo->prepare($adminQuery);
$adminStmt->bindParam(':admin_id', $data->admin_id);
$adminStmt->execute();

if ($adminStmt->rowCount() != 1) {
    http_response_code(403);
    echo json_encode(array("message" => "Access denied. Only admins can add users."));
    exit();
}

// Validate the received data
if (empty($data->username) || empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required fields: username, email, or password."));
    exit();
}

// Optional role with default value
$role = !empty($data->role) ? $data->role : 'client';

// Prepare a query to insert the new user into the database
$query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
$stmt = $pdo->prepare($query);

// Hash the password for security
$password_hash = password_hash($data->password, PASSWORD_BCRYPT);

// Bind parameters to the prepared statement
$stmt->bindParam(':username', $data->username);
$stmt->bindParam(':email', $data->email);
$stmt->bindParam(':password', $password_hash);
$stmt->bindParam(':role', $role);

try {
    // Execute the statement and check if the insert was successful
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "User successfully created."));
    } else {
        throw new Exception("Unable to create user.");
    }
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) { // Duplicate entry
        http_response_code(409);
        echo json_encode(array("message" => "User already exists with the provided email or username."));
    } else {
        http_response_code(503); // Service unavailable
        echo json_encode(array("message" => "Failed to create user due to a database error.", "error" => $e->getMessage()));
    }
} catch (Exception $e) {
    http_response_code(500); // Internal server error
    echo json_encode(array("message" => $e->getMessage()));
}

?>
