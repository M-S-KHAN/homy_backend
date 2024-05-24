<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

if (empty($data->admin_id) || empty($data->user_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required information: admin_id or user_id."));
    exit();
}

if (empty($data->username) && empty($data->email) && empty($data->role)) {
    http_response_code(400);
    echo json_encode(array("message" => "No new information provided for update."));
    exit();
}

try {
    // First, verify if the requester is an admin
    $adminCheckQuery = "SELECT role FROM users WHERE id = :admin_id AND role = 'admin'";
    $adminCheckStmt = $pdo->prepare($adminCheckQuery);
    $adminCheckStmt->bindParam(':admin_id', $data->admin_id);
    $adminCheckStmt->execute();

    $isAdmin = $adminCheckStmt->rowCount() > 0;

    // Update user information
    $updateFields = [];
    if (!empty($data->username)) {
        $updateFields[] = "username = :username";
    }
    if (!empty($data->email)) {
        $updateFields[] = "email = :email";
    }
    if (!empty($data->role) && $isAdmin) { // Only allow role updates if admin
        $updateFields[] = "role = :role";
    }

    $updateQuery = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :user_id";
    $updateStmt = $pdo->prepare($updateQuery);

    if (!empty($data->username)) {
        $updateStmt->bindParam(':username', $data->username);
    }
    if (!empty($data->email)) {
        $updateStmt->bindParam(':email', $data->email);
    }
    if (!empty($data->role) && $isAdmin) {
        $updateStmt->bindParam(':role', $data->role);
    }

    $updateStmt->bindParam(':user_id', $data->user_id);

    if ($updateStmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "User information successfully updated."));
    } else {
        throw new Exception("Unable to update user information.");
    }
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}

?>
