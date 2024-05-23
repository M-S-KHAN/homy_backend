<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Get role and requesting user ID from the query parameters
$requestedRole = isset($_GET['role']) ? $_GET['role'] : null;
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if ($userId === null) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing user ID."));
    exit();
}

try {
    // Check if the requesting user is an admin
    $userCheckQuery = "SELECT role FROM users WHERE id = :user_id AND role = 'admin'";
    $userCheckStmt = $pdo->prepare($userCheckQuery);
    $userCheckStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $userCheckStmt->execute();

    if ($userCheckStmt->rowCount() == 0) {
        // The requesting user is not an admin
        http_response_code(403); // Forbidden
        echo json_encode(array("message" => "Access denied. Only admins can view user data."));
        exit();
    }

    // Fetch users based on the role if provided
    $query = "SELECT * FROM users" . ($requestedRole ? " WHERE role = :role" : "");
    $stmt = $pdo->prepare($query);

    if ($requestedRole) {
        $stmt->bindParam(':role', $requestedRole, PDO::PARAM_STR);
    }

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(array("users" => $users));

} catch (PDOException $e) {
    http_response_code(503); // Service unavailable
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500); // Internal server error
    echo json_encode(array("message" => $e->getMessage()));
}

?>
