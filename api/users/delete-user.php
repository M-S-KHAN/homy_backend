<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

if (empty($data->user_id) || empty($data->admin_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required information."));
    exit();
}

// Check if the requester is an admin or the user themselves
$authQuery = "SELECT role FROM users WHERE id = :admin_id AND (role = 'admin' OR id = :user_id)";
$authStmt = $pdo->prepare($authQuery);
$authStmt->bindParam(':admin_id', $data->admin_id);
$authStmt->bindParam(':user_id', $data->user_id);
$authStmt->execute();

if ($authStmt->rowCount() == 0) {
    http_response_code(403);
    echo json_encode(array("message" => "Access denied. Only admins or the user themselves can delete accounts."));
    exit();
}

// Proceed with deleting the user
$deleteQuery = "DELETE FROM users WHERE id = :user_id";
$deleteStmt = $pdo->prepare($deleteQuery);
$deleteStmt->bindParam(':user_id', $data->user_id);

try {
    if ($deleteStmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "User successfully deleted."));
    } else {
        throw new Exception("Unable to delete user.");
    }
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Failed to delete user due to a database error.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}

?>
