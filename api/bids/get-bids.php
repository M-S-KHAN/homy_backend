<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if ($userId === null) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing or invalid user ID."));
    exit();
}

try {
    // Determine the user's role
    $roleQuery = "SELECT role FROM users WHERE id = :user_id";
    $roleStmt = $pdo->prepare($roleQuery);
    $roleStmt->bindParam(':user_id', $userId);
    $roleStmt->execute();
    $userRole = $roleStmt->fetch(PDO::FETCH_ASSOC)['role'];

    if (!$userRole) {
        http_response_code(404);
        echo json_encode(array("message" => "User role not found."));
        exit();
    }

    // Build the query based on the user's role
    if ($userRole === 'admin') {
        $query = "SELECT b.*, u.id as user_id, u.username, u.email, p.id as property_id, p.title, p.description
                  FROM bids b
                  JOIN users u ON u.id = b.user_id
                  JOIN properties p ON p.id = b.property_id";
    } elseif ($userRole === 'client') {
        $query = "SELECT b.*, u.id as user_id, u.username, u.email, p.id as property_id, p.title, p.description
                  FROM bids b
                  JOIN users u ON u.id = b.user_id
                  JOIN properties p ON p.id = b.property_id
                  WHERE b.user_id = :user_id";
    } elseif ($userRole === 'agent') {
        $query = "SELECT b.*, u.id as user_id, u.username, u.email, p.id as property_id, p.title, p.description
                  FROM bids b
                  JOIN users u ON u.id = b.user_id
                  JOIN properties p ON p.id = b.property_id
                  WHERE p.owner_id = :user_id";
    } else {
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. Invalid user role for this operation."));
        exit();
    }

    $stmt = $pdo->prepare($query);
    if ($userRole !== 'admin') {
        $stmt->bindParam(':user_id', $userId);
    }
    $stmt->execute();
    $bids = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($bids)) {
        http_response_code(200);
        // Return an empty array if no bids are found
        echo json_encode(array("bids" => []));
    } else {
        foreach ($bids as &$bid) {
            $bid['by'] = [
                'user_id' => $bid['user_id'],
                'username' => $bid['username'],
                'email' => $bid['email']
            ];
            $bid['property'] = [
                'property_id' => $bid['property_id'],
                'title' => $bid['title'],
                'description' => $bid['description']
            ];
            unset($bid['user_id'], $bid['username'], $bid['email'], $bid['property_id'], $bid['title'], $bid['description']); // Clean up
        }
        http_response_code(200);
        echo json_encode(array("bids" => $bids));
    }

} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}
