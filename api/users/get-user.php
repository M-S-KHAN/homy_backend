<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Retrieve user_id and admin_id from the query parameters
$requestingUserId = isset($_GET['admin_id']) ? $_GET['admin_id'] : null; // Assume the admin_id is the ID of the requester
$targetUserId = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if ($requestingUserId === null || $targetUserId === null) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing user ID or requester's admin ID."));
    exit();
}

try {
    // First, check if the requester is the user himself or an admin
    $authQuery = "SELECT id, role FROM users WHERE id = :requestingUserId AND (id = :targetUserId OR role = 'admin')";
    $authStmt = $pdo->prepare($authQuery);
    $authStmt->bindParam(':requestingUserId', $requestingUserId);
    $authStmt->bindParam(':targetUserId', $targetUserId);
    $authStmt->execute();

    if ($authStmt->rowCount() == 0) {
        http_response_code(403); // Forbidden
        echo json_encode(array("message" => "Access denied. You do not have permission to view this user's data."));
        exit();
    }

    // Fetch the user data if the requester is authorized
    $userQuery = "SELECT id, username, email, role, created_at FROM users WHERE id = :targetUserId";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->bindParam(':targetUserId', $targetUserId);
    $userStmt->execute();

    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        http_response_code(200);
        echo json_encode(array("user" => $userData));
    } else {
        http_response_code(404); // Not Found
        echo json_encode(array("message" => "User not found."));
    }

} catch (PDOException $e) {
    http_response_code(503); // Service unavailable
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500); // Internal server error
    echo json_encode(array("message" => $e->getMessage()));
}

?>
